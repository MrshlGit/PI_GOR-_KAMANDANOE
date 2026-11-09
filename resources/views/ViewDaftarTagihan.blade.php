@php
    $user = auth()->user();
    $menu = [
        ['label' => 'Dashboard',         'url' => '/dashboard', 'is' => 'dashboard*', 'ready' => true,
         'icon' => '<path d="m3 11 9-8 9 8"/><path d="M5 10v10h14V10"/>'],
        ['label' => 'Info Turnamen',     'url' => '#',          'is' => 'turnamen*',  'ready' => false,
         'icon' => '<path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0V4z"/><path d="M17 5h3v2a3 3 0 0 1-3 3M7 5H4v2a3 3 0 0 0 3 3"/>'],
        ['label' => 'Layanan Informasi',  'url' => '#',          'is' => 'layanan*',   'ready' => false,
         'icon' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>'],
        ['label' => 'Pemesanan',         'url' => route('booking.create'),   'is' => 'booking*',   'ready' => true,
         'icon' => '<circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M3 4h2l2.4 11h10.2L20 7H6"/>'],
        ['label' => 'Transaksi',         'url' => '/transaksi', 'is' => 'transaksi*', 'ready' => true,
         'icon' => '<path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3z"/><path d="M9 8h6M9 12h6"/>'],
    ];
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Tagihan · GOR Kamandanoe</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/daftartagihan.css') }}">
</head>
<body>

<div class="app">
    <aside class="sidebar" id="sidebar">
        <a href="/dashboard" class="brand">GOR <span>Kamandanoe</span></a>
        <nav class="menu">
            @foreach ($menu as $m)
                <a href="{{ $m['url'] }}"
                   @class(['active' => request()->is($m['is']) || ($m['label'] == 'Transaksi' && request()->is('transaksi*')), 'soon' => !$m['ready']])
                   @if(!$m['ready']) title="Segera hadir" @endif>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $m['icon'] !!}</svg>
                    {{ $m['label'] }}
                </a>
            @endforeach
        </nav>
        <form method="POST" action="/logout" class="logout">
            @csrf
            <button type="submit">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h3M16 8l4 4-4 4M20 12H9"/></svg>
                Logout
            </button>
        </form>
    </aside>

    <div class="main">
        <header class="topbar">
            <button class="burger" id="burger" aria-label="Menu">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
            <label class="search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                <input id="cari" type="search" placeholder="Cari lapangan">
            </label>
            <div class="top-right">
                <div class="dropdown">
                    <button class="user-btn trigger">
                        <span class="avatar">{{ strtoupper(substr($user->username ?? 'U', 0, 1)) }}</span>
                        <div><b>{{ $user->username ?? 'Isnan' }}</b><small>{{ ucfirst($user->role ?? 'User') }}</small></div>
                    </button>
                </div>
            </div>
        </header>

        <main class="content">
            <section class="content-body">
                <div class="header-card">
                    <h1>Daftar Tagihan</h1>
                    <p>Sebelum melakukan pembayaran mohon dicek semua barang yang telah dipesan sesuai dengan deskripsi pembayaran.!!</p>
                </div>

                <div class="data-grid">
                    <div class="customer-card">
                        <div class="card-title-bar">
                            <span>Customer</span>
                        </div>
                        <div class="card-body">
                            <div class="form-row">
                                <label>Nama Customer</label>
                                <input type="text" value="{{ $customer['nama'] }}" readonly class="input-white">
                            </div>
                            <div class="form-row">
                                <label>No Telepon</label>
                                <input type="text" value="{{ $customer['telepon'] }}" readonly class="input-gray">
                            </div>
                            <div class="form-row">
                                <label>Email</label>
                                <input type="text" value="{{ $customer['email'] }}" readonly class="input-gray">
                            </div>
                        </div>
                    </div>

                    <div class="table-card">
                        <table>
                            <thead>
                                <tr>
                                    <th>NAMA</th>
                                    <th>JENIS</th>
                                    <th>JUMLAH</th>
                                    <th>DURASI</th>
                                    <th>HARGA</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tagihans as $item)
                                    <tr>
                                        <td>{{ $item['nama'] }}</td>
                                        <td>{{ $item['jenis'] }}</td>
                                        <td>{{ $item['jumlah'] }}</td>
                                        <td>{{ $item['durasi'] }}</td>
                                        <td>{{ $item['harga'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" style="padding: 20px; color: #666;">Belum ada tagihan atau pemesanan aktif.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="action-footer">
                    <a href="{{ route('pembayaran.index') }}" class="btn btn-confirm" style="text-decoration: none;">
                        Konfirmasi Pembayaran
                    </a>
                </div>
            </section>
        </main>
    </div>
</div>

<script src="{{ asset('js/dashboard.js') }}"></script>
</body>
</html>