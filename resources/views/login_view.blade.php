<?php
include("../../app/Http/Controllers/login.php");
?>

<!DOCTYPE html>
<html>
<!-- saya -->
<head>

    <title>Login</title>

    <link rel="stylesheet" href="../css/style.css">

</head>

<body>

    <div class="form-container">

        <h2>LOGIN</h2>

        <form method="POST">

            <input type="text" name="username" class="input" placeholder="Username" required>

            <input type="password" name="password" class="input" placeholder="Password" required>

            <button type="submit" name="login" class="button">
                LOGIN
            </button>

        </form>

    </div>

</body>

</html>