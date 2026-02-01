<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TStudentTable extends Model
{
    use HasFactory;
    
    // DEFINIÇÃO CHAVE: Força o Laravel a usar o nome da tabela no singular
    protected $table = 't_student_table'; 

    protected $guarded = []; // Permitir que o Seeder funcione

    // Não precisamos de fillable/casts, mas é bom ter o $guarded para seeders.
}