<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class UsersStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_users_are_verified_active_and_have_a_hashed_password(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->postJson(route('users.store'), [
                'nrk' => 1234567,
                'name' => 'New User',
                'email' => 'new-user@example.test',
                'password' => 'secret-password',
                'system_login' => true,
            ])
            ->assertOk();

        $user = User::where('email', 'new-user@example.test')->firstOrFail();

        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue($user->is_active);
        $this->assertTrue(Hash::check('secret-password', $user->password));
        $this->assertTrue($user->getDirectPermissions()->contains('name', 'system.login'));

        $this->actingAs($admin)
            ->getJson(route('users.edit', $user))
            ->assertOk()
            ->assertJsonPath('data.system_login', true);

        $this->post('/logout');
        $this->post('/login', [
            'login' => 'new-user@example.test',
            'password' => 'secret-password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
    }

    public function test_system_login_toggle_revokes_permission_when_unchecked(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::create([
            'name' => 'system.login',
            'guard_name' => 'web',
        ]));

        $this->actingAs($admin)
            ->postJson(route('users.store'), [
                'id' => $user->id,
                'nrk' => $user->nrk,
                'name' => $user->name,
                'email' => $user->email,
                'system_login' => false,
            ])
            ->assertOk();

        $this->assertFalse($user->fresh()->getDirectPermissions()->contains('name', 'system.login'));

        $this->actingAs($admin)
            ->getJson(route('users.edit', $user))
            ->assertOk()
            ->assertJsonPath('data.system_login', false);
    }
}
