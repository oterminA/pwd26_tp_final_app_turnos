<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('especialidad_medico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medico_id')->constrained('medicos');
            $table->foreignId('especialidad_id')->constrained('especialidades');
            $table->unique(['medico_id', 'especialidad_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('especialidad_medico');
    }
};
