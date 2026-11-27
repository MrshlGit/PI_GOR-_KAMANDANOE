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
    <header class="tn-head">
        <div>
            <h1>Event</h1>
            <p>Temukan turnamen yang akan berlangsung dan daftarkan timmu.</p>
        </div>
        <div class="tn-head-actions">
            <a class="tn-view-all" href="{{ route('turnamen.index', ['tab' => 'semua']) }}">Lihat Semua</a>
            <label for="dlg-ajukan-toggle" class="tn-btn">+ Ajukan Turnamen</label>
        </div>
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

    <div class="tn-grid">
    @forelse ($turnamen as $t)
        @php
            $fase = $t->fase();
            [$teksFase, $warnaFase] = $label[$fase];
            $milikku = $t->id_user == auth()->id();
            $terdaftar = in_array($t->id_turnamen, $idIkut, true);
            $jml = $t->jumlahPeserta();
            $sisa = $t->sisaHari();
            $hitung = $sisa === null ? '' : ($sisa > 1 ? "H-{$sisa}" : ($sisa === 1 ? 'Besok' : ($sisa === 0 ? 'Hari ini' : '')));
            $share = rawurlencode("Yuk ikut turnamen {$t->nama_turnamen} di GOR Kamandanoe, "
                . $t->tgl_pelaksanaan?->locale('id')->translatedFormat('j F Y') . '. Biaya: ' . $rupiah($t->biaya_pendaftaran));
        @endphp

        <article class="tn-card">
            @if ($t->poster)
                <a class="tn-card-image" href="{{ asset($t->poster) }}" target="_blank" rel="noopener">
                    <img class="tn-poster" src="{{ asset($t->poster) }}" alt="Poster {{ $t->nama_turnamen }}" loading="lazy">
                </a>
            @else
                <div class="tn-poster-placeholder" aria-label="Poster belum tersedia">
                    <span>GOR KAMANDANOE</span>
                    <strong>TURNAMEN</strong>
                </div>
            @endif
            <div class="tn-card-body">
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
              <p class="tn-event-date">
                  {{ $t->tgl_pelaksanaan?->locale('id')->translatedFormat('j F Y') ?? '-' }}
                  @if ($t->jam_mulai)
                      <span aria-hidden="true">·</span> {{ substr($t->jam_mulai, 0, 5) }} WIB
                      @if ($t->jam_selesai) - {{ substr($t->jam_selesai, 0, 5) }} WIB @endif
                  @endif
              </p>
              <p class="tn-event-location">{{ $t->lokasi ?: 'GOR Kamandanoe' }}</p>
              <div class="tn-event-price">
                  <span>Biaya pendaftaran</span>
                  <strong>{{ $rupiah($t->biaya_pendaftaran) }}</strong>
              </div>

              <details class="tn-desc">
                  <summary>Detail turnamen</summary>
                  <dl class="tn-meta">
                      <div><dt>Hadiah</dt><dd>{{ $t->hadiah ?: '-' }}</dd></div>
                      @if ($t->batas_pendaftaran)
                          <div><dt>Batas daftar</dt><dd>{{ $t->batas_pendaftaran->locale('id')->translatedFormat('j F Y') }}</dd></div>
                      @endif
                      <div><dt>Kontak</dt><dd>{{ $t->kontak ?: '-' }}</dd></div>
                      <div><dt>Peserta</dt><dd>{{ $jml }} / {{ $t->kuota_tim }} tim</dd></div>
                  </dl>
                  @if ($t->deskripsi)
                      <p>{{ $t->deskripsi }}</p>
                  @endif
              </details>

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
            </div>
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
</div>




{{-- ===== POP-UP AJUKAN TURNAMEN ===== --}}
<input
    type="checkbox"
    id="dlg-ajukan-toggle"
    class="tn-modal-toggle"
    aria-label="Buka form pengajuan turnamen"
    @checked(request()->boolean('buka_form_turnamen') || session('buka_form_turnamen') || session('buka_ajukan'))
>
<div class="tn-ajukan-modal">
    <div class="tn-dlg wide tn-ajukan-panel">
        <form method="POST" action="{{ route('turnamen.store') }}" enctype="multipart/form-data">
        @csrf
        <h3>Ajukan Turnamen</h3>
        <p class="tn-sub">
            {{ $perluPersetujuan ? 'Pengajuan akan diperiksa admin sebelum tampil di daftar.' : 'Turnamen langsung tampil di daftar.' }}
        </p>
        @unless ($sudahBookingAktif)
            <p class="tn-note bad">Silakan booking lapangan terlebih dahulu sebelum mengajukan turnamen.</p>
        @endunless

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
            <label>Jam mulai
                <input type="time" name="jam_mulai" value="{{ old('jam_mulai') }}">
                @error('jam_mulai', 'ajukan') <small class="tn-fe">{{ $message }}</small> @enderror
            </label>
            <label>Jam selesai
                <input type="time" name="jam_selesai" value="{{ old('jam_selesai') }}">
                @error('jam_selesai', 'ajukan') <small class="tn-fe">{{ $message }}</small> @enderror
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

        <label for="dlg-ajukan-lapangan" class="tn-btn tn-lapangan-trigger">Booking Lapangan</label>


        <!-- POP UP ICE PESAN LAPANGAN -->

        <input
            type="checkbox"
            id="dlg-ajukan-lapangan"
            class="tn-lapangan-toggle"
            aria-label="Buka form pengajuan turnamen"
            @checked(session('buka_ajukan'))
        >
        <div class="tn-ajukan-lapangan">
            <section class="tn-lapangan-panel" role="dialog" aria-modal="true" aria-labelledby="tn-lapangan-title">
                <h2 id="tn-lapangan-title">Pesan Lapangan</h2>
                <p>Silakan pesan lapangan terlebih dahulu sebelum mengajukan turnamen.</p>
                <a href="{{ route('booking.create2') }}" class="tn-btn ghost">Pesan Lapangan</a>
                <label for="dlg-ajukan-lapangan" class="tn-btn ghost">Tutup</label>
            </section>
        </div>

        <div class="tn-dact">
            <label for="dlg-ajukan-toggle" class="tn-btn ghost">Batal</label>
            <button type="submit" class="tn-btn" @disabled(! $sudahBookingAktif)>Ajukan</button>
        </div>
        </form>
    </div>
</div>

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
    @if (session('buka_daftar'))
        (function () {
            var d = document.getElementById('dlg-daftar-' + @json(session('buka_daftar')));
            if (d) d.showModal();
        })();
    @endif
</script>
@endsection