<?php

declare(strict_types=1);

return [
    'navigation_groups' => [
        'logs' => 'Logs',
    ],

    'email_logs' => [
        'navigation_label' => 'Email log',
        'model_label' => 'email log',
        'plural_model_label' => 'email logs',
        'statuses' => [
            'sending' => 'Sending',
            'sent' => 'Sent',
        ],
        'table' => [
            'notification' => 'Notification',
            'message_id' => 'Message ID',
        ],
        'infolist' => [
            'message' => 'Message',
            'body' => 'Body',
            'body_not_stored' => 'Body not stored (a sensitive or empty message, or sent while bodies were off).',
            'delivery' => 'Delivery',
            'notification_class' => 'Notification class',
            'correlation_id' => 'Correlation ID',
        ],
    ],
];
