<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cotizacion extends Model
{
    use HasFactory;

    protected $table = 'cotizaciones';

    protected $fillable = [
        'seguro_id', 'aseguradora', 'prima', 'cobertura', 'condiciones', 'seleccionada',
    ];

    protected $casts = [
        'prima' => 'decimal:2',
        'cobertura' => 'decimal:2',
        'seleccionada' => 'boolean',
    ];

    public function seguro(): BelongsTo
    {
        return $this->belongsTo(Seguro::class);
    }
}
