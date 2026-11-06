<!DOCTYPE html>
<html>

<head>
    <title>Booking Lapangan</title>
    <link rel="stylesheet" href="{{ asset('css/boking.css') }}">
</head>

<body>

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

        <form method="POST" action="{{ route('booking.store') }}">
            @csrf

            <fieldset class="court-picker">
                <legend>Pilih Lapangan</legend>
                @foreach ($lapangans as $lapangan)
                    <label class="court-option">
                        <input
                            type="radio"
                            name="id_lapangan"
                            value="{{ $lapangan->id_lapangan }}"
                            @checked(old('id_lapangan') === $lapangan->id_lapangan)
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

            <input type="number" name="durasi" min="1" class="input" placeholder="Durasi / Jam" value="{{ old('durasi') }}" required>

            <button type="submit" class="button" @disabled($lapangans->isEmpty())>
                Booking Sekarang
            </button>

        </form>
    </div>

</body>

</html>