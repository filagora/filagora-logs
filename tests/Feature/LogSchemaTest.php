<?php

declare(strict_types=1);

use ForumKit\Core\Schema\Adopter;
use ForumKit\Core\Schema\AdoptionStatus;
use ForumKit\Core\Schema\SchemaRegistry;
use ForumKit\Core\Schema\TableShape;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

const LOG_MIGRATION = '2026_10_12_000000_create_forumkit_log_tables';

function logMigration(): object
{
    return require __DIR__.'/../../database/migrations/'.LOG_MIGRATION.'.php';
}

afterEach(function () {
    Schema::dropIfExists('email_logs');
    config(['forumkit.table_prefix' => 'forumkit_']);
    Schema::dropIfExists('forumkit_email_logs');
    logMigration()->up();
});

it('ships a snapshot of the table that matches the migration, column order included', function () {
    $snapshot = app(SchemaRegistry::class)->get('logs');

    expect($snapshot)->not->toBeNull()
        ->and(array_keys($snapshot->tables))->toBe(['email_logs'])
        ->and($snapshot->migrationNames())->toBe([LOG_MIGRATION]);

    foreach ($snapshot->tables as $base => $expected) {
        expect(TableShape::inspect($base)->differencesFrom($expected, compareColumnOrder: true))->toBe([], $base);
    }
});

it('pins the email log table: json recipients, status default and its three indexes', function () {
    $logs = TableShape::inspect('email_logs');

    expect($logs->columns['status']['default'])->toBe('sending')
        ->and($logs->columns['to']['nullable'])->toBeFalse()
        ->and($logs->columns['cc']['nullable'])->toBeTrue()
        ->and(array_keys($logs->indexes))->toEqualCanonicalizing(['primary', 'email_logs_status_index', 'email_logs_created_at_index', 'email_logs_correlation_id_index']);
});

it('adopts the live tables, which have no prefix, and records the migration', function () {
    config(['forumkit.table_prefix' => '']);
    logMigration()->down();
    config(['forumkit.table_prefix' => '']);
    logMigration()->up();
    DB::table('migrations')->where('migration', LOG_MIGRATION)->delete();

    $plan = app(Adopter::class)->planFor(app(SchemaRegistry::class)->get('logs'));

    expect($plan->status)->toBe(AdoptionStatus::Adoptable, implode('; ', $plan->differences));

    $this->artisan('forumkit:adopt', ['--package' => 'logs'])->assertExitCode(0);

    expect(DB::table('migrations')->where('migration', LOG_MIGRATION)->exists())->toBeTrue();
});

it('refuses tables that lost an index', function () {
    config(['forumkit.table_prefix' => '']);
    logMigration()->down();
    logMigration()->up();
    Schema::table('email_logs', fn ($table) => $table->dropIndex('email_logs_status_index'));

    $plan = app(Adopter::class)->planFor(app(SchemaRegistry::class)->get('logs'));

    expect($plan->status)->toBe(AdoptionStatus::Mismatch)
        ->and($plan->differences)->toContain('email_logs: index email_logs_status_index is missing');
});
