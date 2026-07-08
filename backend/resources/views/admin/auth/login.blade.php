<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrator — Kopi Senja</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --primary-coffee: #3E2723;
            --primary-coffee-light: #5D4037;
            --accent-cream: #FFF8F0;
            --accent-cream-dark: #F5EBE0;
            --text-dark: #2B1A11;
            --bg-body: #FAF6F0;
            --accent-gold: #D4A373;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, var(--primary-coffee) 0%, #1e0e0a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 24px;
            padding: 40px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.35);
        }

        .logo-area {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-icon {
            font-size: 3rem;
            color: var(--primary-coffee);
            background-color: var(--accent-cream);
            width: 80px;
            height: 80px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin-bottom: 15px;
            box-shadow: 0 6px 20px rgba(62, 39, 35, 0.15);
        }

        .logo-title {
            font-weight: 800;
            color: var(--primary-coffee);
            font-size: 1.6rem;
            margin-bottom: 5px;
        }

        .form-control {
            border-radius: 12px;
            padding: 12px 16px;
            border: 1px solid var(--accent-cream-dark);
            background-color: #fcfcfc;
            color: var(--text-dark);
            font-weight: 500;
        }

        .form-control:focus {
            border-color: var(--primary-coffee);
            box-shadow: 0 0 0 0.25rem rgba(62, 39, 35, 0.1);
            background-color: #ffffff;
        }

        .btn-login {
            background-color: var(--primary-coffee);
            color: var(--accent-cream);
            border-radius: 12px;
            padding: 14px;
            font-weight: 700;
            font-size: 1rem;
            width: 100%;
            border: none;
            transition: all 0.2s;
            box-shadow: 0 4px 15px rgba(62, 39, 35, 0.25);
        }

        .btn-login:hover {
            background-color: var(--primary-coffee-light);
            color: var(--accent-cream);
        }

        .form-label {
            font-weight: 600;
            color: var(--primary-coffee);
            font-size: 0.9rem;
            margin-bottom: 8px;
        }

        .alert-premium {
            border-radius: 12px;
            border: none;
            font-weight: 500;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="logo-area">
        <div class="logo-icon">
            <i class="bi bi-cup-hot-fill"></i>
        </div>
        <h1 class="logo-title">Kopi Senja</h1>
        <p class="text-muted small">Panel Kontrol Administrator</p>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-premium d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-premium d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-premium mb-4" role="alert">
            <ul class="m-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.login') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label">Alamat Email</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" placeholder="admin@kopisenja.com" required autofocus>
        </div>
        
        <div class="mb-4">
            <label for="password" class="form-label">Kata Sandi</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan kata sandi" required>
        </div>

        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                <label class="form-check-label text-muted small" for="remember">
                    Ingat saya di perangkat ini
                </label>
            </div>
        </div>

        <button type="submit" class="btn btn-login">
            Masuk Sekarang <i class="bi bi-box-arrow-in-right ms-2"></i>
        </button>
    </form>
</div>

</body>
</html>
