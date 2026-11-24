<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medicos', function (Blueprint $table) {
            $table->json('obras_sociales')->nullable();
        });

        // conserva la cobertura que ya tenian los medicos cargados
        DB::table('medicos')->whereNotNull('obra_social')->orderBy('id')->chunkById(100, function ($medicos) {
            foreach ($medicos as $medico) {
                if ($medico->obra_social !== '') {
                    DB::table('medicos')->where('id', $medico->id)->update([
                        'obras_sociales' => json_encode([$medico->obra_social], JSON_UNESCAPED_UNICODE),
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('medicos', function (Blueprint $table) {
            $table->dropColumn('obras_sociales');
        });
    }
};
