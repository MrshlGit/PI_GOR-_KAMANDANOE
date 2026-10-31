<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login · GOR Kamandanoe</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Lato:wght@700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>
<div class="auth">

    @include('auth_side')

    <main class="auth-main">
        <div class="card">

            <div class="tabs">
                <a href="/login" class="on">Login</a>
                <a href="/register">Daftar Akun</a>
            </div>

            <h1>Masuk ke Akun Anda</h1>
            <p class="sub">Silahkan masuk ke akun GOR Kamandanoe anda untuk melanjutkan pemesanan lapangan</p>

            @if (session('success'))
                <div class="alert alert-ok">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-err">
                    @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            @endif

            <form method="POST" action="/login">
                @csrf

                <div class="field">
                    <label for="username">Username</label>
                    <div class="input-wrap">
                        <svg class="ic"><use href="#i-user"/></svg>
                        <input class="input" id="username" name="username" value="{{ old('username') }}" placeholder="Username" required autofocus>
                    </div>
                </div>

                <div class="field">
                    <div class="label-row"><label for="password" style="margin:0">Password</label><a href="#">Lupa Kata Sandi?</a></div>
                    <div class="input-wrap">
                        <svg class="ic"><use href="#i-lock"/></svg>
                        <input class="input" id="password" type="password" name="password" placeholder="Minimal 8 karakter" required>
                        <button type="button" class="eye" onclick="lihatPassword(this)" aria-label="Tampilkan password"><svg><use href="#i-eye"/></svg></button>
                    </div>
                </div>

                <label class="check"><input type="checkbox" name="setuju" checked> Saya menyetujui ketentuan pengguna</label>

                <button type="submit" class="button">Login →</button>
            </form>

            <p class="switch">Belum punya akun? <a href="/register">Daftar akun</a></p>
        </div>
    </main>

</div>
</body>
</html>