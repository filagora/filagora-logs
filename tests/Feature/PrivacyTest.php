<?php

declare(strict_types=1);

use Filagora\Core\Privacy\PrivacyContributorRegistry;
use Filagora\Core\Privacy\RetentionRuleRegistry;
use Filagora\Core\Settings\SettingsRegistry;
use Filagora\Logs\Models\EmailLog;
use Filagora\Logs\Tests\Support\TestUser;

function agedLog(array $attributes, int $days): EmailLog
{
    $log = EmailLog::create(array_merge(['status' => 'sent'], $attributes));
    $log->newQuery()->whereKey($log->id)->toBase()->update(['created_at' => now()->subDays($days)]);

    return $log;
}

it('contributes the log rows that list a person\'s address, and erases them', function () {
    $user = TestUser::make('Jane');
    $user->forceFill(['email' => 'Jane@Example.test'])->save();
    EmailLog::create(['to' => ['Jane@Example.test'], 'subject' => 'Welcome', 'status' => 'sent']);
    EmailLog::create(['to' => ['x@example.test'], 'cc' => ['jane@example.test'], 'subject' => 'Copy', 'status' => 'sent']);
    EmailLog::create(['to' => ['other@example.test'], 'subject' => 'Not hers', 'status' => 'sent']);

    $contributor = app(PrivacyContributorRegistry::class)->all()['email_logs'];

    expect(array_column($contributor->export($user), 'subject'))->toBe(['Welcome', 'Copy']);

    $contributor->erase($user);

    expect(EmailLog::query()->pluck('subject')->all())->toBe(['Not hers']);
});

it('never exports or erases the whole log for a user with no address', function () {
    $user = TestUser::make('Nobody');
    $user->forceFill(['email' => ''])->save();
    EmailLog::create(['to' => ['a@example.test'], 'status' => 'sent']);

    $contributor = app(PrivacyContributorRegistry::class)->all()['email_logs'];

    expect($contributor->export($user))->toBe([]);

    $contributor->erase($user);
    expect(EmailLog::count())->toBe(1);
});

it('prunes rows older than the retention setting, 60 days by default', function () {
    $old = agedLog(['to' => ['a@example.test']], 61);
    $kept = agedLog(['to' => ['b@example.test']], 59);
    $rule = app(RetentionRuleRegistry::class)->all()['logs.email_logs'];

    expect(app(SettingsRegistry::class)->integer('mail_log_retention_days'))->toBe(60)
        ->and($rule->prune(true))->toBe(1)
        ->and(EmailLog::count())->toBe(2)
        ->and($rule->prune(false))->toBe(1)
        ->and(EmailLog::pluck('id')->all())->toBe([$kept->id])
        ->and($old->exists)->toBeTrue();
});

it('follows the setting, and keeps everything when it is zero', function () {
    agedLog(['to' => ['a@example.test']], 20);
    $rule = app(RetentionRuleRegistry::class)->all()['logs.email_logs'];

    app(SettingsRegistry::class)->set('mail_log_retention_days', 10);
    expect($rule->prune(true))->toBe(1);

    app(SettingsRegistry::class)->set('mail_log_retention_days', 0);
    expect($rule->prune(false))->toBe(0)->and(EmailLog::count())->toBe(1);
});
