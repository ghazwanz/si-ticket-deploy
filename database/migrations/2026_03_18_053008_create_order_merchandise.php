<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_merchandise', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignUuid('merchandise_variant_id')->constrained('merchandise_variants')->restrictOnDelete();
            $table->uuid('merch_token')->unique();
            $table->unsignedSmallInteger('quantity');
            $table->unsignedBigInteger('unit_price');             // snapshot: base_price + price_adjustment at purchase
            $table->boolean('is_picked_up')->default(false);
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamps();

            $table->index(['order_id']);
            $table->index(['merchandise_variant_id', 'is_picked_up']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_merchandise');
    }
};
