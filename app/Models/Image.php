<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Image extends Model
{
    /** @use HasFactory<\Database\Factories\ImageFactory> */
    use HasFactory;

    protected $fillable = [
        'image_path',
        'time_offset',
        'order',
        'event_id',
    ];

    protected function casts(): array
    {
        return [
            'time_offset' => 'float',
            'order' => 'integer',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        // Eliminar la imagen anterior cuando se actualiza el image_path
        static::updating(function (Image $image) {
            if ($image->isDirty('image_path')) {
                $oldImagePath = $image->getOriginal('image_path');
                if ($oldImagePath && Storage::disk('public')->exists($oldImagePath)) {
                    Storage::disk('public')->delete($oldImagePath);
                }
            }
        });

        // Eliminar el archivo físico del storage cuando se elimina el registro
        static::deleting(function (Image $image) {
            if ($image->image_path && Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }
        });
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Casas de las que esta imagen está excluida (no se mostrará en ellas).
     */
    public function excludedHouses(): BelongsToMany
    {
        return $this->belongsToMany(House::class, 'image_house');
    }

    /**
     * Scope para filtrar imágenes visibles para una casa específica.
     * Las imágenes se muestran en una casa si NO están en la lista de casas excluidas.
     */
    public function scopeVisibleForHouse($query, ?int $houseId)
    {
        return $query->whereDoesntHave('excludedHouses', function ($q) use ($houseId) {
            $q->where('houses.id', $houseId);
        });
    }
}
