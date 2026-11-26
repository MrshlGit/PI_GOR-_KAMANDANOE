<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Menunggu Verifikasi · GOR Kamandanoe</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/menungguverifikasi.css') }}">
</head>
<body class="verify-page">
    <div class="verify-outer">
        <h1>Menunggu Verifikasi</h1>
        <div class="verify-inner">
            <div class="verify-label">Total Transaksi</div>
            <div class="verify-amount">{{ $totalFormatted }}</div>
            <div class="loader" aria-label="Memuat"></div>
            <a href="{{ route('pembayaran.riwayat') }}" class="btn-cek-status">cek status</a>
        </div>
    </div>
</body>
</html>