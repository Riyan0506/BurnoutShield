<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prediction_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('risk_level', ['Low', 'Moderate', 'High']);
            $table->decimal('burnout_probability', 5, 2)->default(0); // 0-100
            $table->string('model_used')->default('Random Forest');
            $table->json('probabilities')->nullable(); // {Low:x, Moderate:y, High:z}
            $table->json('feature_importance')->nullable();
            $table->json('top_risk_factors')->nullable();
            $table->timestamps();
        });

        Schema::create('recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prediction_id')->constrained('prediction_results')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->enum('category', ['sleep', 'exercise', 'work', 'mental', 'social', 'nutrition', 'general'])->default('general');
            $table->enum('priority', ['high', 'medium', 'low'])->default('medium');
            $table->boolean('is_completed')->default(false);
            $table->text('gemini_context')->nullable();
            $table->timestamps();
        });

        Schema::create('calendar_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('access_token');
            $table->text('refresh_token')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('scope')->nullable();
            $table->timestamps();
        });

        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('recommendation_id')->constrained()->onDelete('cascade');
            $table->string('google_event_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('event_date');
            $table->enum('status', ['pending', 'synced', 'failed'])->default('pending');
            $table->timestamps();
        });

        Schema::create('model_performances', function (Blueprint $table) {
            $table->id();
            $table->string('model_name');
            $table->decimal('accuracy', 6, 4)->default(0);
            $table->decimal('precision_score', 6, 4)->default(0);
            $table->decimal('recall_score', 6, 4)->default(0);
            $table->decimal('f1_score', 6, 4)->default(0);
            $table->decimal('roc_auc', 6, 4)->default(0);
            $table->string('balancing_method')->default('Random Oversample');
            $table->boolean('is_best')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('model_performances');
        Schema::dropIfExists('calendar_events');
        Schema::dropIfExists('calendar_tokens');
        Schema::dropIfExists('recommendations');
        Schema::dropIfExists('prediction_results');
    }
};
