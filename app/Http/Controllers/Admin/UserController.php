<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrganizerStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Mail\OrganizerApprovalMail;
use App\Mail\OrganizerRejectionMail;
use App\Models\User;
use App\Services\Admin\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    /**
     * Display a listing of the users.
     */
    public function index(Request $request): View
    {
        $users = $this->userService->getPaginatedUsers($request->all());

        return view('admin.users.index', compact('users'));
    }

    /**
     * Display the specified user intelligence.
     */
    public function show(User $user): View
    {
        $user->load(['organizerProfile', 'events.category', 'orders']);

        return view('admin.users.show', compact('user'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->userService->createUser($request->validated());

        return back()->with('status', 'User berhasil ditambahkan');
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->userService->updateUser($user, $request->validated());

        return back()->with('status', 'User berhasil diperbarui');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        try {
            $this->userService->deleteUser($user);

            return back()->with('status', 'User berhasil diarsipkan');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Toggle the active status of the specified user.
     */
    public function toggleStatus(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot suspend your own account.']);
        }

        $user->update(['is_active' => ! $user->is_active]);

        $message = $user->is_active ? 'User access has been restored.' : 'User access has been restricted.';

        return back()->with('status', $message);
    }

    /**
     * Approve the organizer registration.
     */
    public function approveOrganizer(User $user): RedirectResponse
    {
        if ($user->role !== UserRole::Organizer) {
            return back()->withErrors(['error' => 'User is not an Event Organizer.']);
        }

        $user->update(['is_active' => true]);
        $user->organizerProfile->update([
            'status' => OrganizerStatus::Approved,
            'rejection_reason' => null,
        ]);

        Mail::to($user->email)->send(new OrganizerApprovalMail($user));

        return back()->with('status', 'Penyelenggara berhasil disetujui dan diaktifkan.');
    }

    /**
     * Reject the organizer registration.
     */
    public function rejectOrganizer(Request $request, User $user): RedirectResponse
    {
        if ($user->role !== UserRole::Organizer) {
            return back()->withErrors(['error' => 'User is not an Event Organizer.']);
        }

        $request->validate([
            'rejection_reason' => 'required|string|min:10',
        ]);

        $user->organizerProfile->update([
            'status' => OrganizerStatus::Rejected,
            'rejection_reason' => $request->rejection_reason,
        ]);

        Mail::to($user->email)->send(new OrganizerRejectionMail($user, $request->rejection_reason));

        return back()->with('status', 'Pendaftaran penyelenggara berhasil ditolak.');
    }
}
