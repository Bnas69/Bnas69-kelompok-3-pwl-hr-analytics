<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Login dashboard HR Analytics Kelompok 3.">
    <title>Login | HR Analytics Kelompok 3</title>
    @vite(['resources/css/app.css'])
</head>
<body class="login-page" style="--login-bg: url('{{ asset('images/login-bg.png') }}')">
    <main class="login-shell">
        <section class="login-card">
            <div class="login-header">
                <h1>Login Dashboard</h1>
                <p>Kelompok 3 - Human Resource Analytics</p>
            </div>

            @if ($errors->has('login'))
                <div class="login-error">{{ $errors->first('login') }}</div>
            @endif

            <form method="post" action="{{ route('login.submit') }}" class="login-form">
                @csrf
                <div>
                    <label for="username">Username</label>
                    <input id="username" name="username" type="text" value="{{ old('username') }}" autocomplete="username" required autofocus>
                    @error('username')
                        <small>{{ $message }}</small>
                    @enderror
                </div>

                <div>
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required>
                    @error('password')
                        <small>{{ $message }}</small>
                    @enderror
                </div>

                <button type="submit">Masuk</button>
            </form>
        </section>
    </main>
</body>
</html>
