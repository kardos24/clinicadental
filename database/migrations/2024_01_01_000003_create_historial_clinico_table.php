<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_clinico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->tinyInteger('dia');
            $table->tinyInteger('mes');
            $table->year('anio');
            $table->string('signo')->nullable();           // Signo clínico / código
            $table->text('diagnostico');
            $table->integer('num_sesiones')->default(1);
            $table->decimal('importe', 10, 2)->default(0);
            $table->text('tratamiento_realizado')->nullable();
            $table->string('recibo')->nullable();          // Nº de recibo
            $table->decimal('debe', 10, 2)->default(0);
            $table->decimal('haber', 10, 2)->default(0);
            $table->decimal('saldo', 10, 2)->default(0);  // Calculado: debe - haber
            $table->foreignId('gestor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('cliente_id');
            $table->index(['anio', 'mes', 'dia']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_clinico');
    }
};
