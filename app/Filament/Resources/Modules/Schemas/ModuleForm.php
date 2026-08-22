<?php

namespace App\Filament\Resources\Modules\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ModuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        Section::make('Module Information')
                            ->columnSpan(2)
                            ->columns(1)
                            ->description('Basic module details.')
                            ->schema([
                                TextInput::make('module_number')
                                    ->numeric()
                                    ->required(),
                            ]),

                        Section::make('Configuration')
                            ->columnSpan(1)
                            ->description('Assign categories and manage the module availability.')
                            ->schema([
                                Toggle::make('is_active')
                                    ->default(true),

                                Select::make('categories')
                                    ->relationship('categories', 'name')
                                    ->multiple()
                                    ->preload(),
                            ]),
                    ]),
            ]);
    }
}
