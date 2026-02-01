<?php

// database/migrations/..._create_membership_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('laboratory_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['ADM', 'EDITOR', 'VISUALIZADOR']);
            $table->timestamps();
            
            // Garante que um usuário só tenha uma função por laboratório
            $table->unique(['user_id', 'laboratory_id']); 
        });
    }

    public function down()
    {
        Schema::dropIfExists('memberships');
    }
};