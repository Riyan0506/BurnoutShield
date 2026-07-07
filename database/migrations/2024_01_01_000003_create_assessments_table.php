<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Work data
            $table->decimal('work_hours_per_week', 5, 1)->default(40);
            $table->decimal('overtime_hours', 5, 1)->default(0);
            $table->integer('meetings_per_day')->default(0);
            $table->integer('deadlines_missed')->default(0);

            // Wellness & Lifestyle
            $table->integer('job_satisfaction')->default(5); // 1-10
            $table->integer('manager_support')->default(5);  // 1-10
            $table->integer('work_life_balance')->default(5); // 1-10
            $table->decimal('sleep_hours', 4, 1)->default(7);
            $table->integer('physical_activity_days')->default(3);
            $table->decimal('screen_time_hours', 4, 1)->default(8);
            $table->integer('caffeine_intake')->default(2);
            $table->integer('social_support_score')->default(5); // 1-10
            $table->boolean('has_therapy')->default(false);
            $table->boolean('seeks_professional_help')->default(false);

            // Psychological Indicators
            $table->integer('stress_level')->default(5);    // 1-10
            $table->integer('anxiety_score')->default(4);   // 1-10
            $table->integer('depression_score')->default(3); // 1-10

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
