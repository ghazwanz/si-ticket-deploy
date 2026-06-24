<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Order;
use App\Models\User;
use App\Notifications\VerifyPendingEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile dashboard.
     */
    public function index(): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user->role === UserRole::Admin) {
            return redirect()->route('admin.settings.index');
        }

        if ($user->role === UserRole::Organizer) {
            return redirect()->route('organizer.settings');
        }

        // For Regular Users
        $recentOrders = Order::with('event')->where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        return view('profile.index', [
            'user' => $user,
            'recentOrders' => $recentOrders,
        ]);
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->name = $request->name;

        $emailChanged = false;
        if ($request->email !== $user->email) {
            $user->pending_email = $request->email;
            Notification::route('mail', $request->email)->notify(new VerifyPendingEmail($request->email, $user));
            $emailChanged = true;
        }

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $user->profile_photo_path = $request->file('profile_photo')->store('profile-photos', 'public');
        } elseif ($request->remove_photo === '1' || $request->remove_photo === 'true') {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $user->profile_photo_path = null;
        }

        $user->save();

        if ($emailChanged) {
            return back()->with('status', 'verification-link-sent');
        }

        return back()->with('status', 'profile-updated');
    }

    /**
     * Verify the user's pending email address change.
     */
    public function verifyPendingEmail(Request $request, string $id, string $hash): RedirectResponse
    {
        $user = User::findOrFail($id);

        if ($user->id !== $request->user()->id) {
            abort(403);
        }

        if (! hash_equals((string) $hash, sha1((string) $user->pending_email))) {
            return redirect()->route('profile.index')->with('error', 'Tautan verifikasi email tidak valid atau sudah kadaluwarsa.');
        }

        $user->email = $user->pending_email;
        $user->pending_email = null;
        $user->email_verified_at = now();
        $user->save();

        return redirect()->route('profile.index')->with('success', 'Alamat email Anda berhasil diperbarui dan diverifikasi.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
