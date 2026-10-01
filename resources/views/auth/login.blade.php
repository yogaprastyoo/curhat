<!DOCTYPE html>
<html lang="id">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Login | Curhat</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="hold-transition login-page">
        <div class="login-box">
            <div class="login-logo"><b>CUR</b>HAT</div>
            <div class="card">
                <div class="card-body login-card-body">
                    <p class="login-box-msg">Masuk ke Portal Curhat</p>
                    <form method="POST" action="{{ route('login.process') }}">
                        @csrf
                        <div class="input-group mb-3">
                            <input type="email" name="email" class="form-control" placeholder="Email"
                                value="{{ old('email') }}" required>
                            <div class="input-group-append">
                                <div class="input-group-text"><span class="fas fa-envelope"></span></div>
                            </div>
                        </div>
                        <div class="input-group mb-3">
                            <input type="password" name="password" class="form-control" placeholder="Password" required>
                            <div class="input-group-append">
                                <div class="input-group-text"><span class="fas fa-lock"></span></div>
                            </div>
                        </div>
                        @error('email')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <button type="submit" class="btn btn-primary btn-block">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </body>

</html>
