@extends('layouts.dashboard')
@section('title', 'Dashboard')

@section('content')

@php
    $slides = [
        ['judul' => 'Turnamen Antar Desa', 'teks' => 'Ayo ikuti ajang kejuaraan daerah Jogja', 'img' => 'turnamen1.jpg'],
        ['judul' => 'Turnamen Antar Desa', 'teks' => 'Ayo ikuti ajang kejuaraan daerah Jogja', 'img' => 'turnamen2.jpg'],
        ['judul' => 'Turnamen Antar Desa', 'teks' => 'Ayo ikuti ajang kejuaraan daerah Jogja', 'img' => 'turnamen3.jpg'],
        ['judul' => 'Turnamen Antar Desa', 'teks' => 'Ayo ikuti ajang kejuaraan daerah Jogja', 'img' => 'turnamen1.jpg'],
        ['judul' => 'Turnamen Antar Desa', 'teks' => 'Ayo ikuti ajang kejuaraan daerah Jogja', 'img' => 'turnamen2.jpg'],
    ];
@endphp

<section class="welcome fade-up">
    <h1>Selamat Datang, {{ auth()->user()->username }}....</h1>
    <p>Jl. Kadisoka, Banjeng, Maguwoharjo, Kec. Depok, Kabupaten Sleman, Daerah Istimewa Yogyakarta 55281</p>
</section>

<section class="carousel fade-up">
    <div class="track" id="track">
        @foreach ($slides as $s)
            <article class="slide" style="--img:url('{{ asset('img/'.$s['img']) }}')">
                <div><b>{{ $s['judul'] }}</b><small>{{ $s['teks'] }}</small></div>
            </article>
        @endforeach
    </div>
    <div class="dots" id="dots"></div>
</section>

<section class="fade-up">
    <div class="sec-title">
        <h2>Lapangan &amp; Harga</h2>
        <p>Berikut lapangan yang disediakan oleh Gor Kamandanoe</p>
    </div>

    <div class="cards">
        @foreach ($lapangans as $lapangan)
            @php($gambar = strtolower($lapangan->tipe_lantai) === 'kayu' ? 'lapangankayu.jpg' : 'lapangankarpet.jpg')
            <article class="lap" data-nama="{{ $lapangan->nama_lapangan }}">
                <div class="pic" style="--img:url('{{ asset('img/'.$gambar) }}')"></div>
                <div class="body">
                    <h3>{{ $lapangan->nama_lapangan }}</h3>
                    <p>Lapangan dengan lantai {{ strtolower($lapangan->tipe_lantai) }}.</p>
                    <div class="lap-foot">
                        <div class="price">Rp. {{ number_format((float) $lapangan->harga_per_jam, 0, ',', '.') }} <small>/ jam</small></div>
                        <a href="{{ route('booking.create') }}" class="book">Booking &gt;</a>
                    </div>
                </div>
            </article>
        @endforeach
    </div>
</section>

<section class="map fade-up">
    <h2>
        <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="#f98a2e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s7-6.2 7-11.5A7 7 0 0 0 5 9.5C5 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/></svg>
        Temukan Tempat Kami.
    </h2>
    <p>Jl. Kadisoka, Banjeng, Maguwoharjo, Kec. Depok, Kabupaten Sleman, Daerah Istimewa Yogyakarta 55281</p>
    <iframe src="https://www.google.com/maps?q=-7.7533754,110.4410462&z=16&output=embed" loading="lazy" allowfullscreen></iframe>
</section>
@endsection