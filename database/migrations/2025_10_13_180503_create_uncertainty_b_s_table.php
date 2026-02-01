<?php

// database/migrations/..._create_uncertainty_b_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('uncertainty_b', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calibration_id')->constrained()->onDelete('cascade');
            $table->string('reference'); 
            $table->decimal('value', 15, 8); // Limite de erro máximo 'a' (Ex: Resolução/2)
            $table->enum('distribution', ['RETANGULAR', 'TRIANGULAR', 'MEIA_FAIXA']);
            // Graus de liberdade (v_i) para o cálculo de Welch-Satterthwaite.
            // Para Tipo B, geralmente são tratados como infinito, usaremos um valor alto.
            $table->integer('degrees_of_freedom')->default(1000); 
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('uncertainty_b');
    }
};