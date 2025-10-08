<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function getActiveEventAttribute(): ?Event
    {
        return $this->events()
            ->where('is_active', true)
            ->where('start_datetime', '<=', now())
            ->where('end_datetime', '>=', now())
            ->first();
    }
}
