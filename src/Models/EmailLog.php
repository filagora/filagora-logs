<?php

declare(strict_types=1);

namespace ForumKit\Logs\Models;

use ForumKit\Core\Support\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string|null $correlation_id
 * @property string $status
 * @property string|null $mailer
 * @property list<string> $to
 * @property list<string>|null $cc
 * @property list<string>|null $bcc
 * @property string|null $subject
 * @property string|null $notification_class
 * @property string|null $message_id
 * @property Carbon|null $sent_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class EmailLog extends Model
{
    protected $fillable = [
        'correlation_id',
        'status',
        'mailer',
        'to',
        'cc',
        'bcc',
        'subject',
        'notification_class',
        'message_id',
        'sent_at',
    ];

    public function getTable(): string
    {
        return Tables::name('email_logs');
    }

    protected function casts(): array
    {
        return [
            'to' => 'array',
            'cc' => 'array',
            'bcc' => 'array',
            'sent_at' => 'datetime',
        ];
    }

    /**
     * Rows that list the given address in To, CC or BCC. Addresses are stored as sent, so both
     * the given spelling and its lowercase form are matched. A blank address matches nothing,
     * so an erasure can never widen into deleting every row.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeForRecipient(Builder $query, string $email): Builder
    {
        $email = trim($email);

        if ($email === '') {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $recipients) use ($email): void {
            foreach (array_unique([$email, Str::lower($email)]) as $candidate) {
                foreach (['to', 'cc', 'bcc'] as $column) {
                    $recipients->orWhereJsonContains($column, $candidate);
                }
            }
        });
    }
}
