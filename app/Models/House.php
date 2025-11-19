<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class House extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'location',
        'default_image_path',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Imágenes de las que esta casa está excluida (no se mostrarán en esta casa).
     */
    public function excludedFromImages(): BelongsToMany
    {
        return $this->belongsToMany(Image::class, 'image_house');
    }
}
