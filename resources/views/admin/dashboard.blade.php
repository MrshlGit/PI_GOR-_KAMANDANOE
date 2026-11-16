@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
    @php
        $kartu = [
            ['Total Pengguna',       $stat['pengguna'],   'users',  true],
            ['Turnamen Aktif',       $stat['turnamen'],   'trophy', true],
            ['Pendapatan Bulan Ini', $stat['pendapatan'], 'act',    false],
            ['Pemesanan Baru',       $stat['baru'],       'cart',   true],
        ];
        $periode = ['hari' => 'Hari', 'minggu' => 'Minggu', 'bulan' => 'Bulan', 'tahun' => 'Tahun'];
    @endphp

    <div class="top">
        <button class="burger" id="burger" aria-label="Menu"><svg class="ic"><use href="#i-menu"/></svg></button>
        <div class="judul"><h1>Dashboard</h1></div>
        <label class="search">
            <svg class="ic"><use href="#i-search"/></svg>
            <input id="cari" type="text" placeholder="Cari aktivitas, pengguna...">
        </label>
    </div>

    <div class="dashboard-grid">
        <section class="stats dashboard-stats">
            @foreach ($kartu as [$label, $nilai, $ikon, $hitung])
                <div class="box stat">
                    <div class="h">{{ $label }} <span class="ico"><svg class="ic"><use href="#i-{{ $ikon }}"/></svg></span></div>
                    <strong @if ($hitung) data-count="{{ $nilai }}" @endif>{{ $nilai }}</strong>
                </div>
            @endforeach
        </section>

        <section class="box dashboard-chart" id="grafik" data-url="{{ route('admin.grafik') }}" data-awal="{{ json_encode($grafik) }}">
            <div class="box-h">
                <div>
                    <h2>Grafik Jumlah Booking</h2>
                    <small class="muted" id="grafik-ket"></small>
                </div>
                <div class="tabs" role="tablist">
                    @foreach ($periode as $kode => $nama)
                        <button type="button" data-periode="{{ $kode }}" @class(['on' => $kode === 'hari'])>{{ $nama }}</button>
                    @endforeach
                </div>
            </div>

            <div class="chart" id="grafik-area"></div>
        </section>

        <section class="box dashboard-calendar" id="admin-calendar" aria-labelledby="calendar-title">
            <div class="box-h">
                <h2 id="calendar-title">Kalender</h2>
            </div>
            <div class="calendar-month">
                <button type="button" data-calendar-prev aria-label="Bulan sebelumnya">‹</button>
                <strong data-calendar-month aria-live="polite"></strong>
                <button type="button" data-calendar-next aria-label="Bulan berikutnya">›</button>
            </div>
            <div class="calendar-weekdays" aria-hidden="true">
                <span>Min</span><span>Sen</span><span>Sel</span><span>Rab</span>
                <span>Kam</span><span>Jum</span><span>Sab</span>
            </div>
            <div class="calendar-days" data-calendar-days role="group"></div>
        </section>

        <section class="box dashboard-bookings">
            <div class="box-h">
                <h2>Riwayat Pemesanan Terbaru</h2>
                <a href="#">Lihat Semua</a>
            </div>
            <div class="scroll">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>NAMA</th>
                            <th>LAPANGAN</th>
                            <th>TANGGAL</th>
                            <th>STATUS</th>
                        </tr>
                    </thead>
                    <tbody id="tbody">
                        @forelse ($booking as $b)
                            @php
                                $status = strtolower($b->status_booking);
                                $kelas = match ($status) {
                                    'lunas'    => 'ok',
                                    'menunggu' => 'wait',
                                    default    => '',
                                };
                            @endphp
                            <tr>
                                <td><b>{{ $b->nama_pemesan ?: ($b->user?->username ?? 'User #' . $b->id_user) }}</b></td>
                                <td>{{ $b->lapangan->nama_lapangan ?? $b->id_lapangan }}</td>
                                <td>
                                    {{ $b->tgl_main->format('d M Y') }}
                                    <small>{{ substr($b->jam_mulai, 0, 5) }}</small>
                                </td>
                                <td><span class="pill {{ $kelas }}">{{ $b->status_booking }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4">Belum ada pemesanan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="box dashboard-courts">
            <div class="box-h">
                <h2>Status Lapangan</h2>
            </div>
            <ul class="fl">
                @forelse ($lapangan as $l)
                    <li>
                        <span class="ico"><svg class="ic"><use href="#i-pin"/></svg></span>
                        {{ $l->nama_lapangan }}
                        <span class="pill {{ $l->dipakai ? 'busy' : 'ok' }}">{{ $l->dipakai ? 'Dipakai' : 'Tersedia' }}</span>
                    </li>
                @empty
                    <li>Belum ada data lapangan.</li>
                @endforelse
            </ul>
        </section>
    </div>
@endsection
