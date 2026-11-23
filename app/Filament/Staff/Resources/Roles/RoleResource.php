<?php

namespace App\Filament\Staff\Resources\Roles;

use App\Filament\Staff\Resources\Roles\Pages\EditRole;
use App\Filament\Staff\Resources\Roles\Pages\ListRoles;
use App\Filament\Staff\Resources\Roles\Schemas\RoleForm;
use App\Filament\Staff\Resources\Roles\Tables\RolesTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;

/** Edit the permissions granted to each role. Roles themselves are seeded (see RolePolicy). */
class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Roles & permissions';

    public static function form(Schema $schema): Schema
    {
        return RoleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RolesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
            'edit' => EditRole::route('/{record}/edit'),
        ];
    }
}
