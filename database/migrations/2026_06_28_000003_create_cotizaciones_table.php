<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cotizaciones')) {
            return;
        }

        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seguro_id')->constrained('seguros')->cascadeOnDelete();
            $table->string('aseguradora');
            $table->decimal('prima', 15, 2)->default(0);
            $table->decimal('cobertura', 15, 2)->default(0);
            $table->text('condiciones')->nullable();
            $table->boolean('seleccionada')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cotizaciones');
    }
};
