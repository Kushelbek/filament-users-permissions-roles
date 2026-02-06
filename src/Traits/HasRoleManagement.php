<?

namespace Kushelbek\FilamentUsersPermissionsRoles\Traits;

use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;

trait HasRoleManagement
{
    public static function getRoleManagementForm(): array
    {
        return [
            Section::make('Roles & Permissions')
                ->description('Manage user roles and direct permissions')
                ->collapsible()
                ->schema([
                    Forms\Components\Select::make('roles')
                        ->label('Roles')
                        ->relationship('roles', 'name')
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->maxLength(255),
                        ]),
                    
                    Forms\Components\Select::make('permissions')
                        ->label('Direct Permissions')
                        ->relationship('permissions', 'name')
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->helperText('Permissions assigned directly to user (in addition to role permissions)'),
                ]),
        ];
    }
    
    public static function getAdvancedRoleManagementForm(): array
    {
        return [
            Tabs::make('Permissions Management')
                ->tabs([
                    Tabs\Tab::make('Roles')
                        ->icon('heroicon-o-user-group')
                        ->schema([
                            Forms\Components\CheckboxList::make('roles')
                                ->label('')
                                ->relationship('roles', 'name')
                                ->searchable()
                                ->bulkToggleable()
                                ->gridDirection('row')
                                ->columns(2),
                        ]),
                    
                    Tabs\Tab::make('Direct Permissions')
                        ->icon('heroicon-o-key')
                        ->schema([
                            Forms\Components\CheckboxList::make('permissions')
                                ->label('')
                                ->relationship('permissions', 'name')
                                ->searchable()
                                ->bulkToggleable()
                                ->groupedBy(fn ($permission) => 
                                    explode('.', $permission->name)[0] ?? 'other'
                                ),
                        ]),
                    
                    Tabs\Tab::make('Advanced')
                        ->icon('heroicon-o-cog')
                        ->schema([
                            Forms\Components\Select::make('guard_name')
                                ->label('Guard Name')
                                ->options([
                                    'web' => 'Web',
                                    'api' => 'API',
                                ])
                                ->default('web'),
                            
                            Forms\Components\Toggle::make('is_super_admin')
                                ->label('Super Administrator')
                                ->helperText('Bypass all permission checks')
                                ->reactive()
                                ->afterStateUpdated(function ($set, $state) {
                                    if ($state) {
                                        $set('roles', null);
                                        $set('permissions', null);
                                    }
                                }),
                        ]),
                ]),
        ];
    }
    
    public static function getRoleManagementTableColumns(): array
    {
        return [
            \Filament\Tables\Columns\TextColumn::make('roles.name')
                ->label('Roles')
                ->badge()
                ->separator(',')
                ->limitList(3)
                ->toggleable(),
                
            \Filament\Tables\Columns\TextColumn::make('permissions_count')
                ->label('Direct Permissions')
                ->counts('permissions')
                ->sortable()
                ->toggleable(),
        ];
    }
}