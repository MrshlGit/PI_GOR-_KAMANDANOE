<?php

session_start();
include "../koneksi.php";

$nama = $_SESSION['nama'];

// proses pembayaran
if (isset($_POST['bayar'])) {

    $id_booking = $_POST['id_booking'];
    $metode = $_POST['metode'];

    mysqli_query(
        $conn,
        "UPDATE booking SET
    status='Lunas',
    metode_pembayaran='$metode'
    WHERE id_booking='$id_booking'"
    );

    echo "
    <script>
        alert('Pembayaran berhasil');
        window.location='../view/transaksi_view.php';
    </script>
    ";
}

$data = mysqli_query(
    $conn,
    "SELECT * FROM booking
WHERE nama_user='$nama'"
);

?>
