<?php

namespace App\Models;

use App\Enums\OrganizerStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizerProfile extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'organization_name',
        'phone',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'organization_address',
        'official_contact',
        'legality_document',
        'status',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrganizerStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the organizer has complete bank details for payout.
     */
    public function hasBankDetails(): bool
    {
        return ! empty($this->bank_name)
            && ! empty($this->bank_account_number)
            && ! empty($this->bank_account_name);
    }
}
