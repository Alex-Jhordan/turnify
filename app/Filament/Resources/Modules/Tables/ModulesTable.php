<?php

namespace App\Filament\Resources\Modules\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ModulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('module_number')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('categories.name')
                    ->badge()
                    ->separator(',')
                    ->placeholder('—'),
                TextColumn::make('currentUser.name')
                    ->label('Current user')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->filters([
                TernaryFilter::make('is_active')
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
