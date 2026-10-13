<?php

declare(strict_types=1);

namespace Filagora\Logs\Filament\Resources\EmailLogs\Pages;

use Filament\Resources\Pages\ListRecords;
use Filagora\Logs\Filament\Resources\EmailLogs\EmailLogResource;

class ListEmailLogs extends ListRecords
{
    protected static string $resource = EmailLogResource::class;
}
