<?php

// app/Models/Asset.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    // Adicione esta propriedade:
    protected $fillable = [
        'laboratory_id', 
        'name',
        'reference', 
        'unit', 
        'nominal_value', 
        'image_path'
    ];
    
    // ... (resto da classe, incluindo os relacionamentos) ...

    public function laboratory()
    {
        return $this->belongsTo(Laboratory::class);
    }
    
    public function calibrations()
    {
        return $this->hasMany(Calibration::class);
    }
}