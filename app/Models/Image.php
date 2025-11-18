<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Image extends Model
{
    /** @use HasFactory<\Database\Factories\ImageFactory> */
    use HasFactory;

    protected $fillable = [
        'image_path',
        'time_offset',
        'order',
        'house_id',
        'event_id',
    ];

    protected function casts(): array
    {
        return [
            'time_offset' => 'float',
            'order' => 'integer',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }
}
