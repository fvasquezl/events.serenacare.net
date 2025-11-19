<?php

use App\Models\House;
use Livewire\Volt\Component;

use function Livewire\Volt\layout;

layout('components.layouts.display');

new class extends Component
{
    public string $slug;

    public ?House $house = null;

    public function mount(string $slug): void
    {
        $this->slug = $slug;
        $this->loadHouseData();
    }

    public function loadHouseData(): void
    {
        $this->house = House::where('slug', $this->slug)->firstOrFail();
    }
}; ?>

<div class="relative w-screen h-screen flex items-center justify-center">

    @if($house->default_image_path)
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
    console.log('House Display initialized for house:', @js($house?->name));
</script>
@endscript
