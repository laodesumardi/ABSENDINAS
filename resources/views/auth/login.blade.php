<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Absensi</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f4f6f9;
        }

        .login-wrapper {
            height: 100vh;
        }

        .login-card {
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            border: 1px solid #ddd;
        }

        .login-title {
            font-weight: 600;
            color: #333;
        }

        .login-subtitle {
            font-size: 14px;
            color: #777;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: #0d6efd;
        }

        .btn-login {
            background-color: #0d6efd;
            border: none;
        }

        .btn-login:hover {
            background-color: #0b5ed7;
        }

        .input-group-text {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>

<div class="container login-wrapper d-flex justify-content-center align-items-center">
    <div class="login-card w-100" style="max-width: 380px;">

        <div class="text-center mb-4">
            <i class="fas fa-user-circle fa-2x mb-2 text-primary"></i>
            <div class="login-title">Sistem Absensi</div>
            <div class="login-subtitle">Silakan masuk ke akun Anda</div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger py-2">
                <ul class="mb-0 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label small">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" name="email" class="form-control"
                        value="{{ old('email') }}" required autofocus>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" class="form-control" required>
                </div>
            </div>

            <div class="mb-3 form-check small">
                <input type="checkbox" name="remember" class="form-check-input" id="remember">
                <label class="form-check-label" for="remember">Ingat saya</label>
            </div>

            <button type="submit" class="btn btn-login w-100">
                Login
            </button>
        </form>

    </div>
</div>

</body>
</html>
