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
        Schema::create('order_tickets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignUuid('ticket_category_id')->constrained('ticket_categories')->restrictOnDelete();
            $table->uuid('qr_token')->unique();                   // encoded inside QR image
            $table->string('holder_name');
            $table->unsignedBigInteger('unit_price');             // snapshot of price at time of purchase
            $table->boolean('is_checked_in')->default(false);
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamps();

            $table->index(['order_id']);
            $table->index(['ticket_category_id', 'is_checked_in']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_tickets');
    }
};
