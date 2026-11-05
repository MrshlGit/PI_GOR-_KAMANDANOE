<?php

namespace Tests\Feature;

use App\Models\Admin\Booking;
use App\Models\Admin\Lapangan;
use App\Models\User\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminPemesananTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

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
            $table->string('nama_pemesan', 100)->nullable();
            $table->string('no_hp_pemesan', 20)->nullable();
            $table->date('tgl_main');
            $table->time('jam_mulai');
            $table->unsignedTinyInteger('durasi')->default(1);
            $table->string('status_booking', 20);
            $table->decimal('total_bayar', 10, 2);
        });

        $this->admin = User::create([
            'username' => 'Admin',
            'password' => 'password',
            'role' => 'admin',
        ]);

        Lapangan::create([
            'id_lapangan' => '1',
            'nama_lapangan' => 'Lapangan A',
            'tipe_lantai' => 'Vinyl',
            'harga_per_jam' => 50000,
            'kapasitas' => 4,
            'status_lapangan' => 'Aktif',
        ]);
    }

    public function test_admin_can_view_bookings_and_filter_by_status(): void
    {
        Booking::create([
            'id_booking' => 'BOOKING-1',
            'id_user' => $this->admin->id,
            'id_lapangan' => '1',
            'tgl_main' => today()->toDateString(),
            'jam_mulai' => '09:00:00',
            'durasi' => 1,
            'status_booking' => 'menunggu',
            'total_bayar' => 50000,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.pemesanan.index', ['status' => 'menunggu']))
            ->assertOk()
            ->assertSee('Manajemen Pemesanan')
            ->assertSee('BOOKING-1')
            ->assertSee('Konfirmasi');
    }

    public function test_admin_can_create_offline_booking_and_cannot_double_book(): void
    {
        $payload = [
            'nama_pemesan' => 'Pelanggan Loket',
            'no_hp_pemesan' => '08123456789',
            'id_lapangan' => '1',
            'tgl_main' => today()->toDateString(),
            'jam_mulai' => '09:00',
            'durasi' => 2,
        ];

        $this->actingAs($this->admin)
            ->post(route('admin.pemesanan.store'), $payload)
            ->assertRedirect(route('admin.pemesanan.index'));

        $this->assertDatabaseHas('booking', [
            'nama_pemesan' => 'Pelanggan Loket',
            'no_hp_pemesan' => '08123456789',
            'durasi' => 2,
            'status_booking' => 'dikonfirmasi',
            'total_bayar' => 100000,
        ]);

        $this->actingAs($this->admin)
            ->from(route('admin.pemesanan.index'))
            ->post(route('admin.pemesanan.store'), [...$payload, 'jam_mulai' => '10:00'])
            ->assertSessionHasErrors('jam_mulai');

        $this->assertDatabaseCount('booking', 1);
    }

    public function test_admin_can_confirm_a_pending_booking(): void
    {
        $booking = Booking::create([
            'id_booking' => 'BOOKING-2',
            'id_user' => $this->admin->id,
            'id_lapangan' => '1',
            'tgl_main' => today()->toDateString(),
            'jam_mulai' => '09:00:00',
            'durasi' => 1,
            'status_booking' => 'menunggu',
            'total_bayar' => 50000,
        ]);

        $this->actingAs($this->admin)
            ->patch(route('admin.pemesanan.status', $booking), ['status_booking' => 'dikonfirmasi'])
            ->assertRedirect(route('admin.pemesanan.index'));

        $this->assertDatabaseHas('booking', [
            'id_booking' => 'BOOKING-2',
            'status_booking' => 'dikonfirmasi',
        ]);
    }
}
