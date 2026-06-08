<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Models\UserApprover;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserProfile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationLabel = 'Trang cá nhân';

    protected static ?string $title = 'Thông tin cá nhân';

    protected static ?int $navigationSort = 6;

    protected string $view = 'filament.pages.user-profile';

    public ?array $data = [];

    public function mount(): void
    {
        $user = auth()->user();

        $approverIds = UserApprover::query()
            ->where('user_id', $user->id)
            ->pluck('approver_id')
            ->toArray();

        $this->form->fill([
            'full_name' => $user->full_name,
            'email' => $user->email,
            'username' => $user->username,
            'mobile_phone' => $user->mobile_phone,
            'asgl_id' => $user->asgl_id,
            'department' => $user->department,
            'approvers' => $approverIds,
        ]);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Grid::make(3)
                    ->schema([
                        Section::make('Thông tin cơ bản')
                            ->columns(2)
                            ->schema([
                                TextInput::make('full_name')
                                    ->label('Họ và tên')
                                    ->required(),
                                TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->required(),
                                TextInput::make('username')
                                    ->label('Tên đăng nhập')
                                    ->disabled(),
                                TextInput::make('mobile_phone')
                                    ->label('Số điện thoại'),
                                TextInput::make('asgl_id')
                                    ->label('Mã nhân viên')
                                    ->disabled(),
                                TextInput::make('department')
                                    ->label('Phòng ban')
                                    ->disabled(),
                                Select::make('approvers')
                                    ->label('Người phê duyệt')
                                    ->options(function () {
                                        $approverUserIds = DB::connection('mysql')
                                            ->table('model_has_roles')
                                            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                                            ->where('roles.name', 'approver')
                                            ->where('model_has_roles.model_type', User::class)
                                            ->pluck('model_id')
                                            ->toArray();

                                        return User::whereIn('id', $approverUserIds)
                                            ->pluck('full_name', 'id')
                                            ->toArray();
                                    })
                                    ->multiple()
                                    ->searchable()
                                    ->preload()
                                    ->columnSpanFull(),
                            ])
                            ->columnSpan(2),
                        Section::make('Đổi mật khẩu')
                            ->columns(1)
                            ->description('Bỏ trống nếu không muốn đổi mật khẩu')
                            ->schema([
                                TextInput::make('current_password')
                                    ->label('Mật khẩu hiện tại')
                                    ->password()
                                    ->autocomplete('current-password'),
                                TextInput::make('new_password')
                                    ->label('Mật khẩu mới')
                                    ->password()
                                    ->minLength(8)
                                    ->autocomplete('new-password'),
                                TextInput::make('new_password_confirmation')
                                    ->label('Xác nhận mật khẩu mới')
                                    ->password()
                                    ->same('new_password')
                                    ->requiredWith('new_password'),
                            ])
                            ->columnSpan(1),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Cập nhật thông tin')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $user = auth()->user();

        $update = [
            'full_name' => $data['full_name'] ?? $user->full_name,
            'email' => $data['email'] ?? $user->email,
            'mobile_phone' => $data['mobile_phone'] ?? $user->mobile_phone,
        ];

        // Kiểm tra mật khẩu hiện tại nếu muốn đổi mật khẩu
        if (! empty($data['new_password'])) {
            if (empty($data['current_password']) || ! Hash::check($data['current_password'], $user->password)) {
                Notification::make()
                    ->title('Lỗi')
                    ->body('Mật khẩu hiện tại không chính xác.')
                    ->danger()
                    ->send();

                return;
            }

            $update['password'] = $data['new_password'];
        }

        $user->update($update);

        // Sync approvers — xoá cũ và insert lại
        if (isset($data['approvers'])) {
            UserApprover::query()
                ->where('user_id', $user->id)
                ->delete();

            $now = now();
            foreach ($data['approvers'] as $approverId) {
                UserApprover::query()->insert([
                    'user_id' => $user->id,
                    'approver_id' => $approverId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        Notification::make()
            ->title('Thành công')
            ->body('Thông tin cá nhân đã được cập nhật.')
            ->success()
            ->send();

        $this->data = $data;
        $this->form->fill($this->data);
    }
}
