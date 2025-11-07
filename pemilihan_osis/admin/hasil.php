<?php
require_once '../includes/cekuser.php';
redirectIfNotAdmin();

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pemilihan - Pemilihan Ketua OSIS</title>
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/img/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container my-5">
        <h1 class="mb-4 text-center">Hasil Pemilihan Ketua OSIS</h1>
        
        <div class="chart-container card p-4 shadow-sm mb-5">
            <canvas id="voteChart"></canvas>
        </div>
        
        <div class="vote-summary card p-4 shadow-sm">
            <h2 class="mb-3">Rekap Suara</h2>
            <p><strong>Total Suara:</strong> <?= $total_votes ?></p>
            
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama Calon</th>
                            <th>Kelas</th>
                            <th>Jumlah Suara</th>
                            <th>Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($candidates as $index => $candidate): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($candidate['nama']) ?></td>
                            <td><?= htmlspecialchars($candidate['kelas']) ?></td>
                            <td><?= $candidate['jumlah_suara'] ?></td>
                            <td><?= $total_votes > 0 ? round(($candidate['jumlah_suara'] / $total_votes) * 100, 2) : 0 ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script>
        const ctx = document.getElementById('voteChart').getContext('2d');
        const voteChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [<?= implode(',', array_map(function($c) { return "'" . addslashes($c['nama']) . "'"; }, $candidates)) ?>],
                datasets: [{
                    label: 'Jumlah Suara',
                    data: [<?= implode(',', array_column($candidates, 'jumlah_suara')) ?>],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(255, 206, 86, 0.6)',
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(153, 102, 255, 0.6)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>

    <?php include '../includes/footeradmin.php'; ?>

    <script>
        document.querySelector('a[href*="keluar.php"]').addEventListener('click', function(e) {
            if (!confirm('Apakah Anda yakin ingin keluar?')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
