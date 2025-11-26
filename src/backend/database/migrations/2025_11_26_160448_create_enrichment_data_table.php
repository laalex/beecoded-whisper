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
        Schema::create('enrichment_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->onDelete('cascade');
            $table->string('provider')->default('preply');
            $table->json('company_data')->nullable();
            $table->json('contact_data')->nullable();
            $table->json('social_profiles')->nullable();
            $table->json('technologies')->nullable();
            $table->json('funding_data')->nullable();
            $table->integer('employee_count')->nullable();
            $table->string('industry')->nullable();
            $table->decimal('annual_revenue', 15, 2)->nullable();
            $table->timestamp('enriched_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrichment_data');
    }
};
