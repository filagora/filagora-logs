<?php

declare(strict_types=1);

namespace Filagora\Logs\Filament\Resources\EmailLogs\Tables;

use Filagora\Logs\Models\EmailLog;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EmailLogsTable
{
    public static function configure(Table $table): Table
    {
        $t = static fn (string $key): string => __("filagora-logs::messages.email_logs.table.{$key}");

        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')->numeric()->sortable(),
                TextColumn::make('status')->badge()->colors(['success' => 'sent', 'warning' => 'sending'])->sortable(),
                TextColumn::make('to')
                    ->listWithLineBreaks()
                    ->limitList(3)
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->where('to', 'like', '%'.$search.'%')),
                TextColumn::make('subject')
                    ->limit(60)
                    ->tooltip(fn (EmailLog $record): ?string => $record->subject)
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('notification_class')
                    ->label($t('notification'))
                    ->formatStateUsing(fn (?string $state): ?string => $state !== null ? class_basename($state) : null)
                    ->tooltip(fn (EmailLog $record): ?string => $record->notification_class)
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('mailer')->badge()->placeholder('-')->toggleable(),
                TextColumn::make('message_id')->label($t('message_id'))->placeholder('-')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sent_at')->dateTime()->placeholder('-')->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'sending' => __('filagora-logs::messages.email_logs.statuses.sending'),
                    'sent' => __('filagora-logs::messages.email_logs.statuses.sent'),
                ]),
            ])
            ->recordActions([ViewAction::make()])
            ->recordActionsPosition(RecordActionsPosition::BeforeCells);
    }
}
