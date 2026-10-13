<?php

declare(strict_types=1);

namespace Filagora\Logs\Services;

use Filagora\Core\Settings\SettingsRegistry;
use Filagora\Logs\Models\EmailLog;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Throwable;

/**
 * Records outgoing mail when the `mail_log_sending` setting is on: a row when a message starts
 * sending, completed when it has been sent. Logging never blocks the mail itself, so every
 * call swallows its own failure and reports it to the log.
 */
class EmailActivityLog
{
    public const SETTING = 'mail_log_sending';

    private const CORRELATION_HEADER = 'X-Email-Log-Id';

    public function __construct(private readonly SettingsRegistry $settings) {}

    public function recordSending(MessageSending $event): void
    {
        try {
            if (! $this->enabled()) {
                return;
            }

            $correlationId = (string) Str::uuid();
            $event->message->getHeaders()->addTextHeader(self::CORRELATION_HEADER, $correlationId);

            EmailLog::query()->create(array_merge($this->attributes($event->message, $event->data), [
                'correlation_id' => $correlationId,
                'status' => 'sending',
            ]));
        } catch (Throwable $exception) {
            Log::warning('email_activity_log.record_sending_failed', ['exception' => $exception->getMessage()]);
        }
    }

    public function recordSent(MessageSent $event): void
    {
        try {
            if (! $this->enabled()) {
                return;
            }

            $correlationId = $event->message->getHeaders()->get(self::CORRELATION_HEADER)?->getBodyAsString();
            $attributes = array_merge($this->attributes($event->message, $event->data), [
                'status' => 'sent',
                'message_id' => $event->sent->getMessageId(),
                'sent_at' => now(),
            ]);

            $updated = $correlationId !== null
                && EmailLog::query()->where('correlation_id', $correlationId)->update($attributes);

            if (! $updated) {
                EmailLog::query()->create(array_merge($attributes, ['correlation_id' => $correlationId]));
            }
        } catch (Throwable $exception) {
            Log::warning('email_activity_log.record_sent_failed', ['exception' => $exception->getMessage()]);
        }
    }

    private function enabled(): bool
    {
        return $this->settings->boolean(self::SETTING);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function attributes(Email $message, array $data): array
    {
        return [
            'mailer' => $data['mailer'] ?? null,
            'to' => $this->addresses($message->getTo()),
            'cc' => $this->addresses($message->getCc()),
            'bcc' => $this->addresses($message->getBcc()),
            'subject' => $message->getSubject(),
            'notification_class' => $data['__laravel_notification'] ?? null,
        ];
    }

    /**
     * @param  array<int, Address>  $addresses
     * @return list<string>
     */
    private function addresses(array $addresses): array
    {
        return array_map(static fn (Address $address): string => $address->getAddress(), array_values($addresses));
    }
}
