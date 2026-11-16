@extends('layouts.dashboard')

@section('title', 'Sewa Alat')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/alat.css') }}">
@endpush

@section('content')
       

    <section class="alat-page" aria-labelledby="alat-title">
        <header class="alat-page__header">
            <h1 class="alat-page__title" id="alat-title"><span>Sewa</span> Alat</h1>
            <p>Pilih dan sewa perlengkapan badminton untuk menemani permainan Anda.</p>
        </header>

        <nav class="alat-categories" aria-label="Kategori alat">
            <button type="button" class="alat-categories__item is-active" aria-current="page">Semua Alat</button>
            <button type="button" class="alat-categories__item">Raket Badminton</button>
            <button type="button" class="alat-categories__item">Shuttlecock</button>
            <button type="button" class="alat-categories__item">Sepatu Olahraga</button>
            <button type="button" class="alat-categories__item">Grip & Aksesori</button>
        </nav>

        <div class="alat-page__layout">

            @forelse ($alats as $alat)
                <article class="alat-card">
                    <h2>{{ $alat->nama }}</h2>
                    <p>{{ $alat->deskripsi }}</p>
                    <p>
                        Rp {{ number_format($alat->harga_sewa, 0, ',', '.') }}
                        / {{ $alat->satuan }}
                    </p>
                </article>
            @empty
                <p>Belum ada alat yang tersedia.</p>
            @endforelse
            

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
