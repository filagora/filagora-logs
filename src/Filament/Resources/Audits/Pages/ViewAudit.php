<?php

declare(strict_types=1);

namespace ForumKit\Logs\Filament\Resources\Audits\Pages;

use Filament\Resources\Pages\ViewRecord;
use ForumKit\Logs\Filament\Resources\Audits\AuditResource;

class ViewAudit extends ViewRecord
{
    protected static string $resource = AuditResource::class;
}
