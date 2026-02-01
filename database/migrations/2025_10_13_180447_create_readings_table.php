<?php

// database/migrations/..._create_reading_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
    Schema::create('readings', function (Blueprint $table) {
        $table->id();
        $table->foreignId('calibration_id')->constrained()->onDelete('cascade');
        $table->decimal('value', 15, 8); // O valor lido
        $table->timestamps();
    });
    }

    public function down()
    {
        Schema::dropIfExists('readings');
    }
};