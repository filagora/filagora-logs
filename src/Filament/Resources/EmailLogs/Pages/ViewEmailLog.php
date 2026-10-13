<?php

declare(strict_types=1);

namespace Filagora\Logs\Filament\Resources\EmailLogs\Pages;

use Filagora\Logs\Filament\Resources\EmailLogs\EmailLogResource;
use Filament\Resources\Pages\ViewRecord;

class ViewEmailLog extends ViewRecord
{
    protected static string $resource = EmailLogResource::class;
}
