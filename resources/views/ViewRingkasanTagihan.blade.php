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
    <title>Ringkasan Pembayaran · GOR Kamandanoe</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ringkasantagihan.css') }}">
</head>
<body>

<div class="app">

    {{-- ===== SIDEBAR ===== --}}
    <aside class="sidebar" id="sidebar">
        <a href="/dashboard" class="brand">GOR <span>Kamandanoe</span></a>
        <nav class="menu">
            @foreach ($menu as $m)
                <a href="{{ $m['url'] }}"
                   @class(['active' => request()->is($m['is']) || ($m['label'] == 'Transaksi' && (request()->is('transaksi*') || request()->is('pembayaran*'))), 'soon' => !$m['ready']])
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
                        <span class="avatar">{{ strtoupper(substr($user->username ?? 'U', 0, 1)) }}</span>
                        <div><b>{{ $user->username ?? 'Isnan' }}</b><small>{{ ucfirst($user->role ?? 'User') }}</small></div>
                    </button>
                    <div class="drop">
                        <a href="#">Profil Saya (segera)</a>
                        <form method="POST" action="/logout">@csrf<button type="submit">Logout</button></form>
                    </div>
                </div>
            </div>
        </header>

        {{-- ===== MAIN CONTENT AREA ===== --}}
        <main class="content">
            <section class="content-body">
                
                <div class="header-card">
                    <h1>Ringkasan Pembayaran</h1>
                    <p>Silahkan upload file/foto transaksi bayar anda pada tempat file yang telah disediakan</p>
                </div>

                <!-- FORM UTAMA PEMBAYARAN -->
                <form action="{{ route('pembayaran.store') }}" method="POST" enctype="multipart/form-data" class="payment-grid-form">
                    @csrf
                    <div class="payment-grid">
                        
                        <!-- Kolom Kiri -->
                        <div class="left-column">
                            <div class="items-table-card">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>NAMA</th>
                                            <th>JUMLAH</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($items as $item)
                                            <tr>
                                                <td>{{ $item['nama'] }}</td>
                                                <td>{{ $item['harga'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="upload-section">
                                <h3 class="section-title">Bukti Pembayaran</h3>
                                <div class="upload-row">
                                    <!-- Tombol Panggil QR Code -->
                                    <div class="qr-trigger" onclick="openQrModal()" style="cursor: pointer;">
                                        <svg viewBox="0 0 24 24" width="32" height="32" stroke="currentColor" stroke-width="2" fill="none"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                                        <span style="font-size: 13px; font-weight: 600; color: #f78333;">Klik (QRIS)</span>
                                    </div>

                                    <!-- Box Upload File -->
                                    <div class="dropzone-box" id="dropzoneBox">
                                        <div class="upload-icon-circle">
                                            <svg viewBox="0 0 24 24" width="28" height="28" stroke="currentColor" stroke-width="2" fill="none"><path d="M17 21H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="12 11 12 17"></polyline><polyline points="9 14 12 11 15 14"></polyline></svg>
                                        </div>
                                        <p class="dropzone-text" id="fileLabelText">Tarik & Lepas bukti bayar di sini, atau</p>
                                        <label for="file_upload" class="btn-select-file">Pilih File</label>
                                        <!-- Event onchange untuk mendeteksi file dan mengaktifkan tombol bayar -->
                                        <input type="file" id="file_upload" name="bukti_bayar" style="display:none;" onchange="handleFileSelect(this)">
                                        <span class="file-hint">Format JPG, PNG, atau PDF (Maks. 5MB)</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Kolom Kanan (Ringkasan & Tombol Bayar) -->
                        <div class="right-column">
                            <div class="summary-card">
                                <h2>Ringkasan</h2>
                                <p class="summary-desc">Mohon tinjau detail barang pesanan di bawah ini sebelum melakukan pembayaran.</p>
                                
                                <div class="summary-details">
                                    <div class="summary-row"><span>Tanggal Dibuat</span><strong>{{ $summary['tanggal'] }}</strong></div>
                                    <div class="summary-row"><span>Petugas</span><strong>{{ $summary['petugas'] }}</strong></div>
                                    <div class="summary-row"><span>Subtotal</span><strong>{{ $summary['subtotal'] }}</strong></div>
                                    <div class="summary-row"><span>Potongan Harga</span><strong>{{ $summary['potongan'] }}</strong></div>
                                    <div class="summary-row"><span>PPN</span><strong>{{ $summary['ppn'] }}</strong></div>
                                    <div class="summary-row"><span>Nilai Transaksi</span><strong>{{ $summary['nilai_transaksi'] }}</strong></div>
                                </div>

                                <div class="summary-total-row">
                                    <span>Total :</span>
                                    <strong>{{ $summary['total'] }}</strong>
                                </div>

                                <div class="pay-action-wrapper">
                                    <!-- Tombol Bayar (Default Disabled & Abu-abu) -->
                                    <button type="submit" id="btnPay" class="btn-pay disabled" disabled>Bayar</button>
                                </div>
                            </div>
                        </div>

                    </div>
                </form>

            </section>
        </main>
    </div>
</div>

<!-- MODAL POPUP QRIS -->
<div id="qrModal" class="modal-overlay" style="display:none;">
    <div class="modal-content">
        <h3>Scan QRIS Pembayaran</h3>
        <p>Silahkan scan QR di bawah ini menggunakan m-Banking atau E-Wallet Anda.</p>
        <!-- Contoh gambar QRIS -->
        <div style="margin: 20px 0;">
           <img src="{{ asset('img/QR.jpeg') }}" alt="QRIS GOR Kamandanoe" style="width: 220px; height: 220px; border: 2px solid #ddd; border-radius: 10px; object-fit: contain;">
        </div>
        <button type="button" onclick="closeQrModal()" class="btn-select-file" style="background:#333;">Tutup</button>
    </div>
</div>

<style>
/* CSS Tambahan untuk Modal & State Tombol Disabled */
.btn-pay.disabled {
    background-color: #cccccc !important;
    color: #666666 !important;
    cursor: not-allowed !important;
    opacity: 0.7;
}
.modal-overlay {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.5); display: flex; justify-content: center; align-items: center; z-index: 9999;
}
.modal-content {
    background: #fff; padding: 30px; border-radius: 16px; text-align: center; max-width: 400px; width: 100%;
    box-shadow: 0 4px 20px rgba(0,0,0,0.2);
}
.payment-grid-form { width: 100%; }
</style>

<script>
    lucide.createIcons();

    // Fungsi Popup QR
    function openQrModal() {
        document.getElementById('qrModal').style.display = 'flex';
    }
    function closeQrModal() {
        document.getElementById('qrModal').style.display = 'none';
    }

    // Fungsi Deteksi File Upload & Mengaktifkan Tombol Bayar
    function handleFileSelect(input) {
        const btnPay = document.getElementById('btnPay');
        const fileLabelText = document.getElementById('fileLabelText');
        
        if (input.files && input.files[0]) {
            const fileName = input.files[0].name;
            fileLabelText.textContent = "File dipilih: " + fileName;
            fileLabelText.style.color = "#0a8a18";
            fileLabelText.style.fontWeight = "600";

            // Ubah tombol jadi aktif & warna orange
            btnPay.classList.remove('disabled');
            btnPay.removeAttribute('disabled');
        } else {
            fileLabelText.textContent = "Tarik & Lepas bukti bayar di sini, atau";
            fileLabelText.style.color = "#666";

            // Matikan kembali tombol jika file batal dipilih
            btnPay.classList.add('disabled');
            btnPay.setAttribute('disabled', 'true');
        }
    }
</script>
<script src="{{ asset('js/dashboard.js') }}"></script>
</body>
</html>