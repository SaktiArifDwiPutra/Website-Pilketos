<?php
require_once __DIR__ . '/../includes/koneksi.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/cekuser.php';
requireAdmin();

$stmt = $pdo->query("
    SELECT c.id, c.nama, c.kelas, COUNT(v.id) as jumlah_suara 
    FROM candidates c 
    LEFT JOIN votes v ON c.id = v.candidate_id 
    GROUP BY c.id
");
$candidates = $stmt->fetchAll();

$total_votes = $pdo->query("SELECT COUNT(*) FROM votes")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Hasil Pemilihan - Pemilihan Ketua OSIS</title>
    
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/img/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #3730a3;
            --surface: #ffffff;
            --background: #f8fafc;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --radius-lg: 16px;
            --radius-xl: 24px;
            --shadow-md: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
            --navy-blue: #1e3a8a;
            --emerald-green: #10b981;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background);
            color: var(--text-main);
        }

        /* Banner Atas */
        .page-banner {
            background: linear-gradient(135deg, var(--navy-blue) 0%, var(--primary) 100%);
            color: white;
            padding: 40px 20px 80px;
            border-radius: 0 0 30px 30px;
            text-align: center;
        }

        .page-banner h1 {
            font-weight: 800;
            font-size: 1.8rem;
            margin-bottom: 8px;
        }

        .page-banner p {
            opacity: 0.9;
            font-weight: 400;
            font-size: 0.95rem;
        }

        /* Kontainer Utama (Ditarik ke atas agar menimpa banner) */
        .content-wrapper {
            margin-top: -50px;
            position: relative;
            z-index: 10;
        }

        /* Kartu Summary & Grafik */
        .modern-card {
            background: var(--surface);
            border-radius: var(--radius-lg);
            border: none;
            box-shadow: var(--shadow-md);
            padding: 24px;
            margin-bottom: 24px;
        }

        /* Kartu Total Suara Spesifik */
        .total-votes-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 24px 30px;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border-left: 5px solid var(--emerald-green);
        }

        .total-votes-info h2 {
            font-size: 0.95rem;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .total-votes-info .angka {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--text-main);
            line-height: 1;
            margin: 0;
        }

        .total-votes-icon {
            width: 60px;
            height: 60px;
            background: rgba(16, 185, 129, 0.1);
            color: var(--emerald-green);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
        }

        /* Chart Container */
        .chart-wrapper {
            position: relative;
            height: 350px; /* Tinggi optimal untuk desktop & mobile */
            width: 100%;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Styling Tabel Modern */
        .table-modern {
            margin: 0;
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px; /* Memberi jarak antar baris */
        }

        .table-modern thead th {
            background-color: transparent;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
            padding: 12px 16px;
        }

        .table-modern tbody tr {
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .table-modern tbody tr:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        }

        .table-modern tbody td {
            padding: 16px;
            border: none;
            border-top: 1px solid #f1f5f9;
            border-bottom: 1px solid #f1f5f9;
            color: var(--text-main);
            font-weight: 500;
            vertical-align: middle;
        }

        .table-modern tbody td:first-child {
            border-left: 1px solid #f1f5f9;
            border-radius: 8px 0 0 8px;
            font-weight: 700;
            color: var(--text-muted);
        }

        .table-modern tbody td:last-child {
            border-right: 1px solid #f1f5f9;
            border-radius: 0 8px 8px 0;
        }

        /* Badge Persentase di Tabel */
        .badge-percentage {
            background: rgba(79, 70, 229, 0.1);
            color: var(--primary);
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.9rem;
        }

        /* Progress bar mini di dalam tabel */
        .mini-progress-bg {
            width: 100px;
            height: 6px;
            background: #e2e8f0;
            border-radius: 4px;
            margin-top: 6px;
            overflow: hidden;
        }
        
        .mini-progress-fill {
            height: 100%;
            background: var(--primary);
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="page-banner">
        <div class="container">
            <h1><i class="fa-solid fa-chart-line me-2"></i>Live Count Pemilihan</h1>
            <p>Pantau hasil perolehan suara secara real-time dan transparan.</p>
        </div>
    </div>
    
    <div class="container content-wrapper">
        
        <div class="modern-card total-votes-card">
            <div class="total-votes-info">
                <h2>Total Suara Masuk</h2>
                <p class="angka"><?= number_format($total_votes, 0, ',', '.') ?></p>
            </div>
            <div class="total-votes-icon">
                <i class="fa-solid fa-check-to-slot"></i>
            </div>
        </div>
        
        <div class="modern-card">
            <div class="section-title">
                <i class="fa-solid fa-chart-column text-primary"></i> Statistik Perolehan Suara
            </div>
            <div class="chart-wrapper">
                <canvas id="voteChart"></canvas>
            </div>
        </div>
        
        <div class="modern-card">
            <div class="section-title">
                <i class="fa-solid fa-table-list text-primary"></i> Rincian Detail Kandidat
            </div>
            <div class="table-responsive pb-2">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="30%">Nama Kandidat</th>
                            <th width="20%">Kelas</th>
                            <th width="20%">Jumlah Suara</th>
                            <th width="25%">Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($candidates as $index => $candidate): 
                            $persentase = $total_votes > 0 ? round(($candidate['jumlah_suara'] / $total_votes) * 100, 1) : 0;
                        ?>
                        <tr>
                            <td>#<?= $index + 1 ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($candidate['nama']) ?></td>
                            <td><span class="badge bg-light text-secondary border"><?= htmlspecialchars($candidate['kelas']) ?></span></td>
                            <td class="fs-5 fw-bold text-dark"><?= number_format($candidate['jumlah_suara'], 0, ',', '.') ?></td>
                            <td>
                                <div class="d-flex flex-column justify-content-center">
                                    <span class="badge-percentage align-self-start"><?= $persentase ?>%</span>
                                    <div class="mini-progress-bg">
                                        <div class="mini-progress-fill" style="width: <?= $persentase ?>%;"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    
    <script>
        // Konfigurasi Chart.js yang Elegan dan Profesional
        const ctx = document.getElementById('voteChart').getContext('2d');
        
        // Palet Warna Profesional (Navy, Emerald, Indigo, Sky Blue, Amber)
        const modernColors = [
            'rgba(30, 58, 138, 0.85)',   // Navy Blue
            'rgba(16, 185, 129, 0.85)',  // Emerald Green
            'rgba(79, 70, 229, 0.85)',   // Indigo
            'rgba(14, 165, 233, 0.85)',  // Sky Blue
            'rgba(245, 158, 11, 0.85)'   // Amber
        ];

        const borderColors = [
            'rgba(30, 58, 138, 1)',
            'rgba(16, 185, 129, 1)',
            'rgba(79, 70, 229, 1)',
            'rgba(14, 165, 233, 1)',
            'rgba(245, 158, 11, 1)'
        ];

        const voteChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [<?= implode(',', array_map(function($c) { return "'" . addslashes($c['nama']) . "'"; }, $candidates)) ?>],
                datasets: [{
                    label: 'Perolehan Suara',
                    data: [<?= implode(',', array_column($candidates, 'jumlah_suara')) ?>],
                    backgroundColor: modernColors,
                    borderColor: borderColors,
                    borderWidth: 0, // Dibuat 0 agar tampil mulus tanpa outline
                    borderRadius: 8, // Ujung bar melengkung (Modern)
                    barPercentage: 0.6 // Membuat bar tidak terlalu gemuk
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // Penting agar tinggi chart bisa diatur via CSS
                plugins: {
                    legend: { display: false }, // Sembunyikan legend karena label sudah jelas di bawah
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        titleFont: { size: 14, family: "'Inter', sans-serif" },
                        bodyFont: { size: 14, family: "'Inter', sans-serif", weight: 'bold' },
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + ' Suara';
                            }
                        }
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.04)', // Garis grid sangat tipis
                            drawBorder: false
                        },
                        ticks: {
                            font: { family: "'Inter', sans-serif" },
                            stepSize: 1 // Memaksa kelipatan 1 (karena suara tidak mungkin desimal)
                        }
                    },
                    x: {
                        grid: { display: false }, // Hilangkan garis vertikal agar bersih
                        ticks: {
                            font: { family: "'Inter', sans-serif", weight: '600' }
                        }
                    }
                }
            }
        });
    </script>

    <?php include '../includes/footeradmin.php'; ?>

    <script>
        document.querySelector('a[href*="keluar.php"]')?.addEventListener('click', function(e) {
            if (!confirm('Apakah Anda yakin ingin keluar dari sistem?')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>