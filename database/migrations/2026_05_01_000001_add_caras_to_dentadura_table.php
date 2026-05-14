<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Migra de un estado único por diente a un modelo multi-cara.
     *
     * Cada diente pasa a tener:
     *   - estado_pieza: estado general de la pieza (presente, ausente, corona, puente, implante)
     *   - 5 caras con estado independiente: vestibular, lingual, mesial, distal, oclusal
     *
     * Terminología dental (FDI / ISO 3950):
     *   - Vestibular: cara exterior (hacia labios/mejillas)
     *   - Lingual:    cara interior (hacia lengua; "palatino" en arcada superior)
     *   - Mesial:     cara lateral hacia la línea media
     *   - Distal:     cara lateral opuesta a la línea media
     *   - Oclusal:    superficie de masticación (solo premolares y molares)
     */
    public function up(): void
    {
        Schema::table('dentadura', function (Blueprint $table) {
            // Estado general de la pieza dental
            $table->string('estado_pieza', 20)->default('presente')->after('num_diente');

            // Estado por cara (null = sano / sin patología registrada)
            $table->string('cara_vestibular', 20)->nullable()->after('estado_pieza');
            $table->string('cara_lingual', 20)->nullable()->after('cara_vestibular');
            $table->string('cara_mesial', 20)->nullable()->after('cara_lingual');
            $table->string('cara_distal', 20)->nullable()->after('cara_mesial');
            $table->string('cara_oclusal', 20)->nullable()->after('cara_distal');
        });

        // Migrar datos existentes: mapear el estado antiguo a la nueva estructura
        // Estados de pieza completa
        DB::table('dentadura')->where('estado', 'caido')->update(['estado_pieza' => 'ausente']);
        DB::table('dentadura')->where('estado', 'puente')->update(['estado_pieza' => 'puente']);
        DB::table('dentadura')->where('estado', 'sustituido')->update(['estado_pieza' => 'implante']);

        // Estados que eran de cara: se aplican a vestibular como mejor aproximación
        DB::table('dentadura')->where('estado', 'caries')->update(['cara_vestibular' => 'caries']);
        DB::table('dentadura')->where('estado', 'picado')->update(['cara_vestibular' => 'obturacion']);
        DB::table('dentadura')->where('estado', 'partido')->update(['cara_vestibular' => 'fractura']);

        // Eliminar la columna antigua
        Schema::table('dentadura', function (Blueprint $table) {
            $table->dropColumn('estado');
        });
    }

    public function down(): void
    {
        Schema::table('dentadura', function (Blueprint $table) {
            $table->enum('estado', [
                'sano', 'picado', 'caries', 'partido', 'caido', 'puente', 'sustituido'
            ])->default('sano')->after('num_diente');
        });

        // Revertir: pieza → estado antiguo
        DB::table('dentadura')->where('estado_pieza', 'ausente')->update(['estado' => 'caido']);
        DB::table('dentadura')->where('estado_pieza', 'puente')->update(['estado' => 'puente']);
        DB::table('dentadura')->where('estado_pieza', 'implante')->update(['estado' => 'sustituido']);

        // Revertir: cara vestibular → estado antiguo (mejor aproximación)
        DB::table('dentadura')->where('cara_vestibular', 'caries')->update(['estado' => 'caries']);
        DB::table('dentadura')->where('cara_vestibular', 'obturacion')->update(['estado' => 'picado']);
        DB::table('dentadura')->where('cara_vestibular', 'fractura')->update(['estado' => 'partido']);

        Schema::table('dentadura', function (Blueprint $table) {
            $table->dropColumn([
                'estado_pieza',
                'cara_vestibular',
                'cara_lingual',
                'cara_mesial',
                'cara_distal',
                'cara_oclusal',
            ]);
        });
    }
};
