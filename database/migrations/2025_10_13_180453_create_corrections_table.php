<?php

// database/migrations/..._create_correction_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calibration_id')->constrained()->onDelete('cascade');
            $table->string('reference'); // Descrição da correção
            $table->decimal('value', 15, 8); // O valor da correção (pode ser positivo ou negativo)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('corrections');
    }
};
