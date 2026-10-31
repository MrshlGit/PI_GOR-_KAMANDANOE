<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar · GOR Kamandanoe</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Lato:wght@700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>
<div class="auth">

    @include('auth_side')

    <main class="auth-main">
        <div class="card">

            <div class="tabs">
                <a href="/login">Login</a>
                <a href="/register" class="on">Daftar Akun</a>
            </div>

            <h1>Buat Akun Baru</h1>
            <p class="sub">Lengkapi data berikut untuk mulai memesan lapangan GOR Kamandanoe</p>

            @if ($errors->any())
                <div class="alert alert-err">
                    @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            @endif

            <form method="POST" action="/register">
                @csrf

                <div class="field">
                    <label for="username">Username</label>
                    <div class="input-wrap">
                        <svg class="ic"><use href="#i-user"/></svg>
                        <input class="input" id="username" name="username" value="{{ old('username') }}" placeholder="Username" required>
                    </div>
                </div>

                <div class="field">
                    <label for="email">Email</label>
                    <div class="input-wrap">
                        <svg class="ic"><use href="#i-mail"/></svg>
                        <input class="input" id="email" type="email" name="email" value="{{ old('email') }}" placeholder="user@gmail.com" required>
                    </div>
                </div>

                <div class="field">
                    <label for="noHp">No. Telepon</label>
                    <div class="input-wrap">
                        <svg class="ic"><use href="#i-phone"/></svg>
                        <input class="input" id="noHp" name="noHp" value="{{ old('noHp') }}" placeholder="082299998888" required>
                    </div>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <svg class="ic"><use href="#i-lock"/></svg>
                        <input class="input" id="password" type="password" name="password" placeholder="Minimal 8 karakter" required>
                        <button type="button" class="eye" onclick="lihatPassword(this)" aria-label="Tampilkan password"><svg><use href="#i-eye"/></svg></button>
                    </div>
                </div>

                <div class="field">
                    <label for="password_confirmation">Ulangi Password</label>
                    <div class="input-wrap">
                        <svg class="ic"><use href="#i-lock"/></svg>
                        <input class="input" id="password_confirmation" type="password" name="password_confirmation" placeholder="Ulangi password" required>
                        <button type="button" class="eye" onclick="lihatPassword(this)" aria-label="Tampilkan password"><svg><use href="#i-eye"/></svg></button>
                    </div>
                </div>

                <label class="check"><input type="checkbox" name="setuju" required> Setuju dengan ketentuan pengguna</label>

                <button type="submit" class="button">Daftar →</button>
            </form>

            <p class="switch">Sudah punya akun? <a href="/login">Login</a></p>
        </div>
    </main>

</div>
</body>
</html>