<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->datetime('fecha_hora');
            $table->integer('duracion_minutos')->default(30);
            $table->string('motivo');
            $table->enum('estado', [
                'pendiente',
                'confirmada',
                'cancelada',
                'realizada',
                'no_presentado'
            ])->default('pendiente');
            $table->text('notas')->nullable();
            $table->boolean('recordatorio_enviado')->default(false);
            $table->foreignId('gestor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('cliente_id');
            $table->index('fecha_hora');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
