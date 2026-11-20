@extends('layouts.admin')
@section('title', 'Saran & Kritik')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-saran-kritik.css') }}">
@endpush

@section('content')
    <div class="top">
        <button class="burger" id="burger" aria-label="Menu"><svg class="ic"><use href="#i-menu"/></svg></button>
        <div class="judul">
            <h1>Saran &amp; Kritik</h1>
            <p class="muted">Masukan yang dikirim pengguna untuk meningkatkan layanan.</p>
        </div>
    </div>

    <section class="box">
        <div class="box-h">
            <div>
                <h2>Masukan Pengguna</h2>
                <small class="muted">{{ number_format($saranKritik->total(), 0, ',', '.') }} masukan</small>
            </div>
        </div>

        <div class="scroll">
            <table class="tbl feedback-table">
                <thead>
                    <tr>
                        <th>NAMA PENGIRIM</th>
                        <th>AKUN</th>
                        <th>WAKTU DIKIRIM</th>
                        <th>SARAN &amp; KRITIK</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($saranKritik as $saran)
                        <tr>
                            <td><b>{{ $saran->nama ?: ($saran->user?->username ?? 'Pengguna tidak diketahui') }}</b></td>
                            <td>{{ $saran->user?->username ?? '—' }}</td>
                            <td>{{ $saran->created_at?->locale('id')->translatedFormat('d M Y, H:i') ?? '—' }}</td>
                            <td><p class="feedback-message">{{ $saran->isi }}</p></td>
                        </tr>
                    @empty
                        <tr>
                            <td class="feedback-empty" colspan="4">Belum ada saran atau kritik dari pengguna.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($saranKritik->hasPages())
            <div class="feedback-pagination">{{ $saranKritik->links() }}</div>
        @endif
    </section>
@endsection
