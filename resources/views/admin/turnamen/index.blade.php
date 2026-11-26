@extends('layouts.admin')
@section('title', 'Manajemen Turnamen')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-turnamen.css') }}">
@endpush

@section('content')
    <div class="top">
        <button class="burger" id="burger" aria-label="Menu"><svg class="ic"><use href="#i-menu"/></svg></button>
        <div class="judul">
            <h1>Manajemen Turnamen</h1>
            <p class="muted">Tinjau pengajuan turnamen dan kelola publikasinya.</p>
        </div>
    </div>

    <section class="box admin-tn">
        <nav class="admin-tn-tabs" aria-label="Status pengajuan turnamen">
            @foreach (['menunggu' => 'Menunggu', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak'] as $key => $label)
                <a href="{{ route('admin.turnamen.index', ['status' => $key]) }}" @class(['on' => $status === $key])>
                    {{ $label }} <b>{{ $jumlah[$key] }}</b>
                </a>
            @endforeach
        </nav>

        <div class="admin-tn-list">
            @forelse ($turnamen as $t)
                <article class="admin-tn-card">
                    @if ($t->poster)
                        <a class="admin-tn-poster" href="{{ asset($t->poster) }}" target="_blank" rel="noopener">
                            <img src="{{ asset($t->poster) }}" alt="Poster {{ $t->nama_turnamen }}" loading="lazy">
                        </a>
                    @endif
                    <div class="admin-tn-info">
                        <span class="admin-tn-state {{ $status }}">{{ ucfirst($status) }}</span>
                        <h2>{{ $t->nama_turnamen }}</h2>
                        <p>{{ $t->kategori }} · {{ $t->tgl_pelaksanaan?->locale('id')->translatedFormat('j F Y') ?? '-' }}</p>
                        <p>{{ $t->lokasi ?: 'GOR Kamandanoe' }} · {{ $t->peserta_count }} / {{ $t->kuota_tim }} tim</p>
                        <p>Penyelenggara: {{ $t->penyelenggara->username ?? 'Pengguna' }} · Kontak: {{ $t->kontak ?: '-' }}</p>
                        @if ($t->hadiah)
                            <p>Hadiah: {{ $t->hadiah }}</p>
                        @endif
                        @if ($t->deskripsi)
                            <details><summary>Lihat deskripsi</summary><p class="admin-tn-description">{{ $t->deskripsi }}</p></details>
                        @endif
                    </div>
                    @if ($status === 'menunggu')
                        <div class="admin-tn-actions">
                            <form method="POST" action="{{ route('admin.turnamen.status', $t->id_turnamen) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="disetujui">
                                <button class="admin-tn-approve" type="submit">Setujui</button>
                            </form>
                            <form method="POST" action="{{ route('admin.turnamen.status', $t->id_turnamen) }}" onsubmit="return confirm('Tolak pengajuan turnamen ini?')">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="ditolak">
                                <button class="admin-tn-reject" type="submit">Tolak</button>
                            </form>
                        </div>
                    @endif
                </article>
            @empty
                <p class="admin-tn-empty">Belum ada turnamen berstatus {{ $status }}.</p>
            @endforelse
        </div>

        {{ $turnamen->links() }}
    </section>
@endsection
