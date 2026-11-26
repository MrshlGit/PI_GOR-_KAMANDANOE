<?php

namespace Tests\Feature;

use App\Models\Admin\Turnamen;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class TurnamenTest extends TestCase
{
    use RefreshDatabase;

    private ?string $posterPath = null;

    protected function tearDown(): void
    {
        if ($this->posterPath) {
            File::delete($this->posterPath);
        }

        parent::tearDown();
    }

    public function test_user_can_submit_tournament_with_poster_for_admin_approval(): void
    {
        $user = User::create([
            'username' => 'Pemain',
            'password' => 'password',
            'role' => 'user',
        ]);
        $data = $this->dataTurnamen() + [
            'poster' => UploadedFile::fake()->createWithContent(
                'poster.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/7xcAAAAASUVORK5CYII=')
            ),
        ];

        $this->actingAs($user)
            ->post(route('turnamen.store'), $data)
            ->assertRedirect(route('turnamen.index', ['tab' => 'saya']));

        $turnamen = Turnamen::firstOrFail();
        $this->assertSame('menunggu', $turnamen->status);
        $this->assertSame($user->id, $turnamen->id_user);
        $this->assertNotEmpty($turnamen->poster);
        $this->posterPath = public_path($turnamen->poster);
        $this->assertFileExists($this->posterPath);

        $this->actingAs($user)
            ->get(route('turnamen.index'))
            ->assertOk()
            ->assertDontSee('Kejuaraan Pemain');

        $this->actingAs($user)
            ->get(route('turnamen.index', ['tab' => 'saya']))
            ->assertOk()
            ->assertSee('Kejuaraan Pemain');
    }

    public function test_admin_can_approve_pending_tournament_and_publish_it(): void
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

        $this->actingAs($user)->post(route('turnamen.store'), $this->dataTurnamen());
        $turnamen = Turnamen::firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.turnamen.index'))
            ->assertOk()
            ->assertSee('Kejuaraan Pemain')
            ->assertSee('Setujui');

        $this->actingAs($admin)
            ->patch(route('admin.turnamen.status', $turnamen->id_turnamen), ['status' => 'disetujui'])
            ->assertRedirect();

        $this->assertDatabaseHas('turnamen', [
            'id_turnamen' => $turnamen->id_turnamen,
            'status' => 'disetujui',
        ]);
        $this->actingAs($user)
            ->get(route('turnamen.index'))
            ->assertOk()
            ->assertSee('Kejuaraan Pemain');
    }

    public function test_user_can_submit_tournament_today_and_is_redirected_to_own_submissions(): void
    {
        $user = User::create([
            'username' => 'Pemain',
            'password' => 'password',
            'role' => 'user',
        ]);
        $data = $this->dataTurnamen();
        $data['tgl_pelaksanaan'] = today()->toDateString();
        $data['batas_pendaftaran'] = today()->toDateString();

        $this->actingAs($user)
            ->post(route('turnamen.store'), $data)
            ->assertRedirect(route('turnamen.index', ['tab' => 'saya']));

        $this->assertDatabaseHas('turnamen', [
            'nama_turnamen' => 'Kejuaraan Pemain',
            'status' => 'menunggu',
            'id_user' => $user->id,
        ]);
    }

    private function dataTurnamen(): array
    {
        return [
            'nama_turnamen' => 'Kejuaraan Pemain',
            'kategori' => 'Tunggal Putra',
            'tgl_pelaksanaan' => today()->addDays(10)->toDateString(),
            'batas_pendaftaran' => today()->addDays(5)->toDateString(),
            'lokasi' => 'GOR Kamandanoe',
            'biaya_pendaftaran' => 50000,
            'kuota_tim' => 16,
            'kontak' => '081234567890',
            'deskripsi' => 'Turnamen internal.',
        ];
    }
}
