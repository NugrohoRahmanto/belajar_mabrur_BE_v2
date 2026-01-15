<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\HostGroup;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(fn (string $context): bool => $context === 'create')
                    ->maxLength(255),

                Forms\Components\TextInput::make('username')
                    ->required(fn (string $context): bool => $context === 'create')
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required(fn (string $context): bool => $context === 'create')
                    ->maxLength(255),

                Forms\Components\TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => !empty($state) ? bcrypt($state) : null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->maxLength(255)
                    ->label('Password')
                    ->nullable(),

                Forms\Components\Select::make('role')
                    ->options(fn () => static::roleOptions())
                    ->required(fn (string $context): bool => $context === 'create'),

                Forms\Components\Select::make('group_id')
                    ->label('Group')
                    ->options(fn () => static::groupOptions())
                    ->searchable()
                    ->preload()
                    ->required(fn (string $context): bool => $context === 'create'),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('username')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('role')->sortable(),
                Tables\Columns\TextColumn::make('group_id')
                    ->label('Group')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('token')->limit(20),
                Tables\Columns\TextColumn::make('token_expires_at')->dateTime(),
                Tables\Columns\TextColumn::make('last_active_at')->dateTime(),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->filters([
                SelectFilter::make('group_id')
                    ->label('Group')
                    ->options(fn () => static::groupOptions()),
            ])
            ->actions([
                Tables\Actions\EditAction::make('edit_all')
                    ->label('Edit All'),
                Tables\Actions\Action::make('edit_group')
                    ->label('Edit Group')
                    ->icon('heroicon-o-rectangle-stack')
                    ->modalHeading('Update Group')
                    ->modalSubmitActionLabel('Save Group')
                    ->form([
                        Forms\Components\Select::make('group_id')
                            ->label('Group')
                            ->options(fn () => static::groupOptions())
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->action(fn (User $record, array $data) => $record->update(['group_id' => $data['group_id']]))
                    ->successNotificationTitle('Group updated'),
                Tables\Actions\Action::make('edit_role')
                    ->label('Edit Role')
                    ->icon('heroicon-o-adjustments-vertical')
                    ->modalHeading('Update Role')
                    ->modalSubmitActionLabel('Save Role')
                    ->form([
                        Forms\Components\Select::make('role')
                            ->label('Role')
                            ->options(fn () => static::roleOptions())
                            ->required(),
                    ])
                    ->action(fn (User $record, array $data) => $record->update(['role' => $data['role']]))
                    ->successNotificationTitle('Role updated'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }


    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    protected static function groupOptions(): array
    {
        return HostGroup::query()->orderBy('name')->pluck('name', 'group_id')->all();
    }

    protected static function roleOptions(): array
    {
        return [
            'admin' => 'Admin',
            'host' => 'Host',
            'user' => 'User',
        ];
    }
}
