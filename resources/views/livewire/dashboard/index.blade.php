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
        // Retornar todos los medios del evento para esta casa
        if (isset($house->eventImages) && $house->eventImages->isNotEmpty()) {
            return $house->eventImages->map(function ($image) {
                return [
                    'type' => $image->type ?? 'image',
                    'url' => $image->isImage() ? asset('storage/'.$image->image_path) : null,
                    'youtube_id' => $image->isVideo() ? $image->getYoutubeVideoId() : null,
                    'time_offset' => $image->time_offset,
                ];
            })->toArray();
        }

        // Fallback a imagen por defecto de la casa
        if ($house->default_image_path) {
            return [[
                'type' => 'image',
                'url' => asset('storage/'.$house->default_image_path),
                'youtube_id' => null,
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
                $title = $this->getEventTitle($house);
                $description = $this->getEventDescription($house);
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

    {{-- YouTube IFrame API y componente Alpine para slideshow --}}
    <script>
        // Cargar YouTube IFrame API
        if (!window.YT) {
            var tag = document.createElement('script');
            tag.src = 'https://www.youtube.com/iframe_api';
            var firstScriptTag = document.getElementsByTagName('script')[0];
            firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
        }

        // Storage global para players de YouTube
        window.ytPlayers = window.ytPlayers || {};

        // Callback cuando la API de YouTube está lista
        window.onYouTubeIframeAPIReady = function() {
            window.ytApiReady = true;
        };

        // Componente Alpine para slideshow de medios
        document.addEventListener('alpine:init', () => {
            Alpine.data('mediaSlideshow', (mediaItems, slideshowId) => ({
                media: mediaItems,
                currentIndex: 0,
                timer: null,
                slideshowId: slideshowId,
                currentPlayer: null,
                currentVideoIndex: null,

                init() {
                    // Usar $watch para detectar cambios en currentIndex
                    this.$watch('currentIndex', (newIndex) => {
                        this.handleIndexChange(newIndex);
                    });

                    // Iniciar con el primer elemento
                    this.handleIndexChange(0);
                },

                handleIndexChange(index) {
                    const current = this.media[index];
                    if (!current) return;

                    // Destruir player anterior si existe
                    if (this.currentPlayer) {
                        try {
                            this.currentPlayer.destroy();
                        } catch (e) {}
                        this.currentPlayer = null;
                        this.currentVideoIndex = null;
                    }

                    if (current.type === 'video' && current.youtube_id) {
                        // Esperar a que el DOM se actualice
                        this.$nextTick(() => {
                            this.createYouTubePlayer(current.youtube_id, index);
                        });
                    } else {
                        // Para imágenes, programar el siguiente
                        this.scheduleNext();
                    }
                },

                createYouTubePlayer(videoId, index) {
                    const containerId = this.slideshowId + '-container-' + index;
                    const container = document.getElementById(containerId);

                    if (!container) {
                        console.error('Container not found:', containerId);
                        // Reintentar después de un breve delay
                        setTimeout(() => this.createYouTubePlayer(videoId, index), 200);
                        return;
                    }

                    // Limpiar el contenedor
                    container.innerHTML = '';

                    // Crear div para el player
                    const playerDiv = document.createElement('div');
                    const playerId = this.slideshowId + '-player-' + index;
                    playerDiv.id = playerId;
                    playerDiv.style.width = '100%';
                    playerDiv.style.height = '100%';
                    container.appendChild(playerDiv);

                    const self = this;

                    const createPlayer = () => {
                        self.currentVideoIndex = index;
                        self.currentPlayer = new YT.Player(playerId, {
                            videoId: videoId,
                            width: '100%',
                            height: '100%',
                            playerVars: {
                                autoplay: 1,
                                mute: 1,
                                controls: 0,
                                rel: 0,
                                showinfo: 0,
                                modestbranding: 1,
                                playsinline: 1
                            },
                            events: {
                                onReady: function(event) {
                                    // Asegurarse de que el video empiece
                                    event.target.playVideo();
                                },
                                onStateChange: function(event) {
                                    // Estado 0 = video terminó
                                    if (event.data === YT.PlayerState.ENDED) {
                                        self.nextMedia();
                                    }
                                },
                                onError: function(event) {
                                    console.error('YouTube player error:', event.data);
                                    // En caso de error, pasar al siguiente
                                    self.nextMedia();
                                }
                            }
                        });
                    };

                    if (window.YT && window.YT.Player) {
                        createPlayer();
                    } else {
                        // Esperar a que la API esté lista
                        const checkApi = setInterval(() => {
                            if (window.YT && window.YT.Player) {
                                clearInterval(checkApi);
                                createPlayer();
                            }
                        }, 100);
                    }
                },

                scheduleNext() {
                    clearTimeout(this.timer);
                    const current = this.media[this.currentIndex];
                    if (!current) return;

                    // Solo programar timer para imágenes
                    // Los videos usan el evento onStateChange
                    if (current.type !== 'video') {
                        this.timer = setTimeout(() => {
                            this.nextMedia();
                        }, (current.time_offset || 5) * 1000);
                    }
                },

                nextMedia() {
                    clearTimeout(this.timer);
                    this.currentIndex = (this.currentIndex + 1) % this.media.length;
                }
            }));
        });
    </script>
</div>
