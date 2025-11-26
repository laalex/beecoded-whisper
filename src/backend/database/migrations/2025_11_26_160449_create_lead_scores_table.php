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
        Schema::create('lead_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->onDelete('cascade');
            $table->integer('total_score')->default(0);
            $table->integer('engagement_score')->default(0);
            $table->integer('fit_score')->default(0);
            $table->integer('behavior_score')->default(0);
            $table->integer('recency_score')->default(0);
            $table->json('score_breakdown')->nullable();
            $table->json('factors')->nullable();
            $table->decimal('conversion_probability', 5, 2)->default(0);
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();

            $table->index(['lead_id', 'total_score']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_scores');
    }
};
