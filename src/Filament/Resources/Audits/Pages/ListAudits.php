<?php

declare(strict_types=1);

namespace ForumKit\Logs\Filament\Resources\Audits\Pages;

use Filament\Resources\Pages\ListRecords;
use ForumKit\Logs\Filament\Resources\Audits\AuditResource;

class ListAudits extends ListRecords
{
    protected static string $resource = AuditResource::class;
}
