<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reading extends Model
{
    use HasFactory;
    
    protected $fillable = ['calibration_id', 'value'];

    public function calibration()
    {
        return $this->belongsTo(Calibration::class);
    }
}