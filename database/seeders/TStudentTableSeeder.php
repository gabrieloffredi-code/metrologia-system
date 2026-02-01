<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TStudentTableSeeder extends Seeder
{
    /**
     * Insere valores comuns da Tabela t-Student (para 90%, 95% e 99%).
     */
    public function run()
    {
        // Valores com base em v (Graus de Liberdade)
        $data = [
            // v | t_90% | t_95% | t_99%
            [1, 6.314, 12.706, 63.657],
            [2, 2.920, 4.303, 9.925],
            [3, 2.353, 3.182, 5.841],
            [4, 2.132, 2.776, 4.604],
            [5, 2.015, 2.571, 4.032],
            [6, 1.943, 2.447, 3.707],
            [7, 1.895, 2.365, 3.499],
            [8, 1.860, 2.306, 3.355],
            [9, 1.833, 2.262, 3.250],
            [10, 1.812, 2.228, 3.169],
            [20, 1.725, 2.086, 2.845],
            [30, 1.697, 2.042, 2.750],
            [50, 1.676, 2.009, 2.678],
            [100, 1.660, 1.984, 2.626],
            [500, 1.648, 1.965, 2.586],
            // Valor de infinito (Distribuição Normal) - Usado como fallback ou maior valor
            [99999, 1.645, 1.960, 2.576], 
        ];

        foreach ($data as $item) {
            DB::table('t_student_table')->insert([
                'degrees_of_freedom' => $item[0],
                'p90' => $item[1],
                'p95' => $item[2],
                'p99' => $item[3],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}