<?php

namespace Tests\Feature;

use App\Models\Admin\Booking;
use App\Models\Admin\Lapangan;
use App\Models\User\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminLapanganTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('lapangan', function (Blueprint $table) {
            $table->string('id_lapangan', 11)->primary();
            $table->string('tipe_lantai', 100)->nullable();
            $table->decimal('harga_per_jam', 12, 2)->nullable();
            $table->string('status_lapangan', 100)->nullable();
            $table->string('nama_lapangan', 20)->nullable();
            $table->unsignedTinyInteger('kapasitas')->default(4);
        });

        Schema::create('booking', function (Blueprint $table) {
            $table->string('id_booking', 20)->primary();
            $table->unsignedInteger('id_user');
            $table->string('id_lapangan', 11);
            $table->date('tgl_main');
            $table->time('jam_mulai');
            $table->unsignedTinyInteger('durasi')->default(1);
            $table->string('status_booking', 20);
            $table->decimal('total_bayar', 10, 2);
        });
    }

    public function test_schedule_shows_booking_owner_and_available_slots(): void
    {
        $admin = User::create([
            'username' => 'Admin',
            'password' => 'password',
            'role' => 'admin',
        ]);
        Lapangan::create([
            'id_lapangan' => '1',
            'nama_lapangan' => 'Lapangan A',
            'tipe_lantai' => 'Vinyl Premium',
            'harga_per_jam' => 50000,
            'kapasitas' => 4,
            'status_lapangan' => 'Aktif',
        ]);
        Booking::create([
            'id_booking' => 'BOOKING-1',
            'id_user' => $admin->id,
            'id_lapangan' => '1',
            'tgl_main' => today()->toDateString(),
            'jam_mulai' => '09:00:00',
            'durasi' => 2,
            'status_booking' => 'menunggu',
            'total_bayar' => 100000,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.lapangan.index'))
            ->assertOk()
            ->assertSee('class="menu-disabled" aria-disabled="true"', false)
            ->assertSee('Manajemen Pemesanan')
            ->assertSee('Sedang dibooking oleh')
            ->assertSee('Apakah kamu menyetujuinya?')
            ->assertSee('data-status="pending"', false)
            ->assertSee('data-pemesan="Admin"', false)
            ->assertSee('data-pesan="Lapangan ini belum dipesan."', false)
            ->assertSee('Lapangan ini belum dipesan.')
            ->assertSee('img/images.jpg');
    }

    public function test_admin_can_confirm_a_pending_booking_from_the_schedule(): void
    {
        $admin = User::create([
            'username' => 'Admin',
            'password' => 'password',
            'role' => 'admin',
        ]);
        Lapangan::create([
            'id_lapangan' => '1',
            'nama_lapangan' => 'Lapangan A',
            'tipe_lantai' => 'Vinyl Premium',
            'harga_per_jam' => 50000,
            'kapasitas' => 4,
            'status_lapangan' => 'Aktif',
        ]);
        Booking::create([
            'id_booking' => 'BOOKING-1',
            'id_user' => $admin->id,
            'id_lapangan' => '1',
            'tgl_main' => today()->toDateString(),
            'jam_mulai' => '09:00:00',
            'durasi' => 2,
            'status_booking' => 'menunggu',
            'total_bayar' => 100000,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.lapangan.booking-status', 'BOOKING-1'), [
                'status_booking' => 'dikonfirmasi',
                'tanggal' => today()->toDateString(),
            ])
            ->assertRedirect(route('admin.lapangan.index', ['tanggal' => today()->toDateString()]));

        $this->assertDatabaseHas('booking', [
            'id_booking' => 'BOOKING-1',
            'status_booking' => 'dikonfirmasi',
        ]);
    }

    public function test_schedule_status_action_does_not_change_a_processed_booking(): void
    {
        $admin = User::create([
            'username' => 'Admin',
            'password' => 'password',
            'role' => 'admin',
        ]);
        Booking::create([
            'id_booking' => 'BOOKING-1',
            'id_user' => $admin->id,
            'id_lapangan' => '1',
            'tgl_main' => today()->toDateString(),
            'jam_mulai' => '09:00:00',
            'durasi' => 1,
            'status_booking' => 'dikonfirmasi',
            'total_bayar' => 50000,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.lapangan.booking-status', 'BOOKING-1'), [
                'status_booking' => 'batal',
                'tanggal' => today()->toDateString(),
            ])
            ->assertRedirect(route('admin.lapangan.index', ['tanggal' => today()->toDateString()]))
            ->assertSessionHas('galat');

        $this->assertDatabaseHas('booking', [
            'id_booking' => 'BOOKING-1',
            'status_booking' => 'dikonfirmasi',
        ]);
    }

    public function test_admin_can_create_a_court(): void
    {
        $admin = User::create([
            'username' => 'Admin',
            'password' => 'password',
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.lapangan.store'), [
                'id_lapangan' => '2',
                'nama_lapangan' => 'Lapangan B',
                'tipe_lantai' => 'Vinyl',
                'harga_per_jam' => 60000,
                'kapasitas' => 4,
                'status_lapangan' => 'Aktif',
            ])
            ->assertRedirect(route('admin.lapangan.index'));

        $this->assertDatabaseHas('lapangan', [
            'id_lapangan' => '2',
            'nama_lapangan' => 'Lapangan B',
            'kapasitas' => 4,
        ]);
    }

    // public function test_user_cannot_book_a_time_that_overlaps_an_existing_booking(): void
    // {
    //     $user = User::create([
    //         'username' => 'Pemain',
    //         'password' => 'password',
    //         'role' => 'user',
    //     ]);
    //     $existingUser = User::create([
    //         'username' => 'Pemain Lain',
    //         'password' => 'password',
    //         'role' => 'user',
    //     ]);

    //     Lapangan::create([
    //         'id_lapangan' => '3',
    //         'nama_lapangan' => 'Lapangan C',
    //         'tipe_lantai' => 'Vinyl',
    //         'harga_per_jam' => 50000,
    //         'kapasitas' => 4,
    //         'status_lapangan' => 'Aktif',
    //     ]);
    //     Booking::create([
    //         'id_booking' => 'BOOKING-EXISTING',
    //         'id_user' => $existingUser->id,
    //         'id_lapangan' => '3',
    //         'tgl_main' => today()->addDays(2)->toDateString(),
    //         'jam_mulai' => '10:00:00',
    //         'durasi' => 2,
    //         'status_booking' => 'dikonfirmasi',
    //         'total_bayar' => 100000,
    //     ]);

    //     $this->actingAs($user)
    //         ->from(route('booking.create'))
    //         ->post(route('booking.store'), [
    //             'id_lapangan' => '3',
    //             'tanggal' => today()->addDays(2)->toDateString(),
    //             'jam' => '11:00',
    //             'durasi' => 1,
    //         ])
    //         ->assertRedirect(route('booking.create', ['tanggal' => today()->addDays(2)->toDateString()]))
    //         ->assertSessionHas('booking_result', fn (array $result) => $result['type'] === 'full');

    //     $this->assertDatabaseCount('booking', 1);
    // }

    // public function test_successful_booking_shows_pending_admin_confirmation_result(): void
    // {
    //     $user = User::create([
    //         'username' => 'Pemain',
    //         'password' => 'password',
    //         'role' => 'user',
    //     ]);
    //     Lapangan::create([
    //         'id_lapangan' => '4',
    //         'nama_lapangan' => 'Lapangan D',
    //         'tipe_lantai' => 'Vinyl',
    //         'harga_per_jam' => 50000,
    //         'kapasitas' => 4,
    //         'status_lapangan' => 'Aktif',
    //     ]);
    //     $tanggal = today()->addDays(2)->toDateString();

    //     $this->actingAs($user)
    //         ->post(route('booking.store'), [
    //             'id_lapangan' => '4',
    //             'tanggal' => $tanggal,
    //             'jam' => '10:00',
    //             'durasi' => 1,
    //         ])
    //         ->assertRedirect(route('booking.create', ['tanggal' => $tanggal]))
    //         ->assertSessionHas('booking_result', fn (array $result) => $result['type'] === 'success');

    //     $this->assertDatabaseHas('booking', [
    //         'id_user' => $user->id,
    //         'id_lapangan' => '4',
    //         'status_booking' => 'menunggu',
    //         'total_bayar' => 50000,
    //     ]);
    // }

    // public function test_booking_page_shows_schedule_for_selected_date(): void
    // {
    //     $user = User::create([
    //         'username' => 'Pemain',
    //         'password' => 'password',
    //         'role' => 'user',
    //     ]);
    //     $otherUser = User::create([
    //         'username' => 'Pemain Lain',
    //         'password' => 'password',
    //         'role' => 'user',
    //     ]);

    //     foreach (['1', '2'] as $idLapangan) {
    //         Lapangan::create([
    //             'id_lapangan' => $idLapangan,
    //             'nama_lapangan' => 'Lapangan '.$idLapangan,
    //             'tipe_lantai' => 'Vinyl',
    //             'harga_per_jam' => 50000,
    //             'kapasitas' => 4,
    //             'status_lapangan' => 'Aktif',
    //         ]);
    //     }

    //     $tanggal = today()->addDays(2)->toDateString();
    //     Booking::create([
    //         'id_booking' => 'BOOKING-SAYA',
    //         'id_user' => $user->id,
    //         'id_lapangan' => '1',
    //         'tgl_main' => $tanggal,
    //         'jam_mulai' => '09:00:00',
    //         'durasi' => 2,
    //         'status_booking' => 'menunggu',
    //         'total_bayar' => 100000,
    //     ]);
    //     Booking::create([
    //         'id_booking' => 'BOOKING-ORANG',
    //         'id_user' => $otherUser->id,
    //         'id_lapangan' => '2',
    //         'tgl_main' => $tanggal,
    //         'jam_mulai' => '11:00:00',
    //         'durasi' => 1,
    //         'status_booking' => 'menunggu',
    //         'total_bayar' => 50000,
    //     ]);
    //     Booking::create([
    //         'id_booking' => 'BOOKING-TANGGAL-LAIN',
    //         'id_user' => $otherUser->id,
    //         'id_lapangan' => '1',
    //         'tgl_main' => today()->addDays(3)->toDateString(),
    //         'jam_mulai' => '13:00:00',
    //         'durasi' => 1,
    //         'status_booking' => 'dikonfirmasi',
    //         'total_bayar' => 50000,
    //     ]);

    //     $this->actingAs($user)
    //         ->get(route('booking.create', ['tanggal' => $tanggal]))
    //         ->assertOk()
    //         ->assertSee('value="'.$tanggal.'"', false)
    //         ->assertSee('class="schedule-cell schedule-cell--saya"', false)
    //         ->assertSee('class="schedule-cell schedule-cell--menunggu"', false)
    //         ->assertSee('class="schedule-cell schedule-cell--kosong"', false)
    //         ->assertDontSee('class="schedule-cell schedule-cell--terisi"', false);
    // }

    public function test_user_booking_saves_duration_for_the_schedule(): void
    {
        $user = User::create([
            'username' => 'Pemain',
            'password' => 'password',
            'role' => 'user',
        ]);
        DB::table('lapangan')->insert([
            'id_lapangan' => '3',
            'nama_lapangan' => 'Lapangan C',
            'tipe_lantai' => 'Vinyl',
            'harga_per_jam' => 50000,
            'kapasitas' => 4,
            'status_lapangan' => 'Aktif',
        ]);

        $this->actingAs($user)
            ->post(route('booking.store'), [
                'id_lapangan' => '3',
                'tanggal' => today()->toDateString(),
                'jam' => '09:00',
                'durasi' => 3,
            ])
            ->assertRedirect(route('booking.create'));

        $this->assertDatabaseHas('booking', [
            'id_user' => $user->id,
            'id_lapangan' => '3',
            'durasi' => 3,
            'total_bayar' => 150000,
        ]);
    }
}
