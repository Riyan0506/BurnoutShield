<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\DemographicData;
use App\Models\ModelPerformance;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        $admin = User::create([
            'name'     => 'Admin BurnoutShield',
            'email'    => 'admin@burnoutshield.id',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        DemographicData::create([
            'user_id'          => $admin->id,
            'age'              => 30,
            'gender'           => 'Male',
            'job_role'         => 'Software Engineer',
            'experience_years' => 5,
            'company_size'     => 'Mid-size (50-500)',
            'work_mode'        => 'Hybrid',
        ]);

        // Demo employee
        $employee = User::create([
            'name'     => 'Demo Employee',
            'email'    => 'employee@burnoutshield.id',
            'password' => Hash::make('password'),
            'role'     => 'employee',
        ]);

        DemographicData::create([
            'user_id'          => $employee->id,
            'age'              => 28,
            'gender'           => 'Female',
            'job_role'         => 'Frontend Developer',
            'experience_years' => 3,
            'company_size'     => 'Startup (<50)',
            'work_mode'        => 'Remote',
        ]);

        // Seed model performance from metrics.json
        $metricsPath = base_path('../ml-engine/models/metrics.json');
        if (file_exists($metricsPath)) {
            $metrics = json_decode(file_get_contents($metricsPath), true);
            foreach ($metrics['models'] as $name => $m) {
                ModelPerformance::create([
                    'model_name'       => $name,
                    'accuracy'         => $m['accuracy'],
                    'precision_score'  => $m['precision'],
                    'recall_score'     => $m['recall'],
                    'f1_score'         => $m['f1'],
                    'roc_auc'          => $m['roc_auc'],
                    'balancing_method' => $metrics['balancing'] ?? 'Random Oversample',
                    'is_best'          => ($name === $metrics['best_model']),
                ]);
            }
        }

        $this->command->info('✅ Database seeded successfully!');
        $this->command->info('   Admin:    admin@burnoutshield.id / password');
        $this->command->info('   Employee: employee@burnoutshield.id / password');
    }
}
