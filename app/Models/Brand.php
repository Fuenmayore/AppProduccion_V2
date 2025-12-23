<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    use HasFactory;

    /**
     * Los atributos que se pueden asignar masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Obtener los cambios de referencia asociados a esta marca.
     */
    public function referenceChanges(): HasMany
    {
        return $this->hasMany(ReferenceChange::class);
    }
    
    /**
     * Si en el futuro decides agregar brand_id a la tabla de productos,
     * descomenta esta relación:
     */
    /*
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
    */
}