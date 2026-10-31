<!-- <?php
// include("../../app/Http/Controllers/register.php");
?>

<!DOCTYPE html>
<html>

<head>

    <title>Register</title>

    <link rel="stylesheet" href="../css/style.css">

</head>

<body>

    <div class="form-container">

        <h2>DAFTAR</h2>

        <form method="POST">



            <input type="text" name="username" class="input" placeholder="Username" required>

            <input type="text" name="noHp" class="input" placeholder="Ex: 082299998888" required>

            <input type="text" name="email" class="input" placeholder="user@gmail.com" required>

            <input type="password" name="password" class="input" placeholder="Password" required>

            <button type="submit" name="register" class="button">
                DAFTAR
            </button>

        </form>

        <div class="link">
            Sudah punya akun?
            <a href="{{ route('login') }}">Login</a>
        </div>

    </div>

</body>

</html> -->
@if ($errors->any())
    <div style="color:red">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

<form method="POST" action="/register">
    @csrf

    <input type="text" name="username" class="input" placeholder="Username" value="{{ old('username') }}" required>
    <input type="text" name="noHp" class="input" placeholder="Ex: 082299998888" value="{{ old('noHp') }}" required>
    <input type="email" name="email" class="input" placeholder="user@gmail.com" value="{{ old('email') }}" required>
    <input type="password" name="password" class="input" placeholder="Password" required>
    <input type="password" name="password_confirmation" class="input" placeholder="Ulangi password" required>

    <button type="submit" class="button">DAFTAR</button>
</form>

<a href="/">Sudah punya akun? Login</a>