<?php

namespace Database\Seeders;

// Adicione esta linha para importar o DB
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; 

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Chama o seeder da Tabela T-Student para preencher os valores de referência do GUM.
        $this->call([
            TStudentTableSeeder::class,
            // Outros seeders (como Users, Laboratórios de Teste) seriam adicionados aqui.
        ]);

        // Exemplo de criação de usuário de teste (opcional, mas útil para desenvolvimento)
        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}