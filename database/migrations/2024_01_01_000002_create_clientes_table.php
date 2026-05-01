<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('num_filiacion')->unique()->nullable();
            $table->string('apellidos');
            $table->string('nombre');
            $table->integer('edad')->nullable();
            $table->string('profesion')->nullable();
            $table->string('direccion')->nullable();
            $table->string('cp', 10)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->text('observaciones')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['apellidos', 'nombre']);
            $table->index('num_filiacion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
