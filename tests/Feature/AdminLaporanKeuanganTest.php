<?php

namespace Tests\Feature;

use App\Models\User\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminLaporanKeuanganTest extends TestCase
{
    use RefreshDatabase;

    private ?string $temporaryProofPath = null;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('lapangan', function (Blueprint $table): void {
            $table->string('id_lapangan', 11)->primary();
            $table->string('tipe_lantai', 100)->nullable();
            $table->decimal('harga_per_jam', 12, 2)->nullable();
            $table->string('status_lapangan', 100)->nullable();
            $table->string('nama_lapangan', 20)->nullable();
            $table->unsignedTinyInteger('kapasitas')->default(4);
        });

        Schema::create('booking', function (Blueprint $table): void {
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

        Schema::create('detail_item_booking', function (Blueprint $table): void {
            $table->string('id_detail', 50)->primary();
            $table->string('id_booking', 50);
            $table->decimal('subtotal', 10, 0)->nullable();
            $table->string('id_produk', 50)->nullable();
            $table->integer('kuantitas')->nullable();
        });

        Schema::create('pembayaran', function (Blueprint $table): void {
            $table->string('id_pembayaran', 50)->primary();
            $table->date('tgl_bayar')->nullable();
            $table->string('id_detail_item_booking', 50)->nullable();
            $table->string('metode_bayar', 50)->nullable();
            $table->string('status_pembayaran', 50)->nullable();
            $table->string('bukti_transfer', 255)->nullable();
        });
    }

    protected function tearDown(): void
    {
        if ($this->temporaryProofPath !== null) {
            File::delete($this->temporaryProofPath);
        }

        parent::tearDown();
    }

    public function test_financial_report_uses_paid_sales_and_lists_payment_proofs(): void
    {
        $admin = $this->createUser('finance-admin', 'admin');
        $customer = $this->createUser('finance-customer', 'user');
        $unpaidCustomer = $this->createUser('unpaid-customer', 'user');

        DB::table('lapangan')->insert([
            'id_lapangan' => '1',
            'nama_lapangan' => 'Lapangan 1',
            'harga_per_jam' => 50000,
            'status_lapangan' => 'Aktif',
            'kapasitas' => 4,
        ]);
        DB::table('booking')->insert([
            [
                'id_booking' => 'BOOKING-PAID',
                'id_user' => $customer->id,
                'id_lapangan' => '1',
                'tgl_main' => today()->toDateString(),
                'jam_mulai' => '10:00:00',
                'durasi' => 2,
                'status_booking' => 'lunas',
                'total_bayar' => 100000,
            ],
            [
                'id_booking' => 'BOOKING-WAIT',
                'id_user' => $unpaidCustomer->id,
                'id_lapangan' => '1',
                'tgl_main' => today()->toDateString(),
                'jam_mulai' => '12:00:00',
                'durasi' => 1,
                'status_booking' => 'menunggu',
                'total_bayar' => 50000,
            ],
            [
                'id_booking' => 'BOOKING-CONFIRMED',
                'id_user' => $unpaidCustomer->id,
                'id_lapangan' => '1',
                'tgl_main' => today()->toDateString(),
                'jam_mulai' => '14:00:00',
                'durasi' => 1,
                'status_booking' => 'dikonfirmasi',
                'total_bayar' => 80000,
            ],
        ]);

        $makananId = DB::table('makanans')->insertGetId([
            'nama' => 'Air Mineral',
            'harga_sewa' => 10000,
            'stok' => 20,
            'satuan' => 'botol',
            'aktif' => true,
        ]);
        $alatId = DB::table('alats')->insertGetId([
            'nama' => 'Raket',
            'harga_sewa' => 20000,
            'stok' => 5,
            'satuan' => 'buah',
            'aktif' => true,
        ]);
        $orderId = DB::table('pesanans')->insertGetId([
            'id_user' => $customer->id,
            'status' => 'selesai',
            'status_pembayaran' => 'Lunas',
            'subtotal' => 30000,
            'total' => 30000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('detail_items')->insert([
            [
                'id_pesanan' => $orderId,
                'id_makanan' => $makananId,
                'nama_item' => 'Air Mineral',
                'jumlah' => 1,
                'harga_satuan' => 10000,
                'subtotal' => 10000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_pesanan' => $orderId,
                'id_alat' => $alatId,
                'nama_item' => 'Raket',
                'jumlah' => 1,
                'harga_satuan' => 20000,
                'subtotal' => 20000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $filename = 'finance-proof-'.uniqid().'.png';
        $this->temporaryProofPath = public_path('uploads/bukti_pembayaran/'.$filename);
        File::ensureDirectoryExists(dirname($this->temporaryProofPath));
        file_put_contents(
            $this->temporaryProofPath,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/xYkAAAAASUVORK5CYII=')
        );
        DB::table('detail_item_booking')->insert([
            'id_detail' => 'DETAIL-PAID',
            'id_booking' => 'BOOKING-PAID',
            'subtotal' => 100000,
            'kuantitas' => 2,
        ]);
        DB::table('pembayaran')->insert([
            'id_pembayaran' => 'PAYMENT-PAID',
            'tgl_bayar' => today()->toDateString(),
            'id_detail_item_booking' => 'DETAIL-PAID',
            'metode_bayar' => 'Transfer',
            'status_pembayaran' => 'Pending',
            'bukti_transfer' => $filename,
        ]);

        $report = $this->actingAs($admin)
            ->get(route('admin.laporan-keuangan.index'));

        $report
            ->assertOk()
            ->assertSee('Jumlah Pendapatan')
            ->assertSee('Rp 210.000')
            ->assertSee('Grafik Nilai Pemesanan Lapangan')
            ->assertSee('Total Transaksi')
            ->assertSee('Belum ada data')
            ->assertSee('Total Pelanggan')
            ->assertSee('Top 5 Akun')
            ->assertSee('finance-customer')
            ->assertSee('Pemesanan Konsumsi')
            ->assertSee('Penyewaan Alat')
            ->assertSee('href="'.route('admin.laporan-keuangan.index').'"', false)
            ->assertSee(route('admin.laporan-keuangan.bukti', 'PAYMENT-PAID'), false)
            ->assertDontSee('Rp 260.000');
        $report->assertViewHas('grafik', function (array $grafik): bool {
            $valuesToday = $grafik['periods'][count($grafik['periods']) - 1]['values'];

            return $valuesToday === [180000.0];
        });

        $this->actingAs($admin)
            ->get(route('admin.laporan-keuangan.index', ['periode' => 'bulan']))
            ->assertOk();
        $this->actingAs($admin)
            ->get(route('admin.laporan-keuangan.index', ['periode' => 'tahun']))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.pemesanan.index'))
            ->assertOk()
            ->assertSee(
                '<img src="'.route('admin.laporan-keuangan.bukti', 'PAYMENT-PAID').'"',
                false
            );

        $this->actingAs($admin)
            ->get(route('admin.laporan-keuangan.bukti', 'PAYMENT-PAID'))
            ->assertOk()
            ->assertHeader('content-type', 'image/png');
    }

    public function test_payment_upload_creates_the_booking_detail_reference_used_by_reports(): void
    {
        $user = $this->createUser('payment-customer', 'user');
        DB::table('booking')->insert([
            'id_booking' => 'BOOKING-UPLOAD',
            'id_user' => $user->id,
            'id_lapangan' => '1',
            'tgl_main' => today()->toDateString(),
            'jam_mulai' => '10:00:00',
            'durasi' => 1,
            'status_booking' => 'menunggu',
            'total_bayar' => 50000,
        ]);

        $response = $this->actingAs($user)
            ->post(route('pembayaran.store'), [
                'booking_ids' => ['BOOKING-UPLOAD'],
                'bukti_bayar' => UploadedFile::fake()->createWithContent(
                    'proof.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/xYkAAAAASUVORK5CYII=')
                ),
            ]);

        $response->assertRedirect(route('pembayaran.index'));
        $payment = DB::table('pembayaran')->first();
        $detail = DB::table('detail_item_booking')
            ->where('id_detail', $payment->id_detail_item_booking)
            ->first();

        $this->temporaryProofPath = public_path('uploads/bukti_pembayaran/'.$payment->bukti_transfer);
        $this->assertSame('BOOKING-UPLOAD', $detail->id_booking);
        $this->assertSame(50000.0, (float) $detail->subtotal);
        $this->assertFileExists($this->temporaryProofPath);
    }

    public function test_admin_verification_updates_all_bookings_and_shows_the_result_in_the_bell(): void
    {
        $admin = $this->createUser('verification-admin', 'admin');
        $customer = $this->createUser('verification-customer', 'user');
        DB::table('lapangan')->insert([
            'id_lapangan' => '1',
            'nama_lapangan' => 'Lapangan 1',
            'tipe_lantai' => 'Karpet',
            'harga_per_jam' => 50000,
            'kapasitas' => 4,
            'status_lapangan' => 'Aktif',
        ]);
        DB::table('booking')->insert([
            [
                'id_booking' => 'BOOKING-VERIFY-1',
                'id_user' => $customer->id,
                'id_lapangan' => '1',
                'tgl_main' => today()->toDateString(),
                'jam_mulai' => '10:00:00',
                'durasi' => 1,
                'status_booking' => 'menunggu',
                'total_bayar' => 50000,
            ],
            [
                'id_booking' => 'BOOKING-VERIFY-2',
                'id_user' => $customer->id,
                'id_lapangan' => '1',
                'tgl_main' => today()->toDateString(),
                'jam_mulai' => '11:00:00',
                'durasi' => 2,
                'status_booking' => 'menunggu',
                'total_bayar' => 100000,
            ],
        ]);

        $this->actingAs($customer)
            ->post(route('pembayaran.store'), [
                'booking_ids' => ['BOOKING-VERIFY-1', 'BOOKING-VERIFY-2'],
                'bukti_bayar' => UploadedFile::fake()->createWithContent(
                    'proof.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/xYkAAAAASUVORK5CYII=')
                ),
            ])
            ->assertRedirect(route('pembayaran.index'))
            ->assertSessionHas('success', 'Bukti pembayaran berhasil dikirim.');

        $this->actingAs($customer)
            ->get(route('pembayaran.index'))
            ->assertOk()
            ->assertSee('Bukti pembayaran berhasil dikirim.')
            ->assertSee('Pembayaran menunggu verifikasi')
            ->assertSee('Menunggu verifikasi oleh admin untuk 2 pemesanan.')
            ->assertSee('notification-dropdown open', false)
            ->assertDontSee('payment-pending-status', false);

        $payments = DB::table('pembayaran')->get();
        $this->assertCount(2, $payments);
        $this->assertSame($payments[0]->bukti_transfer, $payments[1]->bukti_transfer);
        $this->temporaryProofPath = public_path('uploads/bukti_pembayaran/'.$payments[0]->bukti_transfer);

        $this->actingAs($admin)
            ->patch(route('admin.pemesanan.status', 'BOOKING-VERIFY-1'), [
                'status_booking' => 'dikonfirmasi',
            ])
            ->assertRedirect(route('admin.pemesanan.index'))
            ->assertSessionHas('sukses', 'Pembayaran untuk 2 pemesanan berhasil diverifikasi.');

        $this->assertDatabaseCount('pembayaran', 2);
        $this->assertDatabaseHas('booking', [
            'id_booking' => 'BOOKING-VERIFY-1',
            'status_booking' => 'dikonfirmasi',
        ]);
        $this->assertDatabaseHas('booking', [
            'id_booking' => 'BOOKING-VERIFY-2',
            'status_booking' => 'dikonfirmasi',
        ]);
        $this->assertDatabaseMissing('pembayaran', ['status_pembayaran' => 'Pending']);

        $this->actingAs($customer)
            ->get(route('tagihan.index'))
            ->assertOk()
            ->assertSee('Terverifikasi')
            ->assertSee('Pesanan berhasil diverifikasi')
            ->assertSee('Pembayaran untuk 2 pemesanan berhasil diverifikasi oleh admin.')
            ->assertDontSee('Bayar Tagihan');

        $this->actingAs($customer)
            ->get(route('notifications.show', $payments[0]->id_pembayaran))
            ->assertRedirect(route('tagihan.index'));
        $this->assertSame(
            'verified',
            session('payment_notification_states')[sha1($payments[0]->bukti_transfer)]
        );
    }

    private function createUser(string $username, string $role): User
    {
        return User::create([
            'username' => $username,
            'email' => $username.'@example.test',
            'password' => 'password',
            'role' => $role,
        ]);
    }
}
