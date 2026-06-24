<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payouts', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                // Drop foreign key first
                $table->dropForeign('payouts_event_id_foreign');
                // Drop unique index
                $table->dropUnique('payouts_event_id_unique');
            } else {
                // On SQLite, drop the unique constraint/index
                $table->dropUnique('payouts_event_id_unique');
            }

            // Add new advance payout columns
            $table->enum('payout_type', ['advance', 'final'])->default('final')->after('organizer_id');
            $table->integer('advance_sequence')->nullable()->after('payout_type');
            $table->unsignedBigInteger('requested_amount')->nullable()->after('net_amount');
            $table->unsignedBigInteger('approved_amount')->nullable()->after('requested_amount');
            $table->text('reason')->nullable()->after('approved_amount');
            $table->text('rejection_reason')->nullable()->after('reason');
            $table->boolean('manual_settlement_required')->default(false)->after('missing_bank_details');

            // Add composite index
            $table->index(['event_id', 'payout_type', 'status'], 'payouts_event_type_status_idx');

            if (DB::getDriverName() !== 'sqlite') {
                // Re-add foreign key referencing events
                $table->foreign('event_id')->references('id')->on('events')->restrictOnDelete();
            }
        });

        // Add 'rejected' to the status enum column (MySQL only)
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE payouts MODIFY COLUMN status ENUM('pending', 'processing', 'completed', 'failed', 'voided', 'rejected') NOT NULL DEFAULT 'pending'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert status enum back to original definition (MySQL only)
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE payouts MODIFY COLUMN status ENUM('pending', 'processing', 'completed', 'failed', 'voided') NOT NULL DEFAULT 'pending'");
        }

        Schema::table('payouts', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                // Drop current foreign key
                $table->dropForeign('payouts_event_id_foreign');
            }

            $table->dropIndex('payouts_event_type_status_idx');

            $table->dropColumn([
                'payout_type',
                'advance_sequence',
                'requested_amount',
                'approved_amount',
                'reason',
                'rejection_reason',
                'manual_settlement_required',
            ]);

            if (DB::getDriverName() !== 'sqlite') {
                // Re-add the unique constraint
                $table->unique('event_id', 'payouts_event_id_unique');
                // Re-add original foreign key
                $table->foreign('event_id')->references('id')->on('events')->restrictOnDelete();
            } else {
                // Re-add unique constraint for SQLite
                $table->unique('event_id', 'payouts_event_id_unique');
            }
        });
    }
};
