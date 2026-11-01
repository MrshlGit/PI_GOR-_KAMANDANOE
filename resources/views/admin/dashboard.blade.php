@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
    @php
        $kartu = [
            ['Total Pengguna', $stat['pengguna'], 'terdaftar di sistem', 'users', true],
            ['Turnamen Aktif', $stat['turnamen'], 'terjadwal mulai hari ini', 'trophy', true],
            ['Pendapatan Bulan Ini', $stat['pendapatan'], 'dari pemesanan bulan ini', 'act', false],
            ['Pemesanan Baru', $stat['baru'], 'perlu konfirmasi manual', 'cart', true],
        ];
    @endphp

    <div class="top">
        <button class="burger" id="burger" aria-label="Menu"><svg class="ic">
                <use href="#i-menu" />
            </svg></button>
        <h1>Dashboard</h1>
        <label class="search">
            <svg class="ic">
                <use href="#i-search" />
            </svg>
            <input id="cari" type="text" placeholder="Cari nama atau lapangan...">
        </label>
    </div>

    <section class="stats">
        @foreach ($kartu as [$label, $nilai, $ket, $ikon, $hitung])
            <div class="box stat">
                <div class="h">{{ $label }} <span class="ico"><svg class="ic">
                            <use href="#i-{{ $ikon }}" />
                        </svg></span></div>
                <strong @if ($hitung) data-count="{{ $nilai }}" @endif>{{ $nilai }}</strong>
                <small>{{ $ket }}</small>
            </div>
        @endforeach
    </section>

    <section class="duo">
        <div class="box">
            <div class="box-h">
                <h2>Riwayat Pemesanan Terbaru</h2><a href="#">Lihat semua</a>
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
                            @php $lunas = strtolower($b->status_booking) === 'lunas'; @endphp
                            <tr>
                                <td><b>{{ $b->username ?? 'User #' . $b->id_user }}</b></td>
                                <td>{{ $b->id_lapangan }}</td>
                                <td>
                                    {{ \Carbon\Carbon::parse($b->tgl_main)->format('d M Y') }}
                                    <small>{{ substr($b->jam_mulai, 0, 5) }}</small>
                                </td>
                                <td><span class="pill {{ $lunas ? 'ok' : 'wait' }}">{{ $b->status_booking }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">Belum ada pemesanan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="box">
            <div class="box-h">
                <h2>Status Lapangan</h2>
            </div>
            <ul class="fl">
                @foreach ($lapangan as [$nama, $tersedia])
                    <li>
                        <span class="ico"><svg class="ic">
                                <use href="#i-pin" />
                            </svg></span> {{ $nama }}
                        <span class="pill ok">{{ $tersedia ? 'Tersedia' : 'Dipakai' }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    <section class="box">
        <div class="box-h">
            <div>
                <h2>Trafik Pengunjung Lapangan</h2>
                <small class="muted">Jumlah pengunjung berdasarkan gender per minggu</small>
            </div>
            <div class="legend"><span class="p"></span>Pria <span class="t"></span>Total <span class="w"></span>Wanita</div>
        </div>

        <div class="chart">
            @foreach ($trafik as $i => [$hari, [$pria, $total, $wanita]])
                <div class="day">
                    {{ $hari }}
                    <div class="bars" style="--n:{{ $i }}">
                        <i class="p" style="height:{{ $pria }}%" title="Pria: {{ $pria }}"></i>
                        <i class="t" style="height:{{ $total }}%" title="Total: {{ $total }}"></i>
                        <i class="w" style="height:{{ $wanita }}%" title="Wanita: {{ $wanita }}"></i>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endsection