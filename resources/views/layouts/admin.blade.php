<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') · Admin GOR Kamandanoe</title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <script src="{{ asset('js/admin.js') }}" defer></script>
</head>
<body>

<svg width="0" height="0" style="position:absolute">
    <symbol id="i-grid" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></symbol>
    <symbol id="i-user" viewBox="0 0 24 24"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></symbol>
    <symbol id="i-users" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></symbol>
    <symbol id="i-trophy" viewBox="0 0 24 24"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6M18 9h1.5a2.5 2.5 0 0 0 0-5H18M4 22h16M10 14.66V17c0 .55-.47.98-1.04 1.21C7.85 18.75 7 20.24 7 22M14 14.66V17c0 .55.47.98 1.04 1.21C16.15 18.75 17 20.24 17 22M18 2H6v7a6 6 0 0 0 12 0V2Z"/></symbol>
    <symbol id="i-pin" viewBox="0 0 24 24"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></symbol>
    <symbol id="i-cal" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></symbol>
    <symbol id="i-cash" viewBox="0 0 24 24"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></symbol>
    <symbol id="i-search" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></symbol>
    <symbol id="i-cart" viewBox="0 0 24 24"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></symbol>
    <symbol id="i-act" viewBox="0 0 24 24"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></symbol>
    <symbol id="i-menu" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></symbol>
    <symbol id="i-warn" viewBox="0 0 24 24"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4M12 17h.01"/></symbol>
</svg>

@php
    $menu = [
        ['Dashboard',            'grid',   'admin.dashboard',  'admin.dashboard'],
        ['Manajemen Akun',       'user',   'admin.akun.index', 'admin.akun.*'],
        ['Manajemen Turnamen',   'trophy', null,               null],
        ['Manajemen Lapangan',   'pin',    null,               null],
        ['Manajemen Pemesanan',  'cal',    null,               null],
        ['Laporan Keuangan',     'cash',   null,               null],
    ];
@endphp

<div class="shell">
    <aside class="sb" id="sb">
        <div class="brand">
            <span class="logo">G</span>
            <span>GOR<br>KAMANDANOE</span>
        </div>

        <nav class="menu">
            @foreach ($menu as [$nama, $ikon, $rute, $pola])
                <a href="{{ $rute ? route($rute) : '#' }}" @class(['on' => $pola && request()->routeIs($pola)])>
                    <svg class="ic"><use href="#i-{{ $ikon }}"/></svg> {{ $nama }}
                </a>
            @endforeach
        </nav>

        <div class="me">
            <span class="av">{{ strtoupper(substr(auth()->user()->username, 0, 1)) }}</span>
            <div><b>{{ auth()->user()->username }}</b><small>Administrator</small></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="out">Logout</button>
            </form>
        </div>
    </aside>

    <main class="main">
        @yield('content')
    </main>
</div>

@if (session('sukses') || session('galat'))
    <div class="toast {{ session('galat') ? 'err' : '' }}" id="toast" role="status">
        {{ session('galat') ?? session('sukses') }}
    </div>
@endif

</body>
</html>
