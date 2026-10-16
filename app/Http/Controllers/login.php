<?php

session_start();
include "../koneksi.php";

if (isset($_POST['login'])) {

    $username = $_POST['username'];
    $password = $_POST['password'];

    $data = mysqli_query($conn,"SELECT * FROM user WHERE username='$username' AND password='$password'");

    $cek = mysqli_num_rows($data);

    if ($cek > 0) {

        $d = mysqli_fetch_array($data);

        $_SESSION['nama'] = $d['username'];
        $_SESSION['role'] = $d['role'];

        if ($d['role'] == "admin") {
            header("location:../admin/dashboard.php");
        } else {
            header("location:home.php");
        }

    } else {

        echo "
        <script>
            alert('Login gagal');
        </script>
        ";

    }
}

?>