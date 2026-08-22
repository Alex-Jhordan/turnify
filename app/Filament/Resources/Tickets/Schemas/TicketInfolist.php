<?php

namespace App\Filament\Resources\Tickets\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TicketInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        Group::make()
                            ->columnSpan(2)
                            ->schema([
                                Section::make('Ticket information')
                                    ->description('General details of the ticket and classification')
                                    ->columns(2)
                                    ->schema([
                                        TextEntry::make('ticket_code')
                                            ->label('Code')
                                            ->weight('bold'),

                                        TextEntry::make('category.name')
                                            ->label('Category')
                                            ->placeholder('-'),

                                        TextEntry::make('status')
                                            ->badge(),

                                        IconEntry::make('is_priority')
                                            ->boolean(),

                                        TextEntry::make('module.id')
                                            ->label('Module')
                                            ->placeholder('-'),

                                        TextEntry::make('user.name')
                                            ->label('Attended by')
                                            ->placeholder('-'),
                                    ]),
                                
                                Section::make('Assistant Information')
                                    ->description('Personal data and identification')
                                    ->columns(3)
                                    ->schema([
                                        TextEntry::make('name'),

                                        TextEntry::make('document_type')
                                            ->badge(),

                                        TextEntry::make('document_number'),
                                    ]),
                            ]),

                        Group::make()
                            ->columnSpan(1)
                            ->schema([
                                Section::make('Tracking and Times')
                                    ->columns(2)
                                    ->description('Metrics and timestamps')
                                    ->schema([
                                        TextEntry::make('call_count')
                                            ->numeric(),

                                        TextEntry::make('called_at')
                                            ->dateTime()
                                            ->placeholder('-'),

                                        TextEntry::make('started_at')
                                            ->dateTime()
                                            ->placeholder('-'),

                                        TextEntry::make('ended_at')
                                            ->dateTime()
                                            ->placeholder('-'),

                                        TextEntry::make('cancelled_at')
                                            ->dateTime()
                                            ->placeholder('-'),
                                    ]),

                                Section::make('Sistema')
                                    ->schema([
                                        TextEntry::make('created_at')
                                            ->dateTime()
                                            ->placeholder('-'),

                                        TextEntry::make('updated_at')
                                            ->dateTime()
                                            ->placeholder('-'),
                                    ]),
                            ]),
                    ]),
        ]);
    }
}
