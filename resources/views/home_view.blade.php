<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GOR Kamandanoe · Pemesanan Lapangan</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Lato:wght@700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
    <script src="{{ asset('js/home.js') }}" defer></script>
</head>
<body>

<header class="nav">
    <div class="nav-in">
        <a href="/" class="logo">GOR KAMANDANOE</a>

        <nav class="nav-menu">
            <a href="#lapangan">Lapangan</a>
            <a href="#cara">Cara Booking</a>
            <a href="#peraturan">Peraturan</a>
        </nav>

        <div class="nav-auth" id="navAuth">
            <span class="pill"></span>
            <a href="/login" class="na">Masuk</a>
            <a href="/register" class="na is-active">Daftar</a>
        </div>
    </div>
</header>

<section class="hero">
    <div class="hero-card">
        <div class="hero-bg" style="--bg:url('{{ asset('img/lapangan.jpeg') }}')"></div>
        <defs>
            <path id="plus" pathLength="1"
                  d="M35 0H65V35H100V65H65V100H35V65H0V35H35Z"/>
        </defs>
    </svg>

   

        <div class="hero-text">
            <p class="eyebrow">Portal pemesanan lapangan</p>
            <h1>Booking lapangan,<br><em>tanpa antre.</em></h1>
            <p class="lead">Cek jadwal, pilih jam, dan bayar. Semua dari HP kamu.</p>
            <div class="cta">
                <a href="/register" class="btn btn-lg">Daftar &amp; booking</a>
                <a href="/login" class="btn-outline btn-lg">Sudah punya akun</a>
            </div>
            <p class="alamat">Jl. Kadisoka, Maguwoharjo, Depok, Sleman, D.I. Yogyakarta 55281 · Buka 08.00 – 22.00</p>
        </div>

        <div class="hero-person">
            <img src="{{ asset('img/kok.png') }}" alt="Pemain badminton" onerror="this.style.display='none'">
        </div>
    </div>
</section>

<section id="lapangan" class="wrap section">
    <h2>Pilih lapangan</h2>
    <div class="grid">
        <div class="card-l"><h3>Lapangan 1</h3><p>Lantai karpet</p><b>Rp 40.000 / jam</b></div>
        <div class="card-l"><h3>Lapangan 2</h3><p>Lantai karpet</p><b>Rp 40.000 / jam</b></div>
        <div class="card-l"><h3>Lapangan 3</h3><p>Lantai kayu</p><b>Rp 30.000 / jam</b></div>
        <div class="card-l"><h3>Lapangan 4</h3><p>Lantai kayu</p><b>Rp 30.000 / jam</b></div>
    </div>
</section>

<section id="cara" class="wrap section">
    <h2>Cara booking</h2>
    <ol class="steps">
        <li><b>1</b> Daftar atau masuk</li>
        <li><b>2</b> Cek jadwal kosong</li>
        <li><b>3</b> Pilih lapangan, tanggal, dan jam</li>
        <li><b>4</b> Bayar via QRIS atau Cash</li>
    </ol>
</section>

@php
    $peraturan = [
        ['Dilarang merokok', 'Seluruh area gedung dan lapangan bebas asap rokok, termasuk rokok elektrik.'],
        ['Wajib sepatu olahraga', 'Gunakan sepatu olahraga bersol karet non-marking. Sepatu bersol keras atau yang meninggalkan bekas dilarang.'],
        ['Datang tepat waktu', 'Waktu bermain sesuai jam booking. Keterlambatan tidak memperpanjang waktu bermain.'],
        ['Dilarang alkohol dan narkoba', 'Dilarang membawa atau mengonsumsi minuman beralkohol dan zat terlarang di area GOR.'],
        ['Jaga kebersihan', 'Buang sampah pada tempatnya. Makanan dan minuman hanya di area yang disediakan, bukan di lapangan.'],
        ['Jaga fasilitas', 'Dilarang merusak net, tiang, lantai, dan fasilitas lain. Kerusakan akibat kelalaian menjadi tanggung jawab penyewa.'],
        ['Pembayaran sebelum bermain', 'Booking dianggap sah setelah pembayaran. Pembatalan mengikuti kebijakan pengelola.'],
        ['Barang pribadi', 'Simpan barang berharga dengan baik. Pengelola tidak bertanggung jawab atas kehilangan.'],
    ];
@endphp

<section id="peraturan" class="wrap section">
    <h2>Peraturan GOR</h2>
    <p class="section-sub">Demi kenyamanan bersama, mohon patuhi aturan berikut.</p>

    <div class="rules">
        @foreach ($peraturan as $i => [$judul, $isi])
            <div @class(['rule', 'rule-no' => $i === 0])>
                <b>{{ $i + 1 }}</b>
                <div>
                    <h3>{{ $judul }}</h3>
                    <p>{{ $isi }}</p>
                </div>
            </div>
        @endforeach
    </div>
</section>

<footer class="foot">© {{ date('Y') }} GOR Kamandanoe</footer>

</body>
</html>