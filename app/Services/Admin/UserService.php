<?php

namespace App\Services\Admin;

use App\Enums\OrganizerStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserService
{
    /**
     * Get a paginated list of users with filters, search, and sorting.
     */
    public function getPaginatedUsers(array $filters): LengthAwarePaginator
    {
        $role = $filters['role'] ?? null;
        $status = $filters['status'] ?? null;
        $search = $filters['search'] ?? null;
        $sort = $filters['sort'] ?? 'created_at';
        $order = $filters['order'] ?? 'desc';
        $isDeletedFilter = $status === 'deleted';

        return User::query()
            ->when($isDeletedFilter, function ($query) {
                return $query->onlyTrashed();
            })
            ->when(! $isDeletedFilter && $role && $role !== 'all', function ($query) use ($role) {
                return $query->where('role', $role);
            })
            ->when(! $isDeletedFilter && $status === 'pending', function ($query) {
                return $query->where('role', UserRole::Organizer)
                    ->whereHas('organizerProfile', function ($q) {
                        $q->where('status', OrganizerStatus::Pending);
                    });
            })
            ->when(! $isDeletedFilter && $search, function ($query) use ($search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(in_array($sort, ['name', 'email', 'role', 'created_at']), function ($query) use ($sort, $order) {
                return $query->orderBy($sort, $order === 'asc' ? 'asc' : 'desc');
            }, function ($query) {
                return $query->latest();
            })
            ->paginate(10)
            ->withQueryString();
    }

    /**
     * Create a new user and its associated profile if necessary.
     */
    public function createUser(array $data): User
    {
        $user = User::create([
            'id' => (string) Str::uuid(),
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => UserRole::from($data['role']),
            'is_active' => $data['is_active'],
        ]);

        if ($user->role === UserRole::Organizer) {
            $this->syncOrganizerProfile($user, $data);
        }

        return $user;
    }

    /**
     * Update an existing user and its associated profile.
     */
    public function updateUser(User $user, array $data): User
    {
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => UserRole::from($data['role']),
            'is_active' => $data['is_active'],
        ];

        if (! empty($data['password'])) {
            $userData['password'] = Hash::make($data['password']);
        }

        $user->update($userData);

        if ($user->role === UserRole::Organizer) {
            $this->syncOrganizerProfile($user, $data);
        }

        return $user;
    }

    /**
     * Sync organizer profile data.
     */
    protected function syncOrganizerProfile(User $user, array $data): void
    {
        $profile = $user->organizerProfile;

        $profileData = [
            'organization_name' => $data['organization_name'] ?? null,
            'phone' => $data['phone'] ?? null,
            'bank_name' => $data['bank_name'] ?? null,
            'bank_account_number' => $data['bank_account_number'] ?? null,
            'bank_account_name' => $data['bank_account_name'] ?? null,
            'organization_address' => $data['organization_address'] ?? null,
            'official_contact' => $data['official_contact'] ?? null,
        ];

        if (isset($data['legality_document']) && $data['legality_document'] instanceof UploadedFile) {
            $profileData['legality_document'] = $data['legality_document']->store('legality_documents', 'public');
        }

        if (! $profile) {
            $profileData['status'] = OrganizerStatus::Pending;
        }

        $user->organizerProfile()->updateOrCreate(
            ['user_id' => $user->id],
            $profileData
        );
    }

    /**
     * Delete a user with safety checks.
     *
     * @throws \Exception
     */
    public function deleteUser(User $user): bool
    {
        if ($user->id === auth()->id()) {
            throw new \Exception('You cannot delete your own account.');
        }

        if ($user->role === UserRole::Organizer) {
            if ($user->hasPublishedEvents()) {
                throw new \Exception('Cannot delete organizer with active published events. Please cancel or complete events first.');
            }

            if ($user->hasPendingPayouts()) {
                throw new \Exception('Cannot delete organizer with pending payouts. Please ensure all financial settlements are completed first.');
            }

            if ($user->events()->whereHas('orders')->exists()) {
                throw new \Exception('Cannot archive organizer with transaction history (Orders). Suspend the account instead to preserve financial audit access.');
            }
        }

        return $user->delete();
    }
}
