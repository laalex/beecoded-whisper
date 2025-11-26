<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add HubSpot-specific fields to enrichment_data
        Schema::table('enrichment_data', function (Blueprint $table) {
            $table->string('hubspot_lifecycle_stage')->nullable()->after('industry');
            $table->json('hubspot_deals')->nullable()->after('hubspot_lifecycle_stage');
            $table->json('hubspot_activities')->nullable()->after('hubspot_deals');
            $table->json('hubspot_owner')->nullable()->after('hubspot_activities');
            $table->timestamp('last_synced_at')->nullable()->after('enriched_at');
            $table->string('sync_error')->nullable()->after('last_synced_at');
        });

        // Create AI analysis table
        Schema::create('ai_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->onDelete('cascade');
            $table->enum('analysis_type', ['full', 'scoring', 'nurturing', 'sentiment'])->default('full');
            $table->json('insights')->nullable();
            $table->json('recommendations')->nullable();
            $table->json('risks')->nullable();
            $table->json('opportunities')->nullable();
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->string('model_used')->default('claude-sonnet-4-20250514');
            $table->timestamp('analyzed_at')->nullable();
            $table->timestamps();

            $table->index(['lead_id', 'analysis_type']);
            $table->index('analyzed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_analyses');

        Schema::table('enrichment_data', function (Blueprint $table) {
            $table->dropColumn([
                'hubspot_lifecycle_stage',
                'hubspot_deals',
                'hubspot_activities',
                'hubspot_owner',
                'last_synced_at',
                'sync_error',
            ]);
        });
    }
};
