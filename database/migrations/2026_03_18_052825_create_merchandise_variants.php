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
        Schema::create('merchandise_variants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('merchandise_item_id')->constrained('merchandise_items')->cascadeOnDelete();
            $table->string('variant_group');                      // e.g. "Size", "Color"
            $table->string('variant_value');                      // e.g. "M", "Red"
            $table->unsignedInteger('stock')->default(0);
            $table->bigInteger('price_adjustment')->default(0);   // signed
            $table->timestamps();

            $table->index('merchandise_item_id');
            $table->unique(['merchandise_item_id', 'variant_group', 'variant_value'], 'merch_variant_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchandise_variants');
    }
};
