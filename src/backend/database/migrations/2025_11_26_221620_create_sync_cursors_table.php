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
        Schema::create('sync_cursors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integration_id')->constrained()->onDelete('cascade');
            $table->string('cursor_type', 50); // contacts, deals, companies, etc.
            $table->timestamp('last_sync_at')->nullable();
            $table->string('cursor_value')->nullable(); // HubSpot's "after" pagination cursor
            $table->unsignedInteger('records_synced')->default(0);
            $table->json('metadata')->nullable(); // Additional sync state
            $table->timestamps();

            $table->unique(['integration_id', 'cursor_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_cursors');
    }
};
