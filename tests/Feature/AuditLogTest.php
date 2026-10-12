<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use ForumKit\Entitlements\Models\UserEntitlementGrant;
use ForumKit\Logs\Filament\Resources\Audits\AuditResource;
use ForumKit\Logs\Filament\Resources\Audits\Pages\ListAudits;
use ForumKit\Logs\Filament\Resources\Audits\Pages\ViewAudit;
use ForumKit\Logs\LogsServiceProvider;
use ForumKit\Logs\Models\Audit;
use ForumKit\Logs\Tests\Support\TestUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

use function Pest\Laravel\actingAs;

class AuditedThing extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'audited_things';

    protected $guarded = [];
}

beforeEach(function () {
    Filament::setCurrentPanel('admin');

    Schema::create('audited_things', function ($table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });
});

afterEach(fn () => Schema::dropIfExists('audited_things'));

function logViewer(string ...$keys): TestUser
{
    $user = TestUser::make('Viewer');

    foreach ($keys as $key) {
        UserEntitlementGrant::create(['user_id' => $user->id, 'entitlement_key' => $key, 'grant_type' => 'manual']);
    }

    actingAs($user);

    return $user;
}

it('writes audits to the package table through its model', function () {
    expect(config('audit.implementation'))->toBe(Audit::class);

    $actor = logViewer();
    $thing = AuditedThing::create(['name' => 'one']);
    $thing->update(['name' => 'two']);

    $audits = Audit::orderBy('id')->get();

    expect($audits->pluck('event')->all())->toBe(['created', 'updated'])
        ->and($audits[1]->old_values)->toBe(['name' => 'one'])
        ->and($audits[1]->new_values)->toBe(['name' => 'two'])
        ->and($audits[0]->user_id)->toBe($actor->id)
        ->and($audits[0]->getTable())->toBe('forumkit_audits')
        ->and($audits[0]->auditable_type)->toBe(AuditedThing::class);
});

it('keeps a model the host already chose for audits', function () {
    $this->app['config']->set('audit.implementation', 'Host\\Audit');

    (new ReflectionMethod(LogsServiceProvider::class, 'useForumKitAuditModel'))
        ->invoke(new LogsServiceProvider($this->app));

    expect(config('audit.implementation'))->toBe('Host\\Audit');
});

it('shows the audit screens only to holders of the logs ability', function () {
    logViewer();
    expect(AuditResource::canViewAny())->toBeFalse();

    logViewer('forumkit.logs.view');
    expect(AuditResource::canViewAny())->toBeTrue()
        ->and(Gate::allows('forumkit.logs.view'))->toBeTrue();
});

it('is read-only', function () {
    logViewer('forumkit.logs.view', 'forumkit.*');
    $thing = AuditedThing::create(['name' => 'one']);
    $audit = Audit::sole();

    expect(AuditResource::canCreate())->toBeFalse()
        ->and(AuditResource::canEdit($audit))->toBeFalse()
        ->and(AuditResource::canDelete($audit))->toBeFalse()
        ->and(AuditResource::canView($audit))->toBeTrue();
});

it('lists audits newest first with the actor, and filters by event', function () {
    $actor = logViewer('forumkit.logs.view');
    $thing = AuditedThing::create(['name' => 'one']);
    $thing->update(['name' => 'two']);
    [$created, $updated] = Audit::orderBy('id')->get()->all();

    Livewire::test(ListAudits::class)
        ->assertCanSeeTableRecords([$created, $updated])
        ->assertSee($actor->name)
        ->filterTable('event', 'updated')
        ->assertCanSeeTableRecords([$updated])
        ->assertCanNotSeeTableRecords([$created]);
});

it('names an audit actor that no longer exists by id', function () {
    logViewer('forumkit.logs.view');
    AuditedThing::create(['name' => 'one']);
    Audit::query()->update(['user_id' => 987654]);

    Livewire::test(ListAudits::class)->assertSee('User #987654');
});

it('shows the old and new values of an audit', function () {
    logViewer('forumkit.logs.view');
    $thing = AuditedThing::create(['name' => 'one']);
    $thing->update(['name' => 'two']);
    $updated = Audit::where('event', 'updated')->sole();

    Livewire::test(ViewAudit::class, ['record' => $updated->getKey()])
        ->assertSee('"name": "one"')
        ->assertSee('"name": "two"');
});

it('keeps the screens closed to a user without the ability', function () {
    logViewer();

    Livewire::test(ListAudits::class)->assertForbidden();
});
