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
        Schema::create('interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['email', 'call', 'meeting', 'note', 'task', 'sms', 'linkedin', 'other'])->default('note');
            $table->enum('direction', ['inbound', 'outbound'])->nullable();
            $table->string('subject')->nullable();
            $table->text('content')->nullable();
            $table->text('summary')->nullable();
            $table->json('metadata')->nullable();
            $table->enum('sentiment', ['positive', 'neutral', 'negative'])->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->string('external_id')->nullable()->index();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interactions');
    }
};
