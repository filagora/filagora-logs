<?php

declare(strict_types=1);

namespace Filagora\Logs\Filament\Resources\EmailLogs\Pages;

use Filament\Resources\Pages\ViewRecord;
use Filagora\Logs\Filament\Resources\EmailLogs\EmailLogResource;

class ViewEmailLog extends ViewRecord
{
    protected static string $resource = EmailLogResource::class;
}
