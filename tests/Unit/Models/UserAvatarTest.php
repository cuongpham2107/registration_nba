<?php

namespace Tests\Unit\Models;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserAvatarTest extends TestCase
{
    public function test_returns_custom_avatar_url_when_avatar_attribute_exists(): void
    {
        $user = new User;
        $user->forceFill([
            'avatar' => 'avatar.jpg',
            'asgl_id' => 'asgl-00735',
        ]);

        $this->assertSame('https://id.asgl.net.vn/avatar/ASGL-00735', $user->avatar);
    }

    public function test_returns_ui_avatar_when_avatar_attribute_is_missing(): void
    {
        $user = new User;
        $user->forceFill([
            'full_name' => 'Nguyen Van A',
        ]);

        $this->assertSame(
            'https://ui-avatars.com/api/?name=N+V+A&color=FFFFFF&background=71717b',
            $user->avatar
        );
    }
}
