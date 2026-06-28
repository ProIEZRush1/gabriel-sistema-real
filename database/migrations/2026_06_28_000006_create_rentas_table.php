<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('rentas')) {
            return;
        }

        Schema::create('rentas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('propiedad_id')->constrained('propiedades')->cascadeOnDelete();
            $table->foreignId('inquilino_id')->constrained('inquilinos')->cascadeOnDelete();
            $table->string('periodo'); // 2026-06, mes que cubre la renta
            $table->decimal('monto', 15, 2)->default(0);
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento');
            $table->date('fecha_pago')->nullable();
            $table->decimal('monto_pagado', 15, 2)->default(0);
            $table->decimal('tasa_moratoria', 5, 2)->default(0); // % mensual sobre saldo vencido
            $table->decimal('interes_moratorio', 15, 2)->default(0); // calculado al cobrar
            $table->string('estado')->default('generada'); // generada, cobrada, con_adeudo
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rentas');
    }
};
