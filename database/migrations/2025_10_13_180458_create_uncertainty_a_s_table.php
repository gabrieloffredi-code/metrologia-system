<?php

// database/migrations/..._create_uncertainty_a_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('uncertainty_a', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calibration_id')->constrained()->onDelete('cascade');
            $table->string('reference'); 
            $table->decimal('value', 15, 8); // Valor da Incerteza Expandida (U_cert)
            $table->decimal('factor_k', 5, 4); // Fator K usado no certificado original
            // Graus de liberdade (v_i) para o cálculo de Welch-Satterthwaite.
            $table->integer('degrees_of_freedom')->default(50); 
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('uncertainty_a');
    }
};