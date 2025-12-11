<?php

namespace App\Filament\Resources\Events\Schemas;

use App\Rules\NoEventOverlap;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Event Information')
                    ->columnSpanFull()
                    ->columns(2) // Establece 2 columnas para esta sección
                    ->schema([
                        // Orden de Tabulación 0: Title (Columna izquierda)
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnOrder(0),

                        // Orden de Tabulación 2: Description (Columna izquierda)
                        Textarea::make('description')
                            ->rows(3)
                            ->columnOrder(2),

                        // Orden de Tabulación 1: Start Date & Time (Columna derecha)
                        DateTimePicker::make('start_datetime')
                            ->label('Start Date & Time')
                            ->required()
                            ->seconds(false)
                            ->native(false)
                            ->columnOrder(1)
                            ->live()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('start_datetime', $state))
                            ->rules([
                                fn (Get $get, $record): NoEventOverlap => new NoEventOverlap(
                                    $get('start_datetime'),
                                    $get('end_datetime'),
                                    (bool) $get('is_active'),
                                    $record?->id
                                ),
                            ]),

                        // Orden de Tabulación 3: End Date & Time (Columna derecha)
                        DateTimePicker::make('end_datetime')
                            ->label('End Date & Time')
                            ->required()
                            ->seconds(false)
                            ->native(false)
                            ->columnOrder(3)
                            ->live()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('end_datetime', $state))
                            ->rules([
                                'after:start_datetime',
                                fn (Get $get, $record): NoEventOverlap => new NoEventOverlap(
                                    $get('start_datetime'),
                                    $get('end_datetime'),
                                    (bool) $get('is_active'),
                                    $record?->id
                                ),
                            ]),

                        // Orden de Tabulación 4: Active (Ocupa ambas columnas)
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->columnSpanFull()
                            ->columnOrder(4)
                            ->live()
                            ->helperText('Los eventos activos no pueden traslaparse con otros eventos activos. Desactiva este evento para permitir traslapes.'),
                    ]),

                Section::make('Medios para el Evento')
                    ->columnSpanFull()
                    ->description('Agregue imágenes o videos de YouTube relacionados con este evento. Puede seleccionar de cuáles casas excluir cada medio.')
                    ->components([
                        Repeater::make('images')
                            ->relationship()
                            ->schema([
                                ToggleButtons::make('type')
                                    ->label('Tipo de medio')
                                    ->options([
                                        'image' => 'Imagen',
                                        'video' => 'Video YouTube',
                                    ])
                                    ->icons([
                                        'image' => Heroicon::OutlinedPhoto,
                                        'video' => Heroicon::OutlinedVideoCamera,
                                    ])
                                    ->default('image')
                                    ->inline()
                                    ->required()
                                    ->live()
                                    ->columnSpanFull(),

                                Grid::make(2)
                                    ->schema([
                                        FileUpload::make('image_path')
                                            ->label('Imagen')
                                            ->image()
                                            ->imageEditor()
                                            ->disk('public')
                                            ->directory('events')
                                            ->visibility('public')
                                            ->maxSize(10240)
                                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg', 'image/webp'])
                                            ->downloadable()
                                            ->openable()
                                            ->previewable()
                                            ->columnSpan(1)
                                            ->visible(fn (Get $get): bool => $get('type') === 'image')
                                            ->required(fn (Get $get): bool => $get('type') === 'image'),

                                        TextInput::make('youtube_url')
                                            ->label('URL de YouTube')
                                            ->url()
                                            ->placeholder('https://www.youtube.com/watch?v=...')
                                            ->helperText('Pegue la URL del video de YouTube (formatos soportados: youtube.com/watch?v=, youtu.be/, youtube.com/embed/)')
                                            ->columnSpan(1)
                                            ->visible(fn (Get $get): bool => $get('type') === 'video')
                                            ->required(fn (Get $get): bool => $get('type') === 'video'),

                                        Grid::make(1)
                                            ->schema([
                                                Select::make('excludedHouses')
                                                    ->label('Excluir de Casas')
                                                    ->relationship('excludedHouses', 'name')
                                                    ->multiple()
                                                    ->searchable()
                                                    ->preload()
                                                    ->helperText('Seleccione las casas donde NO se mostrará este medio. Si no selecciona ninguna, se mostrará en todas las casas.'),

                                                TextInput::make('time_offset')
                                                    ->label('Duración (segundos)')
                                                    ->numeric()
                                                    ->default(5)
                                                    ->required(fn (Get $get): bool => $get('type') === 'image')
                                                    ->helperText('Tiempo en segundos que se mostrará esta imagen')
                                                    ->minValue(1)
                                                    ->step(1)
                                                    ->visible(fn (Get $get): bool => $get('type') === 'image'),
                                            ])
                                            ->columnSpan(1),
                                    ]),
                            ])
                            ->orderColumn('order')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(function (array $state): string {
                                $type = ($state['type'] ?? 'image') === 'video' ? 'Video' : 'Imagen';
                                if (! empty($state['excludedHouses'])) {
                                    $count = is_array($state['excludedHouses']) ? count($state['excludedHouses']) : 1;

                                    return "{$type} (Excluido de {$count} casa(s))";
                                }

                                return "{$type} (Visible en todas las casas)";
                            })
                            ->addActionLabel('Agregar Medio')
                            ->defaultItems(0)
                            ->cloneable(),
                    ]),
            ]);
    }
}
