<?php

// app/Models/Correction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Correction extends Model
{
    use HasFactory;

    protected $fillable = [
        'calibration_id', 
        'reference', 
        'value' // O valor da correção
    ];
    
    public function calibration()
    {
        return $this->belongsTo(Calibration::class);
    }
}