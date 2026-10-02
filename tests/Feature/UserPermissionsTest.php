<?php

namespace Tests\Feature;

use App\Models\MasterMenus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class UserPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_permissions_page_renders_the_menu_assignment_modal(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('user-permissions.index'))
            ->assertOk()
            ->assertSee('User Permissions')
            ->assertSee('Select All');
    }

    public function test_permissions_are_assigned_directly_to_a_user_and_returned_for_edit(): void
    {
        $authenticatedUser = User::factory()->create();
        $targetUser = User::factory()->create();
        $permission = Permission::create(['name' => 'departments.view', 'guard_name' => 'web']);

        $response = $this->actingAs($authenticatedUser)->postJson(route('user-permissions.store'), [
            'user_id' => $targetUser->id,
            'permissions' => [$permission->name],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('model_has_permissions', [
            'permission_id' => $permission->id,
            'model_id' => $targetUser->id,
            'model_type' => User::class,
        ]);

        $this->actingAs($authenticatedUser)
            ->getJson(route('user-permissions.edit', $targetUser))
            ->assertOk()
            ->assertJsonPath('data.permissions.0.id', $permission->id);
    }

    public function test_assignment_options_include_users_and_menu_permissions(): void
    {
        $authenticatedUser = User::factory()->create();
        $permission = Permission::create(['name' => 'departments.view', 'guard_name' => 'web']);
        MasterMenus::create(['name' => 'departments', 'display_name' => 'Departments']);

        $this->actingAs($authenticatedUser)
            ->getJson(route('user-permissions.assignment-options'))
            ->assertOk()
            ->assertJsonFragment(['id' => $authenticatedUser->id, 'name' => $authenticatedUser->name])
            ->assertJsonPath('data.menus.0.available_actions.0.full_name', 'departments.view')
            ->assertJsonMissingPath('data.roles');
    }

    public function test_updating_permissions_replaces_existing_direct_assignments(): void
    {
        $authenticatedUser = User::factory()->create();
        $targetUser = User::factory()->create();
        $oldPermission = Permission::create(['name' => 'departments.view', 'guard_name' => 'web']);
        $newPermission = Permission::create(['name' => 'departments.update', 'guard_name' => 'web']);
        $targetUser->givePermissionTo($oldPermission);

        $this->actingAs($authenticatedUser)
            ->postJson(route('user-permissions.store'), [
                'id' => $targetUser->id,
                'user_id' => $targetUser->id,
                'permissions' => [$newPermission->name],
            ])
            ->assertOk();

        $this->assertDatabaseMissing('model_has_permissions', [
            'permission_id' => $oldPermission->id,
            'model_id' => $targetUser->id,
            'model_type' => User::class,
        ]);
        $this->assertDatabaseHas('model_has_permissions', [
            'permission_id' => $newPermission->id,
            'model_id' => $targetUser->id,
            'model_type' => User::class,
        ]);
    }

    public function test_deleting_a_user_permission_only_removes_direct_permissions(): void
    {
        $authenticatedUser = User::factory()->create();
        $targetUser = User::factory()->create();
        $permission = Permission::create(['name' => 'departments.view', 'guard_name' => 'web']);
        $targetUser->givePermissionTo($permission);

        $this->actingAs($authenticatedUser)
            ->deleteJson(route('user-permissions.destroy', $targetUser))
            ->assertOk();

        $this->assertDatabaseHas('users', ['id' => $targetUser->id]);
        $this->assertDatabaseMissing('model_has_permissions', [
            'permission_id' => $permission->id,
            'model_id' => $targetUser->id,
            'model_type' => User::class,
        ]);
    }
}
