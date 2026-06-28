<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Movimiento extends Model
{
    use HasFactory;

    protected $table = 'movimientos';

    protected $fillable = [
        'auxiliar_id', 'renta_id', 'tipo', 'monto', 'fecha', 'referencia', 'descripcion',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha' => 'date',
    ];

    public function auxiliar(): BelongsTo
    {
        return $this->belongsTo(Auxiliar::class);
    }

    public function renta(): BelongsTo
    {
        return $this->belongsTo(Renta::class);
    }
}
