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
        Schema::table('payouts', function (Blueprint $table) {
            $table->renameColumn('midtrans_reference', 'transfer_reference');
        });

        Schema::table('payouts', function (Blueprint $table) {
            $table->string('proof_photo')->nullable()->after('transfer_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payouts', function (Blueprint $table) {
            $table->dropColumn('proof_photo');
        });

        Schema::table('payouts', function (Blueprint $table) {
            $table->renameColumn('transfer_reference', 'midtrans_reference');
        });
    }
};
