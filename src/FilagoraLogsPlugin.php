<?php

declare(strict_types=1);

namespace Filagora\Logs;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filagora\Logs\Filament\Resources\EmailLogs\EmailLogResource;

class FilagoraLogsPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'filagora-logs';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            EmailLogResource::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
