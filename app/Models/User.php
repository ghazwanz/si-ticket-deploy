<?php

namespace App\Models;

use App\Enums\OrganizerStatus;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUuids, Notifiable, SoftDeletes;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the primary key.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'profile_photo_path',
        'pending_email',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    /**
     * Get the organizer profile associated with the user.
     */
    public function organizerProfile(): HasOne
    {
        return $this->hasOne(OrganizerProfile::class);
    }

    /**
     * Get orders placed by this user.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get events owned by this organizer.
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'organizer_id');
    }

    /**
     * Get payouts reviewed by this admin.
     */
    public function payoutsReviewed(): HasMany
    {
        return $this->hasMany(Payout::class, 'reviewed_by');
    }

    /**
     * Get payouts disbursed by this admin.
     */
    public function payoutsDisbursed(): HasMany
    {
        return $this->hasMany(Payout::class, 'disbursed_by');
    }

    /**
     * Get cancellation requests submitted by this organizer.
     */
    public function cancellationRequests(): HasMany
    {
        return $this->hasMany(CancellationRequest::class, 'requested_by');
    }

    /**
     * Get cancellation requests reviewed by this admin.
     */
    public function cancellationReviews(): HasMany
    {
        return $this->hasMany(CancellationRequest::class, 'reviewed_by');
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * Scope to only active users.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Scope to filter by role.
     */
    public function scopeByRole(Builder $query, UserRole $role): void
    {
        $query->where('role', $role);
    }

    /**
     * Scope to only organizers.
     */
    public function scopeOrganizers(Builder $query): void
    {
        $query->where('role', UserRole::Organizer);
    }

    /**
     * Scope to only admins.
     */
    public function scopeAdmins(Builder $query): void
    {
        $query->where('role', UserRole::Admin);
    }

    // ──────────────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────────────

    /**
     * Get the URL to the user's profile photo.
     */
    public function getAvatarUrlAttribute(): string
    {
        return $this->profile_photo_path
            ? Storage::url($this->profile_photo_path)
            : 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&color=7C3AED&background=F3E8FF';
    }

    /**
     * Get the user's initials for avatar display.
     */
    public function getInitialsAttribute(): string
    {
        $words = explode(' ', trim($this->name));

        if (count($words) >= 2) {
            return mb_strtoupper(mb_substr($words[0], 0, 1).mb_substr(end($words), 0, 1));
        }

        return mb_strtoupper(mb_substr($this->name, 0, 2));
    }

    // ──────────────────────────────────────────────
    // Business Helpers
    // ──────────────────────────────────────────────

    /**
     * Check if the organizer has any events currently published.
     */
    public function hasPublishedEvents(): bool
    {
        return $this->events()->where('status', 'published')->exists();
    }

    /**
     * Check if the organizer has completed events that are awaiting payout settlement.
     */
    public function hasPendingPayouts(): bool
    {
        return $this->events()->where('status', 'completed')
            ->where(function ($query) {
                $query->whereDoesntHave('payout')
                    ->orWhereHas('payout', function ($q) {
                        $q->where('status', '!=', 'completed');
                    });
            })->exists();
    }

    /**
     * Check if the organizer has active paid orders that need fulfillment or settlement.
     */
    public function hasActivePaidOrders(): bool
    {
        if ($this->role !== UserRole::Organizer) {
            return false;
        }

        return Order::whereHas('event', function ($query) {
            $query->where('organizer_id', $this->id)
                ->whereIn('status', ['published', 'pending']);
        })->whereNotNull('paid_at')->exists();
    }

    /**
     * Check if the user is an approved organizer.
     */
    public function isApprovedOrganizer(): bool
    {
        if ($this->role !== UserRole::Organizer) {
            return false;
        }

        return $this->organizerProfile?->status === OrganizerStatus::Approved;
    }
}
