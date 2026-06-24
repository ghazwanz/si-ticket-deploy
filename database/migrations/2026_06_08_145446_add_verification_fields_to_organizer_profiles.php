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
        Schema::table('organizer_profiles', function (Blueprint $table) {
            $table->text('organization_address')->nullable();
            $table->string('official_contact')->nullable();
            $table->string('legality_document')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizer_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'organization_address',
                'official_contact',
                'legality_document',
                'status',
                'rejection_reason',
            ]);
        });
    }
};
