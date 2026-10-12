<?php

declare(strict_types=1);

namespace ForumKit\Logs\Filament\Resources\Audits\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use ForumKit\Logs\Models\Audit;
use Illuminate\Database\Eloquent\Model;

class AuditsTable
{
    public static function configure(Table $table): Table
    {
        $t = static fn (string $key): string => __("forumkit-logs::messages.audits.table.{$key}");

        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')->numeric()->sortable(),
                TextColumn::make('event')->badge()->sortable()->searchable(),
                TextColumn::make('auditable_type')
                    ->label($t('model'))
                    ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '-')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('auditable_id')->label($t('model_id'))->sortable()->searchable(),
                TextColumn::make('user_id')
                    ->label($t('user'))
                    ->getStateUsing(fn (Model $record): string => self::actor($record))
                    ->description(fn (Model $record): ?string => $record->getAttribute('user_id') ? 'ID: '.$record->getAttribute('user_id') : null)
                    ->sortable()
                    ->searchable(),
                TextColumn::make('ip_address')->toggleable()->searchable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('event')->options(fn (): array => Audit::query()->distinct()->orderBy('event')->pluck('event', 'event')->all()),
                SelectFilter::make('auditable_type')
                    ->label($t('model'))
                    ->options(fn (): array => Audit::query()->distinct()->orderBy('auditable_type')->pluck('auditable_type')->mapWithKeys(fn (string $type): array => [$type => class_basename($type)])->all()),
            ])
            ->recordActions([ViewAction::make()])
            ->recordActionsPosition(RecordActionsPosition::BeforeCells);
    }

    /**
     * The actor's display name when the user still exists, otherwise `User #id`.
     */
    public static function actor(Model $audit): string
    {
        $id = $audit->getAttribute('user_id');
        $user = $audit->getRelationValue('user');

        if (! $user instanceof Model) {
            return $id ? 'User #'.$id : '-';
        }

        foreach (['name', 'username', 'email'] as $attribute) {
            $value = $user->getAttribute($attribute);

            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return 'User #'.$id;
    }
}
