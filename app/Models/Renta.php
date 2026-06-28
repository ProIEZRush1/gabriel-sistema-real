<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Renta extends Model
{
    use HasFactory;

    protected $table = 'rentas';

    protected $fillable = [
        'propiedad_id', 'inquilino_id', 'periodo', 'monto', 'fecha_emision',
        'fecha_vencimiento', 'fecha_pago', 'monto_pagado', 'tasa_moratoria',
        'interes_moratorio', 'estado',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'monto_pagado' => 'decimal:2',
        'tasa_moratoria' => 'decimal:2',
        'interes_moratorio' => 'decimal:2',
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
        'fecha_pago' => 'date',
    ];

    protected $appends = ['dias_atraso', 'interes_calculado', 'total_a_pagar'];

    public function propiedad(): BelongsTo
    {
        return $this->belongsTo(Propiedad::class);
    }

    public function inquilino(): BelongsTo
    {
        return $this->belongsTo(Inquilino::class);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class);
    }

    /**
     * Días de atraso respecto al vencimiento (0 si ya está pagada o aún no vence).
     */
    public function getDiasAtrasoAttribute(): int
    {
        if ($this->estado === 'cobrada' || ! $this->fecha_vencimiento) {
            return 0;
        }

        $vencimiento = Carbon::parse($this->fecha_vencimiento)->startOfDay();
        $hoy = Carbon::today();

        return $hoy->greaterThan($vencimiento) ? $vencimiento->diffInDays($hoy) : 0;
    }

    /**
     * Interés moratorio calculado: tasa mensual prorrateada por días de atraso sobre el saldo.
     */
    public function getInteresCalculadoAttribute(): float
    {
        $dias = $this->dias_atraso;
        if ($dias <= 0 || ! $this->tasa_moratoria) {
            return 0.0;
        }

        $saldo = (float) $this->monto - (float) $this->monto_pagado;
        if ($saldo <= 0) {
            return 0.0;
        }

        $tasaDiaria = ((float) $this->tasa_moratoria / 100) / 30;

        return round($saldo * $tasaDiaria * $dias, 2);
    }

    public function getTotalAPagarAttribute(): float
    {
        $saldo = (float) $this->monto - (float) $this->monto_pagado;

        return round(max($saldo, 0) + $this->interes_calculado, 2);
    }

    /**
     * Recalcula el estado de pago automáticamente según pagos y vencimiento.
     */
    public function recalcularEstado(): void
    {
        if ((float) $this->monto_pagado >= (float) $this->monto && $this->fecha_pago) {
            $this->estado = 'cobrada';
        } elseif ($this->fecha_vencimiento && Carbon::today()->greaterThan(Carbon::parse($this->fecha_vencimiento))) {
            $this->estado = 'con_adeudo';
        } else {
            $this->estado = 'generada';
        }
    }
}
