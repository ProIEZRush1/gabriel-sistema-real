<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('propiedades')) {
            return;
        }

        Schema::create('propiedades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('tipo')->default('inmueble'); // casa, departamento, local, etc
            $table->string('direccion')->nullable();
            $table->string('propietario')->nullable();
            $table->decimal('renta_mensual', 15, 2)->default(0);
            $table->string('estado')->default('disponible'); // disponible, rentada, mantenimiento
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('propiedades');
    }
};
