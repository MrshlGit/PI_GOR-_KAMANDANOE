<?php
include "../controller/booking.php";
?>
<!DOCTYPE html>
<html>

<head>
    <title>Booking Lapangan</title>
    <link rel='stylesheet' href='boking.css'>
</head>

<body>

    <div class="form-container">

        <h2>Booking Lapangan</h2>

        <form method="POST">

            <select name="lapangan" class="input">
                <option>Lapangan A</option>
                <option>Lapangan B</option>
                <option>Lapangan C</option>
                <option>Lapangan D</option>
            </select>

            <input type="date" name="tanggal" class="input" required>
            <input type="hidden" name="harga" id="hidden-harga" value="50000">
            <input type="hidden" name="total_bayar" id="hidden-total" value="50000">

            <select name="jam" class="input">
                <option value="8">08:00</option>
                <option value="9">09:00</option>
                <option value="10">10:00</option>
                <option value="11">11:00</option>
                <option value="12">12:00</option>
                <option value="13">13:00</option>
                <option value="14">14:00</option>
                <option value="15">15:00</option>
                <option value="16">16:00</option>
                <option value="17">17:00</option>
                <option value="18">18:00</option>
                <option value="19">19:00</option>
                <option value="20">20:00</option>
                <option value="21">21:00</option>
            </select>

            <input type="number" name="durasi" min="1" class="input" placeholder="Durasi / Jam" required>

            <button type="submit" name="booking" class="button">
                Booking Sekarang
            </button>

        </form>
    </div>

</body>

</html>