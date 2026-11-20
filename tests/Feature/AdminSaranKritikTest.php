<?php

namespace Tests\Feature;

use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSaranKritikTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_feedback_is_saved_and_visible_to_admin(): void
    {
        $user = User::create([
            'username' => 'Pemain',
            'password' => 'password',
            'role' => 'user',
        ]);
        $admin = User::create([
            'username' => 'Admin',
            'password' => 'password',
            'role' => 'admin',
        ]);

        $this->actingAs($user)
            ->post(route('layanan.saran'), [
                'nama' => 'Pemain Lapangan',
                'isi' => 'Mohon tambah tempat duduk di area tunggu.',
            ])
            ->assertRedirect(route('layanan.index').'#saran');

        $this->assertDatabaseHas('saran_kritik', [
            'id_user' => $user->id,
            'nama' => 'Pemain Lapangan',
            'isi' => 'Mohon tambah tempat duduk di area tunggu.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.saran-kritik.index'))
            ->assertOk()
            ->assertSee('Saran & Kritik')
            ->assertSee('Pemain Lapangan')
            ->assertSee('Pemain')
            ->assertSee('Mohon tambah tempat duduk di area tunggu.');
    }
}
