<?php

use App\Models\House;
use Livewire\Volt\Component;

use function Livewire\Volt\layout;

layout('components.layouts.display');

new class extends Component
{
    public string $slug;

    public ?House $house = null;

    public $activeEvent = null;

    public function mount(string $slug): void
    {
        $this->slug = $slug;
        $this->loadHouseData();
    }

    public function loadHouseData(): void
    {
        $this->house = House::where('slug', $this->slug)->with('events')->firstOrFail();
        $this->activeEvent = $this->house->active_event;
    }

    public function getListeners(): array
    {
        if (! $this->house) {
            return [];
        }

        return [
            "echo:house.{$this->house->id},.event.created" => 'refreshEvent',
        ];
    }

    public function refreshEvent(): void
    {
        $this->loadHouseData();
    }
}; ?>

<div x-data="{ refreshInterval: null }" x-init="
    // Auto-refresh every 60 seconds as fallback
    refreshInterval = setInterval(() => {
        $wire.loadHouseData();
    }, 60000);

    // Cleanup on component destroy
    $cleanup = () => clearInterval(refreshInterval);
" class="relative w-screen h-screen flex items-center justify-center">

    @if($activeEvent && $activeEvent->image_path)
        {{-- Active Event Image --}}
        <div class="absolute inset-0">
            <img
                src="{{ Storage::url($activeEvent->image_path) }}"
                alt="{{ $activeEvent->title }}"
                class="w-full h-full object-cover"
            />
            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-8">
                <h1 class="text-4xl md:text-6xl font-bold text-white drop-shadow-lg">
                    {{ $activeEvent->title }}
                </h1>
            </div>
        </div>
    @elseif($house->default_image_path)
        {{-- Default House Image --}}
        <div class="absolute inset-0">
            <img
                src="{{ Storage::url($house->default_image_path) }}"
                alt="{{ $house->name }}"
                class="w-full h-full object-cover"
            />
        </div>
    @else
        {{-- Fallback: House Name & Location --}}
        <div class="text-center">
            <h1 class="text-6xl md:text-8xl font-bold mb-4">{{ $house->name }}</h1>
            @if($house->location)
                <p class="text-2xl md:text-4xl text-gray-400">{{ $house->location }}</p>
            @endif
        </div>
    @endif

</div>

@script
<script>
    // Optional: Add additional WebSocket connection handling here
    console.log('House Events Display initialized for house ID:', @js($house?->id));
</script>
@endscript
