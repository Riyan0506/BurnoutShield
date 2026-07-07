<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ModelPerformance extends Model
{
    protected $fillable = [
        'model_name','accuracy','precision_score','recall_score','f1_score','roc_auc','balancing_method','is_best'
    ];
    protected $casts = [
        'accuracy'=>'float','precision_score'=>'float','recall_score'=>'float',
        'f1_score'=>'float','roc_auc'=>'float','is_best'=>'boolean'
    ];
}
