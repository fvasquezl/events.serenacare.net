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
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

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

                Section::make('Imagenes para el Evento')
                    ->columnSpanFull()
                    ->description('Agregue imágenes relacionadas con este evento. Puede seleccionar de cuáles casas excluir cada imagen.')
                    ->components([
                        Repeater::make('images')
                            ->relationship()
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        FileUpload::make('image_path')
                                            ->label('Image')
                                            ->image()
                                            ->imageEditor()
                                            ->disk('public')
                                            ->directory('events')
                                            ->visibility('public')
                                            ->required()
                                            ->maxSize(10240)
                                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg', 'image/webp'])
                                            ->downloadable()
                                            ->openable()
                                            ->previewable()
                                            ->columnSpan(1),

                                        Grid::make(1)
                                            ->schema([
                                                Select::make('excludedHouses')
                                                    ->label('Exclude from Houses')
                                                    ->relationship('excludedHouses', 'name')
                                                    ->multiple()
                                                    ->searchable()
                                                    ->preload()
                                                    ->helperText('Seleccione las casas donde NO se mostrará esta imagen. Si no selecciona ninguna, se mostrará en todas las casas.'),

                                                TextInput::make('time_offset')
                                                    ->label('Time Offset (seconds)')
                                                    ->numeric()
                                                    ->default(1)
                                                    ->required()
                                                    ->helperText('Time in seconds when this image should be displayed')
                                                    ->minValue(1)
                                                    ->step(1),
                                            ])
                                            ->columnSpan(1),
                                    ]),
                            ])
                            ->orderColumn('order')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(function (array $state): string {
                                if (! empty($state['excludedHouses'])) {
                                    $count = is_array($state['excludedHouses']) ? count($state['excludedHouses']) : 1;

                                    return "Image (Excluded from {$count} house(s))";
                                }

                                return 'Image (Visible in all houses)';
                            })
                            ->addActionLabel('Add Image')
                            ->defaultItems(0)
                            ->cloneable(),
                    ]),
            ]);
    }
}
