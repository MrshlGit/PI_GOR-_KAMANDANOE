@extends('layouts.dashboard')

@section('title', 'Sewa Konsumsi')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/alat1.css') }}">
@endpush

@section('content')
    <section class="alat-page" aria-labelledby="alat-title">
        <h1 class="alat-page__title" id="alat-title"><span>Sewa</span> Konsumsi</h1>

        <div class="alat-page__layout">
            <div class="alat-list" aria-label="Daftar alat">
                @foreach (range(1, 4) as $item)
                    <article class="alat-card">
                        <div class="alat-card__photo" role="img" aria-label="Foto alat belum tersedia"></div>

                        <div class="alat-card__details">
                            <h2>Raket</h2>
                            <p>Harga: Rp 15.000</p>
                            <p>Stok: 5</p>

                            <label class="alat-card__quantity">
                                <span class="sr-only">Jumlah raket</span>
                                <input type="number" min="0" max="5" value="0" inputmode="numeric">
                            </label>

                            <button class="alat-card__order" type="button">Pesan</button>
                        </div>
                    </article>
                @endforeach
            </div>

            <aside class="alat-summary" aria-labelledby="alat-summary-title">
                <h2 id="alat-summary-title">Pesanan</h2>
                <div class="alat-summary__items" aria-label="Daftar pesanan">
                    @foreach (range(1, 4) as $item)
                        <div class="alat-summary__line" aria-hidden="true"></div>
                    @endforeach
                </div>
                <p class="alat-summary__total">Total: Rp —</p>
                <button class="alat-summary__confirm" type="button">Konfirmasi</button>
            </aside>
        </div>
    </section>
@endsection