<?php

declare(strict_types=1);

namespace ForumKit\Logs\Models;

use ForumKit\Core\Support\Tables;
use OwenIt\Auditing\Models\Audit as BaseAudit;

/**
 * One recorded change, as owen-it/laravel-auditing writes it, in the package's prefixed table.
 */
class Audit extends BaseAudit
{
    public function getTable(): string
    {
        return Tables::name('audits');
    }
}
