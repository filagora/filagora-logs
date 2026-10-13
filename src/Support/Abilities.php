<?php

declare(strict_types=1);

namespace Filagora\Logs\Support;

/**
 * The Gate ability that guards the email log screen. It maps to an entitlement key
 * in `filagora-logs.gates`, which a host can point at its own key.
 */
final class Abilities
{
    public const VIEW = 'filagora.logs.view';
}
