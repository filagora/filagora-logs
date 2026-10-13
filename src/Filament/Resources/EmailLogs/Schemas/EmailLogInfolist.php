<?php

declare(strict_types=1);

namespace Filagora\Logs\Filament\Resources\EmailLogs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmailLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        $t = static fn (string $key): string => __("filagora-logs::messages.email_logs.infolist.{$key}");

        return $schema->components([
            Section::make($t('message'))
                ->schema([
                    TextEntry::make('status')->badge(),
                    TextEntry::make('mailer')->badge()->placeholder('-'),
                    TextEntry::make('subject')->placeholder('-')->columnSpanFull(),
                    TextEntry::make('to')->label('To')->listWithLineBreaks()->placeholder('-'),
                    TextEntry::make('cc')->label('Cc')->listWithLineBreaks()->placeholder('-'),
                    TextEntry::make('bcc')->label('Bcc')->listWithLineBreaks()->placeholder('-'),
                    TextEntry::make('notification_class')->label($t('notification_class'))->placeholder('-')->columnSpanFull(),
                ])
                ->columns(2),
            Section::make($t('delivery'))
                ->schema([
                    TextEntry::make('message_id')->label(__('filagora-logs::messages.email_logs.table.message_id'))->copyable()->placeholder('-'),
                    TextEntry::make('correlation_id')->label($t('correlation_id'))->copyable()->placeholder('-'),
                    TextEntry::make('sent_at')->dateTime()->placeholder('-'),
                    TextEntry::make('created_at')->dateTime(),
                ])
                ->columns(2),
        ]);
    }
}
