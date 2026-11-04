@extends('layouts.admin')
@section('title', 'Manajemen Pemesanan')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-pemesanan.css') }}">
@endpush

@section('content')
    <div class="top">
        <button class="burger" id="burger" aria-label="Menu"><svg class="ic"><use href="#i-menu"/></svg></button>
        <div class="judul">
            <h1>Manajemen Pemesanan</h1>
            <p class="muted">Pantau jadwal, konfirmasi booking, dan catat pemesanan langsung.</p>
        </div>
    </div>

    <section class="stats tiga booking-stats" aria-label="Ringkasan pemesanan">
        <div class="box stat"><div class="h">Semua Pemesanan <i class="dot k1"></i></div><strong>{{ number_format($statistik['total'], 0, ',', '.') }}</strong><small>Seluruh riwayat booking</small></div>
        <div class="box stat"><div class="h">Menunggu Konfirmasi <i class="dot k2"></i></div><strong>{{ number_format($statistik['menunggu'], 0, ',', '.') }}</strong><small>Perlu ditindaklanjuti</small></div>
        <div class="box stat"><div class="h">Jadwal Hari Ini <i class="dot k3"></i></div><strong>{{ number_format($statistik['hari_ini'], 0, ',', '.') }}</strong><small>Booking pada {{ today()->format('d M Y') }}</small></div>
        <div class="box stat"><div class="h">Pendapatan Bulan Ini <i class="dot k1"></i></div><strong class="money">Rp {{ number_format($statistik['pendapatan'], 0, ',', '.') }}</strong><small>Tidak termasuk booking batal</small></div>
    </section>

    <section class="box booking-list">
        <div class="box-h">
            <div>
                <h2>Semua Pemesanan</h2>
                <small class="muted">Cari dan saring booking berdasarkan pemesan, status, tanggal, atau lapangan.</small>
            </div>
        </div>
        <form method="GET" action="{{ route('admin.pemesanan.index') }}" class="booking-filters">
            <label class="filter-search">
                <span>Cari pemesan / kode booking</span>
                <input type="search" name="q" value="{{ $filter['q'] ?? '' }}" placeholder="Nama, nomor HP, atau kode...">
            </label>
            <label>
                <span>Status</span>
                <select name="status">
                    <option value="">Semua status</option>
                    @foreach ($statusList as $status)
                        <option value="{{ $status }}" @selected(($filter['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span>Tanggal main</span>
                <input type="date" name="tanggal" value="{{ $filter['tanggal'] ?? '' }}">
            </label>
            <label>
                <span>Lapangan</span>
                <select name="id_lapangan">
                    <option value="">Semua lapangan</option>
                    @foreach ($lapangans as $lapangan)
                        <option value="{{ $lapangan->id_lapangan }}" @selected(($filter['id_lapangan'] ?? '') == $lapangan->id_lapangan)>{{ $lapangan->nama_lapangan }}</option>
                    @endforeach
                </select>
            </label>
            <div class="filter-actions">
                <button class="btn kuning" type="submit">Terapkan</button>
                <a class="btn" href="{{ route('admin.pemesanan.index') }}">Reset</a>
            </div>
        </form>

        <div class="scroll">
            <table class="tbl booking-table">
                <thead>
                    <tr>
                        <th>KODE</th>
                        <th>PEMESAN</th>
                        <th>LAPANGAN</th>
                        <th>JADWAL</th>
                        <th class="kanan">TOTAL</th>
                        <th>STATUS</th>
                        <th class="kanan">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bookings as $booking)
                        @php
                            $status = strtolower((string) $booking->status_booking);
                            $kelas = match ($status) {
                                'menunggu' => 'wait',
                                'dikonfirmasi', 'lunas', 'selesai' => 'ok',
                                'batal', 'dibatalkan', 'cancelled' => 'busy',
                                default => '',
                            };
                        @endphp
                        <tr>
                            <td><b>{{ $booking->id_booking }}</b></td>
                            <td>
                                <b>{{ $booking->nama_pemesan ?: ($booking->user?->username ?? 'User #'.$booking->id_user) }}</b>
                                @if ($booking->no_hp_pemesan)<small>{{ $booking->no_hp_pemesan }}</small>@endif
                            </td>
                            <td>{{ $booking->lapangan?->nama_lapangan ?? $booking->id_lapangan }}</td>
                            <td>
                                {{ \Carbon\Carbon::parse($booking->tgl_main)->format('d M Y') }}
                                <small>{{ substr((string) $booking->jam_mulai, 0, 5) }} · {{ max(1, (int) $booking->durasi) }} jam</small>
                            </td>
                            <td class="kanan">Rp {{ number_format((float) $booking->total_bayar, 0, ',', '.') }}</td>
                            <td><span class="pill {{ $kelas }}">{{ ucfirst($booking->status_booking) }}</span></td>
                            <td class="booking-actions">
                                @if ($status === 'menunggu')
                                    <form method="POST" action="{{ route('admin.pemesanan.status', $booking) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status_booking" value="dikonfirmasi">
                                        <button class="btn confirm-btn" type="submit">Konfirmasi</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.pemesanan.status', $booking) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status_booking" value="batal">
                                        <button class="btn bahaya" type="submit">Batalkan</button>
                                    </form>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td class="empty-bookings" colspan="7">Belum ada pemesanan yang sesuai filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($bookings->hasPages())
            <div class="booking-pagination">{{ $bookings->links() }}</div>
        @endif
    </section>

    <section class="box offline-box">
        <div class="box-h">
            <div>
                <h2>Input Pemesanan Offline</h2>
                <small class="muted">Catat booking langsung dari loket. Jadwal yang bentrok akan ditolak.</small>
            </div>
            <span class="offline-tag">BOOKING LOKET</span>
        </div>
        <form method="POST" action="{{ route('admin.pemesanan.store') }}" class="offline-form">
            @csrf
            <label>
                <span>Nama pemesan</span>
                <input type="text" name="nama_pemesan" value="{{ old('nama_pemesan') }}" maxlength="100" placeholder="Nama lengkap" required>
                @error('nama_pemesan')<small class="field-error">{{ $message }}</small>@enderror
            </label>
            <label>
                <span>Nomor HP</span>
                <input type="tel" name="no_hp_pemesan" value="{{ old('no_hp_pemesan') }}" maxlength="20" placeholder="08xxxxxxxxxx" required>
                @error('no_hp_pemesan')<small class="field-error">{{ $message }}</small>@enderror
            </label>
            <label>
                <span>Lapangan</span>
                <select name="id_lapangan" required>
                    <option value="">Pilih lapangan</option>
                    @foreach ($lapangans as $lapangan)
                        @if (strtolower((string) $lapangan->status_lapangan) !== 'nonaktif')
                            <option value="{{ $lapangan->id_lapangan }}" @selected(old('id_lapangan') == $lapangan->id_lapangan)>{{ $lapangan->nama_lapangan }}</option>
                        @endif
                    @endforeach
                </select>
                @error('id_lapangan')<small class="field-error">{{ $message }}</small>@enderror
            </label>
            <label>
                <span>Tanggal main</span>
                <input type="date" name="tgl_main" value="{{ old('tgl_main', today()->toDateString()) }}" required>
                @error('tgl_main')<small class="field-error">{{ $message }}</small>@enderror
            </label>
            <label>
                <span>Jam mulai</span>
                <input type="time" name="jam_mulai" value="{{ old('jam_mulai') }}" required>
                @error('jam_mulai')<small class="field-error">{{ $message }}</small>@enderror
            </label>
            <label>
                <span>Durasi (jam)</span>
                <input type="number" name="durasi" min="1" max="16" value="{{ old('durasi', 1) }}" required>
                @error('durasi')<small class="field-error">{{ $message }}</small>@enderror
            </label>
            <p class="offline-note">Total biaya dihitung otomatis dari harga per jam lapangan. Booking loket langsung berstatus dikonfirmasi.</p>
            <button type="submit" class="btn kuning offline-submit">Simpan Pemesanan</button>
        </form>
    </section>
@endsection
