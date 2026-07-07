<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DemographicData extends Model
{
    protected $table = 'demographic_data';
    protected $fillable = [
        'user_id','age','gender','job_role','experience_years','company_size','work_mode'
    ];
    protected $casts = ['experience_years' => 'float'];

    public function user() { return $this->belongsTo(User::class); }

    // Map to ML engine field names
    public function toMLArray(): array
    {
        $sizeMap = [
            'Startup (<50)' => 'Startup', 'Mid-size (50-500)' => 'Mid-size',
            'Large (500-5000)' => 'Large', 'MNC (>5000)' => 'MNC'
        ];
        return [
            'age'              => $this->age ?? 30,
            'gender'           => $this->gender ?? 'Male',
            'job_role'         => $this->job_role ?? 'Software Engineer',
            'experience_years' => $this->experience_years ?? 2,
            'company_size'     => $sizeMap[$this->company_size] ?? 'Mid-size',
            'work_mode'        => $this->work_mode ?? 'Hybrid',
        ];
    }
}
