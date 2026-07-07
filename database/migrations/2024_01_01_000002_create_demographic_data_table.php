<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demographic_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('age')->nullable();
            $table->enum('gender', ['Male', 'Female', 'Non-binary', 'Prefer not to say'])->nullable();
            $table->enum('job_role', [
                'Software Engineer', 'Data Scientist', 'DevOps', 'Frontend Developer',
                'Backend Developer', 'Full Stack Developer', 'Product Manager',
                'Project Manager', 'UX Designer', 'QA Engineer', 'CTO', 'Other'
            ])->nullable();
            $table->decimal('experience_years', 4, 1)->nullable();
            $table->enum('company_size', ['Startup (<50)', 'Mid-size (50-500)', 'Large (500-5000)', 'MNC (>5000)'])->nullable();
            $table->enum('work_mode', ['Remote', 'Onsite', 'Hybrid'])->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demographic_data');
    }
};
