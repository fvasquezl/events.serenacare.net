<x-filament-widgets::widget>
    <x-filament::section>
        {{-- @php
            $totalActiveEvents = $this->housesWithEvents->sum(fn($house) => $house->events->count());
        @endphp --}}

        <div class="space-y-4">
            <div class="text-center">
                {{-- <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ $totalActiveEvents }} {{ $totalActiveEvents === 1 ? 'Evento Activo' : 'Eventos Activos' }}
                </h3> --}}
            </div>

            {{-- @if ($totalActiveEvents > 0)
                <div class="space-y-2">
                    @foreach ($this->housesWithEvents as $house)
                        @if ($house->events->isNotEmpty())
                            @foreach ($house->events as $event)
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem; border: 1px solid #e5e7eb; border-radius: 0.5rem; margin-bottom: 0.5rem;">
                                    <div style="flex: 1;">
                                        <p style="font-weight: 600; margin: 0;">{{ $house->name }}</p>
                                        <p style="font-size: 0.875rem; color: #6b7280; margin: 0;">{{ $house->location }}</p>
                                    </div>
                                    <div style="flex: 1; text-align: right;">
                                        <p style="font-size: 0.875rem; margin: 0;">{{ $event->start_datetime->format('d M, H:i') }}</p>
                                        <p style="font-size: 0.875rem; color: #6b7280; margin: 0;">{{ $event->end_datetime->format('d M, H:i') }}</p>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    @endforeach
                </div>
            @else --}}
                {{-- <div class="py-4 text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">No hay eventos activos</p>
                </div>
            @endif --}}
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
