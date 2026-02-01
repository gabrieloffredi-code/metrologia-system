<?php

// database/migrations/..._create_calibration_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('calibrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->onDelete('cascade');
            $table->timestamp('date');
            
            // Input do usuário
            $table->decimal('confidence_level', 5, 2); // Ex: 95.00, 99.00
            
            // Resultados finais calculados
            $table->decimal('mean_value', 15, 8)->nullable();
            $table->decimal('total_correction', 15, 8)->nullable();
            $table->decimal('combined_uncertainty', 15, 8)->nullable(); // uc(y)
            $table->decimal('expanded_uncertainty', 15, 8)->nullable(); // U
            $table->decimal('k_factor', 5, 4)->nullable(); // Fator K t-Student final
            $table->decimal('effective_degrees_of_freedom', 15, 8)->nullable(); // v_eff

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('calibrations');
    }
};