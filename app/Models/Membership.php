<?php

// app/Models/Membership.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Membership extends Model
{
    use HasFactory;
    
    // Campos que podem ser preenchidos em massa
    protected $fillable = ['user_id', 'laboratory_id', 'role'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function laboratory()
    {
        return $this->belongsTo(Laboratory::class);
    }
}