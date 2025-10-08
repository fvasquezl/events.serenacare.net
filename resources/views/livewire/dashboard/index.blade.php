<?php

use App\Models\House;
use Livewire\Volt\Component;

new class extends Component {
    public $houses;
    public $isAdmin;

    public function mount(): void
    {
        $this->loadHouses();

        // dd($this->houses);
    }

    public function loadHouses(): void
    {
        $user = auth()->user();
        $this->isAdmin = is_null($user->house_id);

        if ($this->isAdmin) {
            // Admin users see all houses
            $this->houses = House::with(['events' => function ($query) {
                $query->where('is_active', true)
                    ->where('start_datetime', '<=', now())
                    ->where('end_datetime', '>=', now())
                    ->latest('start_datetime')
                    ->limit(1);
            }])->orderBy('name')->get();
        } else {
            // Regular users see only their assigned house
            $this->houses = House::with(['events' => function ($query) {
                $query->where('is_active', true)
                    ->where('start_datetime', '<=', now())
                    ->where('end_datetime', '>=', now())
                    ->latest('start_datetime')
                    ->limit(1);
            }])->where('id', $user->house_id)->get();
        }
    }

    public function refresh(): void
    {
        $this->loadHouses();
    }

    public function getEventImage($house): ?string
    {
        $activeEvent = $house->events->first();

        if ($activeEvent) {
            return asset('storage/'.$activeEvent->image_path);
        }

        if ($house->default_image_path) {
            return asset('storage/'.$house->default_image_path);
        }

        return null;
    }

    public function getEventTitle($house): ?string
    {
        $activeEvent = $house->events->first();
        return $activeEvent?->title;
    }

    public function getEventDescription($house): ?string
    {
        $activeEvent = $house->events->first();
        return $activeEvent?->description;
    }
}; ?>

<div wire:poll.60s="refresh">
    @if($isAdmin)
        <div class="grid gap-4 md:grid-cols-3 mb-4">
            @foreach($houses as $house)
                <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-black">
                    @php
                        $image = $this->getEventImage($house);
                        $title = $this->getEventTitle($house);
                    @endphp

                    @if($image)
                        <img src="{{ $image }}"
                             alt="{{ $title ?? $house->name }}"
                             class="w-full h-full object-cover">

                        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-4">
                            <h3 class="text-lg font-bold text-white">{{ $house->name }}</h3>
                            @if($title)
                                <p class="text-sm text-white/90">{{ $title }}</p>
                            @else
                                <p class="text-sm text-white/70">Sin evento activo</p>
                            @endif
                        </div>
                    @else
                        <div class="flex items-center justify-center w-full h-full">
                            <div class="text-center text-white">
                                <h3 class="text-2xl font-bold mb-2">{{ $house->name }}</h3>
                                <p class="text-sm text-white/70">{{ $house->location }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        @foreach($houses as $house)
            @php
                $image = $this->getEventImage($house);
                $title = $this->getEventTitle($house);
                $description = $this->getEventDescription($house);
            @endphp

            <div class="relative w-full aspect-[16/9] overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-black mb-4">
                @if($image)
                    <img src="{{ $image }}"
                         alt="{{ $title ?? $house->name }}"
                         class="w-full h-full object-cover">

                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/90 to-transparent p-6">
                        <h2 class="text-3xl font-bold text-white mb-2">{{ $title ?? $house->name }}</h2>
                        @if($description)
                            <p class="text-base text-white/90">{{ $description }}</p>
                        @endif
                    </div>
                @else
                    <div class="flex items-center justify-center w-full h-full">
                        <div class="text-center text-white">
                            <h2 class="text-4xl font-bold mb-4">{{ $house->name }}</h2>
                            <p class="text-xl text-white/70">{{ $house->location }}</p>
                            <p class="text-lg text-white/60 mt-2">Sin evento activo</p>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    @endif

    <div class="flex justify-between items-center text-sm text-neutral-600 dark:text-neutral-400">
        <p>Actualizando cada 60 segundos</p>
        <flux:button wire:click="refresh" variant="ghost" size="sm">
            Actualizar ahora
        </flux:button>
    </div>
</div>