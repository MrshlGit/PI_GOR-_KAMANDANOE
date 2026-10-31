<!-- 
<!DOCTYPE html>
<html>

<head>

    <title>GOR Kamandanoe</title>

    <link rel="stylesheet" href="../css/home.css">

</head>

<body>
    <div class="besar">
        <div class="atas">
            <div class="navbar">
                <img src="../img/canvas.png" class="logo">

                <h1>GOR KAMANDANOE</h1>

            </div>

            <div class="navbar2">

                <a href="booking_view.php"><strong>Booking</strong></a>

                <a href="../controller/jadwal.php"><strong>Cek Jadwal</strong></a>

                <a href="transaksi_view.php"><strong>Pembayaran</strong></a>

                <a href="../logout.php"><strong>Logout</strong></a>

            </div>
        </div>

        <div class="hero">

            <a href="https://www.google.com/maps/place/GOR+Kamandanoe/@-7.7533754,110.4410462,17z/data=!3m1!4b1!4m6!3m5!1s0x2e7a5a32fdfadf89:0x92648a5d8a236746!8m2!3d-7.7533754!4d110.4410462!16s%2Fg%2F11c48nw0j8"
               target="_blank" class="btn">
                📍 Lihat Lokasi GOR
            </a>

        </div>

        <div class="container">

            <div class="card">
                <div class="latar">
                    <h2>Lapangan Karpet 🏸</h2><br>
                    <p>Terdapat 2 lapangan nomor 1 dan 2</p>
                </div>
            </div>

            <div class="cards">
                <div class="latar">
                    <h2>Lapangan Kayu 🏸</h2><br>
                    <p>Terdapat 2 lapangan nomor 3 dan 4</p>
                </div>
            </div>

        </div>

        <div class="container2">

            <div class="card2">
                <div class="topik">
                    <h3>Booking Lapangan</h3>
                </div>
                <div class="isi1">
                    <a href="booking_view.php"></a>
                </div>
            </div>

            <div class="card2">
                <div class="topik">
                    <h3>Cek Jadwal</h3>
                </div>
                <div class="isi2">
                    <a href="../controller/jadwal.php"></a>
                </div>
            </div>

            <div class="card2">
                <div class="topik">
                    <h3>Transaksi</h3>
                </div>
                <div class="isi3">
                    <a href="transaksi_view.php"></a>
                </div>
            </div>


        </div>
    </div>
</body>

</html> -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GOR Kamandanoe · Pemesanan Lapangan</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Lato:wght@700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
</head>
<body>

<header class="nav">
    <div class="wrap nav-in">
        <a href="/" class="logo">GOR KAMANDANOE</a>
        <nav>
            <a href="#lapangan">Lapangan</a>
            <a href="#cara">Cara Booking</a>
            <a href="/login" class="btn-ghost">Masuk</a>
            <a href="/register" class="btn">Daftar</a>
        </nav>
    </div>
</header>

<section class="hero">
    <div class="wrap">
        <p class="eyebrow">Portal pemesanan lapangan</p>
        <h1>Booking lapangan,<br>tanpa antre.</h1>
        <p class="lead">Cek jadwal, pilih jam, dan bayar. Semua dari HP kamu.</p>
        <div class="cta">
            <a href="/register" class="btn btn-lg">Daftar &amp; booking</a>
            <a href="/login" class="btn-outline btn-lg">Sudah punya akun</a>
        </div>
        <p class="alamat">Jl. Kadisoka, Maguwoharjo, Depok, Sleman, D.I. Yogyakarta 55281 · Buka 08.00 – 22.00</p>
    </div>
</section>

<section id="lapangan" class="wrap section">
    <h2>Pilih lapangan</h2>
    <div class="grid">
        <div class="card-l"><h3>Lapangan A</h3><p>Lantai karpet</p><b>Rp 50.000 / jam</b></div>
        <div class="card-l"><h3>Lapangan B</h3><p>Lantai kayu</p><b>Rp 70.000 / jam</b></div>
        <div class="card-l"><h3>Lapangan C</h3><p>Premium</p><b>Rp 90.000 / jam</b></div>
        <div class="card-l"><h3>Lapangan D</h3><p>Premium</p><b>Rp 90.000 / jam</b></div>
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

<footer class="foot">© {{ date('Y') }} GOR Kamandanoe</footer>

</body>
</html>