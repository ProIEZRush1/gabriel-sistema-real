<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('seguros')) {
            return;
        }

        Schema::create('seguros', function (Blueprint $table) {
            $table->id();
            $table->string('poliza')->unique();
            $table->string('tipo'); // inmueble, auto, medico
            $table->string('aseguradora')->nullable();
            $table->string('asegurado');
            $table->string('beneficiario')->nullable();
            $table->text('condiciones')->nullable();
            $table->decimal('suma_asegurada', 15, 2)->default(0);
            $table->decimal('prima', 15, 2)->default(0);
            $table->date('vigencia_inicio')->nullable();
            $table->date('vigencia_fin')->nullable();
            $table->string('estado')->default('activo'); // activo, vencido, cancelado
            $table->foreignId('agente_id')->nullable()->constrained('agentes')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seguros');
    }
};
