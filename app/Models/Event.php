<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'house_id',
        'title',
        'description',
        'image_path',
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

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
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
