<?php

namespace App\Filament\Staff\Resources\Users\Tables;

use App\Enums\Role;
use App\Models\User;
use App\Support\Audit;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

/** Users are never deleted (history references them); they are deactivated. */
class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('roles'))
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->label('Email address')->searchable(),
                TextColumn::make('roles.name')->label('Roles')->badge()
                    ->formatStateUsing(fn (string $state) => Role::tryFrom($state)?->label() ?? $state),
                IconColumn::make('is_active')->label('Active')->boolean(),
                IconColumn::make('two_factor_confirmed_at')->label('2FA')->boolean()
                    ->getStateUsing(fn (User $record) => $record->two_factor_confirmed_at !== null),
                IconColumn::make('is_demo')->label('Demo')->boolean()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('last_login_at')->label('Last sign-in')->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('roles')->relationship('roles', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => Role::tryFrom($record->name)?->label() ?? $record->name),
                TernaryFilter::make('is_active')->label('Active'),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('resetTwoFactor')
                    ->label('Reset 2FA')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->visible(fn (User $record) => $record->two_factor_secret !== null)
                    ->requiresConfirmation()
                    ->modalDescription('Use when a user has lost their phone. They will have to set up two-factor authentication again at next sign-in.')
                    ->action(function (User $record) {
                        $record->forceFill([
                            'two_factor_secret' => null,
                            'two_factor_recovery_codes' => null,
                            'two_factor_confirmed_at' => null,
                        ])->save();
                        Audit::record('two_factor_reset_by_admin', $record);
                        Notification::make()->title('Two-factor authentication reset')->success()->send();
                    }),
            ])
            ->defaultSort('name');
    }
}
