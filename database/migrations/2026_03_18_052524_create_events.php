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
        Schema::create('events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organizer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('category_id')->constrained('event_categories')->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->longText('description');
            $table->string('banner_image')->nullable();
            $table->string('venue_name');
            $table->text('address');
            $table->string('city');
            $table->date('event_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('status', ['draft', 'awaiting_approval', 'published', 'awaiting_cancellation', 'completed', 'cancelled'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            $table->index('status');
            $table->index('event_date');
            $table->index('city');
            $table->index(['status', 'event_date']);
            $table->index('is_featured');
            if (config('database.default') !== 'sqlite') {
                $table->fullText(['name', 'description']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
