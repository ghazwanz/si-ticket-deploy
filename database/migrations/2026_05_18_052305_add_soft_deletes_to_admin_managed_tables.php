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
        foreach ($this->softDeletableTables() as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->softDeletableTables() as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropSoftDeletes();
            });
        }
    }

    /**
     * @return array<int, string>
     */
    private function softDeletableTables(): array
    {
        return [
            'event_categories',
            'users',
            'events',
            'ticket_categories',
            'merchandise_items',
            'merchandise_variants',
            'payouts',
        ];
    }
};
