<?php

namespace Tests\Feature;

use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAkunTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_list_omits_password_column_and_keeps_actions_after_last_online(): void
    {
        $admin = User::create([
            'username' => 'admin-user',
            'email' => 'admin@example.com',
            'password' => 'admin-password',
            'role' => 'admin',
        ]);
        $user = User::create([
            'username' => 'member-user',
            'email' => 'member@example.com',
            'password' => 'member-password',
            'role' => 'user',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.akun.index'))
            ->assertOk()
            ->assertSeeInOrder([
                '<th>USERNAME</th>',
                '<th>ROLE</th>',
                '<th>EMAIL</th>',
                '<th>TERDAFTAR</th>',
                '<th>TERAKHIR ONLINE</th>',
                '<th class="kanan">AKSI</th>',
            ], false)
            ->assertDontSee('<th>PASSWORD</th>', false)
            ->assertDontSee('••••••••')
            ->assertSee('member@example.com')
            ->assertSee('Belum pernah')
            ->assertSee('data-toggle-password', false)
            ->assertSee('Edit')
            ->assertSee('Hapus')
            ->assertDontSee('member-password')
            ->assertDontSee($user->getRawOriginal('password'));
    }

    public function test_successful_login_updates_last_online_timestamp(): void
    {
        $user = User::create([
            'username' => 'member-user',
            'password' => 'member-password',
            'role' => 'user',
        ]);

        $this->post(route('login.submit'), [
            'username' => 'member-user',
            'password' => 'member-password',
        ])->assertRedirect(route('dashboard'));

        $this->assertNotNull($user->fresh()->last_online_at);
    }

    public function test_admin_account_forms_use_admin_requests_to_create_and_update_accounts(): void
    {
        $admin = User::create([
            'username' => 'admin-user',
            'password' => 'admin-password',
            'role' => 'admin',
        ]);
        $member = User::create([
            'username' => 'member-user',
            'password' => 'member-password',
            'role' => 'user',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.akun.store'), [
                'username' => 'new-member',
                'password' => 'new-member-password',
                'role' => 'user',
            ])
            ->assertRedirect(route('admin.akun.index'));

        $this->assertDatabaseHas('user', [
            'username' => 'new-member',
            'role' => 'user',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.akun.update', $member), [
                'username' => 'updated-member',
                'password' => '',
                'role' => 'user',
            ])
            ->assertRedirect(route('admin.akun.index'));

        $this->assertDatabaseHas('user', [
            'id' => $member->id,
            'username' => 'updated-member',
            'role' => 'user',
        ]);
    }
}
