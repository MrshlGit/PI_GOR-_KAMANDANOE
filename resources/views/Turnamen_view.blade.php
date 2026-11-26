@extends('layouts.dashboard')
@section('title', 'Info Turnamen')

@section('content')
@php
    $rupiah = fn ($n) => $n > 0 ? 'Rp ' . number_format($n, 0, ',', '.') : 'Gratis';
    $label = [
        'buka'     => ['Pendaftaran dibuka', 'ok'],
        'penuh'    => ['Kuota penuh', 'bad'],
        'ditutup'  => ['Pendaftaran ditutup', 'mut'],
        'selesai'  => ['Selesai', 'mut'],
        'menunggu' => ['Menunggu persetujuan', 'wait'],
        'ditolak'  => ['Ditolak', 'bad'],
    ];
    $tabs = ['semua' => 'Semua Turnamen', 'ikut' => 'Yang Saya Ikuti', 'saya' => 'Pengajuan Saya'];
    $kosong = [
        'semua' => 'Belum ada turnamen yang cocok. Coba ubah pencarian, atau ajukan turnamen baru.',
        'ikut'  => 'Kamu belum mendaftar di turnamen mana pun.',
        'saya'  => 'Kamu belum mengajukan turnamen.',
    ];
@endphp

<link rel="stylesheet" href="{{ asset('css/Turnamen.css') }}">

<div class="tn">

    {{-- ===== HEADER ===== --}}
    <header class="tn-head">
        <div>
            <h1>Info Turnamen</h1>
            <p>Lihat turnamen yang akan berlangsung, daftarkan timmu, atau ajukan turnamenmu sendiri di GOR Kamandanoe.</p>
        </div>
        <button type="button" class="tn-btn" data-open="dlg-ajukan">+ Ajukan Turnamen</button>
    </header>

    @if (session('tn_ok'))  <p class="tn-note ok">{{ session('tn_ok') }}</p>  @endif
    @if (session('tn_err')) <p class="tn-note bad">{{ session('tn_err') }}</p> @endif

    {{-- ===== TAB + PENCARIAN ===== --}}
    <nav class="tn-tabs">
        @foreach ($tabs as $k => $nama)
            <a href="{{ route('turnamen.index', ['tab' => $k]) }}" @class(['on' => $tab === $k])>
                {{ $nama }} <b>{{ $jumlah[$k] }}</b>
            </a>
        @endforeach
    </nav>

    <form method="GET" action="{{ route('turnamen.index') }}" class="tn-filter">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <input type="search" name="q" value="{{ $q }}" placeholder="Cari nama turnamen atau lokasi...">
        <select name="kategori" onchange="this.form.submit()">
            <option value="">Semua kategori</option>
            @foreach ($kategoriList as $k)
                <option value="{{ $k }}" @selected($kategori === $k)>{{ $k }}</option>
            @endforeach
        </select>
        <button type="submit" class="tn-btn ghost">Cari</button>
    </form>

    @if ($tab === 'saya' && $perluPersetujuan)
        <p class="tn-hint">Pengajuanmu akan tampil di daftar umum setelah disetujui admin.</p>
    @endif

    {{-- ===== DAFTAR TURNAMEN ===== --}}
    @forelse ($turnamen as $t)
        @php
            $fase = $t->fase();
            [$teksFase, $warnaFase] = $label[$fase];
            $milikku = $t->id_user == auth()->id();
            $terdaftar = in_array($t->id_turnamen, $idIkut, true);
            $jml = $t->jumlahPeserta();
            $persen = $t->kuota_tim > 0 ? min(100, round($jml / $t->kuota_tim * 100)) : 0;
            $sisa = $t->sisaHari();
            $hitung = $sisa === null ? '' : ($sisa > 1 ? "H-{$sisa}" : ($sisa === 1 ? 'Besok' : ($sisa === 0 ? 'Hari ini' : '')));
            $share = rawurlencode("Yuk ikut turnamen {$t->nama_turnamen} di GOR Kamandanoe, "
                . $t->tgl_pelaksanaan?->locale('id')->translatedFormat('j F Y') . '. Biaya: ' . $rupiah($t->biaya_pendaftaran));
        @endphp

        <article class="tn-card">
            @if ($t->poster)
                <img class="tn-poster" src="{{ asset($t->poster) }}" alt="Poster {{ $t->nama_turnamen }}" loading="lazy">
            @endif
            <div class="tn-top">
                <div class="tn-chips">
                    @if ($t->kategori) <span class="tn-chip">{{ $t->kategori }}</span> @endif
                    <span class="tn-chip {{ $warnaFase }}">{{ $teksFase }}</span>
                    @if ($milikku) <span class="tn-chip own">Pengajuanmu</span> @endif
                    @if ($terdaftar) <span class="tn-chip ok">Kamu terdaftar</span> @endif
                </div>
                @if ($hitung !== '' && $fase !== 'selesai')
                    <span class="tn-count">{{ $hitung }}</span>
                @endif
            </div>

            <h2>{{ $t->nama_turnamen }}</h2>

            <dl class="tn-meta">
                <div><dt>Tanggal</dt><dd>{{ $t->tgl_pelaksanaan?->locale('id')->translatedFormat('l, j F Y') ?? '-' }}</dd></div>
                <div><dt>Lokasi</dt><dd>{{ $t->lokasi ?: 'GOR Kamandanoe' }}</dd></div>
                <div><dt>Biaya</dt><dd>{{ $rupiah($t->biaya_pendaftaran) }}</dd></div>
                <div><dt>Hadiah</dt><dd>{{ $t->hadiah ?: '-' }}</dd></div>
                @if ($t->batas_pendaftaran)
                    <div><dt>Batas daftar</dt><dd>{{ $t->batas_pendaftaran->locale('id')->translatedFormat('j F Y') }}</dd></div>
                @endif
                <div><dt>Kontak</dt><dd>{{ $t->kontak ?: '-' }}</dd></div>
            </dl>

            <div class="tn-kuota">
                <div class="tn-bar"><i style="width: {{ $persen }}%"></i></div>
                <span>{{ $jml }} / {{ $t->kuota_tim }} tim terdaftar</span>
            </div>

            @if ($t->deskripsi)
                <details class="tn-desc">
                    <summary>Lihat deskripsi</summary>
                    <p>{{ $t->deskripsi }}</p>
                </details>
            @endif

            <footer class="tn-act">
                <small>Diajukan oleh {{ $t->penyelenggara->username ?? 'Admin' }}</small>
                <div class="tn-btns">
                    <a class="tn-btn ghost sm" target="_blank" rel="noopener" href="https://wa.me/?text={{ $share }}">Bagikan</a>

                    @if ($milikku)
                        @if ($t->status !== 'disetujui')
                            <form method="POST" action="{{ route('turnamen.destroy', $t->id_turnamen) }}" onsubmit="return confirm('Hapus pengajuan ini?')">
                                @csrf @method('DELETE')
                                <button class="tn-btn danger sm" type="submit">Hapus pengajuan</button>
                            </form>
                        @endif
                    @elseif ($terdaftar)
                        @if ($fase !== 'selesai')
                            <form method="POST" action="{{ route('turnamen.batal', $t->id_turnamen) }}" onsubmit="return confirm('Batalkan pendaftaran timmu?')">
                                @csrf @method('DELETE')
                                <button class="tn-btn danger sm" type="submit">Batalkan pendaftaran</button>
                            </form>
                        @endif
                    @elseif ($fase === 'buka')
                        <button type="button" class="tn-btn sm" data-open="dlg-daftar-{{ $t->id_turnamen }}">Daftar Tim</button>
                    @else
                        <button type="button" class="tn-btn sm" disabled>{{ $teksFase }}</button>
                    @endif
                </div>
            </footer>
        </article>

        {{-- pop-up daftar tim (hanya untuk turnamen yang bisa didaftar) --}}
        @if (! $milikku && ! $terdaftar && $fase === 'buka')
            @php $buka = session('buka_daftar') === $t->id_turnamen; @endphp
            <dialog id="dlg-daftar-{{ $t->id_turnamen }}" class="tn-dlg">
                <form method="POST" action="{{ route('turnamen.daftar', $t->id_turnamen) }}">
                    @csrf
                    <h3>Daftarkan Tim</h3>
                    <p class="tn-sub">{{ $t->nama_turnamen }} &middot; {{ $rupiah($t->biaya_pendaftaran) }}</p>

                    <label>Nama tim
                        <input type="text" name="nama_tim" maxlength="100" value="{{ $buka ? old('nama_tim') : '' }}" required>
                        @if ($buka) @error('nama_tim', 'daftar') <small class="tn-fe">{{ $message }}</small> @enderror @endif
                    </label>
                    <label>Kontak (WhatsApp)
                        <input type="text" name="kontak" maxlength="20" placeholder="08xxxxxxxxxx" value="{{ $buka ? old('kontak') : '' }}" required>
                        @if ($buka) @error('kontak', 'daftar') <small class="tn-fe">{{ $message }}</small> @enderror @endif
                    </label>

                    <div class="tn-dact">
                        <button type="button" class="tn-btn ghost" data-close>Batal</button>
                        <button type="submit" class="tn-btn">Daftar</button>
                    </div>
                </form>
            </dialog>
        @endif
    @empty
        <p class="tn-empty">{{ $kosong[$tab] }}</p>
    @endforelse
</div>

{{-- ===== POP-UP AJUKAN TURNAMEN ===== --}}
<dialog id="dlg-ajukan" class="tn-dlg wide">
    <form method="POST" action="{{ route('turnamen.store') }}" enctype="multipart/form-data">
        @csrf
        <h3>Ajukan Turnamen</h3>
        <p class="tn-sub">
            {{ $perluPersetujuan ? 'Pengajuan akan diperiksa admin sebelum tampil di daftar.' : 'Turnamen langsung tampil di daftar.' }}
        </p>

        <label>Nama turnamen
            <input type="text" name="nama_turnamen" maxlength="100" value="{{ old('nama_turnamen') }}" required>
            @error('nama_turnamen', 'ajukan') <small class="tn-fe">{{ $message }}</small> @enderror
        </label>

        <div class="tn-row">
            <label>Kategori
                <select name="kategori" required>
                    <option value="">Pilih kategori</option>
                    @foreach ($kategoriList as $k)
                        <option value="{{ $k }}" @selected(old('kategori') === $k)>{{ $k }}</option>
                    @endforeach
                </select>
                @error('kategori', 'ajukan') <small class="tn-fe">{{ $message }}</small> @enderror
            </label>
            <label>Lokasi
                <input type="text" name="lokasi" maxlength="150" placeholder="GOR Kamandanoe" value="{{ old('lokasi') }}">
                @error('lokasi', 'ajukan') <small class="tn-fe">{{ $message }}</small> @enderror
            </label>
        </div>

        <div class="tn-row">
            <label>Tanggal pelaksanaan
                <input id="tn-tgl-pelaksanaan" type="date" name="tgl_pelaksanaan" min="{{ today()->toDateString() }}" value="{{ old('tgl_pelaksanaan') }}" required>
                @error('tgl_pelaksanaan', 'ajukan') <small class="tn-fe">{{ $message }}</small> @enderror
            </label>
            <label>Batas pendaftaran
                <input id="tn-batas-pendaftaran" type="date" name="batas_pendaftaran" min="{{ today()->toDateString() }}" value="{{ old('batas_pendaftaran') }}">
                @error('batas_pendaftaran', 'ajukan') <small class="tn-fe">{{ $message }}</small> @enderror
            </label>
        </div>

        <div class="tn-row">
            <label>Biaya pendaftaran (Rp)
                <input type="number" name="biaya_pendaftaran" min="0" step="1000" value="{{ old('biaya_pendaftaran', 0) }}" required>
                @error('biaya_pendaftaran', 'ajukan') <small class="tn-fe">{{ $message }}</small> @enderror
            </label>
            <label>Kuota tim
                <input type="number" name="kuota_tim" min="2" max="128" value="{{ old('kuota_tim', 16) }}" required>
                @error('kuota_tim', 'ajukan') <small class="tn-fe">{{ $message }}</small> @enderror
            </label>
        </div>

        <div class="tn-row">
            <label>Hadiah
                <input type="text" name="hadiah" maxlength="150" placeholder="Contoh: Rp 2.000.000 + trofi" value="{{ old('hadiah') }}">
                @error('hadiah', 'ajukan') <small class="tn-fe">{{ $message }}</small> @enderror
            </label>
            <label>Kontak penyelenggara
                <input type="text" name="kontak" maxlength="20" placeholder="08xxxxxxxxxx" value="{{ old('kontak') }}" required>
                @error('kontak', 'ajukan') <small class="tn-fe">{{ $message }}</small> @enderror
            </label>
        </div>

        <label>Deskripsi
            <textarea name="deskripsi" rows="4" maxlength="1000" placeholder="Aturan main, syarat peserta, jadwal singkat...">{{ old('deskripsi') }}</textarea>
            @error('deskripsi', 'ajukan') <small class="tn-fe">{{ $message }}</small> @enderror
        </label>

        <label>Poster turnamen (opsional, JPG/PNG/WEBP maks. 5 MB)
            <input type="file" name="poster" accept="image/jpeg,image/png,image/webp">
            @error('poster', 'ajukan') <small class="tn-fe">{{ $message }}</small> @enderror
        </label>

        <div class="tn-dact">
            <button type="button" class="tn-btn ghost" data-close>Batal</button>
            <button type="submit" class="tn-btn">Ajukan</button>
        </div>
    </form>
</dialog>

<script>
    var tglPelaksanaan = document.getElementById('tn-tgl-pelaksanaan');
    var batasPendaftaran = document.getElementById('tn-batas-pendaftaran');
    if (tglPelaksanaan && batasPendaftaran) {
        function sesuaikanBatasPendaftaran() {
            if (tglPelaksanaan.value) {
                batasPendaftaran.max = tglPelaksanaan.value;
                batasPendaftaran.setCustomValidity(
                    batasPendaftaran.value && batasPendaftaran.value > tglPelaksanaan.value
                        ? 'Batas pendaftaran harus sebelum atau sama dengan tanggal pelaksanaan.'
                        : ''
                );
            } else {
                batasPendaftaran.removeAttribute('max');
                batasPendaftaran.setCustomValidity('');
            }
        }

        tglPelaksanaan.addEventListener('change', sesuaikanBatasPendaftaran);
        batasPendaftaran.addEventListener('change', sesuaikanBatasPendaftaran);
        sesuaikanBatasPendaftaran();
    }

    document.querySelectorAll('[data-open]').forEach(function (b) {
        b.addEventListener('click', function () { document.getElementById(b.dataset.open).showModal(); });
    });
    document.querySelectorAll('[data-close]').forEach(function (b) {
        b.addEventListener('click', function () { b.closest('dialog').close(); });
    });
    document.querySelectorAll('dialog').forEach(function (d) {
        d.addEventListener('click', function (e) { if (e.target === d) d.close(); });
    });
    @if (session('buka_ajukan'))
        document.getElementById('dlg-ajukan').showModal();
    @endif
    @if (session('buka_daftar'))
        (function () {
            var d = document.getElementById('dlg-daftar-' + @json(session('buka_daftar')));
            if (d) d.showModal();
        })();
    @endif
</script>
@endsection