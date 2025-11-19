<?php

use App\Models\Event;
use App\Models\House;
use Livewire\Volt\Component;

new class extends Component {
    public $houses;
    public $isAdmin;
    public $currentEvent;

    public function mount(): void
    {
        $this->loadHouses();

        // dd($this->houses);
    }

    public function loadHouses(): void
    {
        $user = auth()->user();
        $this->isAdmin = is_null($user->house_id);

        // Obtener el evento global activo actual
        $this->currentEvent = Event::getCurrentEvent();

        if ($this->isAdmin) {
            // Admin users see all houses
            $this->houses = House::orderBy('name')->get();
        } else {
            // Regular users see only their assigned house
            $this->houses = House::where('id', $user->house_id)->get();
        }

        // Adjuntar imágenes del evento filtradas por cada casa
        if ($this->currentEvent) {
            foreach ($this->houses as $house) {
                $house->eventImages = $this->currentEvent->getImagesForHouse($house->id);
            }
        }
    }

    public function refresh(): void
    {
        $this->loadHouses();
    }

    public function getEventImages($house): array
    {
        // Retornar todas las imágenes del evento para esta casa
        if (isset($house->eventImages) && $house->eventImages->isNotEmpty()) {
            return $house->eventImages->map(function ($image) {
                return [
                    'url' => asset('storage/'.$image->image_path),
                    'time_offset' => $image->time_offset,
                ];
            })->toArray();
        }

        // Fallback a imagen por defecto de la casa
        if ($house->default_image_path) {
            return [[
                'url' => asset('storage/'.$house->default_image_path),
                'time_offset' => 5.0, // Duración por defecto
            ]];
        }

        return [];
    }

    public function getEventTitle($house): ?string
    {
        return $this->currentEvent?->title;
    }

    public function getEventDescription($house): ?string
    {
        return $this->currentEvent?->description;
    }
}; ?>

<div wire:poll.60s="refresh">
    @if($isAdmin)
        <div class="grid gap-4 md:grid-cols-3 mb-4">
            @foreach($houses as $house)
                @php
                    $images = $this->getEventImages($house);
                    $title = $this->getEventTitle($house);
                @endphp

                <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-black"
                     x-data="{
                         images: @js($images),
                         currentIndex: 0,
                         timer: null,
                         init() {
                             if (this.images.length > 1) {
                                 this.startSlideshow();
                             }
                         },
                         startSlideshow() {
                             this.scheduleNext();
                         },
                         scheduleNext() {
                             clearTimeout(this.timer);
                             const currentImage = this.images[this.currentIndex];
                             this.timer = setTimeout(() => {
                                 this.nextImage();
                             }, currentImage.time_offset * 1000);
                         },
                         nextImage() {
                             this.currentIndex = (this.currentIndex + 1) % this.images.length;
                             this.scheduleNext();
                         }
                     }"
                     @refresh-slideshow.window="currentIndex = 0; if (images.length > 1) startSlideshow()">

                    @if(count($images) > 0)
                        {{-- Slideshow de imágenes --}}
                        <template x-for="(image, index) in images" :key="index">
                            <img :src="image.url"
                                 :alt="'{{ $title ?? $house->name }}'"
                                 class="absolute inset-0 w-full h-full object-cover transition-opacity duration-500"
                                 :class="{ 'opacity-100': index === currentIndex, 'opacity-0': index !== currentIndex }"
                                 x-show="true">
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
                        {{-- Fallback sin imágenes --}}
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
                $title = $this->getEventTitle($house);
                $description = $this->getEventDescription($house);
            @endphp

            <div class="relative w-full aspect-[16/9] overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-black mb-4"
                 x-data="{
                     images: @js($images),
                     currentIndex: 0,
                     timer: null,
                     init() {
                         if (this.images.length > 1) {
                             this.startSlideshow();
                         }
                     },
                     startSlideshow() {
                         this.scheduleNext();
                     },
                     scheduleNext() {
                         clearTimeout(this.timer);
                         const currentImage = this.images[this.currentIndex];
                         this.timer = setTimeout(() => {
                             this.nextImage();
                         }, currentImage.time_offset * 1000);
                     },
                     nextImage() {
                         this.currentIndex = (this.currentIndex + 1) % this.images.length;
                         this.scheduleNext();
                     }
                 }"
                 @refresh-slideshow.window="currentIndex = 0; if (images.length > 1) startSlideshow()">

                @if(count($images) > 0)
                    {{-- Slideshow de imágenes --}}
                    <template x-for="(image, index) in images" :key="index">
                        <img :src="image.url"
                             :alt="'{{ $title ?? $house->name }}'"
                             class="absolute inset-0 w-full h-full object-cover transition-opacity duration-500"
                             :class="{ 'opacity-100': index === currentIndex, 'opacity-0': index !== currentIndex }"
                             x-show="true">
                    </template>

                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/90 to-transparent p-6 z-10">
                        <h2 class="text-3xl font-bold text-white mb-2">{{ $title ?? $house->name }}</h2>
                        @if($description)
                            <p class="text-base text-white/90">{{ $description }}</p>
                        @endif
                    </div>
                @else
                    {{-- Fallback sin imágenes --}}
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
