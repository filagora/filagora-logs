<?php

declare(strict_types=1);

use Filagora\Logs\Tests\NoEntitlementsTestCase;
use Filagora\Logs\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->extend(TestCase::class)->use(RefreshDatabase::class)->in('Feature');
pest()->extend(NoEntitlementsTestCase::class)->use(RefreshDatabase::class)->in('NoEntitlements');
