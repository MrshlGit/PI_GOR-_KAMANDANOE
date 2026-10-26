<?php

include "koneksi.php";

if (isset($_POST['register'])) {


    $username = $_POST['username'];
    $noHp = $_POST['noHp'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $hitung = strlen($_POST['password']);

    $data = mysqli_query(
        $conn,
        "SELECT username from user"
    );


    $cek = mysqli_num_rows($data);

    if ($cek > 0) {
        while ($row = mysqli_fetch_array($data)) {
            if ($username == $row['username']) {
                echo "
                    <script>
                        alert('Username sudah digunakan');
                        window.location='../view/register.php';
                    </script>
                    ";
            }
        }
    }

 

    if ($hitung < 8) {
        echo "
            <script>
                alert('password minimal 8 karakter');
                window.location='../view/register_view.php';
            </script>
            
            ";
    }

       

    mysqli_query(
        $conn,
        "INSERT INTO user VALUES(
        NULL,
        '$username',
        '$noHp',
        '$email',
        '$password',
        'user'
    )"
    );

    echo "
    <script>
        alert('Registrasi berhasil');
        window.location='../../../resources/views/login_view.php';
    </script>
    ";
}

?>