<?php

namespace App\Models;

use App\Enums\EventStatus;
use App\Enums\OrderStatus;
use App\Enums\PayoutType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Event extends Model
{
    use HasFactory, HasUuids, SoftDeletes;
    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = ['image_path'];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'organizer_id',
        'category_id',
        'name',
        'slug',
        'description',
        'banner_image',
        'venue_name',
        'address',
        'city',
        'event_date',
        'start_time',
        'end_time',
        'status',
        'is_featured',
        'manual_settlement_required',
        'rejection_message',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'is_featured' => 'boolean',
            'manual_settlement_required' => 'boolean',
            'status' => EventStatus::class,
        ];
    }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(EventCategory::class, 'category_id');
    }

    public function ticketCategories(): HasMany
    {
        return $this->hasMany(TicketCategory::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function merchandiseItems(): HasMany
    {
        return $this->hasMany(MerchandiseItem::class);
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(Payout::class);
    }

    public function latestPayout(): HasOne
    {
        return $this->hasOne(Payout::class)->latestOfMany();
    }

    public function advancePayouts(): HasMany
    {
        return $this->hasMany(Payout::class)->where('payout_type', PayoutType::Advance);
    }

    public function finalPayout(): HasOne
    {
        return $this->hasOne(Payout::class)->where('payout_type', PayoutType::Final);
    }

    /**
     * Backward-compatible alias to finalPayout.
     */
    public function payout(): HasOne
    {
        return $this->finalPayout();
    }

    public function cancellationRequests(): HasMany
    {
        return $this->hasMany(CancellationRequest::class);
    }

    public function latestCancellationRequest(): HasOne
    {
        return $this->hasOne(CancellationRequest::class)->latestOfMany();
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * Scope to only published events.
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', EventStatus::Published);
    }

    /**
     * Scope to published events that haven't passed yet.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', EventStatus::Published)
            ->where('event_date', '>=', now()->toDateString());
    }

    /**
     * Scope to featured events.
     */
    public function scopeFeatured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    /**
     * Scope to filter by city.
     */
    public function scopeByCity(Builder $query, string $city): void
    {
        $query->where('city', $city);
    }

    /**
     * Scope to filter by category.
     */
    public function scopeByCategory(Builder $query, string $categoryId): void
    {
        $query->where('category_id', $categoryId);
    }

    /**
     * Scope to upcoming events, ordered by nearest date.
     */
    public function scopeUpcoming(Builder $query): void
    {
        $query->where('event_date', '>=', now()->toDateString())
            ->orderBy('event_date');
    }

    /**
     * Scope to events belonging to a specific organizer.
     */
    public function scopeForOrganizer(Builder $query, string $organizerId): void
    {
        $query->where('organizer_id', $organizerId);
    }

    // ──────────────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────────────

    /**
     * Get the virtual image path (maps to banner_image).
     */
    public function getImagePathAttribute(): ?string
    {
        return $this->banner_image;
    }
    
    /**
     * Get combined location (venue_name, city).
     */
    public function getLocationAttribute(): string
    {
        return "{$this->venue_name}, {$this->city}";
    }

    /**
     * Combine event_date + start_time into a single Carbon instance.
     */
    public function getStartDateTimeAttribute(): Carbon
    {
        return $this->event_date->copy()->setTimeFromTimeString($this->start_time);
    }

    /**
     * Get total ticket quota across all active categories.
     */
    public function getTotalQuotaAttribute(): int
    {
        return (int) $this->ticketCategories()->where('is_active', true)->sum('quota');
    }

    /**
     * Get total sold tickets across all active categories.
     */
    public function getTotalSoldAttribute(): int
    {
        return (int) $this->ticketCategories()->where('is_active', true)->sum('sold_count');
    }

    /**
     * Get remaining ticket quota across all active categories.
     */
    public function getRemainingQuotaAttribute(): int
    {
        return $this->total_quota - $this->total_sold;
    }

    /**
     * Check if remaining quota falls below 10% of total (PRD "Nearly Sold Out" badge).
     */
    public function getIsNearlySoldOutAttribute(): bool
    {
        $totalQuota = $this->total_quota;

        if ($totalQuota <= 0) {
            return false;
        }

        return ($this->remaining_quota / $totalQuota) < 0.10;
    }

    /**
     * Get the lowest ticket price.
     */
    public function getLowestPriceAttribute(): ?int
    {
        return $this->ticketCategories->where('is_active', true)->min('price');
    }

    /**
     * Get the highest ticket price.
     */
    public function getHighestPriceAttribute(): ?int
    {
        return $this->ticketCategories->where('is_active', true)->max('price');
    }

    // ──────────────────────────────────────────────
    // Business Helpers
    // ──────────────────────────────────────────────

    /**
     * Check if this event has any active ticket or merchandise transactions (paid or pending orders).
     */
    public function hasSales(): bool
    {
        return $this->ticketCategories()->where('sold_count', '>', 0)->exists()
            || $this->orders()->whereIn('status', [OrderStatus::Paid, OrderStatus::Pending])->exists();
    }

    /**
     * Check if the event's start datetime has passed.
     */
    public function isStarted(): bool
    {
        return now()->gte($this->start_date_time);
    }

    /**
     * Check if this event can be cancelled (respects status and hard time cutoff).
     */
    public function canBeCancelled(): bool
    {
        if ($this->isStarted()) {
            return false;
        }

        return in_array($this->status, [
            EventStatus::Draft,
            EventStatus::AwaitingApproval,
            EventStatus::Published,
        ]);
    }
}
