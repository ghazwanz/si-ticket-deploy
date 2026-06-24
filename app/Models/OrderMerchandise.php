<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class OrderMerchandise extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'order_merchandise';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'unit_price' => 'integer',
            'quantity' => 'integer',
            'is_picked_up' => 'boolean',
            'picked_up_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function merchandiseVariant(): BelongsTo
    {
        return $this->belongsTo(MerchandiseVariant::class);
    }

    /**
     * Get the parent merchandise item through the variant.
     */
    public function merchandiseItem(): HasOneThrough
    {
        return $this->hasOneThrough(
            MerchandiseItem::class,
            MerchandiseVariant::class,
            'id', // Foreign key on the intermediate table (variants.id)
            'id', // Foreign key on the target table (items.id)
            'merchandise_variant_id', // Local key on this table
            'merchandise_item_id' // Local key on the intermediate table
        );
    }
}
