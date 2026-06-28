<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inquilino extends Model
{
    use HasFactory;

    protected $table = 'inquilinos';

    protected $fillable = [
        'nombre', 'email', 'telefono', 'identificacion',
    ];

    public function rentas(): HasMany
    {
        return $this->hasMany(Renta::class);
    }
}
