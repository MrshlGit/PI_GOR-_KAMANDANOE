<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ff8737">
    <title>Dashboard — GOR Kamandanoe</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
</head>
<body>
    <div class="dashboard-shell" id="dashboard">
        <aside class="sidebar">
            <a class="brand" href="{{ route('dashboard') }}" aria-label="GOR Kamandanoe, Dashboard">
                <span class="brand-mark" aria-hidden="true">G</span>
                <span class="brand-name">GOR<br>KAMANDANOE</span>
            </a>

            <nav class="sidebar-nav" aria-label="Navigasi utama">
                <a class="nav-link is-active" href="#dashboard">
                    <span class="nav-icon" aria-hidden="true">▦</span><span>Dashboard</span>
                </a>
                <a class="nav-link" href="#users-card">
                    <span class="nav-icon" aria-hidden="true">♙</span><span>Manajemen Akun</span>
                </a>
                <a class="nav-link" href="#tournaments-card">
                    <span class="nav-icon" aria-hidden="true">♜</span><span>Manajemen Turnamen</span>
                </a>
                <a class="nav-link" href="#courts">
                    <span class="nav-icon" aria-hidden="true">⌁</span><span>Manajemen Lapangan</span>
                </a>
                <a class="nav-link" href="#recent-bookings">
                    <span class="nav-icon" aria-hidden="true">▣</span><span>Manajemen Pemesanan</span>
                </a>
                <a class="nav-link" href="#revenue-card">
                    <span class="nav-icon" aria-hidden="true">▤</span><span>Laporan Keuangan</span>
                </a>
            </nav>

            <div class="sidebar-profile">
                <div class="avatar" aria-hidden="true">A</div>
                <div class="profile-copy">
                    <strong>Administrator</strong>
                    <span>Admin GOR</span>
                </div>
                <button class="logout-button" type="button" title="Fungsi keluar belum diaktifkan">Keluar</button>
            </div>
        </aside>

        <main class="main-content">
            <header class="page-header">
                <div>
                    <p class="eyebrow">PANEL ADMINISTRATOR</p>
                    <h1>Dashboard <span class="heading-spark" aria-hidden="true">✳</span></h1>
                    <p class="page-subtitle">Ringkasan aktivitas GOR Kamandanoe.</p>
                </div>
                <label class="search-box">
                    <span aria-hidden="true">⌕</span>
                    <input type="search" placeholder="Cari aktivitas, pengguna..." aria-label="Cari aktivitas atau pengguna">
                    <kbd>/</kbd>
                </label>
            </header>

            <section class="metrics-grid" aria-label="Ringkasan statistik">
                @foreach ($dashboard['metrics'] as $metric)
                    @php
                        $metricId = match ($metric['icon']) {
                            'users' => 'users-card',
                            'trophy' => 'tournaments-card',
                            'wallet' => 'revenue-card',
                            default => 'bookings-card',
                        };
                    @endphp
                    <article class="metric-card metric-card--{{ $metric['icon'] }}" id="{{ $metricId }}">
                        <div class="metric-topline">
                            <span class="metric-label">{{ $metric['label'] }}</span>
                            <span class="metric-icon" aria-hidden="true">
                                @if ($metric['icon'] === 'users') ♙
                                @elseif ($metric['icon'] === 'trophy') ♜
                                @elseif ($metric['icon'] === 'wallet') ↗
                                @else ▣
                                @endif
                            </span>
                        </div>
                        <p class="metric-value">{{ $metric['value'] }}</p>
                        <p class="metric-note"><span aria-hidden="true">●</span> {{ $metric['note'] }}</p>
                    </article>
                @endforeach
            </section>

            <section class="overview-grid">
                <article class="panel bookings-panel" id="recent-bookings">
                    <div class="panel-heading">
                        <div>
                            <p class="eyebrow">AKTIVITAS</p>
                            <h2>Riwayat Pemesanan Terbaru</h2>
                        </div>
                        <a class="text-link" href="#recent-bookings">Lihat semua <span aria-hidden="true">↗</span></a>
                    </div>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Lapangan</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($dashboard['bookings'] as $booking)
                                    <tr>
                                        <td>{{ $booking['name'] }}</td>
                                        <td>{{ $booking['court'] }}</td>
                                        <td>{{ $booking['date'] }}</td>
                                        <td><span class="status-badge">{{ $booking['status'] }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="empty-table">
                                            <span class="empty-icon" aria-hidden="true">▤</span>
                                            <strong>Belum ada pemesanan</strong>
                                            <span>Data pemesanan akan muncul di sini.</span>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>

                <article class="panel courts-panel" id="courts">
                    <div class="panel-heading">
                        <div>
                            <p class="eyebrow">FASILITAS</p>
                            <h2>Status Lapangan</h2>
                        </div>
                        <span class="count-tag">{{ count($dashboard['courts']) }} AREA</span>
                    </div>
                    <div class="court-list">
                        @foreach ($dashboard['courts'] as $court)
                            <div class="court-item">
                                <span class="court-icon court-icon--{{ $court['color'] }}" aria-hidden="true">⌁</span>
                                <strong>{{ $court['name'] }}</strong>
                                <span class="status-badge status-badge--muted">{{ $court['status'] }}</span>
                            </div>
                        @endforeach
                    </div>
                    <a class="panel-action" href="#courts">Kelola lapangan <span aria-hidden="true">→</span></a>
                </article>
            </section>

            <section class="panel traffic-panel" aria-labelledby="traffic-title">
                <div class="panel-heading traffic-heading">
                    <div>
                        <p class="eyebrow">ANALISIS MINGGUAN</p>
                        <h2 id="traffic-title">Trafik Pengunjung Lapangan</h2>
                        <p class="panel-description">Jumlah pengunjung lapangan berdasarkan gender per minggu.</p>
                    </div>
                    <span class="chart-total"><strong>0</strong><span> TOTAL PENGUNJUNG</span></span>
                </div>

                <div class="chart-legend" aria-label="Legenda grafik">
                    <span><i class="legend-dot legend-dot--male"></i> Pria</span>
                    <span><i class="legend-dot legend-dot--total"></i> Total</span>
                    <span><i class="legend-dot legend-dot--female"></i> Wanita</span>
                </div>
                <div class="traffic-chart" role="img" aria-label="Grafik jumlah pengunjung selama tujuh hari, semua data bernilai nol">
                    @foreach ($dashboard['traffic'] as $day)
                        <div class="chart-day">
                            <span class="day-name">{{ $day['day'] }}</span>
                            <div class="bar-group">
                                <div class="bar bar--male" style="--bar-height: {{ $day['male'] }}%" title="Pria: {{ $day['male'] }}"></div>
                                <div class="bar bar--total" style="--bar-height: {{ $day['total'] }}%" title="Total: {{ $day['total'] }}"></div>
                                <div class="bar bar--female" style="--bar-height: {{ $day['female'] }}%" title="Wanita: {{ $day['female'] }}"></div>
                            </div>
                            <div class="day-values">
                                <span>{{ $day['male'] }}</span>
                                <span>{{ $day['total'] }}</span>
                                <span>{{ $day['female'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
                <p class="chart-empty-note">Grafik akan terisi setelah data pengunjung tersedia.</p>
            </section>

            <footer class="page-footer">
                <span><i class="footer-dot"></i> Mode tampilan — belum terhubung ke database</span>
                <span>GOR Kamandanoe <span aria-hidden="true">✳</span> Dashboard</span>
            </footer>
        </main>
    </div>
</body>
</html>
