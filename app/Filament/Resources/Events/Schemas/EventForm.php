<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->components([
                        Select::make('house_id')
                            ->relationship('house', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),

                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),

                        FileUpload::make('image_path')
                            ->label('Event Image')
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
            ]);
    }
}
