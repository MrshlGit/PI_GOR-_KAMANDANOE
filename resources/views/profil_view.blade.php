@extends('layouts.dashboard')
@section('title', 'Profil Saya')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/profil.css') }}">
@endpush

@section('content')
@php
    $rupiah = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
    $warna = [
        'menunggu' => 'wait', 'dikonfirmasi' => 'info', 'lunas' => 'ok',
        'selesai' => 'mut', 'batal' => 'bad', 'dibatalkan' => 'bad', 'cancelled' => 'bad',
    ];
    $data = [
        ['Username', $user->username ?: '-'],
        ['Email', $user->email ?: '-'],
        ['No. HP', $user->noHp ?: '-'],
        ['Peran', ucfirst((string) $user->role)],
        ['ID Akun', '#' . $user->id],
        ['Bergabung', $user->created_at?->locale('id')->translatedFormat('j F Y') ?? 'Belum tercatat'],
    ];
    $statistik = [
        ['Total Booking', $ringkasan['total']],
        ['Booking Aktif', $ringkasan['aktif']],
        ['Booking Selesai', $ringkasan['selesai']],
        ['Total Pengeluaran', $rupiah($ringkasan['belanja'])],
    ];
@endphp

<div class="pf">

    <header class="pf-head">
        <h1>Profil Saya</h1>
        <p>Data akun dan ringkasan aktivitasmu di GOR Kamandanoe.</p>
    </header>

    {{-- ===== KARTU PROFIL + DATA AKUN ===== --}}
    <div class="pf-grid">
        <section class="pf-card pf-me">
            <span class="pf-avatar">{{ $user->inisial }}</span>
            <h2>{{ $user->username }}</h2>
            <span class="pf-role">{{ ucfirst((string) $user->role) }}</span>
            <p class="pf-online">
                @if ($user->last_online_at)
                    Terakhir online {{ $user->last_online_at->locale('id')->diffForHumans() }}
                @else
                    Aktivitas terakhir belum tercatat
                @endif
            </p>
            <div class="pf-btns">
                <a href="{{ route('pilihBook_view') }}" class="pf-btn">Buat Pemesanan</a>
                <a href="{{ route('tagihan.index') }}" class="pf-btn ghost">Lihat Transaksi</a>
            </div>
        </section>

        <section class="pf-card">
            <h3>Data Akun</h3>
            <dl class="pf-data">
                @foreach ($data as [$label, $isi])
                    <div>
                        <dt>{{ $label }}</dt>
                        <dd>{{ $isi }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>
    </div>

    {{-- ===== RINGKASAN AKTIVITAS ===== --}}
    <section class="pf-stats">
        @foreach ($statistik as [$label, $nilai])
            <div class="pf-stat">
                <span>{{ $label }}</span>
                <strong>{{ $nilai }}</strong>
            </div>
        @endforeach
    </section>

    {{-- ===== BOOKING TERAKHIR ===== --}}
    <section class="pf-card">
        <div class="pf-bar">
            <h3>Booking Terakhir</h3>
            <a href="{{ route('tagihan.index') }}">Lihat semua</a>
        </div>

        @if ($terakhir->isEmpty())
            <p class="pf-empty">Kamu belum pernah melakukan pemesanan.</p>
        @else
            <div class="pf-scroll">
                <table class="pf-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Lapangan</th>
                            <th>Jam</th>
                            <th>Status</th>
                            <th class="kanan">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($terakhir as $b)
                            @php $st = strtolower((string) $b->status_booking); @endphp
                            <tr>
                                <td>{{ $b->tgl_main?->locale('id')->translatedFormat('j M Y') ?? '-' }}</td>
                                <td>{{ $b->lapangan->nama_lapangan ?? 'Lapangan ' . $b->id_lapangan }}</td>
                                <td>{{ substr((string) $b->jam_mulai, 0, 5) }} &middot; {{ max(1, (int) $b->durasi) }} jam</td>
                                <td><span class="pf-chip {{ $warna[$st] ?? 'mut' }}">{{ ucfirst($st) }}</span></td>
                                <td class="kanan">{{ $rupiah($b->total_bayar) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>
@endsection