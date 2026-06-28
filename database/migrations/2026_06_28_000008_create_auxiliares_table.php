<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('auxiliares')) {
            return;
        }

        Schema::create('auxiliares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proyecto_id')->constrained('proyectos')->cascadeOnDelete();
            $table->string('nombre'); // Nombre del auxiliar bancario
            $table->string('banco')->nullable();
            $table->string('numero_cuenta')->nullable();
            $table->decimal('saldo_inicial', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auxiliares');
    }
};
