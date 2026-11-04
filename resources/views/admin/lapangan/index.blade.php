@extends('layouts.admin')
@section('title', 'Manajemen Lapangan')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-lapangan.css') }}">
@endpush

@section('content')
    <div class="top">
        <button class="burger" id="burger" aria-label="Menu"><svg class="ic"><use href="#i-menu"/></svg></button>
        <div class="judul">
            <h1>Manajemen Lapangan</h1>
            <p class="muted">Atur lapangan dan pantau jadwal pemesanan per jam.</p>
        </div>
        <button type="button" class="btn kuning" data-modal="m-tambah">+ Tambah Lapangan</button>
    </div>

    <section class="court-stats" aria-label="Ringkasan lapangan">
        <div class="box court-stat"><span>Total Lapangan</span><strong>{{ $statistik['total'] }}</strong></div>
        <div class="box court-stat"><span>Lapangan Aktif</span><strong>{{ $statistik['aktif'] }}</strong></div>
        <div class="box court-stat"><span>Slot Terpesan · {{ \Carbon\Carbon::parse($tanggal)->format('d M Y') }}</span><strong>{{ $statistik['terpesan'] }}</strong></div>
    </section>

    <section class="box schedule-box">
        <div class="box-h">
            <div>
                <h2>Jadwal Lapangan</h2>
                <small class="muted">Pilih slot untuk melihat nama pemesan.</small>
            </div>
            <form method="GET" action="{{ route('admin.lapangan.index') }}" class="date-filter">
                <label for="tanggal-jadwal">Tanggal</label>
                <input id="tanggal-jadwal" type="date" name="tanggal" value="{{ $tanggal }}">
                <button class="btn" type="submit">Lihat</button>
            </form>
        </div>

        <div class="schedule-legend">
            <span><i class="legend-dot available"></i>Tersedia</span>
            <span><i class="legend-dot booked"></i>Dipesan</span>
        </div>

        <div class="schedule-scroll">
            <table class="schedule-table">
                <thead>
                    <tr>
                        <th class="court-heading">Lapangan</th>
                        @foreach ($jam as $hour)
                            <th>{{ sprintf('%02d', $hour) }}-{{ sprintf('%02d', $hour + 1) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lapangans as $lapangan)
                        <tr>
                            <th class="court-name">
                                <b>{{ $lapangan->nama_lapangan }}</b>
                                <small>{{ $lapangan->tipe_lantai }}</small>
                            </th>
                            @foreach ($jam as $hour)
                                @php
                                    $pemesan = array_values(array_unique($jadwal[$lapangan->id_lapangan][$hour] ?? []));
                                    $dipesan = count($pemesan) > 0;
                                    $pesan = $dipesan
                                        ? 'Lapangan ini sudah dipesan oleh ' . implode(', ', $pemesan)
                                        : 'Lapangan ini belum dipesan.';
                                @endphp
                                <td>
                                    <button
                                        type="button"
                                        class="slot {{ $dipesan ? 'slot-booked' : 'slot-available' }}"
                                        data-modal="m-jadwal"
                                        data-status="{{ $dipesan ? 'booked' : 'available' }}"
                                        data-pesan="{{ $dipesan ? 'Lapangan ini sudah dipesan oleh' : $pesan }}"
                                        data-pemesan="{{ implode(', ', $pemesan) }}"
                                        data-lapangan="{{ $lapangan->nama_lapangan }}"
                                        data-jam="{{ sprintf('%02d:00 - %02d:00', $hour, $hour + 1) }}"
                                        aria-label="{{ $lapangan->nama_lapangan }}, {{ sprintf('%02d:00', $hour) }}: {{ $pesan }}"
                                    >{{ $dipesan ? 'Dipesan' : 'Tersedia' }}</button>
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td class="empty-schedule" colspan="{{ count($jam) + 1 }}">Belum ada data lapangan. Tambahkan lapangan untuk mulai mengatur jadwal.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="box courts-box">
        <div class="box-h">
            <div>
                <h2>Status &amp; Konfigurasi Lapangan</h2>
                <small class="muted">Foto lapangan menggunakan gambar yang tersedia di folder public/img.</small>
            </div>
        </div>

        <div class="court-grid">
            @forelse ($lapangans as $lapangan)
                @php($aktif = strtolower((string) $lapangan->status_lapangan) !== 'nonaktif')
                @php($jumlahSlot = count($jadwal[$lapangan->id_lapangan] ?? []))
                <article class="court-card">
                    <img src="{{ asset('img/images.jpg') }}" alt="Foto {{ $lapangan->nama_lapangan }}" loading="lazy">
                    <div class="court-card-body">
                        <div class="court-card-title">
                            <h3>{{ $lapangan->nama_lapangan }}</h3>
                            <span class="court-status {{ $aktif ? 'is-active' : 'is-inactive' }}">{{ $aktif ? 'Aktif' : 'Nonaktif' }}</span>
                        </div>
                        <dl class="court-details">
                            <div><dt>Tipe lantai</dt><dd>{{ $lapangan->tipe_lantai ?: '-' }}</dd></div>
                            <div><dt>Kapasitas</dt><dd>{{ $lapangan->kapasitas }} orang</dd></div>
                            <div><dt>Harga/Jam</dt><dd>Rp {{ number_format((float) $lapangan->harga_per_jam, 0, ',', '.') }}</dd></div>
                            <div><dt>Slot dipesan</dt><dd>{{ $jumlahSlot }} slot · {{ \Carbon\Carbon::parse($tanggal)->format('d M') }}</dd></div>
                        </dl>
                        <div class="court-actions">
                            <button
                                type="button"
                                class="btn"
                                data-modal="m-ubah"
                                data-action="{{ route('admin.lapangan.update', $lapangan) }}"
                                data-id="{{ $lapangan->getKey() }}"
                                data-nama="{{ $lapangan->nama_lapangan }}"
                                data-tipe="{{ $lapangan->tipe_lantai }}"
                                data-harga="{{ $lapangan->harga_per_jam }}"
                                data-kapasitas="{{ $lapangan->kapasitas }}"
                                data-status="{{ $aktif ? 'Aktif' : 'Nonaktif' }}"
                            >Edit</button>
                            <button
                                type="button"
                                class="btn bahaya"
                                data-modal="m-hapus"
                                data-action="{{ route('admin.lapangan.destroy', $lapangan) }}"
                                data-nama="{{ $lapangan->nama_lapangan }}"
                            >Hapus</button>
                        </div>
                    </div>
                </article>
            @empty
                <p class="empty-courts">Belum ada lapangan untuk ditampilkan.</p>
            @endforelse
        </div>
    </section>

    <div @class(['modal', 'open' => $errors->tambah->any()]) id="m-tambah">
        <div class="modal-box">
            <h3>Tambah Lapangan</h3>
            <form method="POST" action="{{ route('admin.lapangan.store') }}">
                @csrf
                <label class="fld">ID Lapangan
                    <input type="text" name="id_lapangan" maxlength="11" value="{{ $errors->tambah->any() ? old('id_lapangan') : '' }}" required>
                    @error('id_lapangan', 'tambah') <span class="err">{{ $message }}</span> @enderror
                </label>
                <label class="fld">Nama Lapangan
                    <input type="text" name="nama_lapangan" maxlength="20" value="{{ $errors->tambah->any() ? old('nama_lapangan') : '' }}" required>
                    @error('nama_lapangan', 'tambah') <span class="err">{{ $message }}</span> @enderror
                </label>
                <label class="fld">Tipe Lantai
                    <input type="text" name="tipe_lantai" maxlength="100" value="{{ $errors->tambah->any() ? old('tipe_lantai') : '' }}" required>
                    @error('tipe_lantai', 'tambah') <span class="err">{{ $message }}</span> @enderror
                </label>
                <label class="fld">Harga per Jam (Rp)
                    <input type="number" name="harga_per_jam" min="0" step="1000" value="{{ $errors->tambah->any() ? old('harga_per_jam') : '' }}" required>
                    @error('harga_per_jam', 'tambah') <span class="err">{{ $message }}</span> @enderror
                </label>
                <label class="fld">Kapasitas (orang)
                    <input type="number" name="kapasitas" min="1" max="32" value="{{ $errors->tambah->any() ? old('kapasitas', 4) : 4 }}" required>
                    @error('kapasitas', 'tambah') <span class="err">{{ $message }}</span> @enderror
                </label>
                <label class="fld">Status
                    <select name="status_lapangan" required>
                        <option value="Aktif" @selected(! $errors->tambah->any() || old('status_lapangan') === 'Aktif')>Aktif</option>
                        <option value="Nonaktif" @selected($errors->tambah->any() && old('status_lapangan') === 'Nonaktif')>Nonaktif</option>
                    </select>
                    @error('status_lapangan', 'tambah') <span class="err">{{ $message }}</span> @enderror
                </label>
                <div class="modal-aksi">
                    <button type="button" class="btn" data-tutup>Batal</button>
                    <button type="submit" class="btn oren">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div @class(['modal', 'open' => $errors->ubah->any()]) id="m-ubah">
        <div class="modal-box">
            <h3>Edit Lapangan</h3>
            <form method="POST" action="{{ $errors->ubah->any() ? route('admin.lapangan.update', old('_lapangan')) : '' }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="_lapangan" data-isi="id" value="{{ $errors->ubah->any() ? old('_lapangan') : '' }}">
                <label class="fld">Nama Lapangan
                    <input type="text" name="nama_lapangan" maxlength="20" data-isi="nama" value="{{ $errors->ubah->any() ? old('nama_lapangan') : '' }}" required>
                    @error('nama_lapangan', 'ubah') <span class="err">{{ $message }}</span> @enderror
                </label>
                <label class="fld">Tipe Lantai
                    <input type="text" name="tipe_lantai" maxlength="100" data-isi="tipe" value="{{ $errors->ubah->any() ? old('tipe_lantai') : '' }}" required>
                    @error('tipe_lantai', 'ubah') <span class="err">{{ $message }}</span> @enderror
                </label>
                <label class="fld">Harga per Jam (Rp)
                    <input type="number" name="harga_per_jam" min="0" step="1000" data-isi="harga" value="{{ $errors->ubah->any() ? old('harga_per_jam') : '' }}" required>
                    @error('harga_per_jam', 'ubah') <span class="err">{{ $message }}</span> @enderror
                </label>
                <label class="fld">Kapasitas (orang)
                    <input type="number" name="kapasitas" min="1" max="32" data-isi="kapasitas" value="{{ $errors->ubah->any() ? old('kapasitas') : '' }}" required>
                    @error('kapasitas', 'ubah') <span class="err">{{ $message }}</span> @enderror
                </label>
                <label class="fld">Status
                    <select name="status_lapangan" data-isi="status" required>
                        <option value="Aktif">Aktif</option>
                        <option value="Nonaktif">Nonaktif</option>
                    </select>
                    @error('status_lapangan', 'ubah') <span class="err">{{ $message }}</span> @enderror
                </label>
                <div class="modal-aksi">
                    <button type="button" class="btn" data-tutup>Batal</button>
                    <button type="submit" class="btn oren">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal hapus" id="m-hapus">
        <div class="modal-box">
            <span class="warn"><svg class="ic"><use href="#i-warn"/></svg></span>
            <h3>Hapus Lapangan?</h3>
            <p class="muted">Lapangan yang memiliki riwayat pemesanan tidak dapat dihapus.</p>
            <div class="target" data-isi="nama"></div>
            <form method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="modal-aksi">
                    <button type="button" class="btn" data-tutup>Batal</button>
                    <button type="submit" class="btn merah">Hapus</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal schedule-modal" id="m-jadwal">
        <div class="modal-box">
            <span class="schedule-alert-icon"><svg class="ic"><use href="#i-warn"/></svg></span>
            <h3 data-isi="lapangan"></h3>
            <p class="muted" data-isi="jam"></p>
            <p class="schedule-message">
                <span data-isi="pesan"></span>
                <strong class="schedule-username" data-isi="pemesan"></strong>
            </p>
            <div class="modal-aksi">
                <button type="button" class="btn oren" data-tutup>Tutup</button>
            </div>
        </div>
    </div>
@endsection
