<?php

declare(strict_types=1);

namespace ForumKit\Logs;

use Filament\Contracts\Plugin;
use Filament\Panel;
use ForumKit\Logs\Filament\Resources\Audits\AuditResource;
use ForumKit\Logs\Filament\Resources\EmailLogs\EmailLogResource;

class ForumKitLogsPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'forumkit-logs';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            AuditResource::class,
            EmailLogResource::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
