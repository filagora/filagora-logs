<?php

declare(strict_types=1);

namespace Filagora\Logs\Tests\Support;

use Filament\Panel;
use Filament\PanelProvider;
use Filagora\Core\FilagoraCorePlugin;
use Filagora\Entitlements\FilagoraEntitlementsPlugin;
use Filagora\Logs\FilagoraLogsPlugin;

class TestPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->plugin(FilagoraCorePlugin::make())
            ->plugin(FilagoraEntitlementsPlugin::make())
            ->plugin(FilagoraLogsPlugin::make());
    }
}
