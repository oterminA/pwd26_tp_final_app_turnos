<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medicos', function (Blueprint $table) {
            // La columna matricula anterior se conserva por compatibilidad.
            $table->string('tipoMat')->nullable();
            $table->string('numMat')->nullable();
            $table->unique(['tipoMat', 'numMat']);
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('medicos', function (Blueprint $table) {
            $table->dropUnique(['tipoMat', 'numMat']);
            $table->dropSoftDeletes();
            $table->dropColumn(['tipoMat', 'numMat']);
        });
    }
};
