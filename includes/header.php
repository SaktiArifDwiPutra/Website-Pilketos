<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= isset($page_title) ? $page_title : 'Pemilihan Ketua OSIS' ?></title>
    
    <link rel="shortcut icon" href="<?= BASE_URL ?>/assets/img/favicon.ico" type="image/x-icon">
    <link rel="icon" href="<?= BASE_URL ?>/assets/img/favicon.png" type="image/png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=1">
    
    <style>
        :root {
            /* Warna Modern Indigo - Ungu */
            --navbar-bg-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            --navbar-mobile-bg: rgba(30, 27, 75, 0.95);
            --text-light: #ffffff;
            --text-hover: rgba(255, 255, 255, 0.8);
            --danger-color: #ef4444; 
            --danger-hover: #dc2626;
        }

        body { font-family: 'Inter', sans-serif; }

        .navbar {
            position: relative;
            z-index: 1030;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .navbar-custom {
            background: var(--navbar-bg-gradient) !important;
        }
        
        .navbar.shadow-sm {
            box-shadow: 0 4px 20px -2px rgba(79, 70, 229, 0.3) !important;
        }

        .navbar-brand {
            letter-spacing: .2px;
            font-weight: 700;
        }
        
        .navbar-brand span.fs-5 {
            font-size: 1.15rem !important;
            line-height: 1.2;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .navbar-brand i { font-size: 1.3rem; }

        .navbar-brand .brand-title-small {
            display: inline;
            font-size: 0.85em;
            font-weight: 500;
            opacity: 0.9;
        }

        @media (min-width: 576px) {
            .navbar-brand span.fs-5 { font-size: 1.25rem !important; }
            .navbar-brand .brand-title-small { display: none; }
        }
        @media (min-width: 768px) {
            .navbar-brand span.fs-5 { font-size: 1.4rem !important; }
        }

        .navbar .nav-link {
            padding: .5rem 1rem;
            font-weight: 500;
            font-size: 0.95rem;
            border-radius: 8px;
            transition: all .2s ease-in-out;
        }
        
        .navbar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.15);
            color: var(--text-light) !important;
            transform: translateY(-1px);
        }

        .btn-logout-filled.btn-danger {
            background-color: var(--danger-color) !important;
            border: none !important;
            border-radius: 8px;
            padding: .45rem 1rem;
            font-weight: 600;
            box-shadow: 0 4px 6px rgba(239, 68, 68, 0.3);
            transition: all 0.2s ease;
        }
        
        .btn-logout-filled.btn-danger:hover {
            background-color: var(--danger-hover) !important;
            transform: translateY(-1px);
            box-shadow: 0 6px 8px rgba(239, 68, 68, 0.4);
        }
        
        .navbar-toggler {
            border: none;
            padding: 0.5rem;
            background: transparent;
        }
        
        .navbar-toggler:focus { box-shadow: none; outline: none; }
        
        /* Mobile Menu Layout Custom */
        @media (max-width: 991.98px) {
            .navbar-collapse {
                display: block !important; /* Paksa tampil untuk animasi */
                position: absolute;
                top: calc(100% + 10px);
                right: 1rem;
                left: 1rem;
                background: var(--navbar-mobile-bg);
                backdrop-filter: blur(10px); 
                -webkit-backdrop-filter: blur(10px);
                padding: 1rem;
                border-radius: 12px;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
                border: 1px solid rgba(255, 255, 255, 0.1);
                
                opacity: 0;
                visibility: hidden;
                transform: translateY(-10px);
                transition: opacity 0.3s ease, transform 0.3s ease, visibility 0.3s;
            }

            .navbar-collapse.show {
                opacity: 1;
                visibility: visible;
                transform: translateY(0);
            }

            .navbar-collapse .navbar-nav {
                gap: 0.5rem;
            }

            .navbar-collapse .nav-link,
            .navbar-collapse .btn {
                width: 100%;
                text-align: left;
                padding: .75rem 1rem;
            }
        }
    </style>
</head>
<body>
<header>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom shadow-sm py-2">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="<?= BASE_URL ?>/">
                <span class="fs-5 text-white">
                    <i class="fa-solid fa-box-archive"></i> Pemilihan 
                    <span class="d-sm-none brand-title-small">Ketua OSIS</span>
                    <span class="d-none d-sm-inline">Wakil & Ketua OSIS 25/26</span>
                </span>
            </a>
            
            <button class="navbar-toggler ms-auto" type="button" id="menuTogglerBtn">
                <i class="fa-solid fa-bars text-white fs-4"></i>
            </button>
            
            <div class="navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/dashboard.php"><i class="fa-solid fa-gauge me-1"></i> Dashboard</a></li>
                            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/tambah_calon.php"><i class="fa-solid fa-user-plus me-1"></i> Calon</a></li>
                            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/tambah_akun.php"><i class="fa-solid fa-users-gear me-1"></i> Akun</a></li>
                            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/admin/hasil.php"><i class="fa-solid fa-chart-pie me-1"></i> Hasil</a></li>
                        <?php else: ?>
                            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/user/dashboard.php"><i class="fa-solid fa-house me-1"></i> Beranda</a></li>
                            <li class="nav-item"><a class="nav-link text-white" href="<?= BASE_URL ?>/user/vote<?= $_SESSION['role'] === 'guru' ? '_guru' : '' ?>.php"><i class="fa-solid fa-check-to-slot me-1"></i> Vote</a></li>
                        <?php endif; ?>
                        <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                            <a class="btn btn-danger btn-logout-filled" href="<?= BASE_URL ?>/keluar.php"><i class="fa-solid fa-right-from-bracket me-1"></i> Keluar</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="<?= BASE_URL ?>/masuk.php"><i class="fa-solid fa-right-to-bracket me-1"></i> Masuk</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var togglerBtn = document.getElementById('menuTogglerBtn');
    var myCollapse = document.getElementById('mainNavbar');
    var togglerIcon = togglerBtn ? togglerBtn.querySelector('i') : null;

    if (!togglerBtn || !myCollapse || !togglerIcon) return;

    // Kendali manual 100% tanpa campur tangan Bootstrap
    togglerBtn.addEventListener('click', function(e) {
        e.stopPropagation(); // Cegah event klik merembet
        
        myCollapse.classList.toggle('show');
        
        // Ganti Ikon
        if (myCollapse.classList.contains('show')) {
            togglerIcon.classList.remove('fa-bars');
            togglerIcon.classList.add('fa-xmark');
        } else {
            togglerIcon.classList.remove('fa-xmark');
            togglerIcon.classList.add('fa-bars');
        }
    });

    // Menutup menu jika user mengeklik di luar area menu
    document.addEventListener('click', function(event) {
        var isClickInsideMenu = myCollapse.contains(event.target);
        var isClickOnButton = togglerBtn.contains(event.target);

        if (!isClickInsideMenu && !isClickOnButton && myCollapse.classList.contains('show')) {
            myCollapse.classList.remove('show');
            togglerIcon.classList.remove('fa-xmark');
            togglerIcon.classList.add('fa-bars');
        }
    });
});
</script>

<main class="container">