<?php

declare(strict_types=1);

use Filagora\Core\Schema\Adopter;
use Filagora\Core\Schema\AdoptionStatus;
use Filagora\Core\Schema\SchemaRegistry;
use Filagora\Core\Schema\TableShape;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

const LOG_MIGRATION = '2026_10_12_000000_create_filagora_log_tables';
const LOG_BODY_MIGRATION = '2026_10_18_171237_add_body_to_filagora_email_logs_table';

function logMigration(): object
{
    return require __DIR__.'/../../database/migrations/'.LOG_MIGRATION.'.php';
}

function bodyMigration(): object
{
    return require __DIR__.'/../../database/migrations/'.LOG_BODY_MIGRATION.'.php';
}

function buildLogTable(): void
{
    logMigration()->up();
    bodyMigration()->up();
}

afterEach(function () {
    Schema::dropIfExists('email_logs');
    config(['filagora.table_prefix' => 'filagora_']);
    Schema::dropIfExists('filagora_email_logs');
    buildLogTable();
});

it('ships a snapshot of the table that matches the migration, column order included', function () {
    $snapshot = app(SchemaRegistry::class)->get('logs');

    expect($snapshot)->not->toBeNull()
        ->and(array_keys($snapshot->tables))->toBe(['email_logs'])
        ->and($snapshot->migrationNames())->toBe([LOG_MIGRATION, LOG_BODY_MIGRATION]);

    foreach ($snapshot->tables as $base => $expected) {
        expect(TableShape::inspect($base)->differencesFrom($expected, compareColumnOrder: true))->toBe([], $base);
    }
});

it('keeps the body in a column of its own, after the subject', function () {
    $logs = TableShape::inspect('email_logs');
    $columns = array_keys($logs->columns);

    expect($logs->columns['body'])->toMatchArray(['type' => 'mediumtext', 'nullable' => true])
        ->and($columns[array_search('subject', $columns, true) + 1])->toBe('body');
});

it('pins the email log table: json recipients, status default and its three indexes', function () {
    $logs = TableShape::inspect('email_logs');

    expect($logs->columns['status']['default'])->toBe('sending')
        ->and($logs->columns['to']['nullable'])->toBeFalse()
        ->and($logs->columns['cc']['nullable'])->toBeTrue()
        ->and(array_keys($logs->indexes))->toEqualCanonicalizing(['primary', 'email_logs_status_index', 'email_logs_created_at_index', 'email_logs_correlation_id_index']);
});

it('adopts the live tables, which have no prefix, and records the migration', function () {
    config(['filagora.table_prefix' => '']);
    logMigration()->down();
    config(['filagora.table_prefix' => '']);
    buildLogTable();
    DB::table('migrations')->whereIn('migration', [LOG_MIGRATION, LOG_BODY_MIGRATION])->delete();

    $plan = app(Adopter::class)->planFor(app(SchemaRegistry::class)->get('logs'));

    expect($plan->status)->toBe(AdoptionStatus::Adoptable, implode('; ', $plan->differences));

    $this->artisan('filagora:adopt', ['--package' => 'logs'])->assertExitCode(0);

    expect(DB::table('migrations')->whereIn('migration', [LOG_MIGRATION, LOG_BODY_MIGRATION])->count())->toBe(2);
});

it('refuses tables that lost an index', function () {
    config(['filagora.table_prefix' => '']);
    logMigration()->down();
    buildLogTable();
    Schema::table('email_logs', fn ($table) => $table->dropIndex('email_logs_status_index'));

    $plan = app(Adopter::class)->planFor(app(SchemaRegistry::class)->get('logs'));

    expect($plan->status)->toBe(AdoptionStatus::Mismatch)
        ->and($plan->differences)->toContain('email_logs: index email_logs_status_index is missing');
});
