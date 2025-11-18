<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Event Information')
                    ->columns(2)
                    ->components([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),

                        DateTimePicker::make('start_datetime')
                            ->label('Start Date & Time')
                            ->required()
                            ->seconds(false)
                            ->native(false),

                        DateTimePicker::make('end_datetime')
                            ->label('End Date & Time')
                            ->required()
                            ->seconds(false)
                            ->native(false)
                            ->after('start_datetime'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->columnSpanFull(),
                    ]),

                Section::make('Imagenes para el Evento')
                    ->description('Agregue imágenes relacionadas con este evento en todas las casas (Excepto la casa seleccionada), y stablecer un desfase de tiempo para su visualización durante el evento.')
                    ->components([
                        Repeater::make('images')
                            ->relationship()
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
                                    ->columnSpanFull(),

                                Select::make('house_id')
                                    ->label('Except House')
                                    ->relationship('house', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Select which house this image belongs to'),

                                TextInput::make('time_offset')
                                    ->label('Time Offset (seconds)')
                                    ->numeric()
                                    ->default(0.1)
                                    ->required()
                                    ->helperText('Time in seconds when this image should be displayed')
                                    ->minValue(0.1)
                                    ->step(0.1),
                            ])
                            ->columns(2)
                            ->orderColumn('order')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['house_id'] ? 'Image for House ID: '.$state['house_id'] : 'New Image')
                            ->addActionLabel('Add Image')
                            ->defaultItems(0)
                            ->cloneable(),
                    ]),
            ]);
    }
}
