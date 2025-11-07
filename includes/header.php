<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title : 'Pemilihan Ketua OSIS' ?></title>
    
    <!-- FAVICON -->
    <link rel="shortcut icon" href="<?= BASE_URL ?>/assets/img/favicon.ico" type="image/x-icon">
    <link rel="icon" href="<?= BASE_URL ?>/assets/img/favicon.png" type="image/png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=1">
    <style>
        :root {
            --navbar-bg-dark: #2c3e50;
            --panel-bg: #343a40; 
            --text-light: #ffffff;
            --text-hover: rgba(255, 255, 255, 0.75);
            --danger-color: #e74c3c;
        }

        .navbar {
            position: relative;
            z-index: 1030;
        }
        .navbar-custom {
            background-color: var(--navbar-bg-dark) !important;
        }
        
        @media (max-width: 991.98px) {
            .navbar .container {
                align-items: center; 
            }
        }

        .navbar-brand {
            letter-spacing: .2px;
            font-weight: 600;
        }
        .navbar-brand span.fs-5 {
            font-size: 1.15rem !important;
            line-height: 1.2;
        }
        .navbar-brand .brand-title-small {
            display: inline;
            font-size: 0.9em;
            font-weight: 400;
            opacity: 0.8;
            margin-left: 0.25rem;
        }
        @media (min-width: 576px) {
            .navbar-brand span.fs-5 {
                font-size: 1.25rem !important; 
            }
            .navbar-brand .brand-title-small {
                display: none;
            }
        }
        @media (min-width: 768px) {
            .navbar-brand span.fs-5 {
                font-size: 1.5rem !important;
            }
        }

        .navbar .nav-link {
            padding: .45rem .8rem;
            font-weight: 500;
            transition: color .15s ease-in-out, background-color .15s ease-in-out;
        }
        .navbar .btn {
            padding: .28rem .8rem;
            border-radius: .35rem;
            font-size: .9rem;
            box-sizing: border-box;
        }
        .navbar.shadow-sm {
            box-shadow: 0 0 15px rgba(0, 0, 0, .4);
        }

        .btn-logout-filled.btn-danger {
            background-color: var(--danger-color) !important;
            border-color: var(--danger-color) !important;
            box-shadow: 0 2px 4px rgba(231, 76, 60, 0.4);
        }
        .btn-logout-filled.btn-danger:hover {
            background-color: #c0392b !important;
            border-color: #c0392b !important;
        }
        
        .navbar-toggler {
            padding: .35rem .5rem;
            border: none;
            outline: none;
            transition: transform .18s ease;
        }
        .navbar-toggler:focus {
            box-shadow: 0 0 0 .25rem rgba(255, 255, 255, .2);
        }
        .navbar-toggler-icon {
            width: 1.25rem;
            height: 1.25rem;
            background-size: 100% 100%;
        }
        .navbar-toggler.active .navbar-toggler-icon {
            transform: rotate(90deg);
        }
        
        @media (max-width: 991.98px) {
            .navbar .container {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .navbar-collapse {
                position: absolute;
                top: 100%;
                right: 1rem;
                left: auto;
                min-width: 180px;
                max-width: calc(100% - 2rem);
                width: max-content;
                
                background: linear-gradient(135deg, #2c3e50 0%, #1a2530 100%); 
                
                padding: .75rem;
                border-radius: 8px;
                box-shadow: 0 10px 25px rgba(0, 0, 0, .4);
                
                opacity: 0;
                visibility: hidden;
                transform: translateY(10px);
                transform-origin: top right;
                transition: opacity .25s ease, transform .25s ease, visibility .25s;
                z-index: 1050;
            }

            .navbar-collapse.show {
                opacity: 1;
                visibility: visible;
                transform: translateY(0);
            }

            .navbar-collapse .navbar-nav {
                display: flex;
                flex-direction: column;
                align-items: stretch;
                gap: .4rem;
                width: 100%;
                margin-left: 0 !important;
            }

            .navbar-collapse .nav-link,
            .navbar-collapse .btn {
                width: 100%;
                text-align: left;
                padding: .6rem .8rem;
                color: var(--text-light);
                white-space: nowrap;
                border-radius: 6px;
                box-sizing: border-box;
            }
            
            .navbar-collapse .nav-link:hover,
            .navbar-collapse .nav-link:focus,
            .navbar-collapse .btn:hover:not(.btn-danger),
            .navbar-collapse .btn:focus:not(.btn-danger) {
                background-color: rgba(255, 255, 255, .1);
                color: var(--text-light);
            }
            
            .navbar-collapse .btn-logout-filled {
                margin-top: .4rem;
            }
        }

        @media (min-width: 992px) {
            .navbar-collapse {
                position: static;
                background: transparent;
                padding: 0;
                opacity: 1;
                transform: none;
                visibility: visible;
            }
            .navbar-nav {
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
<header>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom shadow-sm py-2">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="<?= BASE_URL ?>/">
                <span class="fs-5 fw-semibold">
                    Pemilihan Wakil 
                    <span class="d-sm-none brand-title-small">& Ketua OSIS 2025/2026</span>
                    <span class="d-none d-sm-inline">& Ketua OSIS 2025/2026</span>
                </span>
            </a>
            <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link text-white" href="<?= BASE_URL ?>/admin/dashboard.php">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white" href="<?= BASE_URL ?>/admin/tambah_calon.php">Tambah Calon</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white" href="<?= BASE_URL ?>/admin/tambah_akun.php">Tambah Akun</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white" href="<?= BASE_URL ?>/admin/hasil.php">Hasil</a>
                            </li>
                        <?php elseif ($_SESSION['role'] === 'guru'): ?>
                            <li class="nav-item">
                                <a class="nav-link text-white" href="<?= BASE_URL ?>/user/dashboard.php">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white" href="<?= BASE_URL ?>/user/vote_guru.php">Vote</a>
                            </li>
                        <?php else: // Peran selain admin dan guru, diasumsikan Siswa ?>
                            <li class="nav-item">
                                <a class="nav-link text-white" href="<?= BASE_URL ?>/user/dashboard.php">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white" href="<?= BASE_URL ?>/user/vote.php">Vote</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="btn btn-danger btn-logout-filled" href="<?= BASE_URL ?>/keluar.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="<?= BASE_URL ?>/masuk.php">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>
<main class="container">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var toggler = document.querySelector('.navbar-toggler');
    var collapse = document.getElementById('mainNavbar');

    if (!toggler || !collapse) return;

    collapse.addEventListener('show.bs.collapse', function() {
        toggler.classList.add('active');
        toggler.setAttribute('aria-expanded', 'true');
    });

    collapse.addEventListener('hide.bs.collapse', function() {
        toggler.classList.remove('active');
        toggler.setAttribute('aria-expanded', 'false');
    });
});
</script>
</body>
</html>