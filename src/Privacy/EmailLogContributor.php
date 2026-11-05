<?php

declare(strict_types=1);

namespace Filagora\Logs\Privacy;

use Filagora\Core\Contracts\PrivacyContributor;
use Filagora\Logs\Models\EmailLog;
use Illuminate\Database\Eloquent\Model;

/**
 * The email log rows that list a person's address, for the compliance package's export and
 * erasure. The address is the user's `email` attribute, read before the account is anonymised.
 */
final class EmailLogContributor implements PrivacyContributor
{
    public function key(): string
    {
        return 'email_logs';
    }

    public function export(Model $user): array
    {
        // A list of rows, not a keyed array as the contract documents: it has always been, and the
        // export format must not change. Newer PHPStan versions notice the difference.
        // @phpstan-ignore return.type
        return EmailLog::query()
            ->forRecipient($this->address($user))
            ->orderBy('id')
            ->get(['id', 'status', 'mailer', 'to', 'cc', 'bcc', 'subject', 'body', 'notification_class', 'sent_at', 'created_at'])
            ->toArray();
    }

    public function erase(Model $user): void
    {
        EmailLog::query()->forRecipient($this->address($user))->delete();
    }

    private function address(Model $user): string
    {
        return trim((string) $user->getAttribute('email'));
    }
}
