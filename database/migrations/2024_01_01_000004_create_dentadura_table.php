<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla de estado dental por diente (notación FDI)
     * Cuadrante 1: 11-18 (sup. derecho), Cuadrante 2: 21-28 (sup. izquierdo)
     * Cuadrante 3: 31-38 (inf. izquierdo), Cuadrante 4: 41-48 (inf. derecho)
     * Dientes de leche: 51-55, 61-65, 71-75, 81-85
     */
    public function up(): void
    {
        Schema::create('dentadura', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->string('num_diente', 3);   // Código FDI: 11,12...48
            $table->enum('estado', [
                'sano',
                'picado',
                'caries',
                'partido',
                'caido',
                'puente',
                'sustituido'
            ])->default('sano');
            $table->text('notas')->nullable();
            $table->date('fecha_actualizacion')->nullable();
            $table->timestamps();

            $table->unique(['cliente_id', 'num_diente']);
            $table->index('cliente_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dentadura');
    }
};
