<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function referenceChanges(): HasMany
    {
        return $this->hasMany(ReferenceChange::class);
    }
    
    /**
     * Relación: Una marca tiene muchos productos
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}