
@extends('layouts.dashboard')

@section('title', 'Pembayaran')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/ringkasantagihan.css') }}">
@endpush

@section('content')

<section class="content-body">

    {{-- HEADER --}}
    <div class="header-card">
        <h1>Ringkasan Pembayaran</h1>
        <p>Silahkan upload file/foto transaksi bayar Anda pada tempat file yang telah disediakan</p>
    </div>

    @if (session('success'))
        <div class="payment-feedback payment-feedback-success" role="status">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="payment-feedback payment-feedback-error" role="alert">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="payment-feedback payment-feedback-error" role="alert">{{ $errors->first() }}</div>
    @endif
    {{-- FORM UTAMA PEMBAYARAN --}}
    <form action="{{ route('pembayaran.store') }}"
          method="POST"
          enctype="multipart/form-data"
          class="payment-grid-form">

        @csrf
        @foreach ($items as $item)
            <input type="hidden" name="booking_ids[]" value="{{ $item['id_booking'] }}">
        @endforeach

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
                                    <td colspan="2">Belum ada pemesanan yang siap dibayar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- UPLOAD BUKTI PEMBAYARAN --}}
                @if (count($items) > 0)
                <div class="upload-section">

                    <h3 class="section-title">Bukti Pembayaran</h3>

                    <div class="upload-row">

                        {{-- TOMBOL QRIS --}}
                        <div class="qr-trigger"
                             onclick="openQrModal()"
                             style="cursor: pointer;">

                            <svg viewBox="0 0 24 24"
                                 width="32"
                                 height="32"
                                 stroke="currentColor"
                                 stroke-width="2"
                                 fill="none">

                                <rect x="3" y="3" width="7" height="7"></rect>
                                <rect x="14" y="3" width="7" height="7"></rect>
                                <rect x="14" y="14" width="7" height="7"></rect>
                                <rect x="3" y="14" width="7" height="7"></rect>

                            </svg>

                            <span style="font-size: 13px; font-weight: 600; color: #f78333;">
                                Klik (QRIS)
                            </span>
                        </div>

                        {{-- BOX UPLOAD FILE --}}
                        <div class="dropzone-box" id="dropzoneBox">

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
                                Tarik & Lepas bukti bayar di sini, atau
                            </p>

                            <label for="file_upload" class="btn-select-file">
                                Pilih File
                            </label>

                            <input type="file"
                                   id="file_upload"
                                   name="bukti_bayar"
                                   accept=".jpg,.jpeg,.png,.pdf"
                                   style="display:none;"
                                   onchange="handleFileSelect(this)"
                                   required>

                            <span class="file-hint">
                                Format JPG, PNG, atau PDF (Maks. 5MB; foto besar dikompres otomatis)
                            </span>

                        </div>

                    </div>
                </div>
                @else
                    <div class="payment-empty-state">
                        <strong>Tidak ada tagihan yang siap dibayar.</strong>
                        <span>Booking dengan bukti pembayaran akan muncul di bagian status verifikasi.</span>
                    </div>
                @endif

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

                    {{-- TOMBOL BAYAR --}}
                    <div class="pay-action-wrapper">
                        @if (count($items) > 0)
                        <button type="submit"
                                id="btnPay"
                                class="btn-pay disabled"
                                disabled>
                            Bayar
                        </button>
                        @else
                            <button type="button" class="btn-pay disabled" disabled>Tidak ada tagihan</button>
                        @endif
                    </div>

                </div>

            </div>

        </div>

    </form>

</section>


{{-- MODAL POPUP QRIS --}}
<div id="qrModal" class="modal-overlay" style="display:none;">

    <div class="modal-content">

        <h3>Scan QRIS Pembayaran</h3>

        <p>
            Silahkan scan QR di bawah ini menggunakan m-Banking atau E-Wallet Anda.
        </p>

        <div style="margin: 20px 0;">

            <img src="{{ asset('img/QR.jpeg') }}"
                 alt="QRIS GOR Kamandanoe"
                 style="width: 220px; height: 220px; border: 2px solid #ddd; border-radius: 10px; object-fit: contain;">

        </div>

        <button type="button"
                onclick="closeQrModal()"
                class="btn-select-file"
                style="background:#333; color:#fff;">

            Tutup

        </button>

    </div>

</div>


{{-- CSS KHUSUS PEMBAYARAN --}}
<style>

    .payment-grid-form {
        width: 100%;
    }

    .btn-pay.disabled {
        background-color: #cccccc !important;
        color: #666666 !important;
        cursor: not-allowed !important;
        opacity: 0.7;
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
    }

    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
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


{{-- JAVASCRIPT PEMBAYARAN --}}
<script>

    function openQrModal() {
        document.getElementById('qrModal').style.display = 'flex';
    }

    function closeQrModal() {
        document.getElementById('qrModal').style.display = 'none';
    }

    const MAX_IMAGE_UPLOAD_BYTES = 1700 * 1024;

    function handleFileSelect(input) {
        const btnPay = document.getElementById('btnPay');
        const fileLabelText = document.getElementById('fileLabelText');

        if (!input.files || !input.files[0]) {
            fileLabelText.textContent = 'Tarik & Lepas bukti bayar di sini, atau';
            fileLabelText.style.color = '#666';
            btnPay.classList.add('disabled');
            btnPay.setAttribute('disabled', 'true');
            return;
        }

        const file = input.files[0];
        const isImage = file.type.startsWith('image/') || /\.(png|jpe?g)$/i.test(file.name);
        btnPay.classList.add('disabled');
        btnPay.setAttribute('disabled', 'true');

        if (isImage && file.size > MAX_IMAGE_UPLOAD_BYTES) {
            fileLabelText.textContent = 'Mengoptimalkan foto...';
            compressPaymentImage(file).then(function (compressedFile) {
                const transfer = new DataTransfer();
                transfer.items.add(compressedFile);
                input.files = transfer.files;
                showSelectedFile(compressedFile);
            }).catch(function () {
                fileLabelText.textContent = 'Foto gagal dikompres. Coba pilih foto yang lebih kecil.';
                fileLabelText.style.color = '#b42318';
            });
            return;
        }

        showSelectedFile(file);
    }

    function showSelectedFile(file) {
        const btnPay = document.getElementById('btnPay');
        const fileLabelText = document.getElementById('fileLabelText');
        const sizeMb = (file.size / (1024 * 1024)).toFixed(2);

        fileLabelText.textContent = 'File siap dikirim: ' + file.name + ' (' + sizeMb + ' MB)';
        fileLabelText.style.color = '#0a8a18';
        fileLabelText.style.fontWeight = '600';
        btnPay.classList.remove('disabled');
        btnPay.removeAttribute('disabled');
    }

    function compressPaymentImage(file) {
        return new Promise(function (resolve, reject) {
            const image = new Image();
            const objectUrl = URL.createObjectURL(file);

            image.onload = function () {
                URL.revokeObjectURL(objectUrl);
                if (!image.width || !image.height) {
                    reject(new Error('Foto tidak memiliki dimensi yang valid.'));
                    return;
                }

                const maxDimension = 2000;
                const scale = Math.min(1, maxDimension / Math.max(image.width, image.height));
                const canvas = document.createElement('canvas');
                canvas.width = Math.round(image.width * scale);
                canvas.height = Math.round(image.height * scale);
                const context = canvas.getContext('2d');
                if (!context) {
                    reject(new Error('Browser tidak dapat memproses foto.'));
                    return;
                }
                context.drawImage(image, 0, 0, canvas.width, canvas.height);

                let quality = 0.88;
                const encode = function () {
                    canvas.toBlob(function (blob) {
                        if (!blob) {
                            reject(new Error('Foto tidak dapat dikompres.'));
                            return;
                        }
                        if (blob.size > MAX_IMAGE_UPLOAD_BYTES && quality > 0.64) {
                            quality -= 0.08;
                            encode();
                            return;
                        }
                        if (blob.size > MAX_IMAGE_UPLOAD_BYTES) {
                            reject(new Error('Foto masih terlalu besar setelah dikompres.'));
                            return;
                        }

                        const baseName = file.name.replace(/\.[^.]+$/, '');
                        resolve(new File([blob], baseName + '.jpg', {
                            type: 'image/jpeg',
                            lastModified: Date.now(),
                        }));
                    }, 'image/jpeg', quality);
                };

                encode();
            };

            image.onerror = function () {
                URL.revokeObjectURL(objectUrl);
                reject(new Error('Foto tidak dapat dibaca.'));
            };

            image.src = objectUrl;
        });
    }

</script>

@endsection
