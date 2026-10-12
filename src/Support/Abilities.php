<?php

declare(strict_types=1);

namespace ForumKit\Logs\Support;

/**
 * The Gate ability that guards the audit and email log screens. It maps to an entitlement key
 * in `forumkit-logs.gates`, which a host can point at its own key.
 */
final class Abilities
{
    public const VIEW = 'forumkit.logs.view';
}
