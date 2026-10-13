<?php

declare(strict_types=1);

namespace Filagora\Logs\Filament\Resources\EmailLogs;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filagora\Core\Navigation\NavigationGroupRegistry;
use Filagora\Entitlements\Filament\Concerns\GatedByAbility;
use Filagora\Logs\Filament\Resources\EmailLogs\Pages\ListEmailLogs;
use Filagora\Logs\Filament\Resources\EmailLogs\Pages\ViewEmailLog;
use Filagora\Logs\Filament\Resources\EmailLogs\Schemas\EmailLogInfolist;
use Filagora\Logs\Filament\Resources\EmailLogs\Tables\EmailLogsTable;
use Filagora\Logs\Models\EmailLog;
use Filagora\Logs\Support\Abilities;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class EmailLogResource extends Resource
{
    use GatedByAbility;

    protected static ?string $model = EmailLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::AtSymbol;

    protected static ?int $navigationSort = 30;

    /**
     * Read-only: only viewing is allowed, and only with the logs ability.
     */
    protected static function abilityFor(string|UnitEnum $action, ?Model $record): ?string
    {
        return in_array($action, ['viewAny', 'view'], true) ? Abilities::VIEW : null;
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return app(NavigationGroupRegistry::class)->label('logs');
    }

    public static function getNavigationLabel(): string
    {
        return __('filagora-logs::messages.email_logs.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filagora-logs::messages.email_logs.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filagora-logs::messages.email_logs.plural_model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EmailLogInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmailLogsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmailLogs::route('/'),
            'view' => ViewEmailLog::route('/{record}'),
        ];
    }
}
