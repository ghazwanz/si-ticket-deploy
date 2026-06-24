<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'user_id',
        'event_id',
        'status',
        'total_amount',
        'payment_type',
        'snap_retry_count',
        'failed_at',
        'cancelled_at',
        'midtrans_order_id',
        'midtrans_transaction_id',
        'snap_token',
        'stock_reserved_until',
        'paid_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'total_amount' => 'integer',
            'snap_retry_count' => 'integer',
            'paid_at' => 'datetime',
            'failed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'stock_reserved_until' => 'datetime',
        ];
    }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class)->withTrashed();
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(OrderTicket::class);
    }

    public function merchandise(): HasMany
    {
        return $this->hasMany(OrderMerchandise::class);
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * Scope to only pending orders.
     */
    public function scopePending(Builder $query): void
    {
        $query->where('status', OrderStatus::Pending);
    }

    /**
     * Scope to only paid orders.
     */
    public function scopePaid(Builder $query): void
    {
        $query->where('status', OrderStatus::Paid);
    }

    /**
     * Scope to expired orders (pending + reservation window passed).
     */
    public function scopeExpired(Builder $query): void
    {
        $query->where('status', OrderStatus::Pending)
            ->whereNotNull('stock_reserved_until')
            ->where('stock_reserved_until', '<', now());
    }

    // ──────────────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────────────

    /**
     * Get the number of tickets in this order.
     */
    public function getTicketCountAttribute(): int
    {
        return (int) $this->tickets()->count();
    }

    /**
     * Get the number of merchandise items in this order.
     */
    public function getMerchCountAttribute(): int
    {
        return (int) $this->merchandise()->count();
    }

    // ──────────────────────────────────────────────
    // Business Helpers
    // ──────────────────────────────────────────────

    /**
     * Check if this order's stock reservation has expired.
     */
    public function isExpired(): bool
    {
        return $this->status === OrderStatus::Pending
            && $this->stock_reserved_until !== null
            && now()->gt($this->stock_reserved_until);
    }

    /**
     * Check if payment can be retried for this order.
     */
    public function canRetryPayment(): bool
    {
        return $this->status === OrderStatus::Pending
            && $this->snap_retry_count < 3
            && ! $this->isExpired();
    }
}
