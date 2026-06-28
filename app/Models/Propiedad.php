<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Propiedad extends Model
{
    use HasFactory;

    protected $table = 'propiedades';

    protected $fillable = [
        'nombre', 'tipo', 'direccion', 'propietario', 'renta_mensual', 'estado',
    ];

    protected $casts = [
        'renta_mensual' => 'decimal:2',
    ];

    public function rentas(): HasMany
    {
        return $this->hasMany(Renta::class);
    }
}
