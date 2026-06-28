<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seguro extends Model
{
    use HasFactory;

    protected $table = 'seguros';

    protected $fillable = [
        'poliza', 'tipo', 'aseguradora', 'asegurado', 'beneficiario',
        'condiciones', 'suma_asegurada', 'prima', 'vigencia_inicio',
        'vigencia_fin', 'estado', 'agente_id',
    ];

    protected $casts = [
        'suma_asegurada' => 'decimal:2',
        'prima' => 'decimal:2',
        'vigencia_inicio' => 'date',
        'vigencia_fin' => 'date',
    ];

    public function agente(): BelongsTo
    {
        return $this->belongsTo(Agente::class);
    }

    public function cotizaciones(): HasMany
    {
        return $this->hasMany(Cotizacion::class);
    }
}
