<x-filament-widgets::widget>
    @php
        $activeEvents = $this->activeEvents;
        $totalActiveEvents = $activeEvents->count();
    @endphp

    <x-filament::section>
        <x-slot name="heading">
            Eventos Activos
        </x-slot>

        @if ($totalActiveEvents > 0)
            <x-slot name="headerEnd">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-success-100 dark:bg-success-500/20 px-2.5 py-1 text-xs font-medium text-success-700 dark:text-success-300">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success-500 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-success-500"></span>
                    </span>
                    {{ $totalActiveEvents }} en vivo
                </span>
            </x-slot>
        @endif

        @if ($totalActiveEvents > 0)
            <div class="fi-ta-content relative divide-y divide-gray-200 overflow-x-auto dark:divide-white/10 dark:border-t-white/10 rounded-lg border border-gray-200 dark:border-white/10">
                <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr>
                            {{-- Evento: Mantener a la izquierda --}}
                            <th class="fi-ta-header-cell px-4 py-3 text-start text-sm font-semibold text-gray-950 dark:text-white" style="width: 40%;">
                                Evento
                            </th>
                            {{-- INICIO: Alineado a la izquierda --}}
                            <th class="fi-ta-header-cell px-4 py-3 **text-start** text-sm font-semibold text-gray-950 dark:text-white" style="width: 15%;">
                                Inicio
                            </th>
                            {{-- FIN: Alineado a la izquierda --}}
                            <th class="fi-ta-header-cell px-4 py-3 **text-start** text-sm font-semibold text-gray-950 dark:text-white" style="width: 15%;">
                                Fin
                            </th>
                            {{-- PROGRESO: Alineado a la izquierda --}}
                            <th class="fi-ta-header-cell px-4 py-3 **text-start** text-sm font-semibold text-gray-950 dark:text-white" style="width: 20%;">
                                Progreso
                            </th>
                            {{-- ESTADO: Alineado a la izquierda --}}
                            <th class="fi-ta-header-cell px-4 py-3 **text-start** text-sm font-semibold text-gray-950 dark:text-white" style="width: 10%;">
                                Estado
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
                        @foreach ($activeEvents as $event)
                            @php
                                $totalDuration = $event->start_datetime->diffInMinutes($event->end_datetime);
                                $elapsed = $event->start_datetime->diffInMinutes(now());
                                $progress = $totalDuration > 0 ? min(100, ($elapsed / $totalDuration) * 100) : 0;
                            @endphp
                            <tr class="fi-ta-row hover:bg-gray-50 dark:hover:bg-white/5 transition">
                                {{-- Evento --}}
                                <td class="fi-ta-cell px-4 py-3">
                                    <div class="text-sm font-medium text-gray-950 dark:text-white">{{ $event->title }}</div>
                                    @if ($event->description)
                                        <div class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ Str::limit($event->description, 50) }}</div>
                                    @endif
                                </td>
                                {{-- INICIO (Body): text-start (predeterminado) --}}
                                <td class="fi-ta-cell px-4 py-3 text-sm text-gray-950 dark:text-white **text-start**">
                                    {{ $event->start_datetime->format('d/m/Y H:i') }}
                                </td>
                                {{-- FIN (Body): text-start (predeterminado) --}}
                                <td class="fi-ta-cell px-4 py-3 text-sm text-gray-950 dark:text-white **text-start**">
                                    {{ $event->end_datetime->format('d/m/Y H:i') }}
                                </td>
                                {{-- PROGRESO (Body): Quitamos justify-center y text-center para alinear a la izquierda --}}
                                <td class="fi-ta-cell px-4 py-3">
                                    <div class="flex items-center **justify-start** gap-2"> {{-- CAMBIADO a justify-start --}}
                                        <div class="w-20 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                            <div
                                                class="h-full rounded-full @if($progress < 50) bg-success-500 @elseif($progress < 80) bg-warning-500 @else bg-danger-500 @endif"
                                                style="width: {{ $progress }}%"
                                            ></div>
                                        </div>
                                        <span class="text-sm text-gray-600 dark:text-gray-400 w-8 **text-start**">{{ number_format($progress, 0) }}%</span> {{-- CAMBIADO a text-start --}}
                                    </div>
                                </td>
                                {{-- ESTADO (Body): text-start (predeterminado) --}}
                                <td class="fi-ta-cell px-4 py-3 **text-start**">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-success-50 dark:bg-success-500/10 px-2 py-0.5 text-xs font-medium text-success-700 dark:text-success-400 ring-1 ring-inset ring-success-600/20">
                                        <span class="h-1.5 w-1.5 rounded-full bg-success-500"></span>
                                        Activo
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-12">
                <div class="rounded-full bg-gray-100 dark:bg-gray-800 p-3 mb-4">
                    <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                </div>
                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-1">No hay eventos activos</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center">Los eventos activos aparecerán aquí automáticamente.</p>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>