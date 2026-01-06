<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContentResource\Pages;
use App\Models\Content;
use App\Models\HostGroup;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContentResource extends Resource
{
    protected static ?string $model = Content::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?array $sampleContentCache = null;

    public static function form(Forms\Form $form): Forms\Form
    {
        $sample = static::sampleContent();

        return $form
            ->schema([
                Section::make('Content Detail')
                    ->schema([
                        Grid::make(2)->schema([
                            Forms\Components\Select::make('group_id')
                                ->label('Group')
                                ->options(fn () => HostGroup::orderBy('name')->pluck('name', 'group_id'))
                                ->searchable()
                                ->required(),
                            Forms\Components\TextInput::make('category')
                                ->label('Category')
                                ->required()
                                ->maxLength(100)
                                ->placeholder($sample['category'] ?? null),
                        ]),
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder($sample['name'] ?? null),
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder($sample['description'] ?? null),
                        Grid::make(3)->schema([
                            Forms\Components\Textarea::make('arabic')
                                ->rows(3)
                                ->placeholder($sample['arabic'] ?? null),
                            Forms\Components\Textarea::make('latin')
                                ->rows(3)
                                ->placeholder($sample['latin'] ?? null),
                            Forms\Components\Textarea::make('translate_id')
                                ->label('Translation (ID)')
                                ->rows(3)
                                ->placeholder($sample['translate_id'] ?? null),
                        ])->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('category')->badge()->sortable(),
                Tables\Columns\TextColumn::make('group_id')
                    ->label('Group')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('group_id')
                    ->label('Group')
                    ->options(fn () => HostGroup::orderBy('name')->pluck('name', 'group_id')),
                SelectFilter::make('category')
                    ->label('Category')
                    ->options(fn () => Content::query()
                        ->select('category')
                        ->distinct()
                        ->pluck('category', 'category')
                        ->filter()
                        ->toArray()),
            ])
            ->defaultSort('updated_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContents::route('/'),
            'create' => Pages\CreateContent::route('/create'),
            'edit' => Pages\EditContent::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }

    public static function canEdit($record): bool
    {
        return static::canViewAny();
    }

    public static function canDelete($record): bool
    {
        return static::canViewAny();
    }

    protected static function sampleContent(): array
    {
        if (static::$sampleContentCache !== null) {
            return static::$sampleContentCache;
        }

        $record = Content::query()
            ->select('name', 'category', 'description', 'arabic', 'latin', 'translate_id')
            ->orderBy('id')
            ->first();

        static::$sampleContentCache = $record?->toArray() ?? [];

        return static::$sampleContentCache;
    }
}
