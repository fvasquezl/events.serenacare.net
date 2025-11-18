<?php

namespace App\Filament\Widgets;

use App\Models\House;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class ActiveEventsWidget extends Widget
{
    protected string $view = 'filament.widgets.active-events-widget';

    protected int|string|array $columnSpan = 'full';

    // public function getHousesWithEventsProperty(): Collection
    // {
    //     // return House::with(['events' => function ($query) {
    //     //     $query->where('is_active', true)
    //     //         ->where('start_datetime', '<=', now())
    //     //         ->where('end_datetime', '>=', now());
    //     // }])->get();
    // }
}
