<?php

namespace App\Filament\Staff\Resources\Users\Schemas;

use App\Enums\Role;
use App\Rules\UgandaPhone;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role as RoleModel;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Account')->columns(2)->schema([
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('email')->label('Email address')->email()->required()
                    ->unique(ignoreRecord: true)->maxLength(255),
                TextInput::make('phone')->tel()->placeholder('+256772123456')
                    ->dehydrateStateUsing(fn (?string $state) => UgandaPhone::normalise($state))
                    ->rule(new UgandaPhone)->nullable(),
                TextInput::make('password')->password()->revealable()
                    ->label(fn (string $operation) => $operation === 'create' ? 'Initial password' : 'New password (leave blank to keep)')
                    ->required(fn (string $operation) => $operation === 'create')
                    ->rule(Password::default())
                    ->dehydrated(fn (?string $state) => filled($state)),
                Toggle::make('is_active')->label('Active (can sign in)')->default(true),
            ]),

            Section::make('Roles')->schema([
                CheckboxList::make('roles')
                    ->relationship('roles', 'name')
                    ->getOptionLabelFromRecordUsing(fn (RoleModel $record) => Role::tryFrom($record->name)?->label() ?? $record->name)
                    ->columns(2)
                    ->required()
                    ->helperText('System administrator and Town Engineer must set up two-factor authentication at first sign-in.'),
            ]),
        ]);
    }
}
