<?php

declare(strict_types=1);

namespace Filagora\Logs;

use Filagora\Core\Modules\Module;
use Filagora\Core\Modules\ModuleRegistry;
use Filagora\Core\Navigation\NavigationGroupDefinition;
use Filagora\Core\Navigation\NavigationGroupRegistry;
use Filagora\Core\Privacy\PrivacyContributorRegistry;
use Filagora\Core\Privacy\RetentionRuleRegistry;
use Filagora\Core\Privacy\TableRetentionRule;
use Filagora\Core\Schema\PackageSchema;
use Filagora\Core\Schema\SchemaRegistry;
use Filagora\Core\Settings\SettingDefinition;
use Filagora\Core\Settings\SettingsRegistry;
use Filagora\Core\Settings\SettingType;
use Filagora\Core\Support\GateAbilities;
use Filagora\Core\Support\Tables;
use Filagora\Entitlements\Registry\Definition;
use Filagora\Entitlements\Registry\EntitlementRegistry;
use Filagora\Logs\Privacy\EmailLogContributor;
use Filagora\Logs\Services\EmailActivityLog;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class LogsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/filagora-logs.php', 'filagora-logs');

        $this->app->singleton(EmailActivityLog::class);
    }

    public function boot(): void
    {
        $this->app->make(ModuleRegistry::class)->register(
            new Module('logs', 'Filagora Logs', 'filagora/filagora-logs', requires: ['core']),
        );

        $this->app->make(SchemaRegistry::class)->register(
            PackageSchema::fromFile('logs', __DIR__.'/../database/schema.php', __DIR__.'/../database/migrations'),
        );

        $this->registerNavigationGroup();
        $this->declareEntitlements();
        $this->defineGates();
        $this->declareSettings();
        $this->registerPrivacy();

        Event::listen(MessageSending::class, [EmailActivityLog::class, 'recordSending']);
        Event::listen(MessageSent::class, [EmailActivityLog::class, 'recordSent']);

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'filagora-logs');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/filagora-logs.php' => config_path('filagora-logs.php'),
            ], 'filagora-logs-config');
        }
    }

    private function registerNavigationGroup(): void
    {
        $this->app->make(NavigationGroupRegistry::class)->register(new NavigationGroupDefinition(
            key: 'logs',
            label: fn (): string => __('filagora-logs::messages.navigation_groups.logs'),
            icon: 'heroicon-o-clipboard-document-list',
            sort: 90,
        ));
    }

    /**
     * With `filagora-entitlements` installed, the key the ability maps to is declared so
     * `filagora:entitlements:sync` creates its definition. Without it there is nothing to
     * declare it to.
     */
    private function declareEntitlements(): void
    {
        if (! class_exists(EntitlementRegistry::class) || ! $this->app->bound(EntitlementRegistry::class)) {
            return;
        }

        $registry = $this->app->make(EntitlementRegistry::class);

        foreach (GateAbilities::keys('filagora-logs.gates') as $entitlement) {
            $registry->define(new Definition($entitlement, wildcardChildren: false));
        }
    }

    private function defineGates(): void
    {
        GateAbilities::define('filagora-logs.gates');
    }

    /**
     * What the email log contributes to export, erasure and retention, for the compliance package
     * (or any host) to run. Nothing here needs that package installed.
     */
    private function registerPrivacy(): void
    {
        $this->app->make(PrivacyContributorRegistry::class)->register(new EmailLogContributor);

        $this->app->make(RetentionRuleRegistry::class)->register(new TableRetentionRule(
            'logs.email_logs',
            Tables::name('email_logs'),
            'created_at',
            fn (): int => $this->app->make(SettingsRegistry::class)->integer(EmailActivityLog::RETENTION_SETTING, 60),
        ));
    }

    private function declareSettings(): void
    {
        $settings = $this->app->make(SettingsRegistry::class);
        $this->declareRetentionSetting($settings);

        $settings->define(new SettingDefinition(
            EmailActivityLog::SETTING,
            SettingType::Boolean,
            false,
            label: 'Log Email Sending Activity',
            description: 'When enabled, every outgoing email is recorded in the Email Logs resource (recipients, subject, notification class, and delivery status).',
        ));
    }

    private function declareRetentionSetting(SettingsRegistry $settings): void
    {
        $settings->define(new SettingDefinition(
            EmailActivityLog::RETENTION_SETTING,
            SettingType::Integer,
            60,
            label: 'Email Log Retention Days',
            description: 'How many days email log rows are kept before the privacy prune deletes them. Zero keeps them.',
        ));
    }
}
