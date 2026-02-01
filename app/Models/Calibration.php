<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calibration extends Model
{
    use HasFactory;

    // Adicione esta propriedade com todas as colunas que o controller está salvando
    protected $fillable = [
        'asset_id',
        'date',
        'confidence_level',
        'mean_value',
        'total_correction',
        'combined_uncertainty',
        'expanded_uncertainty',
        'k_factor',
        'effective_degrees_of_freedom',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    // Relacionamentos
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
    
    // As calibrações têm muitos detalhes (leituras, incertezas, correções)
    public function readings()
    {
        return $this->hasMany(Reading::class);
    }
    
    public function corrections()
    {
        return $this->hasMany(Correction::class);
    }
    
    public function uncertaintyA()
    {
        return $this->hasMany(UncertaintyA::class);
    }
    
    public function uncertaintyB()
    {
        return $this->hasMany(UncertaintyB::class);
    }
}