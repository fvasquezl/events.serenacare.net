<?php

use App\Models\Event;
use App\Models\House;
use Livewire\Volt\Component;

new class extends Component {
    public $houses;
    public $isAdmin;

    public function mount(): void
    {
        $this->loadHouses();
    }

    public function loadHouses(): void
    {
        $user = auth()->user();
        $this->isAdmin = is_null($user->house_id);

        if ($this->isAdmin) {
            // Admin users see all houses
            $this->houses = House::orderBy('name')->get();
        } else {
            // Regular users see only their assigned house
            $this->houses = House::where('id', $user->house_id)->get();
        }
    }

    public function refresh(): void
    {
        $this->loadHouses();
    }

    /**
     * Obtiene el evento activo actual.
     */
    public function getCurrentEvent(): ?Event
    {
        return Event::getCurrentEvent();
    }

    /**
     * Obtiene las imágenes del evento para una casa específica.
     */
    public function getEventImages(House $house): array
    {
        $event = $this->getCurrentEvent();

        // Si hay evento activo, obtener sus imágenes filtradas por casa
        if ($event) {
            $images = $event->getImagesForHouse($house->id);

            if ($images->isNotEmpty()) {
                return $images->map(function ($image) {
                    return [
                        'type' => $image->type ?? 'image',
                        'url' => $image->isImage() ? asset('storage/'.$image->image_path) : null,
                        'youtube_id' => $image->isVideo() ? $image->getYoutubeVideoId() : null,
                        'time_offset' => $image->time_offset,
                    ];
                })->toArray();
            }
        }

        // Fallback a imagen por defecto de la casa
        if ($house->default_image_path) {
            return [[
                'type' => 'image',
                'url' => asset('storage/'.$house->default_image_path),
                'youtube_id' => null,
                'time_offset' => 5.0,
            ]];
        }

        return [];
    }

    public function getEventTitle(): ?string
    {
        return $this->getCurrentEvent()?->title;
    }

    public function getEventDescription(): ?string
    {
        return $this->getCurrentEvent()?->description;
    }
}; ?>

<div wire:poll.60s="refresh">
    @if($isAdmin)
        <div class="grid gap-4 md:grid-cols-3 mb-4">
            @foreach($houses as $house)
                @php
                    $images = $this->getEventImages($house);
                    $title = $this->getEventTitle();
                @endphp

                <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-black"
                     wire:ignore
                     x-data="mediaSlideshow(@js($images), 'yt-admin-{{ $house->id }}')"
                     @refresh-slideshow.window="currentIndex = 0; scheduleNext()">

                    @if(count($images) > 0)
                        {{-- Slideshow de medios --}}
                        <template x-for="(item, index) in media" :key="index">
                            <div class="absolute inset-0 transition-opacity duration-500"
                                 :class="{ 'opacity-100 z-[1]': index === currentIndex, 'opacity-0 z-0': index !== currentIndex }">
                                {{-- Imagen --}}
                                <img x-show="item.type === 'image'"
                                     :src="item.url"
                                     :alt="'{{ $title ?? $house->name }}'"
                                     class="w-full h-full object-cover">
                                {{-- Video container - siempre presente para videos --}}
                                <div x-show="item.type === 'video'"
                                     :id="slideshowId + '-container-' + index"
                                     class="w-full h-full">
                                </div>
                            </div>
                        </template>

                        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-4 z-10">
                            <h3 class="text-lg font-bold text-white">{{ $house->name }}</h3>
                            @if($title)
                                <p class="text-sm text-white/90">{{ $title }}</p>
                            @else
                                <p class="text-sm text-white/70">Sin evento activo</p>
                            @endif
                        </div>
                    @else
                        {{-- Fallback sin medios --}}
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
                $images = $this->getEventImages($house);
                $title = $this->getEventTitle();
                $description = $this->getEventDescription();
            @endphp

            <div class="relative w-full aspect-[16/9] overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-black mb-4"
                 wire:ignore
                 x-data="mediaSlideshow(@js($images), 'yt-user-{{ $house->id }}')"
                 @refresh-slideshow.window="currentIndex = 0; scheduleNext()">

                @if(count($images) > 0)
                    {{-- Slideshow de medios --}}
                    <template x-for="(item, index) in media" :key="index">
                        <div class="absolute inset-0 transition-opacity duration-500"
                             :class="{ 'opacity-100 z-[1]': index === currentIndex, 'opacity-0 z-0': index !== currentIndex }">
                            {{-- Imagen --}}
                            <img x-show="item.type === 'image'"
                                 :src="item.url"
                                 :alt="'{{ $title ?? $house->name }}'"
                                 class="w-full h-full object-cover">
                            {{-- Video container - siempre presente para videos --}}
                            <div x-show="item.type === 'video'"
                                 :id="slideshowId + '-container-' + index"
                                 class="w-full h-full">
                            </div>
                        </div>
                    </template>

                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/90 to-transparent p-6 z-10">
                        <h2 class="text-3xl font-bold text-white mb-2">{{ $title ?? $house->name }}</h2>
                        @if($description)
                            <p class="text-base text-white/90">{{ $description }}</p>
                        @endif
                    </div>
                @else
                    {{-- Fallback sin medios --}}
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
