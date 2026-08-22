<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        Section::make('Category Information')
                            ->columnSpan(2)
                            ->columns(2)
                            ->description('Information used to identify and generate tickets.')
                            ->schema([
                                TextInput::make('name')
                                    ->required(),
                                TextInput::make('prefix')
                                    ->required()
                                    ->maxLength(10),
                            ]),
                        Section::make('Configuration')
                            ->columnSpan(1)
                            ->description('Manage the category availability.')
                            ->schema([
                                Toggle::make('is_active')
                                    ->default(true),
                            ]),
                    ]),
            ]);
    }
}