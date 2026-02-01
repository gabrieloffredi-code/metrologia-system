<?php

// app/Models/UncertaintyB.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UncertaintyB extends Model
{
    use HasFactory;

    protected $table = 'uncertainty_b'; // Garante o nome correto
    
    protected $fillable = [
        'calibration_id', 
        'reference', 
        'value', 
        'distribution'
        // 'degrees_of_freedom' é opcional aqui, pois tem default na migration
    ];
    
    public function calibration()
    {
        return $this->belongsTo(Calibration::class);
    }
}