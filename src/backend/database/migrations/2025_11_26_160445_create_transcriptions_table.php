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
        Schema::create('transcriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('interaction_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['voice_note', 'call_recording', 'meeting', 'voice_input'])->default('voice_note');
            $table->string('audio_file_path')->nullable();
            $table->text('transcript')->nullable();
            $table->text('summary')->nullable();
            $table->json('action_items')->nullable();
            $table->json('key_points')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transcriptions');
    }
};
