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
        Schema::create('sequence_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sequence_id')->constrained()->onDelete('cascade');
            $table->integer('order')->default(0);
            $table->enum('type', ['email', 'task', 'wait', 'condition', 'sms', 'linkedin'])->default('email');
            $table->string('name');
            $table->text('content')->nullable();
            $table->string('subject')->nullable();
            $table->integer('delay_days')->default(0);
            $table->integer('delay_hours')->default(0);
            $table->json('conditions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sequence_steps');
    }
};
