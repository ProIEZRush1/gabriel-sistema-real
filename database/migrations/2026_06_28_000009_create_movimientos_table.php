<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('movimientos')) {
            return;
        }

        Schema::create('movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auxiliar_id')->constrained('auxiliares')->cascadeOnDelete();
            // Interconexión con rentas: un cobro de renta se refleja como movimiento bancario
            $table->foreignId('renta_id')->nullable()->constrained('rentas')->nullOnDelete();
            $table->string('tipo'); // pago, transferencia, cobro
            $table->decimal('monto', 15, 2)->default(0);
            $table->date('fecha');
            $table->string('referencia')->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos');
    }
};
