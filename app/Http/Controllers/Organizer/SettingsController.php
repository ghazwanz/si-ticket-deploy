<?php

namespace App\Http\Controllers\Organizer;

use App\Enums\OrganizerStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Organizer\UpdatePasswordRequest;
use App\Http\Requests\Organizer\UpdateSettingsRequest;
use App\Notifications\VerifyPendingEmail;
use App\Services\Organizer\PayoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Display the Event Organizer settings view.
     */
    public function index(Request $request): View
    {
        $user = $request->user()->load('organizerProfile');

        return view('organizer.settings', compact('user'));
    }

    /**
     * Update the Event Organizer profile details.
     */
    public function updateProfile(UpdateSettingsRequest $request): RedirectResponse
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

        $profileData = [
            'organization_name' => $request->organization_name,
            'phone' => $request->phone,
            'organization_address' => $request->organization_address,
            'official_contact' => $request->official_contact,
            'bank_name' => $request->bank_name,
            'bank_account_number' => $request->bank_account_number,
            'bank_account_name' => $request->bank_account_name,
        ];

        if ($request->hasFile('legality_document')) {
            $profileData['legality_document'] = $request->file('legality_document')->store('legality_documents', 'public');
        }

        $currentProfile = $user->organizerProfile;
        if ($currentProfile && $currentProfile->status === OrganizerStatus::Rejected) {
            $profileData['status'] = OrganizerStatus::Pending;
            $profileData['rejection_reason'] = null;
        }

        $user->organizerProfile()->updateOrCreate(
            ['user_id' => $user->id],
            $profileData
        );

        $user->unsetRelation('organizerProfile');

        app(PayoutService::class)->autoResnapshotBankDetails($user);

        if ($emailChanged) {
            return back()->with('status', 'verification-link-sent');
        }

        return back()->with('status', 'profile-updated');
    }

    /**
     * Update the Event Organizer password.
     */
    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('status', 'password-updated');
    }
}
