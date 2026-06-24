<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MerchandiseVariant extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'merchandise_item_id',
        'variant_group',
        'variant_value',
        'stock',
        'price_adjustment',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'stock' => 'integer',
            'price_adjustment' => 'integer',
        ];
    }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function item(): BelongsTo
    {
        return $this->belongsTo(MerchandiseItem::class, 'merchandise_item_id');
    }

    public function orderMerchandise(): HasMany
    {
        return $this->hasMany(OrderMerchandise::class);
    }

    // ──────────────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────────────

    /**
     * Get the final price (base_price + price_adjustment).
     */
    public function getFinalPriceAttribute(): int
    {
        return ($this->item->base_price ?? 0) + $this->price_adjustment;
    }

    /**
     * Check if this variant is sold out.
     */
    public function getIsSoldOutAttribute(): bool
    {
        return $this->stock <= 0;
    }
}
