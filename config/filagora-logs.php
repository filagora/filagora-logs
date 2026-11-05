<?php

declare(strict_types=1);

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;

return [

    /*
    |--------------------------------------------------------------------------
    | Gate abilities
    |--------------------------------------------------------------------------
    |
    | Maps the ability that guards the email log screen to the entitlement
    | that allows it. Point it at a different entitlement to use your own keys.
    |
    */
    'gates' => [
        'filagora.logs.view' => 'filagora.logs.view',
    ],

    /*
    |--------------------------------------------------------------------------
    | Message bodies
    |--------------------------------------------------------------------------
    |
    | Off by default: a mail's text can hold private content, and staff read
    | this log. When on, `never_store` lists the notification classes whose text
    | is left out: anything with a live token or signed link, or private content.
    | A site's own such mail must be added here.
    |
    */
    'bodies' => [
        'enabled' => false,
        'never_store' => [
            ResetPassword::class,
            VerifyEmail::class,
        ],
    ],

];
