<?php

declare(strict_types=1);

namespace ForumKit\Logs\Tests\Support;

use Illuminate\Support\Facades\Schema;

final class Rebuild
{
    public static function emailLogs(): void
    {
        $migration = require __DIR__.'/../../database/migrations/2026_10_12_000000_create_forumkit_log_tables.php';

        Schema::dropIfExists('forumkit_email_logs');
        $migration->up();
    }
}
