<?php

namespace App\Filament\Pages;

use App\Models\User;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Facades\Filament;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\SimplePage;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Http;

/**
 * @property Form $form
 */
class Login extends SimplePage
{
    use InteractsWithFormActions;
    use WithRateLimiting;

    protected static string $view = 'filament-panels::pages.auth.login';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(): void
    {
        if (Filament::auth()->check()) {
            redirect()->intended(Filament::getUrl());
        }

        $this->form->fill();
    }
    /**
     * Ghi đè phần login để login bằng api của ASGL
     * @return LoginResponse|null
     */
    public function authenticate(): ?LoginResponse
    {
        //Check nếu login bằng tài khoảnt trong database đúng thì tiếp tục nếu không thì login bằng api của ASGL


        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();
            return null;
        }
        $data = $this->form->getState();
        if(!Filament::auth()->attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false)){
            $loginAsgl = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post('https://id.asgl.net.vn/api/auth/login',
            [
                'login' => $data['username'],
                'password' => $data['password'],
            ]);
            if(!$loginAsgl->successful()){
                $this->throwFailureValidationException();
            }
            $userResponse = $loginAsgl->json()['data']['user'];

            // Tìm user dựa trên asgl_id (unique identifier từ ASGL)
            // Nếu không tìm thấy thì tìm theo username
            $user = User::where('asgl_id', $userResponse['id'])
                ->orWhere('username', $userResponse['username'])
                ->first();
            if ($user) {
                // Cập nhật thông tin user hiện tại
                $user->update([
                    'name' => $userResponse['full_name'],
                    'username' => $userResponse['username'],
                    'mobile_phone' => $userResponse['mobile_phone'],
                    'asgl_id' => $userResponse['id'],
                    'avatar' => $userResponse['avatar'],
                    'department_name' => $userResponse['positions'][0]['department']['short_code'] ?? null,
                ]);
            } else {
                // Tạo user mới nếu chưa tồn tại
                $user = User::create([
                    'name' => $userResponse['full_name'],
                    'username' => $userResponse['username'],
                    'mobile_phone' => $userResponse['mobile_phone'],
                    'asgl_id' => $userResponse['id'],
                    'avatar' => $userResponse['avatar'],
                    'email' => $userResponse['email'] ?? $userResponse['username'],
                    'password' => \Illuminate\Support\Str::password(),
                    'department_name' => $userResponse['positions'][0]['department']['short_code'] ?? null,
                ]);
                
                // KHÔNG tự động gán role - Admin sẽ gán role thủ công
                // Nếu muốn auto-assign role, uncomment dòng dưới:
                // $user->assignRole('panel_user');
            }
            
            Filament::auth()->login($user);
        }
        else
        {
            $user = Filament::auth()->user();
        }


        if (
            ($user instanceof FilamentUser) &&
            (! $user->canAccessPanel(Filament::getCurrentPanel()))
        ) {
            Filament::auth()->logout();
            $this->throwFailureValidationException();
        }
        session()->regenerate();
        return app(LoginResponse::class);
    }

    protected function getRateLimitedNotification(TooManyRequestsException $exception): ?Notification
    {
        return Notification::make()
            ->title(__('filament-panels::pages/auth/login.notifications.throttled.title', [
                'seconds' => $exception->secondsUntilAvailable,
                'minutes' => $exception->minutesUntilAvailable,
            ]))
            ->body(array_key_exists('body', __('filament-panels::pages/auth/login.notifications.throttled') ?: []) ? __('filament-panels::pages/auth/login.notifications.throttled.body', [
                'seconds' => $exception->secondsUntilAvailable,
                'minutes' => $exception->minutesUntilAvailable,
            ]) : null)
            ->danger();
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.username' => __('filament-panels::pages/auth/login.messages.failed'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form;
    }

    /**
     * @return array<int | string, string | Form>
     */
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getRememberFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('username')
            ->label('Tên đăng nhập')
            ->required()
            ->autocomplete('username')
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('filament-panels::pages/auth/login.form.password.label'))
            ->hint(filament()->hasPasswordReset() ? new HtmlString(Blade::render('<x-filament::link :href="filament()->getRequestPasswordResetUrl()" tabindex="3"> {{ __(\'filament-panels::pages/auth/login.actions.request_password_reset.label\') }}</x-filament::link>')) : null)
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->autocomplete('current-password')
            ->required()
            ->extraInputAttributes(['tabindex' => 2]);
    }

    protected function getRememberFormComponent(): Component
    {
        return Checkbox::make('remember')
            ->label(__('filament-panels::pages/auth/login.form.remember.label'));
    }

    public function registerAction(): Action
    {
        return Action::make('register')
            ->link()
            ->label(__('filament-panels::pages/auth/login.actions.register.label'))
            ->url(filament()->getRegistrationUrl());
    }

    public function getTitle(): string | Htmlable
    {
        return __('filament-panels::pages/auth/login.title');
    }

    public function getHeading(): string | Htmlable
    {
        return __('filament-panels::pages/auth/login.heading');
    }

    /**
     * @return array<Action | ActionGroup>
     */
    protected function getFormActions(): array
    {
        return [
            $this->getAuthenticateFormAction(),
        ];
    }

    protected function getAuthenticateFormAction(): Action
    {
        return Action::make('authenticate')
            ->label(__('filament-panels::pages/auth/login.form.actions.authenticate.label'))
            ->submit('authenticate');
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'username' => $data['username'],
            'password' => $data['password'],
        ];
    }
}
