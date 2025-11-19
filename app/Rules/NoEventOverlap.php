<?php

namespace App\Rules;

use App\Models\Event;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoEventOverlap implements ValidationRule
{
    protected $startDatetime;

    protected $endDatetime;

    protected $eventId;

    protected $isActive;

    /**
     * Create a new rule instance.
     *
     * @param  string  $startDatetime  Campo start_datetime
     * @param  string  $endDatetime  Campo end_datetime
     * @param  bool  $isActive  Si el evento está activo
     * @param  int|null  $eventId  ID del evento actual (para excluirlo al editar)
     */
    public function __construct($startDatetime, $endDatetime, $isActive = true, $eventId = null)
    {
        $this->startDatetime = $startDatetime;
        $this->endDatetime = $endDatetime;
        $this->isActive = $isActive;
        $this->eventId = $eventId;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Si no tenemos ambas fechas, no podemos validar el traslape
        if (! $this->startDatetime || ! $this->endDatetime) {
            return;
        }

        // Solo validar traslapes si el evento que estamos creando/editando está activo
        if (! $this->isActive) {
            return;
        }

        // Buscar eventos ACTIVOS que se traslapen
        $overlappingEvents = Event::where('is_active', true)
            ->where(function ($query) {
                // El evento se traslapa si:
                // 1. El inicio está entre el inicio y fin de otro evento
                $query->whereBetween('start_datetime', [$this->startDatetime, $this->endDatetime])
                    // 2. El fin está entre el inicio y fin de otro evento
                    ->orWhereBetween('end_datetime', [$this->startDatetime, $this->endDatetime])
                    // 3. El evento engloba completamente a otro evento
                    ->orWhere(function ($q) {
                        $q->where('start_datetime', '<=', $this->startDatetime)
                            ->where('end_datetime', '>=', $this->endDatetime);
                    });
            });

        // Excluir el evento actual si estamos editando
        if ($this->eventId) {
            $overlappingEvents->where('id', '!=', $this->eventId);
        }

        // Si hay eventos activos traslapados, fallar la validación
        if ($overlappingEvents->exists()) {
            $event = $overlappingEvents->first();
            $fail("Ya existe otro evento activo en esas fechas: '{$event->title}' ({$event->start_datetime->format('d/m/Y H:i')} - {$event->end_datetime->format('d/m/Y H:i')}). Para crear este evento, primero desactiva el evento existente o desactiva este nuevo evento.");
        }
    }
}
