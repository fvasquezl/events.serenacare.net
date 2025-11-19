<?php

namespace App\Filament\Resources\Events\Tables;

use App\Models\Event;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                TextColumn::make('start_datetime')
                    ->label('Start')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),

                TextColumn::make('end_datetime')
                    ->label('End')
                    ->dateTime('M d, Y H:i')
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->sortable()
                    ->disabled(function ($record) {
                        // Si el evento ya está activo, siempre permitir desactivarlo
                        if ($record->is_active) {
                            return false;
                        }

                        // Si está desactivado, verificar si hay eventos activos que se traslapen
                        $overlappingEvent = Event::where('is_active', true)
                            ->where('id', '!=', $record->id)
                            ->where(function ($query) use ($record) {
                                $query->whereBetween('start_datetime', [$record->start_datetime, $record->end_datetime])
                                    ->orWhereBetween('end_datetime', [$record->start_datetime, $record->end_datetime])
                                    ->orWhere(function ($q) use ($record) {
                                        $q->where('start_datetime', '<=', $record->start_datetime)
                                            ->where('end_datetime', '>=', $record->end_datetime);
                                    });
                            })
                            ->first();

                        // Deshabilitar si hay un evento activo traslapado
                        return $overlappingEvent !== null;
                    })
                    ->tooltip(function ($record) {
                        // Si está activo, no mostrar tooltip
                        if ($record->is_active) {
                            return null;
                        }

                        // Buscar evento activo traslapado
                        $overlappingEvent = Event::where('is_active', true)
                            ->where('id', '!=', $record->id)
                            ->where(function ($query) use ($record) {
                                $query->whereBetween('start_datetime', [$record->start_datetime, $record->end_datetime])
                                    ->orWhereBetween('end_datetime', [$record->start_datetime, $record->end_datetime])
                                    ->orWhere(function ($q) use ($record) {
                                        $q->where('start_datetime', '<=', $record->start_datetime)
                                            ->where('end_datetime', '>=', $record->end_datetime);
                                    });
                            })
                            ->first();

                        if ($overlappingEvent) {
                            return "Bloqueado por: '{$overlappingEvent->title}' ({$overlappingEvent->start_datetime->format('d/m/Y H:i')} - {$overlappingEvent->end_datetime->format('d/m/Y H:i')})";
                        }

                        return 'Clic para activar';
                    }),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // SelectFilter::make('house')
                //     ->relationship('house', 'name')
                //     ->searchable()
                //     ->preload(),

                TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All events')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('start_datetime', 'desc');
    }
}
