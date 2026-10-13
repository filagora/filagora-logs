<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Filagora\Core\Models\Setting;
use Filagora\Core\Settings\SettingDefinitions;
use Filagora\Core\Settings\SettingsRegistry;
use Filagora\Core\Settings\SettingType;
use Filagora\Entitlements\Models\UserEntitlementGrant;
use Filagora\Logs\Filament\Resources\EmailLogs\EmailLogResource;
use Filagora\Logs\Filament\Resources\EmailLogs\Pages\ListEmailLogs;
use Filagora\Logs\Filament\Resources\EmailLogs\Pages\ViewEmailLog;
use Filagora\Logs\Models\EmailLog;
use Filagora\Logs\Tests\Support\Rebuild;
use Filagora\Logs\Tests\Support\TestUser;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Filament::setCurrentPanel('admin');
    config(['mail.default' => 'array']);
});

function enableEmailLogging(bool $on = true): void
{
    Setting::query()->updateOrCreate(['key' => 'mail_log_sending'], ['value' => $on ? '1' : '0', 'value_type' => 'boolean']);
    app(SettingsRegistry::class)->flush();
}

function sendTestMail(string $to = 'Jane@Example.test', string $subject = 'Hello'): void
{
    Mail::raw('body', fn ($message) => $message->to($to)->cc('cc@example.test')->subject($subject));
}

it('declares the setting off by default, with the live key and type', function () {
    $definition = app(SettingDefinitions::class)->get('mail_log_sending');

    expect($definition)->not->toBeNull()
        ->and($definition->type)->toBe(SettingType::Boolean)
        ->and($definition->default)->toBeFalse()
        ->and($definition->public)->toBeFalse()
        ->and($definition->label)->toBe('Log Email Sending Activity')
        ->and(app(SettingsRegistry::class)->boolean('mail_log_sending'))->toBeFalse();
});

it('records nothing while the setting is off', function () {
    sendTestMail();

    expect(EmailLog::count())->toBe(0);
});

it('records a sent message once: recipients, subject, mailer and a message id', function () {
    enableEmailLogging();

    sendTestMail('Jane@Example.test', 'Welcome');

    $log = EmailLog::sole();
    expect($log->status)->toBe('sent')
        ->and($log->to)->toBe(['Jane@Example.test'])
        ->and($log->cc)->toBe(['cc@example.test'])
        ->and($log->bcc)->toBe([])
        ->and($log->subject)->toBe('Welcome')
        ->and($log->mailer)->toBe('array')
        ->and($log->correlation_id)->not->toBeNull()
        ->and($log->sent_at)->not->toBeNull();
});

it('keeps one row per message across several sends', function () {
    enableEmailLogging();

    sendTestMail('a@example.test');
    sendTestMail('b@example.test');

    expect(EmailLog::count())->toBe(2)
        ->and(EmailLog::pluck('correlation_id')->unique())->toHaveCount(2);
});

it('never blocks the mail when logging fails', function () {
    enableEmailLogging();
    Schema::drop('filagora_email_logs');

    expect(fn () => sendTestMail())->not->toThrow(Throwable::class);

    Rebuild::emailLogs();
});

it('finds the rows for a recipient in to, cc or bcc, by the given spelling and its lowercase form, and nothing for a blank address', function () {
    EmailLog::create(['to' => ['Jane@Example.test'], 'status' => 'sent']);
    EmailLog::create(['to' => ['x@example.test'], 'cc' => ['jane@example.test'], 'status' => 'sent']);
    EmailLog::create(['to' => ['x@example.test'], 'bcc' => ['jane@example.test'], 'status' => 'sent']);
    EmailLog::create(['to' => ['other@example.test'], 'status' => 'sent']);

    // Addresses are stored as sent, so the given spelling and its lowercase form are matched.
    expect(EmailLog::forRecipient('jane@example.test')->count())->toBe(2)
        ->and(EmailLog::forRecipient('JANE@example.test')->count())->toBe(2)
        ->and(EmailLog::forRecipient('Jane@Example.test')->count())->toBe(3)
        ->and(EmailLog::forRecipient('  ')->count())->toBe(0)
        ->and(EmailLog::forRecipient('')->count())->toBe(0);
});

it('lists and shows email logs to a viewer, read-only', function () {
    $user = TestUser::make('Viewer');
    UserEntitlementGrant::create(['user_id' => $user->id, 'entitlement_key' => 'filagora.logs.view', 'grant_type' => 'manual']);
    actingAs($user);
    $log = EmailLog::create(['to' => ['jane@example.test'], 'subject' => 'Hi there', 'status' => 'sent', 'notification_class' => 'App\\Notifications\\Welcome']);

    Livewire::test(ListEmailLogs::class)
        ->assertCanSeeTableRecords([$log])
        ->assertSee('Welcome')
        ->searchTable('Hi there')
        ->assertCanSeeTableRecords([$log])
        ->filterTable('status', 'sending')
        ->assertCanNotSeeTableRecords([$log]);

    Livewire::test(ViewEmailLog::class, ['record' => $log->getKey()])->assertSee('jane@example.test');

    expect(EmailLogResource::canCreate())->toBeFalse()
        ->and(EmailLogResource::canEdit($log))->toBeFalse()
        ->and(EmailLogResource::canDelete($log))->toBeFalse();
});

it('keeps email logs closed to a user without the ability', function () {
    actingAs(TestUser::make('Nobody'));

    Livewire::test(ListEmailLogs::class)->assertForbidden();
});
