<?php

declare(strict_types=1);

return [
    'navigation_groups' => [
        'logs' => 'Logs',
    ],

    'audits' => [
        'navigation_label' => 'Audit log',
        'model_label' => 'audit log',
        'plural_model_label' => 'audit logs',
        'table' => [
            'model' => 'Model',
            'model_id' => 'Model ID',
            'user' => 'User',
        ],
        'infolist' => [
            'audit' => 'Audit',
            'actor_type' => 'Actor type',
            'actor' => 'Actor',
            'changes' => 'Changes',
            'old_values' => 'Old values',
            'new_values' => 'New values',
        ],
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
            'delivery' => 'Delivery',
            'notification_class' => 'Notification class',
            'correlation_id' => 'Correlation ID',
        ],
    ],
];
