<?php

namespace App\Filament\Staff\Resources\Roles\Tables;

use App\Enums\Role;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount(['permissions', 'users']))
            ->columns([
                TextColumn::make('name')->label('Role')
                    ->formatStateUsing(fn (string $state) => Role::tryFrom($state)?->label() ?? $state),
                TextColumn::make('permissions_count')->label('Permissions'),
                TextColumn::make('users_count')->label('Users'),
            ])
            ->recordActions([EditAction::make()])
            ->paginated(false);
    }
}
