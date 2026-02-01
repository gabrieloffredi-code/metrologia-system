<?php

// database/migrations/..._create_t_student_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('t_student_table', function (Blueprint $table) {
            $table->id();
            $table->integer('degrees_of_freedom')->unique(); // v (nu)
            $table->decimal('p90', 8, 4); // Coeficiente t para 90%
            $table->decimal('p95', 8, 4); // Coeficiente t para 95%
            $table->decimal('p99', 8, 4); // Coeficiente t para 99%
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('t_student_table');
    }
};