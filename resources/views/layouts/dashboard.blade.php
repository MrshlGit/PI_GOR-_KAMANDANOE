<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') · GOR Kamandanoe</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
</head>
<body>
@php
    $user = auth()->user();
    $menu = [
        ['label' => 'Dashboard',          'url' => '/dashboard', 'is' => 'dashboard*', 'ready' => true,
         'icon' => '<path d="m3 11 9-8 9 8"/><path d="M5 10v10h14V10"/>'],
        ['label' => 'Info Turnamen',      'url' => '#',          'is' => 'turnamen*',  'ready' => false,
         'icon' => '<path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0V4z"/><path d="M17 5h3v2a3 3 0 0 1-3 3M7 5H4v2a3 3 0 0 0 3 3"/>'],
        ['label' => 'Layanan Informasi',  'url' => '#',          'is' => 'layanan*',   'ready' => false,
         'icon' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>'],
        ['label' => 'Pemesanan',          'url' => '/booking',   'is' => 'booking*',   'ready' => false,
         'icon' => '<circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/><path d="M3 4h2l2.4 11h10.2L20 7H6"/>'],
        ['label' => 'Transaksi',          'url' => '/transaksi', 'is' => 'transaksi*', 'ready' => false,
         'icon' => '<path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3z"/><path d="M9 8h6M9 12h6"/>'],
    ];
@endphp

<div class="app">

    {{-- ===== SIDEBAR ===== --}}
    <aside class="sidebar" id="sidebar">
        <a href="/dashboard" class="brand">GOR <span>Kamandanoe</span></a>

        <nav class="menu">
            @foreach ($menu as $m)
                <a href="{{ $m['url'] }}"
                   @class(['active' => request()->is($m['is']), 'soon' => !$m['ready']])
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

        {{-- ===== TOPBAR ===== --}}
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
                    <button class="icon-btn ring trigger" aria-label="Notifikasi">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9a6 6 0 0 1 12 0c0 6 2 7 2 7H4s2-1 2-7"/><path d="M10 20a2 2 0 0 0 4 0"/></svg>
                        <i></i>
                    </button>
                    <div class="drop"><p class="empty">Belum ada notifikasi</p></div>
                </div>

                <div class="dropdown">
                    <button class="user-btn trigger">
                        <span class="avatar">{{ strtoupper(substr($user->username, 0, 1)) }}</span>
                        <div><b>{{ $user->username }}</b><small>{{ ucfirst($user->role) }}</small></div>
                    </button>
                    <div class="drop">
                        <a href="#">Profil Saya (segera)</a>
                        <form method="POST" action="/logout">@csrf<button type="submit">Logout</button></form>
                    </div>
                </div>
            </div>
        </header>

        <main class="content">
            @yield('content')
        </main>

        {{-- ===== FOOTER ===== --}}
        <footer class="foot">
            <div class="foot-brand">GOR<br>Kamandanoe</div>
            <div><h4>Tentang Kami</h4><a href="#">gorkamandanoe.com</a><a href="#">gorkamandanoe.com</a><a href="#">gorkamandanoe.com</a></div>
            <div><h4>Partner</h4><a href="#">gorkamandanoe.com</a><a href="#">gorkamandanoe.com</a><a href="#">gorkamandanoe.com</a></div>
            <div><h4>Media Sosial</h4><a href="#">gorkamandanoe.com</a><a href="#">gorkamandanoe.com</a><a href="#">gorkamandanoe.com</a></div>
        </footer>

    </div>
</div>

<script src="{{ asset('js/dashboard.js') }}"></script>
</body>
</html>