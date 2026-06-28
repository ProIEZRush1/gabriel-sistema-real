<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agente extends Model
{
    use HasFactory;

    protected $table = 'agentes';

    protected $fillable = [
        'nombre', 'email', 'telefono', 'comision', 'activo',
    ];

    protected $casts = [
        'comision' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function seguros(): HasMany
    {
        return $this->hasMany(Seguro::class);
    }
}
