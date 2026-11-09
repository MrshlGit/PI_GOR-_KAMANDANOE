@extends('layouts.dashboard')

@section('title', 'Sewa Lapangan')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/boking.css') }}">
@endpush

@section('content')
    <div class="booking-layout">
        <div class="form-container">

            <h2>Booking Lapangan</h2>
            @if (session('success'))
                <p class="success">{{ session('success') }}</p>
            @endif

            @if ($errors->any())
                <div class="error">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('booking.store') }}" id="booking-form">
                @csrf

                <fieldset class="court-picker">
                    <legend>Pilih Lapangan</legend>
                    @foreach ($lapangans as $lapangan)
                        <label class="court-option">
                            <input
                                type="radio"
                                name="id_lapangan"
                                value="{{ $lapangan->id_lapangan }}"
                                @checked((string) old('id_lapangan') === (string) $lapangan->id_lapangan)
                                required
                            >
                            <img src="{{ asset('img/lapangankarpet.jpg') }}" alt="" loading="lazy">
                            <span>
                                <strong>{{ $lapangan->nama_lapangan }}</strong>
                                <small>{{ $lapangan->tipe_lantai }} · Rp {{ number_format((float) $lapangan->harga_per_jam, 0, ',', '.') }}/jam</small>
                            </span>
                        </label>
                    @endforeach
                </fieldset>

                <input type="date" name="tanggal" class="input" value="{{ old('tanggal') }}" required>

                <select name="jam" class="input" required>
                    <option value="">Pilih jam</option>
                    @foreach (range(8, 21) as $hour)
                        @php($time = sprintf('%02d:00', $hour))
                        <option value="{{ $time }}" @selected(old('jam') === $time)>{{ $time }}</option>
                    @endforeach
                </select>

                <input type="number" name="durasi" min="1" max="16" class="input" placeholder="Durasi / Jam" value="{{ old('durasi') }}" required>

                <button type="submit" class="button" @disabled($lapangans->isEmpty())>
                    Booking Sekarang
                </button>
            </form>
        </div>

        <section class="schedule-panel" aria-labelledby="schedule-title">
            <header class="schedule-header">
                <div>
                    <h2 id="schedule-title">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</h2>
                    <p>Ringkasan jadwal lapangan per jam.</p>
                </div>
                <button class="schedule-booking-button" type="submit" form="booking-form">
                    Booking <span aria-hidden="true">→</span>
                </button>
            </header>

            <div class="schedule-legend" aria-label="Keterangan status pembayaran">
                <strong>Status Bayar</strong>
                <span><i class="legend-color legend-color--unpaid"></i>Belum Bayar</span>
                <span><i class="legend-color legend-color--deposit"></i>DP 50%</span>
                <span><i class="legend-color legend-color--paid"></i>Lunas</span>
                <span><i class="legend-color legend-color--member"></i>Member Bulanan</span>
            </div>

            <div class="schedule-table-wrap">
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th scope="col">Waktu</th>
                            @forelse ($lapangans as $lapangan)
                                <th scope="col">{{ $lapangan->nama_lapangan }} ({{ $lapangan->tipe_lantai }})</th>
                            @empty
                                <th scope="col">Belum ada lapangan aktif</th>
                            @endforelse
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (range(8, 21) as $hour)
                            <tr>
                                <th scope="row">
                                    {{ sprintf('%02d:00 - %02d:00', $hour, $hour + 1) }}
                                </th>
                                @forelse ($lapangans as $lapangan)
                                    <td aria-label="Jadwal {{ $lapangan->nama_lapangan }} pukul {{ sprintf('%02d:00', $hour) }} belum ditampilkan"></td>
                                @empty
                                    <td>-</td>
                                @endforelse
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection