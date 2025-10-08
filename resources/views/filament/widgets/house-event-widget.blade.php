<x-filament-widgets::widget>
    <x-filament::section>
        @if ($this->house)
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold">{{ $this->house->name }}</h3>
                <span class="text-sm text-gray-500">{{ $this->house->location }}</span>
            </div>

            @if ($this->activeEvent)
                <div class="space-y-3">
                    @if ($this->activeEvent->image_path)
                        <div class="aspect-video w-full overflow-hidden rounded-lg bg-gray-100">
                            <img
                                src="{{ Storage::url($this->activeEvent->image_path) }}"
                                alt="{{ $this->activeEvent->title }}"
                                class="h-full w-full object-cover"
                            >
                        </div>
                    @endif

                    <div>
                        <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ $this->activeEvent->title }}</h4>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $this->activeEvent->description }}</p>
                    </div>

                    <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                        <span>{{ $this->activeEvent->start_datetime->format('d M, H:i') }}</span>
                        <span>-</span>
                        <span>{{ $this->activeEvent->end_datetime->format('d M, H:i') }}</span>
                    </div>
                </div>
            @else
                <div class="py-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No hay ningún evento activo</p>
                </div>
            @endif
        </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>