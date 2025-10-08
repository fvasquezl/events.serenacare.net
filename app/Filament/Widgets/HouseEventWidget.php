<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use App\Models\House;
use Filament\Widgets\Widget;

class HouseEventWidget extends Widget
{
    protected string $view = 'filament.widgets.house-event-widget';

    protected int|string|array $columnSpan = 1;

    public $houseId;

    public function getHouseProperty(): ?House
    {
        if (!$this->houseId) {
            \Log::error('HouseEventWidget: houseId is null or not set');
            return null;
        }
        \Log::info('HouseEventWidget: loading house', ['houseId' => $this->houseId]);
        return House::find($this->houseId);
    }

    public function getActiveEventProperty(): ?Event
    {
        if (!$this->house) {
            return null;
        }

        return $this->house->events()
            ->where('is_active', true)
            ->where('start_datetime', '<=', now())
            ->where('end_datetime', '>=', now())
            ->first();
    }
}
