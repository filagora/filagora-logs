<?php

declare(strict_types=1);

namespace ForumKit\Logs;

use ForumKit\Core\Modules\Module;
use ForumKit\Core\Modules\ModuleRegistry;
use ForumKit\Core\Navigation\NavigationGroupDefinition;
use ForumKit\Core\Navigation\NavigationGroupRegistry;
use ForumKit\Core\Schema\PackageSchema;
use ForumKit\Core\Schema\SchemaRegistry;
use ForumKit\Core\Settings\SettingDefinition;
use ForumKit\Core\Settings\SettingsRegistry;
use ForumKit\Core\Settings\SettingType;
use ForumKit\Entitlements\Registry\Definition;
use ForumKit\Entitlements\Registry\EntitlementRegistry;
use ForumKit\Entitlements\Services\AuthorizationService;
use ForumKit\Logs\Services\EmailActivityLog;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class LogsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/forumkit-logs.php', 'forumkit-logs');

        $this->app->singleton(EmailActivityLog::class);
    }

    public function boot(): void
    {
        $this->app->make(ModuleRegistry::class)->register(
            new Module('logs', 'ForumKit Logs', 'zcwilt/forumkit-logs', requires: ['core', 'entitlements']),
        );

        $this->app->make(SchemaRegistry::class)->register(
            PackageSchema::fromFile('logs', __DIR__.'/../database/schema.php', __DIR__.'/../database/migrations'),
        );

        $this->registerNavigationGroup();
        $this->declareEntitlements();
        $this->defineGates();
        $this->declareSettings();

        Event::listen(MessageSending::class, [EmailActivityLog::class, 'recordSending']);
        Event::listen(MessageSent::class, [EmailActivityLog::class, 'recordSent']);

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'forumkit-logs');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/forumkit-logs.php' => config_path('forumkit-logs.php'),
            ], 'forumkit-logs-config');
        }
    }

    private function registerNavigationGroup(): void
    {
        $this->app->make(NavigationGroupRegistry::class)->register(new NavigationGroupDefinition(
            key: 'logs',
            label: fn (): string => __('forumkit-logs::messages.navigation_groups.logs'),
            icon: 'heroicon-o-clipboard-document-list',
            sort: 90,
        ));
    }

    private function declareEntitlements(): void
    {
        $registry = $this->app->make(EntitlementRegistry::class);

        foreach (config('forumkit-logs.gates', []) as $entitlement) {
            if (is_string($entitlement) && $entitlement !== '') {
                $registry->define(new Definition($entitlement, wildcardChildren: false));
            }
        }
    }

    private function defineGates(): void
    {
        foreach (config('forumkit-logs.gates', []) as $ability => $entitlement) {
            if (! is_string($ability) || ! is_string($entitlement)) {
                continue;
            }

            Gate::define($ability, fn (?Authenticatable $user): bool => $this->app->make(AuthorizationService::class)->allows($user, $entitlement));
        }
    }

    private function declareSettings(): void
    {
        $this->app->make(SettingsRegistry::class)->define(new SettingDefinition(
            EmailActivityLog::SETTING,
            SettingType::Boolean,
            false,
            label: 'Log Email Sending Activity',
            description: 'When enabled, every outgoing email is recorded in the Email Logs resource (recipients, subject, notification class, and delivery status).',
        ));
    }
}
