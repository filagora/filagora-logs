<?php

declare(strict_types=1);

namespace ForumKit\Logs\Filament\Resources\Audits;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use ForumKit\Core\Navigation\NavigationGroupRegistry;
use ForumKit\Entitlements\Filament\Concerns\GatedByAbility;
use ForumKit\Logs\Filament\Resources\Audits\Pages\ListAudits;
use ForumKit\Logs\Filament\Resources\Audits\Pages\ViewAudit;
use ForumKit\Logs\Filament\Resources\Audits\Schemas\AuditInfolist;
use ForumKit\Logs\Filament\Resources\Audits\Tables\AuditsTable;
use ForumKit\Logs\Models\Audit;
use ForumKit\Logs\Support\Abilities;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class AuditResource extends Resource
{
    use GatedByAbility;

    protected static ?string $model = Audit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::FingerPrint;

    protected static ?int $navigationSort = 10;

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
        return __('forumkit-logs::messages.audits.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('forumkit-logs::messages.audits.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('forumkit-logs::messages.audits.plural_model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AuditInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AuditsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAudits::route('/'),
            'view' => ViewAudit::route('/{record}'),
        ];
    }
}
