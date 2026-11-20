@extends('layouts.admin')
@section('title', 'Laporan Keuangan')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-laporan-keuangan.css') }}">
@endpush

@section('content')
    <div class="top">
        <button class="burger" id="burger" aria-label="Menu"><svg class="ic"><use href="#i-menu"/></svg></button>
        <div class="judul">
            <h1>Laporan Keuangan</h1>
            <p class="muted">Ringkasan pendapatan dan aktivitas pemesanan.</p>
        </div>
    </div>

    <section class="stats finance-stats" aria-label="Ringkasan keuangan">
        <div class="box stat">
            <div class="h">Jumlah Pendapatan <i class="dot"></i></div>
            <strong>Rp {{ number_format($jumlahPendapatan, 0, ',', '.') }}</strong>
            <small>Transaksi lunas atau terverifikasi</small>
        </div>
        <div class="box stat">
            <div class="h">Total Transaksi <i class="dot"></i></div>
            <strong>{{ number_format($jumlahTransaksi, 0, ',', '.') }}</strong>
            <small>Booking dan pesanan terbayar</small>
        </div>
        <div class="box stat">
            <div class="h">Total Pengeluaran <i class="dot"></i></div>
            <strong class="finance-unavailable">Belum ada data</strong>
            <small>Aplikasi belum mencatat pengeluaran</small>
        </div>
        <div class="box stat">
            <div class="h">Total Pelanggan <i class="dot"></i></div>
            <strong>{{ number_format($jumlahPelanggan, 0, ',', '.') }}</strong>
            <small>Akun dengan transaksi yang tidak dibatalkan</small>
        </div>
    </section>

    <section class="box finance-chart" aria-labelledby="finance-chart-title">
        <div class="box-h">
            <div>
                <h2 id="finance-chart-title">Grafik Nilai Pemesanan Lapangan</h2>
                <small class="muted">Nilai booking berstatus Dikonfirmasi, Lunas, atau Selesai per lapangan.</small>
            </div>
            <form method="GET" action="{{ route('admin.laporan-keuangan.index') }}" class="finance-period-form">
                <label for="periode">Tampilkan</label>
                <select id="periode" name="periode">
                    <option value="hari" @selected($periode === 'hari')>Per hari</option>
                    <option value="bulan" @selected($periode === 'bulan')>Per bulan</option>
                    <option value="tahun" @selected($periode === 'tahun')>Per tahun</option>
                </select>
                <button class="btn kuning" type="submit">Terapkan</button>
            </form>
        </div>
        <div id="finance-chart" class="finance-chart-content" data-chart='@json($grafik)'></div>
    </section>

    <div class="finance-insights">
        <section class="box finance-top-customers" aria-labelledby="top-customers-title">
            <div class="box-h">
                <div>
                    <h2 id="top-customers-title">Top 5 Akun</h2>
                    <small class="muted">Diurutkan berdasarkan total transaksi terbayar.</small>
                </div>
            </div>
            <div class="scroll">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>PERINGKAT</th>
                            <th>AKUN</th>
                            <th class="kanan">TOTAL TRANSAKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($topPelanggan as $index => $pelanggan)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><b>{{ $pelanggan['username'] }}</b></td>
                                <td class="kanan">Rp {{ number_format($pelanggan['total'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3">Belum ada transaksi terbayar.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="box finance-sales-mix" aria-labelledby="sales-mix-title">
            <div class="box-h">
                <div>
                    <h2 id="sales-mix-title">Penjualan Terbesar</h2>
                    <small class="muted">Perbandingan pendapatan berdasarkan layanan.</small>
                </div>
            </div>
            <div id="finance-donut" class="finance-donut-layout" data-chart='@json($kategoriPenjualan)'>
                <div class="finance-donut" role="img" aria-label="Komposisi penjualan"></div>
                <ul class="finance-donut-legend"></ul>
            </div>
        </section>
    </div>

    <section class="box finance-bookings" aria-labelledby="finance-bookings-title">
        <div class="box-h">
            <div>
                <h2 id="finance-bookings-title">Data Pemesanan dan Bukti Pembayaran</h2>
                <small class="muted">Lihat detail akun, lapangan, dan bukti yang sudah diunggah.</small>
            </div>
        </div>
        <div class="scroll">
            <table class="tbl finance-bookings-table">
                <thead>
                    <tr>
                        <th>PEMESAN</th>
                        <th>AKUN / EMAIL</th>
                        <th>LAPANGAN</th>
                        <th>JADWAL</th>
                        <th class="kanan">TOTAL</th>
                        <th>STATUS</th>
                        <th class="kanan">BUKTI BAYAR</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bookings as $booking)
                        @php
                            $status = strtolower((string) $booking->status_booking);
                            $kelas = match ($status) {
                                'menunggu' => 'wait',
                                'lunas', 'dikonfirmasi', 'selesai' => 'ok',
                                'batal', 'dibatalkan', 'cancelled' => 'busy',
                                default => '',
                            };
                            $pemesan = $booking->nama_pemesan
                                ?: ($booking->user?->username ?? 'User #'.$booking->id_user);
                            $bukti = $booking->payment_proof;
                        @endphp
                        <tr>
                            <td><b>{{ $pemesan }}</b></td>
                            <td>
                                {{ $booking->user?->username ?? '—' }}
                                @if ($booking->user?->email)
                                    <small>{{ $booking->user->email }}</small>
                                @endif
                            </td>
                            <td>{{ $booking->lapangan?->nama_lapangan ?? $booking->id_lapangan }}</td>
                            <td>
                                {{ \Carbon\Carbon::parse($booking->tgl_main)->format('d M Y') }}
                                <small>{{ substr((string) $booking->jam_mulai, 0, 5) }} · {{ max(1, (int) $booking->durasi) }} jam</small>
                            </td>
                            <td class="kanan">Rp {{ number_format((float) $booking->total_bayar, 0, ',', '.') }}</td>
                            <td><span class="pill {{ $kelas }}">{{ ucfirst($booking->status_booking) }}</span></td>
                            <td class="kanan">
                                @if ($bukti?->available)
                                    <a class="btn kuning" href="{{ route('admin.laporan-keuangan.bukti', $bukti->id_pembayaran) }}" target="_blank" rel="noopener">Lihat</a>
                                @else
                                    <span class="muted">Belum ada</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7">Belum ada data pemesanan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($bookings->hasPages())
            <div class="finance-pagination">{{ $bookings->links() }}</div>
        @endif
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin-laporan-keuangan.js') }}" defer></script>
@endpush
