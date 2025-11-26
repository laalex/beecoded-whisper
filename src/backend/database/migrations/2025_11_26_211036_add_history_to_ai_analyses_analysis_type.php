<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Handle enum modification for different database drivers.
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: recreate table with new enum
            Schema::create('ai_analyses_new', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lead_id')->constrained()->onDelete('cascade');
                $table->enum('analysis_type', ['full', 'scoring', 'nurturing', 'sentiment', 'history'])->default('full');
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

            DB::statement('INSERT INTO ai_analyses_new SELECT * FROM ai_analyses');
            Schema::drop('ai_analyses');
            Schema::rename('ai_analyses_new', 'ai_analyses');

        } elseif ($driver === 'pgsql') {
            // PostgreSQL: drop and recreate CHECK constraint
            DB::statement('ALTER TABLE ai_analyses DROP CONSTRAINT IF EXISTS ai_analyses_analysis_type_check');
            DB::statement("ALTER TABLE ai_analyses ADD CONSTRAINT ai_analyses_analysis_type_check CHECK (analysis_type::text = ANY (ARRAY['full'::text, 'scoring'::text, 'nurturing'::text, 'sentiment'::text, 'history'::text]))");

        } else {
            // MySQL: modify enum column
            DB::statement("ALTER TABLE ai_analyses MODIFY COLUMN analysis_type ENUM('full', 'scoring', 'nurturing', 'sentiment', 'history') DEFAULT 'full'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            Schema::create('ai_analyses_old', function (Blueprint $table) {
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

            DB::statement("INSERT INTO ai_analyses_old SELECT * FROM ai_analyses WHERE analysis_type != 'history'");
            Schema::drop('ai_analyses');
            Schema::rename('ai_analyses_old', 'ai_analyses');

        } elseif ($driver === 'pgsql') {
            // Delete history records first
            DB::table('ai_analyses')->where('analysis_type', 'history')->delete();
            DB::statement('ALTER TABLE ai_analyses DROP CONSTRAINT IF EXISTS ai_analyses_analysis_type_check');
            DB::statement("ALTER TABLE ai_analyses ADD CONSTRAINT ai_analyses_analysis_type_check CHECK (analysis_type::text = ANY (ARRAY['full'::text, 'scoring'::text, 'nurturing'::text, 'sentiment'::text]))");

        } else {
            DB::statement("ALTER TABLE ai_analyses MODIFY COLUMN analysis_type ENUM('full', 'scoring', 'nurturing', 'sentiment') DEFAULT 'full'");
        }
    }
};
