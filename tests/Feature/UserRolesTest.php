<?php

namespace Tests\Feature;

use App\Models\Roles;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class UserRolesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_roles_page_renders_the_assignment_table(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('user-roles.index'))
            ->assertOk()
            ->assertSee('User Roles')
            ->assertSee('Roles')
            ->assertDontSee('Delete');
    }

    public function test_assignment_options_include_users_and_guard_roles(): void
    {
        $authenticatedUser = User::factory()->create();
        $targetUser = User::factory()->create();
        Roles::create(['name' => 'editor', 'guard_name' => 'web']);

        $this->actingAs($authenticatedUser)
            ->getJson(route('user-roles.assignment-options'))
            ->assertOk()
            ->assertJsonFragment([
                'id' => $targetUser->id,
                'name' => $targetUser->name,
                'email' => $targetUser->email,
            ])
            ->assertJsonPath('data.roles.0.name', 'editor');
    }

    public function test_user_roles_are_synced_to_the_model_has_roles_table(): void
    {
        $authenticatedUser = User::factory()->create();
        $targetUser = User::factory()->create();
        $oldRole = Roles::create(['name' => 'old-role', 'guard_name' => 'web']);
        $newRole = Roles::create(['name' => 'new-role', 'guard_name' => 'web']);
        $targetUser->assignRole($oldRole);

        $response = $this->actingAs($authenticatedUser)->postJson(route('user-roles.store'), [
            'user_id' => $targetUser->id,
            'roles' => [(string) $newRole->id],
        ]);

        $response->assertOk();
        $this->assertDatabaseMissing('model_has_roles', [
            'role_id' => $oldRole->id,
            'model_id' => $targetUser->id,
            'model_type' => User::class,
        ]);
        $this->assertDatabaseHas('model_has_roles', [
            'role_id' => $newRole->id,
            'model_id' => $targetUser->id,
            'model_type' => User::class,
        ]);

        $this->actingAs($authenticatedUser)
            ->getJson(route('user-roles.edit', $targetUser))
            ->assertOk()
            ->assertJsonPath('data.roles.0.id', $newRole->id);
    }

    public function test_user_roles_resource_does_not_register_a_delete_route(): void
    {
        $this->assertFalse(Route::has('user-roles.destroy'));
    }
}
