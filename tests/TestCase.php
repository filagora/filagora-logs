<?php

declare(strict_types=1);

namespace ForumKit\Logs\Tests;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Schemas\SchemasServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use ForumKit\Core\CoreServiceProvider;
use ForumKit\Entitlements\EntitlementsServiceProvider;
use ForumKit\Logs\LogsServiceProvider;
use ForumKit\Logs\Tests\Support\TestPanelProvider;
use ForumKit\Logs\Tests\Support\TestSupportServiceProvider;
use ForumKit\Logs\Tests\Support\TestUser;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use OwenIt\Auditing\AuditingServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            ActionsServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            BladeIconsServiceProvider::class,
            FilamentServiceProvider::class,
            FormsServiceProvider::class,
            InfolistsServiceProvider::class,
            LivewireServiceProvider::class,
            NotificationsServiceProvider::class,
            SchemasServiceProvider::class,
            SupportServiceProvider::class,
            TablesServiceProvider::class,
            WidgetsServiceProvider::class,
            CoreServiceProvider::class,
            EntitlementsServiceProvider::class,
            AuditingServiceProvider::class,
            LogsServiceProvider::class,
            TestSupportServiceProvider::class,
            TestPanelProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('forumkit.user_model', TestUser::class);
        $app['config']->set('auth.providers.users.model', TestUser::class);
        $app['config']->set('audit.console', true);
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('k', 32)));
    }
}
