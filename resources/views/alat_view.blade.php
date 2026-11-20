@extends('layouts.dashboard')

@section('title', 'Sewa Alat')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/alat.css') }}">
    <style>
        .alat-modal { position: fixed; inset: 0; z-index: 1000; display: grid; place-items: center; padding: 20px; background: rgba(23, 32, 51, .58); }
        .alat-modal__panel { width: min(100%, 460px); padding: 25px; border-radius: 16px; background: #fff; box-shadow: 0 18px 55px rgba(0, 0, 0, .22); }
        .alat-modal__panel h2 { margin: 0 0 10px; color: #172033; font-size: 1.25rem; }
        .alat-modal__panel p { color: #687386; line-height: 1.6; }
        .alat-modal__list { display: grid; gap: 8px; padding: 14px 0; border-top: 1px solid #edf0f3; border-bottom: 1px solid #edf0f3; color: #465166; }
        .alat-modal__list p { display: flex; justify-content: space-between; gap: 12px; margin: 0; }
        .alat-modal__total { display: flex; justify-content: space-between; margin: 15px 0 20px; color: #172033; font-weight: 800; }
        .alat-modal__actions { display: flex; flex-wrap: wrap; gap: 10px; }
        .alat-modal__button { display: inline-flex; min-height: 42px; flex: 1; align-items: center; justify-content: center; padding: 9px 14px; border: 0; border-radius: 8px; background: var(--o, #f98a2e); color: #fff; font: 700 .85rem 'Inter', sans-serif; text-decoration: none; cursor: pointer; }
        .alat-modal__button--secondary { border: 1px solid #dfe4ea; background: #fff; color: #354052; }
        .alat-modal--error .alat-modal__panel { border-top: 5px solid #d64545; }
        .alat-modal--success .alat-modal__panel { border-top: 5px solid #29945c; }
    </style>
@endpush

@section('content')
       

    <section class="alat-page" aria-labelledby="alat-title">
        <header class="alat-page__header">
            <h1 class="alat-page__title" id="alat-title"><span>Sewa</span> Alat</h1>
            <p>Pilih dan sewa perlengkapan badminton untuk menemani permainan Anda.</p>
        </header>

        <nav class="alat-categories" aria-label="Kategori alat">
            <button type="button" class="alat-categories__item is-active" aria-current="page">Semua Alat</button>
            <button type="button" class="alat-categories__item">Raket Badminton</button>
            <button type="button" class="alat-categories__item">Shuttlecock</button>
            <button type="button" class="alat-categories__item">Sepatu Olahraga</button>
            <button type="button" class="alat-categories__item">Grip & Aksesori</button>
        </nav>

        <div class="alat-page__layout">
            <div class="alat-list">
                @forelse ($alats as $alat)
                    <article class="alat-card">
                        <div class="alat-card__details">
                            <h2>{{ $alat->nama }}</h2>
                            <p>Stok: {{ $alat->stok }} {{ $alat->satuan }}</p>
                            <p>{{ $alat->deskripsi }}</p>
                            <p class="alat-card__price">
                                Rp {{ number_format($alat->harga_sewa, 0, ',', '.') }}
                                <span>/ {{ $alat->satuan }}</span>
                            </p>
                        </div>
                        <form class="alat-card__actions" method="GET" action="{{ route('alat.create') }}">
                            <input type="hidden" name="id_alat" value="{{ $alat->id_alat }}">
                            <label class="alat-card__quantity">
                                <input
                                    type="number"
                                    name="jumlah"
                                    min="1"
                                    max="{{ $alat->stok }}"
                                    value="{{ old('id_alat') == $alat->id_alat ? old('jumlah', 1) : 1 }}"
                                    aria-label="Jumlah {{ $alat->nama }}"
                                    @disabled($alat->stok < 1)
                                    required
                                >
                            </label>
                            <button class="alat-card__order" type="submit" @disabled($alat->stok < 1)>
                                Tambah Pesanan
                            </button>
                        </form>
                        @if ($errors->has('jumlah') && old('id_alat') == $alat->id_alat)
                            <p role="alert">{{ $errors->first('jumlah') }}</p>
                        @endif
                    </article>
                @empty
                    <p>Belum ada alat yang tersedia.</p>
                @endforelse
            </div>

            <aside class="alat-summary" aria-labelledby="alat-summary-title">
                <h2 id="alat-summary-title">Pesanan Saya</h2>
                <div class="alat-summary__items">
                    @if (session('success'))
                        <p role="status">{{ session('success') }}</p>
                    @endif
                    @forelse ($pesanan as $item)
                        <p>
                            {{ $item->nama }} × {{ $item->jumlah_pesan }}
                            <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </p>
                    @empty
                        <p>Belum ada alat dipilih.</p>
                    @endforelse
                </div>
                <dl class="alat-summary__total">
                    <div>
                        <dt>Subtotal Sewa</dt>
                        <dd>Rp {{ number_format($totalPesanan, 0, ',', '.') }}</dd>
                    </div>
                    <div>
                        <dt>Total Tagihan</dt>
                        <dd>Rp {{ number_format($totalPesanan, 0, ',', '.') }}</dd>
                    </div>
                </dl>
                <form method="GET" action="{{ route('alat.create') }}">
                    <input type="hidden" name="modal" value="confirm">
                    <button class="alat-summary__confirm" type="submit" @disabled($pesanan->isEmpty())>
                        Konfirmasi Pesanan
                    </button>
                </form>
            </aside>
        </div>
    </section>

    @if (request()->query('modal') === 'confirm' && $pesanan->isNotEmpty())
        <div class="alat-modal">
            <section class="alat-modal__panel" role="dialog" aria-modal="true" aria-labelledby="alat-confirm-title">
                <h2 id="alat-confirm-title">Konfirmasi Pesanan</h2>
                <p>Pastikan jumlah dan total pesanan alat Anda sudah benar. Lanjutkan pemesanan?</p>
                <div class="alat-modal__list">
                    @foreach ($pesanan as $item)
                        <p>
                            <span>{{ $item->nama }} × {{ $item->jumlah_pesan }}</span>
                            <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </p>
                    @endforeach
                </div>
                <div class="alat-modal__total">
                    <span>Total</span>
                    <span>Rp {{ number_format($totalPesanan, 0, ',', '.') }}</span>
                </div>
                <div class="alat-modal__actions">
                    <a class="alat-modal__button alat-modal__button--secondary" href="{{ route('alat.create') }}">Periksa Lagi</a>
                    <form method="POST" action="{{ route('alat.confirm') }}">
                        @csrf
                        <button class="alat-modal__button" type="submit">Ya, Lanjutkan</button>
                    </form>
                </div>
            </section>
        </div>
    @endif

    @if (session('orderResult'))
        <div class="alat-modal alat-modal--{{ session('orderResult.type') }}">
            <section class="alat-modal__panel" role="dialog" aria-modal="true" aria-labelledby="alat-result-title">
                <h2 id="alat-result-title">{{ session('orderResult.title') }}</h2>
                <p>{{ session('orderResult.message') }}</p>
                <div class="alat-modal__actions">
                    <a class="alat-modal__button" href="{{ route('alat.create') }}">Tutup</a>
                </div>
            </section>
        </div>
    @endif
@endsection
