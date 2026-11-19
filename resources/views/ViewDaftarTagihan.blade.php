@extends('layouts.dashboard')

@section('title', 'Daftar Tagihan')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/daftartagihan.css') }}">
@endpush

@section('content')
    <link rel="stylesheet" href="{{ asset('css/daftartagihan.css') }}">

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
                        <input type="text" value="{{ $customer['nama'] ?? '-' }}" readonly class="input-white">
                    </div>
                    <div class="form-row">
                        <label>No Telepon</label>
                        <input type="text" value="{{ $customer['telepon'] ?? '-' }}" readonly class="input-gray">
                    </div>
                    <div class="form-row">
                        <label>Email</label>
                        <input type="text" value="{{ $customer['email'] ?? '-' }}" readonly class="input-gray">
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
                        @forelse($tagihans ?? [] as $item)
                            <tr>
                                <td>{{ $item['nama'] }}</td>
                                <td>{{ $item['jenis'] }}</td>
                                <td>{{ $item['jumlah'] }}</td>
                                <td>{{ $item['durasi'] }}</td>
                                <td>Rp {{ number_format($item['harga'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="padding: 20px; color: #666;">Belum ada tagihan atau pemesanan aktif.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(!empty($tagihans) && count($tagihans) > 0)
                        <tfoot>
                            <tr style="font-weight: bold; background: #f9f9f9;">
                                <td colspan="4" style="text-align: right; padding: 12px;">Total Kumulatif:</td>
                                <td style="padding: 12px;">Rp {{ number_format($totalKumulatif ?? 0, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        <div class="action-footer">
            <a href="{{ route('pembayaran.index') }}" class="btn btn-confirm" style="text-decoration: none;">
                Konfirmasi Pembayaran
            </a>
        </div>
    </section>
@endsection