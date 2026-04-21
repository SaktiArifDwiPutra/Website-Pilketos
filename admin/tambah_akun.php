<?php
require_once '../includes/koneksi.php';
require_once '../includes/functions.php';
require_once '../includes/cekuser.php';
redirectIfNotAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    if (empty($username) || empty($password) || empty($role)) {
        $error = "Semua field harus diisi!";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->fetch()) {
            $error = "Username sudah digunakan! Silakan gunakan username lain.";
        } else {
            $hashedPassword = hashPassword($password);
            
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->execute([$username, $hashedPassword, $role]);
            
            header('Location: tambah_akun.php?success=1');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Tambah Akun - Pemilihan Ketua OSIS</title>
    
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/img/favicon.ico">
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* Variabel Warna Modern */
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --surface: #ffffff;
            --background: #f3f4f6;
            --text-main: #111827;
            --text-muted: #6b7280;
            --border-color: #d1d5db;
            --input-focus: #818cf8;
            --radius-md: 12px;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background);
            color: var(--text-main);
            -webkit-font-smoothing: antialiased;
        }

        .page-container {
            max-width: 600px; /* Dibuat lebih kecil dari form calon karena isiannya sedikit */
            margin: 40px auto;
            padding: 0 20px;
        }

        .page-header {
            margin-bottom: 30px;
            text-align: center;
        }

        .header-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
            box-shadow: 0 8px 15px rgba(79, 70, 229, 0.3);
            transform: rotate(-5deg);
            transition: transform 0.3s ease;
        }

        .page-container:hover .header-icon {
            transform: rotate(0deg) scale(1.05);
        }

        .page-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-main);
            margin: 0;
        }

        .page-header p {
            color: var(--text-muted);
            font-size: 0.95rem;
            margin-top: 5px;
        }

        /* Form Card */
        .form-card {
            background: var(--surface);
            padding: 30px;
            border-radius: 16px;
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(0,0,0,0.05);
        }

        .form-group { margin-bottom: 20px; }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-main);
        }

        /* Input dengan Ikon di dalamnya */
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
            pointer-events: none; /* Agar tidak mengganggu klik */
        }

        .form-control {
            width: 100%;
            padding: 12px 16px 12px 40px; /* Padding kiri dilonggarkan untuk ikon */
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius-md);
            font-family: inherit;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            background-color: #f9fafb;
            box-sizing: border-box;
            outline: none;
        }

        .form-control:focus {
            border-color: var(--primary);
            background-color: var(--surface);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .form-control:focus + i,
        .form-control:not(:placeholder-shown) + i {
            color: var(--primary);
        }

        /* Styling khusus Select */
        select.form-control {
            appearance: none; /* Hilangkan panah default */
            cursor: pointer;
        }

        .select-wrapper::after {
            content: '\f107'; /* Panah bawah FontAwesome */
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            pointer-events: none;
        }

        /* Tombol */
        .form-actions {
            margin-top: 30px;
            display: flex;
            flex-direction: column; /* Bawaan HP ke bawah */
            gap: 12px;
        }

        @media (min-width: 576px) {
            .form-actions {
                flex-direction: row; /* Desktop ke samping */
            }
            .form-actions .btn-modern {
                flex: 1; /* Lebar seimbang */
            }
        }

        .btn-modern {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary-modern {
            background: linear-gradient(135deg, var(--primary) 0%, #7c3aed 100%);
            color: white;
            box-shadow: 0 4px 6px rgba(79, 70, 229, 0.2);
        }

        .btn-primary-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(79, 70, 229, 0.3);
        }

        .btn-secondary-modern {
            background-color: #ffffff;
            color: var(--text-main);
            border: 1px solid var(--border-color);
        }

        .btn-secondary-modern:hover {
            background-color: #f3f4f6;
        }

        /* Alerts */
        .alert-modern {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: fadeInDown 0.4s ease;
        }
        .alert-error {
            background-color: #fef2f2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }
        .alert-success {
            background-color: #f0fdf4;
            color: #166534;
            border-left: 4px solid #22c55e;
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="page-container">
        
        <div class="page-header">
            <div class="header-icon">
                <i class="fa-solid fa-user-shield"></i>
            </div>
            <h1>Tambah Akun Pengguna</h1>
            <p>Buat akun baru untuk Siswa atau Guru</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert-modern alert-error">
                <i class="fa-solid fa-triangle-exclamation fs-5"></i>
                <div><?= htmlspecialchars($error) ?></div>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert-modern alert-success">
                <i class="fa-solid fa-circle-check fs-5"></i>
                <div>Akun berhasil ditambahkan dan disimpan di database!</div>
            </div>
        <?php endif; ?>
        
        <div class="form-card">
            <form method="POST">
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrapper">
                        <input type="text" id="username" name="username" class="form-control" required autocomplete="off">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="role">Hak Akses (Role)</label>
                    <div class="input-wrapper select-wrapper">
                        <select id="role" name="role" class="form-control" required>
                            <option value="" disabled selected>Pilih Hak Akses</option>
                            <option value="siswa">Siswa</option>
                            <option value="guru">Guru</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-modern btn-primary-modern">
                        <i class="fa-solid fa-plus"></i> Tambah Akun
                    </button>
                    <a href="dashboard.php" class="btn-modern btn-secondary-modern">
                        Batal
                    </a>
                </div>
                
            </form>
        </div>
    </div>
    
    <?php include '../includes/footeradmin.php'; ?>

    <script>
        document.querySelector('a[href*="keluar.php"]')?.addEventListener('click', function(e) {
            if (!confirm('Apakah Anda yakin ingin keluar dari sistem?')) {
                e.preventDefault();
            }
        });
        
        // Membersihkan URL dari parameter ?success=1 setelah 3 detik agar tidak muncul terus kalau di-refresh
        if(window.history.replaceState) {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('success')) {
                setTimeout(() => {
                    urlParams.delete('success');
                    const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
                    window.history.replaceState(null, '', newUrl);
                }, 3000); // Alert sukses hilang dari URL setelah 3 detik
            }
        }
    </script>
</body>
</html>