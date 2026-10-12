<?php

declare(strict_types=1);

namespace ForumKit\Logs\Tests\Support;

use Filament\Panel;
use Filament\PanelProvider;
use ForumKit\Core\ForumKitCorePlugin;
use ForumKit\Entitlements\ForumKitEntitlementsPlugin;
use ForumKit\Logs\ForumKitLogsPlugin;

class TestPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->plugin(ForumKitCorePlugin::make())
            ->plugin(ForumKitEntitlementsPlugin::make())
            ->plugin(ForumKitLogsPlugin::make());
    }
}
