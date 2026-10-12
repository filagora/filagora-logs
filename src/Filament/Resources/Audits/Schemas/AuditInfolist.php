<?php

declare(strict_types=1);

namespace ForumKit\Logs\Filament\Resources\Audits\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use ForumKit\Logs\Filament\Resources\Audits\Tables\AuditsTable;
use Illuminate\Database\Eloquent\Model;

class AuditInfolist
{
    public static function configure(Schema $schema): Schema
    {
        $t = static fn (string $key): string => __("forumkit-logs::messages.audits.infolist.{$key}");

        return $schema->components([
            Section::make($t('audit'))
                ->schema([
                    TextEntry::make('id'),
                    TextEntry::make('event'),
                    TextEntry::make('auditable_type')
                        ->label(__('forumkit-logs::messages.audits.table.model'))
                        ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '-'),
                    TextEntry::make('auditable_id')->label(__('forumkit-logs::messages.audits.table.model_id')),
                    TextEntry::make('user_type')->label($t('actor_type'))->placeholder('-'),
                    TextEntry::make('user_id')
                        ->label($t('actor'))
                        ->formatStateUsing(fn (mixed $state, Model $record): string => $state ? AuditsTable::actor($record).' (ID: '.$state.')' : '-')
                        ->placeholder('-'),
                    TextEntry::make('url')->placeholder('-')->columnSpanFull(),
                    TextEntry::make('ip_address')->placeholder('-'),
                    TextEntry::make('user_agent')->placeholder('-')->columnSpanFull(),
                    TextEntry::make('created_at')->dateTime(),
                    TextEntry::make('updated_at')->dateTime(),
                ])
                ->columns(2),
            Section::make($t('changes'))
                ->schema([
                    TextEntry::make('old_values')->label($t('old_values'))->state(fn (Model $record): string => self::payload($record->getAttribute('old_values')))->columnSpanFull()->copyable(),
                    TextEntry::make('new_values')->label($t('new_values'))->state(fn (Model $record): string => self::payload($record->getAttribute('new_values')))->columnSpanFull()->copyable(),
                ])
                ->columns(1),
        ]);
    }

    private static function payload(mixed $state): string
    {
        if (is_string($state) && trim($state) !== '') {
            return $state;
        }

        if (is_array($state)) {
            return (string) json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        return '{}';
    }
}
