<?php

namespace App\Filament\Staff\Resources\Roles\Schemas;

use App\Enums\Role;
use Filament\Forms\Components\CheckboxList;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Permission;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('role')
                ->state(fn ($record) => Role::tryFrom($record->name)?->label() ?? $record->name),
            CheckboxList::make('permissions')
                ->relationship('permissions', 'name')
                ->getOptionLabelFromRecordUsing(fn (Permission $record) => str($record->name)->replace(['.', '_'], ' ')->ucfirst()->toString())
                ->columns(['default' => 1, 'md' => 2, 'xl' => 3])
                ->bulkToggleable()
                ->columnSpanFull(),
        ]);
    }
}
