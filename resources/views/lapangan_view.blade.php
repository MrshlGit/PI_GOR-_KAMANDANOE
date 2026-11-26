@extends('layouts.dashboard')

@section('title', 'Sewa Lapangan')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/boking.css') }}">
@endpush

@section('content')
    <div class="booking-layout">
        <section class="schedule-panel" aria-labelledby="schedule-title">
            <header class="schedule-header">
                <div>
                    <h2 id="schedule-title">{{ $labelTanggal }}</h2>
                    <p>Ringkasan jadwal lapangan per jam.</p>
                    <form method="GET" action="{{ route('booking.create') }}" class="schedule-date">
                        <label for="schedule-date">Pilih tanggal</label>
                        <input
                            type="date"
                            name="tanggal"
                            id="schedule-date"
                            value="{{ $tanggal->toDateString() }}"
                            required
                        >
                        <button type="submit">Lihat</button>
                    </form>
                </div>
                <label class="schedule-booking-button" for="booking-confirm-toggle">
                    Booking <span aria-hidden="true">→</span>
                </label>
            </header>

            <div class="schedule-legend" aria-label="Keterangan status jadwal">
                <strong>Status Jadwal</strong>
                <span><i class="legend-color legend-color--available"></i>Tersedia</span>
                <span><i class="legend-color legend-color--waiting"></i>Menunggu</span>
                <span><i class="legend-color legend-color--occupied"></i>Terisi</span>
                <span><i class="legend-color legend-color--mine"></i>Booking saya</span>
                <span><i class="legend-color legend-color--maintenance"></i>Maintenance</span>
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
                        @foreach ($jadwal as $hour => $baris)
                            <tr>
                                <th scope="row">
                                    {{ sprintf('%02d:00 - %02d:00', $hour, $hour + 1) }}
                                </th>
                                @foreach ($lapangans as $lapangan)
                                    @php
                                        $status = $baris[$lapangan->id_lapangan];
                                    @endphp
                                    <td class="schedule-cell schedule-cell--{{ $status }}">
                                        {{ $labelStatus[$status] }}
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>



        <br>
        <div class="form-container">
            @php
                $hasilBooking = session('booking_result');
                if (! $hasilBooking && $errors->any()) {
                    $hasilBooking = [
                        'type' => 'error',
                        'title' => 'Booking belum berhasil',
                        'message' => $errors->first(),
                    ];
                }
            @endphp
            <input
                type="checkbox"
                id="booking-confirm-toggle"
                class="booking-confirm-toggle"
                aria-label="Buka konfirmasi booking"
            >

            <h2>Booking Lapangan</h2>
            <p>Pilih lapangan & waktu sesuai kebutuhan Anda.</p>

            @if ($errors->any())
                <div class="error">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('booking.store') }}" id="booking-form">
                @csrf
                <div class="form">
                    <div class="kiri">
                        <fieldset class="court-picker">
                            <legend>Pilih Lapangan</legend>
                            @foreach ($lapanganBooking as $lapangan)
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
                    </div>

                    <div class="kanan">
                        <input
                            type="date"
                            name="tanggal"
                            id="booking-date"
                            class="input"
                            placeholder="Pilih tanggal"
                            value="{{ old('tanggal') }}"
                            min="{{ now()->toDateString() }}"
                            required
                        >
                        <div class="waktu">
                            <select name="jam" id="booking-time" class="input" required>
                                <option value="">Pilih jam</option>
                                @foreach (range(8, 21) as $hour)
                                    @php($time = sprintf('%02d:00', $hour))
                                    <option value="{{ $time }}" @selected(old('jam') === $time)>{{ $time }}</option>
                                @endforeach
                            </select>
                        
                            <input type="number" name="durasi" min="1" max="16" class="input" placeholder="Durasi / Jam" value="{{ old('durasi') }}" required>
                        </div>

                        <label for="booking-confirm-toggle" class="button" @if ($lapanganBooking->isEmpty()) aria-disabled="true" @endif>
                            Booking Sekarang
                        </label>
                    </div>
                </div>
            </form>

            <div class="booking-confirm-modal" role="dialog" aria-modal="true" aria-labelledby="booking-confirm-title">
                <div class="booking-confirm-modal__panel">
                    <h2 id="booking-confirm-title">Konfirmasi Booking</h2>
                    <p>Pastikan lapangan, tanggal, jam, dan durasi yang Anda pilih sudah benar sebelum melanjutkan.</p>
                    <div class="booking-confirm-modal__actions">
                        <label for="booking-confirm-toggle" class="booking-confirm-modal__cancel">Periksa Kembali</label>
                        <button type="submit" form="booking-form" class="booking-confirm-modal__submit" @disabled($lapanganBooking->isEmpty())>
                            Lanjutkan Booking
                        </button>
                    </div>
                </div>
            </div>

            @if ($hasilBooking)
                <div class="booking-result-modal booking-result-modal--{{ $hasilBooking['type'] }}" role="dialog" aria-modal="true" aria-labelledby="booking-result-title">
                    <div class="booking-result-modal__panel">
                        <span class="booking-result-modal__icon" aria-hidden="true">
                            {{ $hasilBooking['type'] === 'success' ? '✓' : '!' }}
                        </span>
                        <h2 id="booking-result-title">{{ $hasilBooking['title'] }}</h2>
                        <p>{{ $hasilBooking['message'] }}</p>
                        
                        @if ($hasilBooking['type'] === 'success')
                            <a href="{{ route('tagihan.index') }}" class="booking-result-modal__close">
                                Lanjutkan ke pembayaran
                            </a>
                        @else 
                             <a href="{{ route('booking.create') }}" class="booking-result-modal__close">
                                Kembali
                            </a>
                        @endif
                    </div>
                </div>
            @endif
            </div>
        </div>

    <script>
        (() => {
            const dateInput = document.getElementById('booking-date');
            const timeInput = document.getElementById('booking-time');
            const timezone = @json(config('app.timezone'));

            const getCurrentDateTime = () => {
                const parts = new Intl.DateTimeFormat('en-CA', {
                    timeZone: timezone,
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit',
                    hourCycle: 'h23',
                }).formatToParts(new Date());
                const values = Object.fromEntries(parts.map(({ type, value }) => [type, value]));

                return {
                    date: `${values.year}-${values.month}-${values.day}`,
                    time: `${values.hour}:${values.minute}`,
                };
            };

            const updateAvailableTimes = () => {
                const current = getCurrentDateTime();

                Array.from(timeInput.options).forEach((option) => {
                    option.disabled = Boolean(
                        option.value
                        && dateInput.value === current.date
                        && option.value <= current.time
                    );
                });

                if (timeInput.selectedOptions[0]?.disabled) {
                    timeInput.value = '';
                }
            };

            dateInput.addEventListener('change', updateAvailableTimes);
            updateAvailableTimes();
        })();
    </script>
@endsection