@extends('layouts.dashboard')

@section('title', 'Pembayaran')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/ringkasantagihan.css') }}">
    <link rel="stylesheet" href="{{ asset('css/menungguverifikasi.css') }}">
@endpush

@section('content')

<section class="content-body">

    {{-- HEADER --}}
    <div class="header-card">
        <h1>Ringkasan Pembayaran</h1>
        <p>Tinjau pesanan Anda lalu klik tombol <strong>Bayar</strong> untuk menampilkan QRIS dan mengunggah bukti pembayaran.</p>
    </div>

    {{-- ALERT VALIDASI / ERROR / SUKSES --}}
    @if($errors->any())
        <div class="alert-box alert-error">
            {{ $errors->first() }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert-box alert-error">
            {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="alert-box alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- TOGGLE TAMPILKAN PANEL BAYAR TANPA JS --}}
    <input type="checkbox"
           id="showPaymentToggle"
           class="state-checkbox"
           {{ ($errors->any() || session('error') || request()->boolean('show_upload') || session('show_upload')) ? 'checked' : '' }}>

    {{-- TOGGLE MODAL QRIS TANPA JS --}}
    <input type="checkbox"
           id="qrModalToggle"
           class="state-checkbox">

    {{-- MODAL POPUP QRIS (TERSEMBUNYI SAMPAI ICON QR DIKLIK) --}}
    <div id="qrModal" class="modal-overlay">
        <div class="modal-content">
            <h3>Scan QRIS Pembayaran</h3>
            <p>Silahkan scan QR di bawah ini menggunakan m-Banking atau E-Wallet Anda.</p>

            <div style="margin: 20px 0;">
                <img src="{{ asset('img/QR.jpeg') }}"
                     alt="QRIS GOR Kamandanoe"
                     style="width: 220px; height: 220px; border: 2px solid #ddd; border-radius: 10px; object-fit: contain;">
            </div>

            <label for="qrModalToggle"
                   class="btn-select-file"
                   style="background:#333; color:#fff; border:none; cursor:pointer; display:inline-block;">
                Tutup
            </label>
        </div>
    </div>

    {{-- FORM UTAMA PEMBAYARAN --}}
    <form action="{{ route('pembayaran.store') }}"
          method="POST"
          enctype="multipart/form-data"
          class="payment-grid-form"
          id="paymentForm">

        @csrf

        <div class="payment-grid">

            {{-- KOLOM KIRI: ITEM DAN UPLOAD --}}
            <div class="left-column">

                <div class="items-table-card">
                    <table>
                        <thead>
                            <tr>
                                <th>NAMA</th>
                                <th>JUMLAH/HARGA</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($items as $item)
                                <tr>
                                    <td>{{ $item['nama'] }}</td>
                                    <td>{{ $item['harga'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" style="text-align: center; padding: 20px; color: #777;">
                                        Tidak ada tagihan aktif.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- UPLOAD BUKTI PEMBAYARAN (MUNCUL SAAT CHECKBOX BAYAR AKTIF) --}}
                <div class="upload-section" id="uploadSection">

                    <h3 class="section-title">Bukti Pembayaran</h3>

                    <div class="upload-row" style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">

                        {{-- ICON QRIS (MEMICU CHECKBOX MODAL) --}}
                        <label for="qrModalToggle"
                               class="qr-trigger"
                               style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-width: 110px; padding: 14px; border: 1px solid #f78333; border-radius: 10px; background: #fff; cursor: pointer;">

                            <svg viewBox="0 0 24 24"
                                 width="32"
                                 height="32"
                                 stroke="#f78333"
                                 stroke-width="2"
                                 fill="none">

                                <rect x="3" y="3" width="7" height="7"></rect>
                                <rect x="14" y="3" width="7" height="7"></rect>
                                <rect x="14" y="14" width="7" height="7"></rect>
                                <rect x="3" y="14" width="7" height="7"></rect>

                            </svg>

                            <span style="font-size: 13px; font-weight: 600; color: #f78333; margin-top: 6px;">
                                Klik (QRIS)
                            </span>
                        </label>

                        {{-- BOX UPLOAD FILE --}}
                        <div class="dropzone-box" id="dropzoneBox" style="flex: 1; min-width: 240px;">

                            <div class="upload-icon-circle">
                                <svg viewBox="0 0 24 24"
                                     width="28"
                                     height="28"
                                     stroke="currentColor"
                                     stroke-width="2"
                                     fill="none">

                                    <path d="M17 21H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2z"></path>
                                    <polyline points="12 11 12 17"></polyline>
                                    <polyline points="9 14 12 11 15 14"></polyline>

                                </svg>
                            </div>

                            <p class="dropzone-text" id="fileLabelText">
                                Pilih file bukti bayar Anda di bawah ini
                            </p>

                            <input type="file"
                                   id="file_upload"
                                   name="bukti_bayar"
                                   accept=".jpg,.jpeg,.png,.pdf"
                                   class="native-file-input"
                                   style="display: inline-block; width: auto; max-width: 100%; color: #333; overflow: visible;"
                                   required>

                            <span class="file-hint">
                                Format JPG, PNG, atau PDF (Maks. 5MB)
                            </span>

                        </div>

                    </div>

                </div>

            </div>

            {{-- KOLOM KANAN: RINGKASAN --}}
            <div class="right-column">

                <div class="summary-card">

                    <h2>Ringkasan</h2>

                    <p class="summary-desc">
                        Mohon tinjau detail barang pesanan di bawah ini sebelum melakukan pembayaran.
                    </p>

                    <div class="summary-details">

                        <div class="summary-row">
                            <span>Tanggal Dibuat</span>
                            <strong>{{ $summary['tanggal'] }}</strong>
                        </div>

                        <div class="summary-row">
                            <span>Petugas</span>
                            <strong>{{ $summary['petugas'] }}</strong>
                        </div>

                        <div class="summary-row">
                            <span>Subtotal</span>
                            <strong>{{ $summary['subtotal'] }}</strong>
                        </div>

                        <div class="summary-row">
                            <span>Potongan Harga</span>
                            <strong>{{ $summary['potongan'] }}</strong>
                        </div>

                        <div class="summary-row">
                            <span>PPN</span>
                            <strong>{{ $summary['ppn'] }}</strong>
                        </div>

                        <div class="summary-row">
                            <span>Nilai Transaksi</span>
                            <strong>{{ $summary['nilai_transaksi'] }}</strong>
                        </div>

                    </div>

                    <div class="summary-total-row">
                        <span>Total :</span>
                        <strong>{{ $summary['total'] }}</strong>
                    </div>

                    {{-- TOMBOL BAYAR: MEMBUKA PANEL UPLOAD, LALU MENJADI TOMBOL SUBMIT --}}
                    <div class="pay-action-wrapper">
                        <label for="showPaymentToggle"
                               id="btnShowPayment"
                               class="btn-pay btn-pay-label">
                            Bayar
                        </label>

                        <button type="submit"
                                id="btnSubmitPayment"
                                class="btn-pay">
                            Bayar
                        </button>
                    </div>

                </div>

            </div>

        </div>

    </form>

</section>


{{-- MODAL POPUP MENUNGGU VERIFIKASI (TANPA JS) --}}
@if(session('menunggu_verifikasi_id'))
    <input type="checkbox" id="verifyModalDismiss" class="state-checkbox">
    <div id="verifyModal" class="verify-modal-overlay">
        <div class="verify-outer">
            <label for="verifyModalDismiss" class="verify-close" aria-label="Tutup">&times;</label>
            <h1>Menunggu Verifikasi</h1>
            <div class="verify-inner">
                <div class="verify-label">Total Transaksi</div>
                <div class="verify-amount">{{ session('total_transaksi') ?? ($summary['total'] ?? 'Rp 0') }}</div>
                <div class="verify-loader" aria-label="Menunggu verifikasi"></div>
                <a href="{{ route('pembayaran.riwayat') }}" class="btn-cek-status">cek status</a>
            </div>
        </div>
    </div>
@endif


{{-- CSS KHUSUS PEMBAYARAN (TANPA JS) --}}
<style>

    .state-checkbox {
        display: none;
    }

    .payment-grid-form {
        width: 100%;
    }

    .alert-box {
        padding: 12px 18px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
    }

    .alert-error {
        background-color: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .alert-success {
        background-color: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    /* Sembunyikan upload section sampai #showPaymentToggle dicentang */
    #uploadSection {
        display: none;
    }

    #showPaymentToggle:checked ~ .payment-grid-form #uploadSection {
        display: block !important;
    }

    /* Sebelum panel terbuka: tampilkan label Bayar, sembunyikan tombol submit */
    #btnShowPayment {
        display: block;
    }

    #btnSubmitPayment {
        display: none;
    }

    /* Setelah panel terbuka: sembunyikan label Bayar, tampilkan tombol submit Bayar */
    #showPaymentToggle:checked ~ .payment-grid-form #btnShowPayment {
        display: none !important;
    }

    #showPaymentToggle:checked ~ .payment-grid-form #btnSubmitPayment {
        display: block !important;
    }

    .native-file-input {
        display: inline-block !important;
        width: auto !important;
        max-width: 100% !important;
        color: #333 !important;
        overflow: visible !important;
        margin: 10px 0 !important;
        font-size: 13px !important;
    }

    .btn-pay {
        background-color: #f78333;
        color: white;
        border: none;
        padding: 12px;
        width: 100%;
        border-radius: 8px;
        font-weight: 700;
        cursor: pointer;
        transition: opacity 0.2s, transform 0.15s;
    }

    .btn-pay-label {
        display: block;
        text-align: center;
        box-sizing: border-box;
    }

    .btn-pay:hover {
        opacity: 0.92;
    }

    /* Modal QRIS dikontrol #qrModalToggle */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    #qrModalToggle:checked ~ #qrModal {
        display: flex !important;
    }

    /* Tutup modal verifikasi saat #verifyModalDismiss dicentang */
    #verifyModalDismiss:checked + #verifyModal {
        display: none;
    }

    .modal-content {
        background: #fff;
        padding: 30px;
        border-radius: 16px;
        text-align: center;
        max-width: 400px;
        width: 100%;
        box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    }

</style>

@endsection