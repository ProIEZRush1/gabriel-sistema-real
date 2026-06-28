<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Auxiliar extends Model
{
    use HasFactory;

    protected $table = 'auxiliares';

    protected $fillable = [
        'proyecto_id', 'nombre', 'banco', 'numero_cuenta', 'saldo_inicial',
    ];

    protected $casts = [
        'saldo_inicial' => 'decimal:2',
    ];

    protected $appends = ['saldo_actual'];

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class);
    }

    /**
     * Saldo actual = saldo inicial + cobros - pagos - transferencias.
     */
    public function getSaldoActualAttribute(): float
    {
        $saldo = (float) $this->saldo_inicial;

        foreach ($this->movimientos as $mov) {
            if ($mov->tipo === 'cobro') {
                $saldo += (float) $mov->monto;
            } else { // pago, transferencia
                $saldo -= (float) $mov->monto;
            }
        }

        return round($saldo, 2);
    }
}
