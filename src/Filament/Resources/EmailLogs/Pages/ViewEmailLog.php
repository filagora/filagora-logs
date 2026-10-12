<?php

declare(strict_types=1);

namespace ForumKit\Logs\Filament\Resources\EmailLogs\Pages;

use Filament\Resources\Pages\ViewRecord;
use ForumKit\Logs\Filament\Resources\EmailLogs\EmailLogResource;

class ViewEmailLog extends ViewRecord
{
    protected static string $resource = EmailLogResource::class;
}
