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
        Schema::create('payouts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('event_id')->unique()->constrained('events')->restrictOnDelete();
            $table->foreignUuid('organizer_id')->constrained('users')->restrictOnDelete();
            $table->unsignedBigInteger('gross_amount');           // total settled revenue for the event
            $table->unsignedBigInteger('platform_fee');           // deducted platform fee (IDR)
            $table->unsignedBigInteger('net_amount');             // disbursed to organizer
            $table->string('payout_bank_name')->nullable();
            $table->string('payout_account_number')->nullable();
            $table->string('payout_account_holder')->nullable();
            $table->boolean('missing_bank_details')->default(false);
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignUuid('disbursed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('fee_percentage', 5, 2);              // snapshot of fee % at time of payout
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'voided', 'rejected'])->default('pending');
            $table->string('midtrans_reference')->nullable();
            $table->timestamp('disbursed_at')->nullable();
            $table->timestamps();

            $table->index(['organizer_id', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payouts');
    }
};
