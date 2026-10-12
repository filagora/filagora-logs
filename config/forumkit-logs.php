<?php

declare(strict_types=1);

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
        'forumkit.logs.view' => 'forumkit.logs.view',
    ],

];
