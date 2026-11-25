<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('disponibilidades', function (Blueprint $table) {
            // Las franjas anteriores quedan generales hasta que se editen.
            $table->foreignId('especialidad_id')->nullable()->constrained('especialidades')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('disponibilidades', function (Blueprint $table) {
            $table->dropForeign(['especialidad_id']);
            $table->dropColumn('especialidad_id');
        });
    }
};
