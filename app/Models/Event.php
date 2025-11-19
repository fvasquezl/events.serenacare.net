<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'start_datetime',
        'end_datetime',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'start_datetime' => 'datetime',
            'end_datetime' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }

    /**
     * Scope para obtener eventos activos en el momento actual.
     */
    public function scopeCurrentlyActive($query)
    {
        return $query
            ->where('is_active', true)
            ->where('start_datetime', '<=', now())
            ->where('end_datetime', '>=', now());
    }

    /**
     * Obtiene el evento global activo actual.
     */
    public static function getCurrentEvent(): ?Event
    {
        return static::currentlyActive()
            ->latest('start_datetime')
            ->first();
    }

    /**
     * Obtiene las imágenes de este evento filtradas para una casa específica.
     * Las imágenes con house_id NULL se muestran en todas las casas.
     * Las imágenes con house_id específico se EXCLUYEN de esa casa.
     */
    public function getImagesForHouse(?int $houseId)
    {
        return $this->images()
            ->visibleForHouse($houseId)
            ->orderBy('order')
            ->get();
    }

    /**
     * Determina si el evento está activo en el momento actual.
     * Compara fecha, hora y minutos completos.
     */
    public function isCurrentlyActive(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if (! $this->start_datetime || ! $this->end_datetime) {
            return false;
        }

        $now = now();

        return $now->greaterThanOrEqualTo($this->start_datetime)
            && $now->lessThanOrEqualTo($this->end_datetime);
    }
}
