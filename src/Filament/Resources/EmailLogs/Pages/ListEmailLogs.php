<?php

declare(strict_types=1);

namespace ForumKit\Logs\Filament\Resources\EmailLogs\Pages;

use Filament\Resources\Pages\ListRecords;
use ForumKit\Logs\Filament\Resources\EmailLogs\EmailLogResource;

class ListEmailLogs extends ListRecords
{
    protected static string $resource = EmailLogResource::class;
}
