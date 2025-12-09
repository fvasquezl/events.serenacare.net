<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;

class ActiveEventsWidget extends Widget
{
    protected string $view = 'filament.widgets.active-events-widget';

    protected int|string|array $columnSpan = 'full';

    public function getActiveEventsProperty(): Collection
    {
        return Event::currentlyActive()
            ->orderBy('start_datetime')
            ->get();
    }
}
