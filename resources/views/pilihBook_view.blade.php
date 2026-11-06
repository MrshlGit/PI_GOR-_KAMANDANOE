@extends('layouts.dashboard')

@section('title', 'Pilih Pemesanan')

@section('content')
    <div class="form-container">
        <h2>Pilih Jenis</h2>

        <form method="POST" action="{{ route('booking.pilih.store') }}">
            @csrf

            <button type="submit" name="tujuan" value="lapangan">
                Booking Lapangan
            </button>

            <button type="submit" name="tujuan" value="alat">
                Booking Alat
            </button>

            <button type="submit" name="tujuan" value="makanan">
                Booking Makanan
            </button>
        </form>
    </div>
@endsection
