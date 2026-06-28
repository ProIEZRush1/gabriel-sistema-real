<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('inquilinos')) {
            return;
        }

        Schema::create('inquilinos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('email')->nullable();
            $table->string('telefono')->nullable();
            $table->string('identificacion')->nullable(); // RFC / INE
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquilinos');
    }
};
