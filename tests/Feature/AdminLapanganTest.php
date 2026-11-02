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
            ->assertSee('Lapangan ini sudah dipesan oleh Admin')
            ->assertSee('Lapangan belum dipesan sama sekali')
            ->assertSee('img/lapangan.jpeg');
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
