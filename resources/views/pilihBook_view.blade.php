@extends('layouts.dashboard')

@section('title', 'Pilih Pemesanan')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pilih-book.css') }}">
@endpush

@section('content')
    <section class="pilih-book-page" aria-labelledby="pilih-book-title">
        <header class="pilih-book-heading">
            <h1 id="pilih-book-title">Pilih Jenis</h1>
            <p>Silakan tentukan jenis pemesanan yang ingin Anda lakukan di GOR Kamandanoe.</p>
        </header>

        <div class="pilih-book-grid">
            <article class="pilih-book-card">
                <div class="pilih-book-card__icon" aria-hidden="true"></div>
                <h2>Booking Lapangan</h2>
                <p>Pesan lapangan badminton, futsal, atau basket indoor lengkap dengan pilihan jadwal jam bermain.</p>
                <form method="POST" action="{{ route('booking.pilih.store') }}">
                    @csrf
                    <button type="submit" name="tujuan" value="lapangan">Pilih Lapangan</button>
                </form>
            </article>

            <article class="pilih-book-card">
                <div class="pilih-book-card__icon" aria-hidden="true"></div>
                <h2>Booking Alat</h2>
                <p>Sewa perlengkapan olahraga seperti raket, shuttlecock, bola basket, bola futsal, dan rompi tim.</p>
                <form method="POST" action="{{ route('booking.pilih.store') }}">
                    @csrf
                    <button type="submit" name="tujuan" value="alat">Sewa Peralatan</button>
                </form>
            </article>

            <article class="pilih-book-card">
                <div class="pilih-book-card__icon" aria-hidden="true"></div>
                <h2>Booking Makanan</h2>
                <p>Pesan minuman isotonik, air mineral dingin, camilan, dan katering konsumsi pemain terlebih dahulu.</p>
                <form method="POST" action="{{ route('booking.pilih.store') }}">
                    @csrf
                    <button type="submit" name="tujuan" value="makanan">Pesan Makanan</button>
                </form>
            </article>
        </div>
    </section>
@endsection
