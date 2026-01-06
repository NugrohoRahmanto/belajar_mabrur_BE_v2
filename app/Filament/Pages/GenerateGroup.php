<?php

namespace App\Filament\Pages;

use App\Actions\HostGroups\GenerateHostGroup;
use App\Models\HostGroup;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class GenerateGroup extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?string $navigationLabel = 'Generate Group';
    protected static ?string $title = 'Generate Host Group';

    protected static string $view = 'filament.pages.generate-group';

    public ?array $data = [];

    public function mount(): void
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $this->form->fill($this->defaultFormState());
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
            Section::make('Group Identity')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Group Name')
                        ->required()
                        ->maxLength(150),
                    Forms\Components\TextInput::make('description')
                        ->label('Description')
                        ->maxLength(500),
                ]),

            Section::make('Account Provisioning')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\TextInput::make('host_count')
                            ->label('Number of Hosts')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->required(),
                        Forms\Components\TextInput::make('host_password')
                            ->label('Host Password')
                            ->password()
                            ->revealable()
                            ->minLength(6)
                            ->required(),
                    ]),
                    Grid::make(2)->schema([
                        Forms\Components\TextInput::make('user_count')
                            ->label('Number of Users')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->required(),
                        Forms\Components\TextInput::make('user_password')
                            ->label('User Password')
                            ->password()
                            ->revealable()
                            ->minLength(6)
                            ->required(),
                    ]),
                ]),

            Section::make('Template Copy')
                ->schema([
                    Forms\Components\Toggle::make('copy_contents')
                        ->label('Copy Template Contents')
                        ->default(true)
                        ->reactive(),
                    Forms\Components\Select::make('template_group_id')
                        ->label('Template Group')
                        ->options(fn () => HostGroup::orderBy('name')->pluck('name', 'group_id'))
                        ->searchable()
                        ->helperText('Contents from this group will be cloned when generating the new group.')
                        ->default(HostGroup::default()?->group_id)
                        ->disabled(fn (callable $get) => ! $get('copy_contents'))
                        ->required(fn (callable $get) => (bool) $get('copy_contents')),
                ]),
            ])
            ->statePath('data')
            ->model(HostGroup::class);
    }

    public function submit(): void
    {
        $state = $this->form->getState();
        $state['is_active'] = true;

        [$group, $copied, $hosts, $users] = app(GenerateHostGroup::class)->handle($state);

        Notification::make()
            ->title('Group generated')
            ->body(
                "Group {$group->name} created. {$copied} contents duplicated, " .
                "{$hosts} host(s) and {$users} user(s) provisioned."
            )
            ->success()
            ->send();

        $this->form->fill($this->defaultFormState());
    }

    private function defaultFormState(): array
    {
        return [
            'name'              => null,
            'description'       => null,
            'copy_contents'     => true,
            'template_group_id' => HostGroup::default()?->group_id,
            'host_count'        => 1,
            'user_count'        => 1,
            'host_password'     => null,
            'user_password'     => null,
        ];
    }
}
