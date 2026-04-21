<?php
require_once 'includes/koneksi.php';
require_once 'includes/functions.php';
include 'includes/favicon-head.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: user/dashboard.php');
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && verifyPassword($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header('Location: admin/dashboard.php');
        } else {
            header('Location: user/dashboard.php');
        }
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - Pemilihan Ketua OSIS</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Variabel Warna Modern */
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --bg-color: #f3f4f6;
            --surface: #ffffff;
            --text-main: #111827;
            --text-muted: #6b7280;
            --input-border: #d1d5db;
            --input-focus: #818cf8;
            --danger: #ef4444;
            --danger-bg: #fef2f2;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
            -webkit-font-smoothing: antialiased;
        }

        .login-wrapper {
            width: 100%;
            max-width: 420px;
        }

        .login-card {
            background: var(--surface);
            padding: 40px 30px;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }

        /* Header Login dengan Ikon Keren */
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin: 0 auto 16px;
            transform: rotate(-10deg);
            box-shadow: 0 8px 15px rgba(79, 70, 229, 0.3);
            transition: transform 0.3s ease;
        }

        .login-wrapper:hover .login-icon {
            transform: rotate(0deg) scale(1.05);
        }

        .login-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0 0 8px 0;
            color: var(--text-main);
        }

        .login-header p {
            color: var(--text-muted);
            font-size: 0.95rem;
            margin: 0;
        }

        /* Form Input Styling */
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 0.9rem;
            color: var(--text-main);
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 1rem;
            transition: color 0.3s ease;
        }

        .form-group input {
            width: 100%;
            padding: 12px 14px 12px 40px; /* Padding kiri lebih besar untuk ruang ikon */
            border: 1px solid var(--input-border);
            border-radius: 8px;
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.3s ease;
            box-sizing: border-box;
            outline: none;
            background-color: #f9fafb;
        }

        .form-group input:focus {
            border-color: var(--primary);
            background-color: var(--surface);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .form-group input:focus + i,
        .form-group input:not(:placeholder-shown) + i {
            color: var(--primary);
        }

        /* Tombol Login */
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(79, 70, 229, 0.2);
            margin-top: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(79, 70, 229, 0.3);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        /* Alert Error Modern */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-danger {
            background-color: var(--danger-bg);
            color: #991b1b;
            border: 1px solid #fecaca;
            border-left: 4px solid var(--danger);
        }

        /* Link Kembali */
        .back-link {
            display: block;
            text-align: center;
            margin-top: 24px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .back-link:hover {
            color: var(--primary);
        }

        /* Animasi masuk */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-card {
            animation: fadeUp 0.5s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }
    </style>
</head>
<body>

    <div class="login-wrapper">
        <div class="login-card">
            
            <div class="login-header">
                <div class="login-icon">
                    <i class="fa-solid fa-fingerprint"></i>
                </div>
                <h1>Selamat Datang</h1>
                <p>Silakan masuk untuk memberikan suara</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><?= $error ?></span>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrapper">
                        <input type="text" id="username" name="username" placeholder="Masukkan username" required autocomplete="off">
                        <i class="fa-solid fa-user"></i>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                        <i class="fa-solid fa-lock"></i>
                    </div>
                </div>
                
                <button type="submit" class="btn-submit">Masuk ke Sistem</button>
            </form>
        </div>
    </div>

</body>
</html>