<?php

// database/migrations/..._create_assets_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laboratory_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('reference')->nullable();
            $table->string('unit'); // Ex: mm, °C
            $table->decimal('nominal_value', 10, 4)->nullable();
            $table->string('image_path')->nullable(); // Caminho para a imagem/foto da peça
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('assets');
    }
};
