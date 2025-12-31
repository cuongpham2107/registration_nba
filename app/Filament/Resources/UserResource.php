<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\ViewField;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class UserResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Quản lý người dùng';
    protected static ?string $pluralModelLabel = 'Danh sách tài khoản';
    public static function getNavigationSort(): ?int
    {
        return 10;
    }
    public static function getNavigationGroup(): ?string
    {
        return __('filament-shield::filament-shield.nav.group') ?? '';
    }

    public static function getNavigationLabel(): string
    {
        return __(key: 'filament-panels::pages/users/navigation.label') ?? '';
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Thông tin tài khoản')
                    ->columns([
                        'sm' => 1,
                        'md' => 2,
                        'lg' => 4,
                    ])
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Họ và tên')
                            ->required()
                            ->columnSpan([
                                'sm' => 1,
                                'md' => 2,
                                'lg' => 2,
                            ]),
                        Forms\Components\TextInput::make('email')
                            ->label('Địa chỉ Email')
                            ->prefixIcon('heroicon-o-envelope')
                            ->required()
                            ->columnSpan([
                                'sm' => 1,
                                'md' => 2,
                                'lg' => 2,
                            ]),
                        Forms\Components\TextInput::make('mobile_phone')
                            ->label('Số điện thoại')
                            ->prefixIcon('heroicon-o-phone')
                            ->columnSpan([
                                'sm' => 1,
                                'md' => 1,
                                'lg' => 2,
                            ]),
                            Forms\Components\TextInput::make('asgl_id')
                            ->label('Mã nhân viên ASGL')
                            ->columnSpan([
                                'sm' => 1,
                                'md' => 1,
                                'lg' => 2,
                            ]),
                        Forms\Components\TextInput::make('username')
                            ->label('Tài khoản')
                            ->columnSpan([
                                'sm' => 1,
                                'md' => 1,
                                'lg' => 2,
                            ]),
                        Forms\Components\TextInput::make('password')
                            ->label('Mật khẩu')
                            ->password()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->minLength(8)
                            ->validationAttribute('mật khẩu')
                            ->columnSpan([
                                'sm' => 1,
                                'md' => 1,
                                'lg' => 2,
                            ]),
                        
                        Forms\Components\TextInput::make('department_name')
                            ->label('Phòng ban')
                            ->columnSpan([
                                'sm' => 1,
                                'md' => 2,
                                'lg' => 2,
                            ]),
                        Forms\Components\Select::make('approver_id')
                            ->label('Người phê duyệt')
                            ->relationship(
                                name: 'approver', 
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query) => $query->whereHas('roles', function (Builder $query) {
                                    $query->where('name', 'approver');
                                }))
                            ->searchable()
                            ->preload()
                            ->columnSpan([
                                'sm' => 1,
                                'md' => 2,
                                'lg' => 2,
                            ]),
                    ])
                    ->columnSpan(2),
                Forms\Components\Grid::make(1)
                    ->schema([
                        Section::make('Phân quyền')
                            ->columns(1)
                            ->schema([
                                Forms\Components\Select::make('roles')
                                    ->label('Quyền')
                                    ->relationship('roles', 'name')
                                    ->multiple()
                                    ->searchable(['name','email'])
                                    ->preload()
                            ])
                            ->columnSpan('full')
                            ->hidden(fn () => !auth()->user()?->hasRole('super_admin')),
                        Section::make('Hình đại diện')
                            ->schema([
                                ViewField::make('avatar')
                                    ->view('filament.forms.components.avatar')
                                    ->label(''),
                            ])
                            ->columnSpan('full'),
                    ])
                    ->columnSpan(1),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->label('Avatar')
                    ->width(40)
                    ->circular()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Họ và tên')
                    ->searchable(),
                Tables\Columns\TextColumn::make('username')
                    ->label('Tài khoản')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Địa chỉ Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('department_name')
                    ->label('Phòng ban')
                    ->searchable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Quyền')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime()
                    ->sortable(),
                    // ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->modifyQueryUsing(function (Builder $query) {
                $user = auth()->user();
                
                // Nếu chưa login, return query rỗng
                if (!$user) {
                    return $query->whereRaw('1 = 0');
                }
                // Super admin thấy tất cả
                if ($user->hasRole('super_admin')) {
                    return $query;
                }
                // User thường chỉ thấy của mình (người tạo)
                return $query->where('id', $user->id);
            })
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }
}
