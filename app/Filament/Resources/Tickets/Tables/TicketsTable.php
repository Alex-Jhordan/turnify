<?php

namespace App\Filament\Resources\Tickets\Tables;

use App\Enums\TicketStatus;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class TicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ticket_code')
                    ->label('Ticket')
                    ->description(fn ($record) => $record->category?->name)
                    ->searchable([
                        'ticket_code',
                        'category.name',
                    ]),
                TextColumn::make('name')
                    ->label('Attendee')
                    ->description(fn ($record) => "{$record->document_type->value}: {$record->document_number}")
                    ->searchable(['name', 'document_type', 'document_number']),
                TextColumn::make('status')
                    ->badge(),
                IconColumn::make('is_priority')
                    ->boolean(),
                TextColumn::make('module.module_number')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('user.name')
                    ->searchable()
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(TicketStatus::class),
                TernaryFilter::make('is_priority'),
                SelectFilter::make('user_id')
                    ->relationship('user', 'name'),
            ])
            ->recordActions([
                ViewAction::make(),

                Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Cancel ticket')
                    ->modalDescription('Are you sure you want to cancel this ticket? This action cannot be undone.')
                    ->visible(fn ($record) => $record->status !== TicketStatus::Cancelled)
                    ->action(function ($record) {
                        $record->update([
                            'status' => TicketStatus::Cancelled,
                            'cancelled_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Ticket cancelled')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
