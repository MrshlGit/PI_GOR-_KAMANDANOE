<?php

namespace Tests\Feature;

use App\Models\Admin\Turnamen;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TurnamenTest extends TestCase
{
    use RefreshDatabase;

    private ?string $posterPath = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('booking')) {
            Schema::create('booking', function (Blueprint $table): void {
                $table->string('id_booking')->primary();
                $table->unsignedInteger('id_user');
                $table->string('id_lapangan');
                $table->date('tgl_main');
                $table->time('jam_mulai');
                $table->unsignedTinyInteger('durasi')->default(1);
                $table->string('status_booking');
                $table->decimal('total_bayar', 12, 2);
            });
        }
    }

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
        $this->createActiveBooking($user);
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

        $this->createActiveBooking($user);
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

    public function test_admin_can_create_and_publish_a_tournament_with_its_schedule(): void
    {
        $admin = User::create([
            'username' => 'Admin',
            'password' => 'password',
            'role' => 'admin',
        ]);
        $data = array_merge($this->dataTurnamen(), [
            'nama_turnamen' => 'Event Admin',
            'jam_mulai' => '09:00',
            'jam_selesai' => '12:00',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.turnamen.store'), $data)
            ->assertRedirect(route('admin.turnamen.index', ['tab' => 'akan_datang']));

        $turnamen = Turnamen::firstOrFail();
        $this->assertSame(Turnamen::STATUS_APPROVED, $turnamen->status);
        $this->assertSame($admin->id, $turnamen->id_user);
        $this->assertSame('09:00', substr($turnamen->jam_mulai, 0, 5));
        $this->assertSame('12:00', substr($turnamen->jam_selesai, 0, 5));

        $this->actingAs($admin)
            ->get(route('turnamen.index'))
            ->assertOk()
            ->assertSee('Event Admin')
            ->assertSee('tn-poster-placeholder')
            ->assertSee('09:00 WIB')
            ->assertSee('12:00 WIB');
    }

    public function test_admin_event_tabs_filter_ended_and_archived_tournaments(): void
    {
        $organizer = User::create([
            'username' => 'Penyelenggara',
            'password' => 'password',
            'role' => 'user',
        ]);
        $admin = User::create([
            'username' => 'Admin',
            'password' => 'password',
            'role' => 'admin',
        ]);

        Turnamen::create(array_merge($this->dataTurnamen(), [
            'id_turnamen' => 'TRN-END-001',
            'id_user' => $organizer->id,
            'nama_turnamen' => 'Event Berakhir',
            'tgl_pelaksanaan' => today()->subDay()->toDateString(),
            'status' => Turnamen::STATUS_APPROVED,
        ]));
        Turnamen::create(array_merge($this->dataTurnamen(), [
            'id_turnamen' => 'TRN-OLD-001',
            'id_user' => $organizer->id,
            'nama_turnamen' => 'Event Otomatis Arsip',
            'tgl_pelaksanaan' => today()->subDays(31)->toDateString(),
            'status' => Turnamen::STATUS_APPROVED,
        ]));
        Turnamen::create(array_merge($this->dataTurnamen(), [
            'id_turnamen' => 'TRN-ARC-001',
            'id_user' => $organizer->id,
            'nama_turnamen' => 'Event Arsip',
            'status' => Turnamen::STATUS_REJECTED,
        ]));

        $this->actingAs($admin)
            ->get(route('admin.turnamen.index', ['tab' => 'berakhir']))
            ->assertOk()
            ->assertSee('Event Berakhir')
            ->assertDontSee('Event Arsip')
            ->assertDontSee('Event Otomatis Arsip');

        $this->actingAs($admin)
            ->get(route('admin.turnamen.index', ['tab' => 'arsip']))
            ->assertOk()
            ->assertSee('Event Arsip')
            ->assertSee('Event Otomatis Arsip')
            ->assertDontSee('Event Berakhir');
    }

    public function test_user_can_submit_tournament_today_and_is_redirected_to_own_submissions(): void
    {
        $user = User::create([
            'username' => 'Pemain',
            'password' => 'password',
            'role' => 'user',
        ]);
        $this->createActiveBooking($user);
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

    public function test_user_cannot_submit_tournament_without_active_court_booking(): void
    {
        $user = User::create([
            'username' => 'Pemain',
            'password' => 'password',
            'role' => 'user',
        ]);

        $this->actingAs($user)
            ->post(route('turnamen.store'), $this->dataTurnamen())
            ->assertRedirect(route('turnamen.index'))
            ->assertSessionHas('tn_err')
            ->assertSessionHas('buka_form_turnamen', true);

        $this->assertDatabaseCount('turnamen', 0);
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

    private function createActiveBooking(User $user): void
    {
        DB::table('booking')->insert([
            'id_booking' => uniqid('BOOK-', true),
            'id_user' => $user->id,
            'id_lapangan' => 'LAP-1',
            'tgl_main' => today()->toDateString(),
            'jam_mulai' => '09:00:00',
            'durasi' => 1,
            'status_booking' => 'menunggu',
            'total_bayar' => 50000,
        ]);
    }
}
