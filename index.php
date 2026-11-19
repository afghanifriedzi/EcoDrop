<?php
require 'config.php';

$qStats = mysqli_query($conn, "SELECT COALESCE(SUM(berat_kg),0) AS total_kg, COALESCE(SUM(poin),0) AS total_poin, COUNT(*) AS total_transaksi FROM setoran WHERE status = 'Terverifikasi'");
$stats = mysqli_fetch_assoc($qStats);

$qLeader = mysqli_query($conn, "
    SELECT u.nama, u.alamat, COALESCE(SUM(s.berat_kg),0) AS total_kg, COALESCE(SUM(s.poin),0) AS total_poin
    FROM users u
    JOIN setoran s ON u.id = s.user_id
    WHERE u.role = 'warga' AND s.status = 'Terverifikasi'
    GROUP BY u.id
    ORDER BY total_poin DESC
    LIMIT 5
");

$qChart = mysqli_query($conn, "
    SELECT kategori, COALESCE(SUM(berat_kg),0) AS total_berat
    FROM setoran
    WHERE status = 'Terverifikasi'
    GROUP BY kategori
    ORDER BY total_berat DESC
");
$chartLabels = [];
$chartData   = [];
while ($c = mysqli_fetch_assoc($qChart)) {
    $chartLabels[] = $c['kategori'];
    $chartData[]   = (float) $c['total_berat'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoDrop - Bank Sampah Digital &amp; Poin Sosial</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php"><i class="bi bi-recycle me-2"></i>EcoDrop</a>
        <div class="d-flex gap-2">
            <?php if (isLoggedIn()): ?>
                <a href="dashboard.php" class="btn btn-light btn-sm fw-semibold text-success">Dashboard</a>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-outline-light btn-sm">Login</a>
                <a href="register.php" class="btn btn-light btn-sm fw-semibold text-success">Daftar Warga</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<header class="bg-success text-white py-5 text-center">
    <div class="container">
        <span class="badge bg-warning text-dark mb-2 px-3 py-2 rounded-pill fw-semibold">Layanan Publik &amp; Lingkungan</span>
        <h1 class="display-5 fw-bold mb-3">Ubah Sampah Terpilah Jadi Poin Sosial</h1>
        <p class="lead mx-auto mb-4" style="max-width: 650px;">
            Setorkan sampah anorganik dari rumah, kumpulkan poin sosial yang dapat ditukar kebutuhan pokok, dan pantau dampak nyata bagi lingkungan sekitar.
        </p>
        <div class="d-flex flex-column flex-sm-row justify-content-center gap-2 px-4">
            <?php if (isLoggedIn()): ?>
                <a href="tambah.php" class="btn btn-warning btn-lg fw-bold text-dark">Ajukan Setor Sampah</a>
                <a href="dashboard.php" class="btn btn-outline-light btn-lg">Buka Dashboard</a>
            <?php else: ?>
                <a href="register.php" class="btn btn-warning btn-lg fw-bold text-dark">Mulai Setor Sekarang</a>
                <a href="login.php" class="btn btn-outline-light btn-lg">Masuk Akun</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<main class="container my-5">
    <!-- Statistik Dampak -->
    <div class="row g-4 mb-5 text-center">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-4">
                    <i class="bi bi-box-seam text-success fs-1"></i>
                    <h2 class="fw-bold mt-2 mb-0"><?= number_format($stats['total_kg'], 1) ?> Kg</h2>
                    <p class="text-muted mb-0">Sampah Terkelola &amp; Terverifikasi</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-4">
                    <i class="bi bi-coin text-warning fs-1"></i>
                    <h2 class="fw-bold mt-2 mb-0"><?= number_format($stats['total_poin']) ?> Poin</h2>
                    <p class="text-muted mb-0">Total Poin Sosial Tersalurkan</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body py-4">
                    <i class="bi bi-people-fill text-primary fs-1"></i>
                    <h2 class="fw-bold mt-2 mb-0"><?= number_format($stats['total_transaksi']) ?> Aksi</h2>
                    <p class="text-muted mb-0">Transaksi Penyetoran Berhasil</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Leaderboard Kontributor -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-success"><i class="bi bi-trophy-fill text-warning me-2"></i>Leaderboard Top Kontributor Warga</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Rank</th>
                                <th>Nama Warga</th>
                                <th>Total Berat</th>
                                <th class="text-end">Poin</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $rank = 1; while ($row = mysqli_fetch_assoc($qLeader)): ?>
                            <tr>
                                <td class="fw-bold">
                                    <?php if ($rank === 1): ?>
                                        <span class="badge bg-warning text-dark">#1 Juara</span>
                                    <?php elseif ($rank === 2): ?>
                                        <span class="badge bg-secondary">#2</span>
                                    <?php elseif ($rank === 3): ?>
                                        <span class="badge bg-danger">#3</span>
                                    <?php else: ?>
                                        #<?= $rank ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= e($row['nama']) ?></div>
                                    <small class="text-muted"><?= e($row['alamat']) ?></small>
                                </td>
                                <td><?= number_format($row['total_kg'], 1) ?> Kg</td>
                                <td class="text-end fw-bold text-success"><?= number_format($row['total_poin']) ?> pts</td>
                            </tr>
                            <?php $rank++; endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Grafik & Tarif -->
        <div class="col-lg-5 d-flex flex-column gap-4">
            <!-- Grafik Komposisi Sampah -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-success"><i class="bi bi-pie-chart-fill me-2"></i>Komposisi Sampah Terkelola (Kg)</h5>
                </div>
                <div class="card-body">
                    <div style="max-height: 230px;" class="d-flex justify-content-center">
                        <canvas id="sampahChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Tabel Tarif Konversi Poin -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-success"><i class="bi bi-calculator me-2"></i>Tarif Konversi Poin Sampah</h5>
                </div>
                <div class="card-body">
                    <p class="small text-muted">Setiap <strong>1 Poin = Rp 50</strong> nilai konversi paket bantuan sosial.</p>
                    <ul class="list-group list-group-flush">
                        <?php foreach (getTarifPoin() as $kat => $nilai): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><?= e($kat) ?></span>
                            <span class="badge bg-success rounded-pill"><?= $nilai ?> Poin / Kg</span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</main>

<footer class="bg-white border-top py-4 text-center text-muted small">
    &copy; <?= date('Y') ?> EcoDrop — Bank Sampah Digital &amp; Poin Sosial
</footer>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('sampahChart');
if (ctx) {
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($chartLabels) ?>,
            datasets: [{
                data: <?= json_encode($chartData) ?>,
                backgroundColor: ['#198754', '#ffc107', '#0dcaf0', '#6f42c1', '#fd7e14'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right' }
            }
        }
    });
}
</script>

</body>
</html>