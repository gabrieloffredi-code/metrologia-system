<?php

// app/Models/Laboratory.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laboratory extends Model
{
    protected $fillable = ['name', 'description'];

    // Membros do laboratório
    public function members()
    {
        return $this->belongsToMany(User::class, 'memberships')
                    ->withPivot('role'); // Puxa a função de cada membro
    }

    // Peças cadastradas neste laboratório
    public function assets()
    {
        return $this->hasMany(Asset::class);
    }
}