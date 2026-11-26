@extends('layouts.admin')
@section('title', 'Saran & Kritik')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-saran-kritik.css') }}">
@endpush

@section('content')
    <div class="top feedback-page-top">
        <button class="burger" id="burger" aria-label="Menu"><svg class="ic"><use href="#i-menu"/></svg></button>
        <div class="judul">
            <h1><span class="feedback-title-icon" aria-hidden="true"><svg class="ic"><use href="#i-message"/></svg></span>Saran &amp; Kritik</h1>
            <p class="muted">Masukan yang dikirim pengguna untuk meningkatkan layanan.</p>
        </div>
    </div>

    <section class="box feedback-panel" aria-labelledby="feedback-panel-title">
        <div class="box-h feedback-panel-heading">
            <div class="feedback-panel-title">
                <h2 id="feedback-panel-title">Masukan Pengguna</h2>
                <p class="muted">Baca masukan pelanggan untuk membantu meningkatkan layanan GOR.</p>
            </div>
            <div class="feedback-count" aria-label="{{ number_format($saranKritik->total(), 0, ',', '.') }} masukan diterima">
                <strong>{{ number_format($saranKritik->total(), 0, ',', '.') }}</strong>
                <span>masukan diterima</span>
            </div>
        </div>

        <div class="feedback-grid">
            @forelse ($saranKritik as $saran)
                @php
                    $pengirim = $saran->nama ?: ($saran->user?->username ?? 'Pengguna tidak diketahui');
                    $isiSingkat = \Illuminate\Support\Str::limit($saran->isi, 180);
                    $isiPanjang = \Illuminate\Support\Str::length($saran->isi) > 180;
                @endphp
                <article class="feedback-card">
                    <div class="feedback-card-heading">
                        <span class="feedback-type-icon" aria-hidden="true">
                            <svg class="ic"><use href="#i-message"/></svg>
                        </span>
                        <span>Saran &amp; Kritik</span>
                    </div>
                    <div class="feedback-card-body">
                        <p class="feedback-message">{{ $isiSingkat }}</p>
                        @if ($isiPanjang)
                            <details class="feedback-more">
                                <summary>Lihat selengkapnya</summary>
                                <p class="feedback-message">{{ $saran->isi }}</p>
                            </details>
                        @endif
                    </div>
                    <footer class="feedback-card-footer">
                        <span class="feedback-avatar" aria-hidden="true">
                            {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($pengirim, 0, 1)) }}
                        </span>
                        <span class="feedback-sender">{{ $pengirim }}</span>
                        <time class="feedback-date" datetime="{{ $saran->created_at?->toIso8601String() }}">
                            <svg class="ic" aria-hidden="true"><use href="#i-cal"/></svg>
                            {{ $saran->created_at?->locale('id')->translatedFormat('d M Y, H:i') ?? '—' }}
                        </time>
                    </footer>
                </article>
            @empty
                <div class="feedback-empty">
                    <span class="feedback-empty-icon" aria-hidden="true">
                        <svg class="ic"><use href="#i-message"/></svg>
                    </span>
                    <b>Belum ada masukan</b>
                    <span>Saran atau kritik dari pengguna akan muncul di sini.</span>
                </div>
            @endforelse
        </div>

        @if ($saranKritik->hasPages())
            <div class="feedback-pagination">{{ $saranKritik->links() }}</div>
        @endif
    </section>
@endsection
