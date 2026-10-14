<?php

declare(strict_types=1);

use Filagora\Core\Modules\ModuleRegistry;
use Filagora\Entitlements\Registry\EntitlementRegistry;
use Filagora\Logs\Filament\Resources\EmailLogs\EmailLogResource;
use Filagora\Logs\Filament\Resources\EmailLogs\Pages\ListEmailLogs;
use Filagora\Logs\Support\Abilities;
use Filagora\Logs\Tests\Support\HostAccess;
use Filagora\Logs\Tests\Support\TestUser;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Filament::setCurrentPanel('admin');
    HostAccess::$granted = [];
});

function logsHostUser(string ...$keys): TestUser
{
    $user = TestUser::make('Host');
    HostAccess::$granted[$user->id] = array_values($keys);

    return $user;
}

it('runs with no entitlements package loaded, needing only core', function () {
    $modules = app(ModuleRegistry::class);

    expect($modules->get('logs')->requires)->toBe(['core'])
        ->and($modules->has('entitlements'))->toBeFalse()
        ->and($modules->missingRequirements())->toBe([])
        ->and(app()->bound(EntitlementRegistry::class))->toBeFalse();
});

it('follows the host\'s own checker for the email log', function () {
    expect(Gate::forUser(logsHostUser('filagora.logs.view'))->allows(Abilities::VIEW))->toBeTrue()
        ->and(Gate::forUser(logsHostUser())->allows(Abilities::VIEW))->toBeFalse();

    actingAs(logsHostUser());
    expect(EmailLogResource::canViewAny())->toBeFalse();
    Livewire::test(ListEmailLogs::class)->assertForbidden();

    actingAs(logsHostUser('filagora.logs.view'));
    expect(EmailLogResource::canViewAny())->toBeTrue();
    Livewire::test(ListEmailLogs::class)->assertSuccessful();
});
