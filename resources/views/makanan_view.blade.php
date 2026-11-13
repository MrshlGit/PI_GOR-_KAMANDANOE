@extends('layouts.dashboard')

@section('title', 'Sewa Makanan')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/alat.css') }}">
@endpush

@section('content')
    @php
        $alatContoh = [
            [
                'nama' => 'Raket Yonex Astrox Pro',
                'deskripsi' => 'Tarikan senar kencang, ideal untuk serangan.',
                'harga' => '15.000',
                'satuan' => 'sesi main',
                'jenis' => 'racket',
            ],
            [
                'nama' => 'Raket Li-Ning Windstorm',
                'deskripsi' => 'Ringan dan nyaman untuk bermain.',
                'harga' => '15.000',
                'satuan' => 'sesi main',
                'jenis' => 'racket',
            ],
            [
                'nama' => 'Shuttlecock Tournament',
                'deskripsi' => 'Shuttlecock untuk permainan badminton.',
                'harga' => '95.000',
                'satuan' => 'slop (12 pcs)',
                'jenis' => 'shuttlecock',
            ],
            [
                'nama' => 'Sepatu Badminton Non-Marking',
                'deskripsi' => 'Sepatu untuk bermain di lapangan indoor.',
                'harga' => '20.000',
                'satuan' => 'sesi main',
                'jenis' => 'shoe',
            ],
        ];
    @endphp

    <section class="alat-page" aria-labelledby="alat-title">
        <header class="alat-page__header">
            <h1 class="alat-page__title" id="alat-title"><span>Sewa</span> Makanan</h1>
            <p>Pilih dan sewa perlengkapan badminton untuk menemani permainan Anda.</p>
        </header>

        <nav class="alat-categories" aria-label="Kategori alat">
            <button type="button" class="alat-categories__item is-active" aria-current="page">Semua Alat</button>
            <button type="button" class="alat-categories__item">Raket Badminton</button>
            <button type="button" class="alat-categories__item">Shuttlecock</button>
            <button type="button" class="alat-categories__item">Sepatu Olahraga</button>
            <button type="button" class="alat-categories__item">Grip &amp; Aksesori</button>
        </nav>

        <div class="alat-page__layout">
            <div class="alat-list" aria-label="Daftar alat">
                @foreach ($alatContoh as $alat)
                    <article class="alat-card">
                        <div class="alat-card__photo" aria-hidden="true">
                            @if ($alat['jenis'] === 'racket')
                                <svg viewBox="0 0 100 100" fill="none">
                                    <ellipse cx="50" cy="33" rx="17" ry="21" stroke="currentColor" stroke-width="4"/>
                                    <path d="M41 49 55 68M52 48 39 68M48 54l-1 16m8-17-12 14m-8-19 18 18m-14-25 16 15" stroke="currentColor" stroke-width="2"/>
                                    <path d="m45 67 11 8-7 12-9-8z" fill="currentColor"/>
                                </svg>
                            @elseif ($alat['jenis'] === 'shuttlecock')
                                <svg viewBox="0 0 100 100" fill="none">
                                    <path d="m50 24-19 28h38L50 24Zm-19 28-8 9m46-9 8 9M24 61h52M28 68h44M33 76h34M39 84h22" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            @else
                                <svg viewBox="0 0 100 100" fill="none">
                                    <path d="M23 58 34 48l19 5 12 10 14 4v13H24l-4-7 3-15Z" stroke="currentColor" stroke-width="4" stroke-linejoin="round"/>
                                    <path d="m38 50 6-14 17 4 7 15M28 69h49" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            @endif
                        </div>

                        <div class="alat-card__details">
                            <h2>{{ $alat['nama'] }}</h2>
                            <p>{{ $alat['deskripsi'] }}</p>
                            <p class="alat-card__price">Rp {{ $alat['harga'] }} <span>/ {{ $alat['satuan'] }}</span></p>

                            <div class="alat-card__actions">
                                <label class="alat-card__quantity">
                                    <span class="sr-only">Jumlah {{ $alat['nama'] }}</span>
                                    <input type="number" min="0" value="0" inputmode="numeric">
                                </label>
                                <button class="alat-card__order" type="button">Pesan</button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <aside class="alat-summary" aria-labelledby="alat-summary-title">
                <h2 id="alat-summary-title">Pesanan Saya</h2>
                <div class="alat-summary__items">
                    <p>Belum ada alat dipilih.</p>
                </div>
                <dl class="alat-summary__total">
                    <div><dt>Subtotal Sewa</dt><dd>Rp —</dd></div>
                    <div><dt>Total Tagihan</dt><dd>Rp —</dd></div>
                </dl>
                <button class="alat-summary__confirm" type="button">Konfirmasi Pesanan</button>
            </aside>
        </div>
    </section>
@endsection
