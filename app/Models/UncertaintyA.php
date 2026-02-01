<?php

// app/Models/UncertaintyA.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UncertaintyA extends Model
{
    use HasFactory;

    protected $table = 'uncertainty_a'; // Garante o nome correto
    
    protected $fillable = [
        'calibration_id', 
        'reference', 
        'value', 
        'factor_k',
        'degrees_of_freedom'
        // 'degrees_of_freedom' é opcional aqui, pois tem default na migration
    ];
    
    public function calibration()
    {
        return $this->belongsTo(Calibration::class);
    }
}