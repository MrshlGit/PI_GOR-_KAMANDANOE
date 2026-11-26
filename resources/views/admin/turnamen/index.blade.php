@extends('layouts.admin')
@section('title', 'Daftar Event')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-turnamen.css') }}">
@endpush

@section('content')
    <div class="top">
        <button class="burger" id="burger" aria-label="Menu"><svg class="ic"><use href="#i-menu"/></svg></button>
        <div class="judul">
            <h1>Daftar Event</h1>
            <p class="muted">Lihat dan kelola turnamen GOR Kamandanoe.</p>
        </div>
        <button type="button" class="admin-tn-create-button" data-open="admin-tn-create">+ Buat Event</button>
    </div>

    <section class="box admin-tn">
        @if (session('sukses'))
            <p class="admin-tn-alert success">{{ session('sukses') }}</p>
        @endif
        @if (session('galat'))
            <p class="admin-tn-alert error">{{ session('galat') }}</p>
        @endif

        <nav class="admin-tn-tabs" aria-label="Filter daftar event">
            @foreach (['akan_datang' => 'Akan Datang', 'berakhir' => 'Berakhir', 'arsip' => 'Arsip'] as $key => $label)
                <a href="{{ route('admin.turnamen.index', ['tab' => $key, 'q' => $search]) }}" @class(['on' => $tab === $key])>
                    {{ $label }} <b>{{ $jumlah[$key] }}</b>
                </a>
            @endforeach
        </nav>

        <form method="GET" action="{{ route('admin.turnamen.index') }}" class="admin-tn-search">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input type="search" name="q" value="{{ $search }}" placeholder="Cari nama event atau lokasi...">
            <button type="submit">Cari Event</button>
        </form>

        <div class="admin-tn-list">
            @forelse ($turnamen as $t)
                @php
                    $stateLabel = [
                        'menunggu' => 'Menunggu Persetujuan',
                        'disetujui' => 'Publish',
                        'ditolak' => 'Ditolak',
                    ][$t->status] ?? ucfirst($t->status);
                    $stateClass = $t->status;
                    if ($t->status === 'disetujui' && $tab === 'berakhir') {
                        $stateLabel = 'Berakhir';
                        $stateClass = 'berakhir';
                    } elseif ($t->status === 'disetujui' && $tab === 'arsip') {
                        $stateLabel = 'Arsip';
                        $stateClass = 'arsip';
                    }
                @endphp
                <article class="admin-tn-card">
                    @if ($t->poster)
                        <a class="admin-tn-poster" href="{{ asset($t->poster) }}" target="_blank" rel="noopener">
                            <img src="{{ asset($t->poster) }}" alt="Poster {{ $t->nama_turnamen }}" loading="lazy">
                        </a>
                    @else
                        <div class="admin-tn-poster-placeholder" aria-label="Poster belum tersedia">
                            <span>GOR KAMANDANOE</span>
                            <strong>EVENT</strong>
                        </div>
                    @endif
                    <div class="admin-tn-info">
                        <span class="admin-tn-state {{ $stateClass }}">{{ $stateLabel }}</span>
                        <h2>{{ $t->nama_turnamen }}</h2>
                        <p>{{ $t->lokasi ?: 'GOR Kamandanoe' }}</p>
                        <p>
                            {{ $t->tgl_pelaksanaan?->locale('id')->translatedFormat('j F Y') ?? '-' }}
                            @if ($t->jam_mulai)
                                &middot; {{ substr($t->jam_mulai, 0, 5) }} WIB
                                @if ($t->jam_selesai) - {{ substr($t->jam_selesai, 0, 5) }} WIB @endif
                            @endif
                        </p>
                    </div>
                    <details class="admin-tn-manage">
                        <summary>Kelola Event</summary>
                        <div class="admin-tn-manage-panel">
                            <dl>
                                <div><dt>Kategori</dt><dd>{{ $t->kategori }}</dd></div>
                                <div><dt>Pendaftaran</dt><dd>{{ $t->biaya_pendaftaran > 0 ? 'Rp '.number_format($t->biaya_pendaftaran, 0, ',', '.') : 'Gratis' }}</dd></div>
                                <div><dt>Peserta</dt><dd>{{ $t->peserta_count }} / {{ $t->kuota_tim }} tim</dd></div>
                                <div><dt>Penyelenggara</dt><dd>{{ $t->penyelenggara->username ?? 'Admin' }}</dd></div>
                                <div><dt>Kontak</dt><dd>{{ $t->kontak ?: '-' }}</dd></div>
                                <div><dt>Hadiah</dt><dd>{{ $t->hadiah ?: '-' }}</dd></div>
                            </dl>
                            @if ($t->deskripsi)
                                <p class="admin-tn-description">{{ $t->deskripsi }}</p>
                            @endif
                            @if ($t->status === 'menunggu')
                                <div class="admin-tn-actions">
                                    <form method="POST" action="{{ route('admin.turnamen.status', $t->id_turnamen) }}">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="disetujui">
                                        <button class="admin-tn-approve" type="submit">Setujui &amp; Publikasikan</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.turnamen.status', $t->id_turnamen) }}" onsubmit="return confirm('Tolak pengajuan turnamen ini?')">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="ditolak">
                                        <button class="admin-tn-reject" type="submit">Tolak Pengajuan</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </details>
                </article>
            @empty
                <p class="admin-tn-empty">
                    @if ($tab === 'berakhir')
                        Belum ada event yang berakhir dalam 30 hari terakhir.
                    @elseif ($tab === 'arsip')
                        Belum ada event yang masuk arsip.
                    @else
                        Belum ada event yang cocok dengan pencarian.
                    @endif
                </p>
            @endforelse
        </div>

        {{ $turnamen->links() }}
    </section>

    <dialog id="admin-tn-create" class="admin-tn-dialog">
        <form method="POST" action="{{ route('admin.turnamen.store') }}" enctype="multipart/form-data">
            @csrf
            <header>
                <div>
                    <h2>Buat Event</h2>
                    <p>Event yang dibuat admin akan langsung dipublikasikan.</p>
                </div>
                <button type="button" class="admin-tn-dialog-close" data-close aria-label="Tutup">×</button>
            </header>

            <label>Nama event
                <input type="text" name="nama_turnamen" maxlength="100" value="{{ old('nama_turnamen') }}" required>
                @error('nama_turnamen', 'buatEvent') <small>{{ $message }}</small> @enderror
            </label>

            <div class="admin-tn-form-row">
                <label>Kategori
                    <select name="kategori" required>
                        <option value="">Pilih kategori</option>
                        @foreach (\App\Models\Admin\Turnamen::KATEGORI as $kategori)
                            <option value="{{ $kategori }}" @selected(old('kategori') === $kategori)>{{ $kategori }}</option>
                        @endforeach
                    </select>
                    @error('kategori', 'buatEvent') <small>{{ $message }}</small> @enderror
                </label>
                <label>Lokasi
                    <input type="text" name="lokasi" maxlength="150" value="{{ old('lokasi', 'GOR Kamandanoe') }}">
                    @error('lokasi', 'buatEvent') <small>{{ $message }}</small> @enderror
                </label>
            </div>

            <div class="admin-tn-form-row">
                <label>Tanggal pelaksanaan
                    <input id="admin-tn-date" type="date" name="tgl_pelaksanaan" min="{{ today()->toDateString() }}" value="{{ old('tgl_pelaksanaan') }}" required>
                    @error('tgl_pelaksanaan', 'buatEvent') <small>{{ $message }}</small> @enderror
                </label>
                <label>Batas pendaftaran
                    <input id="admin-tn-deadline" type="date" name="batas_pendaftaran" min="{{ today()->toDateString() }}" value="{{ old('batas_pendaftaran') }}">
                    @error('batas_pendaftaran', 'buatEvent') <small>{{ $message }}</small> @enderror
                </label>
            </div>

            <div class="admin-tn-form-row">
                <label>Jam mulai
                    <input type="time" name="jam_mulai" value="{{ old('jam_mulai') }}">
                    @error('jam_mulai', 'buatEvent') <small>{{ $message }}</small> @enderror
                </label>
                <label>Jam selesai
                    <input type="time" name="jam_selesai" value="{{ old('jam_selesai') }}">
                    @error('jam_selesai', 'buatEvent') <small>{{ $message }}</small> @enderror
                </label>
            </div>

            <div class="admin-tn-form-row">
                <label>Biaya pendaftaran (Rp)
                    <input type="number" name="biaya_pendaftaran" min="0" step="1000" value="{{ old('biaya_pendaftaran', 0) }}" required>
                    @error('biaya_pendaftaran', 'buatEvent') <small>{{ $message }}</small> @enderror
                </label>
                <label>Kuota tim
                    <input type="number" name="kuota_tim" min="2" max="128" value="{{ old('kuota_tim', 16) }}" required>
                    @error('kuota_tim', 'buatEvent') <small>{{ $message }}</small> @enderror
                </label>
            </div>

            <div class="admin-tn-form-row">
                <label>Hadiah
                    <input type="text" name="hadiah" maxlength="150" value="{{ old('hadiah') }}">
                    @error('hadiah', 'buatEvent') <small>{{ $message }}</small> @enderror
                </label>
                <label>Kontak penyelenggara
                    <input type="text" name="kontak" maxlength="20" value="{{ old('kontak') }}" required>
                    @error('kontak', 'buatEvent') <small>{{ $message }}</small> @enderror
                </label>
            </div>

            <label>Deskripsi
                <textarea name="deskripsi" rows="3" maxlength="1000">{{ old('deskripsi') }}</textarea>
                @error('deskripsi', 'buatEvent') <small>{{ $message }}</small> @enderror
            </label>

            <label>Poster (opsional, maks. 5 MB)
                <input type="file" name="poster" accept="image/jpeg,image/png,image/webp">
                @error('poster', 'buatEvent') <small>{{ $message }}</small> @enderror
            </label>

            <footer>
                <button type="button" class="admin-tn-cancel" data-close>Batal</button>
                <button type="submit" class="admin-tn-submit">Buat &amp; Publikasikan</button>
            </footer>
        </form>
    </dialog>

    <script>
        document.querySelectorAll('[data-open]').forEach(function (button) {
            button.addEventListener('click', function () {
                document.getElementById(button.dataset.open).showModal();
            });
        });
        document.querySelectorAll('[data-close]').forEach(function (button) {
            button.addEventListener('click', function () {
                button.closest('dialog').close();
            });
        });
        document.querySelectorAll('dialog').forEach(function (dialog) {
            dialog.addEventListener('click', function (event) {
                if (event.target === dialog) dialog.close();
            });
        });
        var eventDate = document.getElementById('admin-tn-date');
        var eventDeadline = document.getElementById('admin-tn-deadline');
        if (eventDate && eventDeadline) {
            function syncEventDeadline() {
                eventDeadline.max = eventDate.value || '';
            }
            eventDate.addEventListener('change', syncEventDeadline);
            syncEventDeadline();
        }
        @if ($errors->getBag('buatEvent')->any())
            document.getElementById('admin-tn-create').showModal();
        @endif
    </script>
@endsection
