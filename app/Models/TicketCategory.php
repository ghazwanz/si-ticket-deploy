<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketCategory extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'event_id',
        'name',
        'description',
        'price',
        'quota',
        'max_per_user',
        'sold_count',
        'sale_start_at',
        'sale_end_at',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'quota' => 'integer',
            'sold_count' => 'integer',
            'max_per_user' => 'integer',
            'sale_start_at' => 'datetime',
            'sale_end_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function orderTickets(): HasMany
    {
        return $this->hasMany(OrderTicket::class);
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * Scope to only active ticket categories.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Scope to active categories currently within their sale window.
     */
    public function scopeOnSale(Builder $query): void
    {
        $query->where('is_active', true)
            ->where('sale_start_at', '<=', now())
            ->where('sale_end_at', '>=', now());
    }

    /**
     * Scope to categories that are on sale and have remaining quota.
     */
    public function scopeAvailable(Builder $query): void
    {
        $query->onSale()
            ->whereColumn('sold_count', '<', 'quota');
    }

    // ──────────────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────────────

    /**
     * Get remaining tickets (quota - sold_count).
     */
    public function getRemainingAttribute(): int
    {
        return $this->quota - $this->sold_count;
    }

    /**
     * Check if this category is fully sold out.
     */
    public function getIsSoldOutAttribute(): bool
    {
        return $this->remaining <= 0;
    }

    /**
     * Check if this category is currently within its sale window and active.
     */
    public function getIsOnSaleAttribute(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();

        return ($this->sale_start_at === null || $now->gte($this->sale_start_at))
            && ($this->sale_end_at === null || $now->lte($this->sale_end_at));
    }
}
