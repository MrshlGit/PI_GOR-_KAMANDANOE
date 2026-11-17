@extends('layouts.dashboard')
@section('title', 'Layanan Informasi')

@section('content')
@php
    $legenda = [
        'saya'        => 'Booking saya',
        'menunggu'    => 'Menunggu konfirmasi',
        'terisi'      => 'Terisi',
        'maintenance' => 'Maintenance',
    ];
    $aturan = [
        ['ban',   'Dilarang merokok',    'Area lapangan bebas asap rokok demi kenyamanan semua pemain.'],
        ['star',  'Jaga sportivitas',    'Hormati lawan, wasit, dan sesama pengguna lapangan.'],
        ['ban',   'Dilarang mabuk',      'Dilarang membawa atau mengonsumsi minuman keras di area GOR.'],
        ['shoe',  'Wajib sepatu non-marking', 'Gunakan sepatu yang tidak meninggalkan bekas di lantai.'],
        ['clean', 'Jagalah kebersihan',  'Buang sampah pada tempatnya setelah selesai bermain.'],
        ['ban',   'Dilarang berkelahi',  'Perselisihan diselesaikan baik-baik, pelanggar bisa dikeluarkan.'],
    ];
    $ikon = [
        'ban'   => '<circle cx="12" cy="12" r="9"/><path d="M5.6 5.6l12.8 12.8"/>',
        'star'  => '<path d="m12 3 2.7 5.5 6 .9-4.4 4.2 1 6-5.3-2.8-5.3 2.8 1-6L3.3 9.4l6-.9z"/>',
        'shoe'  => '<path d="M3 17v-3c2 0 4-1 5-3l2 2 3-1 5 3c1 .6 3 1 3 3v2H3z"/>',
        'clean' => '<path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13"/>',
    ];
@endphp

<link rel="stylesheet" href="{{ asset('css/layanan.css') }}">

<div class="li">

    {{-- ===== JADWAL LAPANGAN ===== --}}
    <section class="li-sec">
        <header class="li-head">
            <div>
                <h1>Jadwal Lapangan.</h1>
                <p>Pantau ketersediaan lapangan agar pemesananmu tidak bentrok dengan pemain lain.<br>Pilih tanggal untuk melihat jadwal pada hari tersebut.</p>
            </div>
            <form method="GET" action="{{ route('layanan.index') }}" class="li-date">
                <label>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                    <input type="date" name="tanggal" value="{{ $tanggal->toDateString() }}" onchange="this.form.submit()">
                </label>
            </form>
        </header>

        <div class="li-card">
            <div class="li-bar">
                <div>
                    <h2>{{ $labelTanggal }}</h2>
                    <p>Jam operasional {{ sprintf('%02d.00', array_key_first($jadwal)) }} - {{ sprintf('%02d.00', array_key_last($jadwal) + 1) }}</p>
                </div>
                <a href="{{ route('pilihBook_view') }}" class="li-btn">BOOKING &rarr;</a>
            </div>

            <ul class="li-legend">
                <li><span class="dot s-kosong"></span>Tersedia</li>
                @foreach ($legenda as $kode => $teks)
                    <li><span class="dot s-{{ $kode }}"></span>{{ $teks }}</li>
                @endforeach
            </ul>

            <div class="li-scroll">
                <table class="li-table">
                    <thead>
                        <tr>
                            <th>WAKTU</th>
                            @foreach ($lapangans as $l)
                                <th>{{ $l->nama_lapangan ?? 'Lapangan '.$l->id_lapangan }}<small>{{ $l->tipe_lantai }}</small></th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($jadwal as $jam => $baris)
                            <tr>
                                <th>{{ sprintf('%02d:00 - %02d:00', $jam, $jam + 1) }}</th>
                                @foreach ($lapangans as $l)
                                    @php $st = $baris[$l->id_lapangan]; @endphp
                                    <td class="s-{{ $st }}">{{ $legenda[$st] ?? '' }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    {{-- ===== PERATURAN ===== --}}
    <section class="li-sec li-rules">
        <h2>Peraturan Saat Berada<br>Di Lapangan.</h2>
        <div class="li-rule-grid">
            @foreach ($aturan as [$k, $judul, $isi])
                <article class="li-rule">
                    <span class="li-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $ikon[$k] !!}</svg></span>
                    <div><h3>{{ $judul }}</h3><p>{{ $isi }}</p></div>
                </article>
            @endforeach
        </div>
    </section>

    <div class="li-banner" style="--img:url('{{ asset('img/lapangan.jpeg') }}')"><span>GOR KAMANDANOE</span></div>

    {{-- ===== SARAN & KRITIK ===== --}}
    <section class="li-sec li-saran" id="saran">
        <div class="li-saran-head">
            <h2>Saran &amp; Kritik</h2>
            <p>Sampaikan masukanmu untuk membantu kami meningkatkan layanan.</p>
        </div>

        <form method="POST" action="{{ route('layanan.saran') }}" class="li-form">
            @csrf
            <h3>Tuliskan Saran atau Kritik</h3>

            @if (session('sukses_saran'))
                <p class="li-ok">{{ session('sukses_saran') }}</p>
            @endif
            @if ($errors->any())
                <div class="li-err">@foreach ($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>
            @endif

            <label>Nama
                <input type="text" name="nama" maxlength="100" value="{{ old('nama', auth()->user()->username) }}" placeholder="Nama kamu">
            </label>
            <label>Isi Saran
                <textarea name="isi" rows="5" maxlength="1000" placeholder="Tulis saran atau kritikmu di sini..." required>{{ old('isi') }}</textarea>
            </label>
            <button type="submit">Kirim</button>
        </form>
    </section>
</div>
@endsection