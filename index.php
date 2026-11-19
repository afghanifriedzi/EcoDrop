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
    <style>
        .hero-slide-card {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(4px);
        }
        #heroCarousel .carousel-indicators {
            margin-bottom: 0.75rem;
        }
    </style>
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

<!-- Hero Carousel Promosi Kebersihan & Daur Ulang -->
<header class="bg-success text-white position-relative">
    <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
        </div>

        <div class="carousel-inner py-5">
            <!-- Slide 1: Kampanye Utama -->
            <div class="carousel-item active">
                <div class="container py-2">
                    <div class="row align-items-center justify-content-center g-4">
                        <div class="col-lg-7 text-center text-lg-start">
                            <span class="badge bg-warning text-dark mb-2 px-3 py-2 rounded-pill fw-semibold">
                                <i class="bi bi-globe-asia-australia me-1"></i>Layanan Publik &amp; Lingkungan
                            </span>
                            <h1 class="display-5 fw-bold mb-3">Ubah Sampahmu Jadi Poin Sosial</h1>
                            <p class="lead mb-4">
                                Setorkan sampah anorganik dari rumah, kumpulkan poin sosial yang dapat ditukar kebutuhan pokok, dan pantau dampak nyata bagi lingkungan sekitar.
                            </p>
                            <div class="d-flex flex-column flex-sm-row justify-content-center justify-content-lg-start gap-2">
                                <?php if (isLoggedIn()): ?>
                                    <a href="tambah.php" class="btn btn-warning btn-lg fw-bold text-dark">Ajukan Setor Sampah</a>
                                    <a href="dashboard.php" class="btn btn-outline-light btn-lg">Buka Dashboard</a>
                                <?php else: ?>
                                    <a href="register.php" class="btn btn-warning btn-lg fw-bold text-dark">Mulai Setor Sekarang</a>
                                    <a href="login.php" class="btn btn-outline-light btn-lg">Masuk Akun</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-lg-4 d-none d-lg-block">
                            <div class="hero-slide-card rounded-4 p-4 text-center shadow-sm">
                                <i class="bi bi-recycle text-warning" style="font-size: 5rem;"></i>
                                <h5 class="fw-bold mt-2 mb-1">Gerakan Zero Waste</h5>
                                <p class="small text-white-50 mb-0">Kurangi timbunan TPA mulai dari pemilahan sampah di rumah tangga.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slide 2: Edukasi Pilah Sampah -->
            <div class="carousel-item">
                <div class="container py-2">
                    <div class="row align-items-center justify-content-center g-4">
                        <div class="col-lg-7 text-center text-lg-start">
                            <span class="badge bg-light text-success mb-2 px-3 py-2 rounded-pill fw-semibold">
                                <i class="bi bi-stars me-1"></i>Kampanye Lingkungan Bersih
                            </span>
                            <h1 class="display-5 fw-bold mb-3">Pilah dari Rumah, Lingkungan Makin Sehat</h1>
                            <p class="lead mb-4">
                                Pisahkan botol plastik, kardus, logam, kaca, dan elektronik bekas agar mudah didaur ulang sekaligus bernilai poin tinggi hingga <strong>50 Poin/Kg</strong>.
                            </p>
                            <div class="d-flex flex-column flex-sm-row justify-content-center justify-content-lg-start gap-2">
                                <?php if (isLoggedIn()): ?>
                                    <a href="tambah.php" class="btn btn-warning btn-lg fw-bold text-dark">Setor Sampah Terpilah</a>
                                <?php else: ?>
                                    <a href="register.php" class="btn btn-warning btn-lg fw-bold text-dark">Gabung Warga Peduli</a>
                                    <a href="login.php" class="btn btn-outline-light btn-lg">Masuk Akun</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-lg-4 d-none d-lg-block">
                            <div class="hero-slide-card rounded-4 p-4 text-center shadow-sm">
                                <div class="d-flex justify-content-center gap-3 text-warning mb-2" style="font-size: 3rem;">
                                    <i class="bi bi-cup-straw"></i>
                                    <i class="bi bi-box2-heart"></i>
                                    <i class="bi bi-cpu"></i>
                                </div>
                                <h5 class="fw-bold mt-2 mb-1">5 Kategori Terkelola</h5>
                                <p class="small text-white-50 mb-0">Plastik, Kertas/Kardus, Logam/Kaleng, Kaca, dan Sampah Elektronik.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slide 3: Layanan Jemput & Sembako -->
            <div class="carousel-item">
                <div class="container py-2">
                    <div class="row align-items-center justify-content-center g-4">
                        <div class="col-lg-7 text-center text-lg-start">
                            <span class="badge bg-warning text-dark mb-2 px-3 py-2 rounded-pill fw-semibold">
                                <i class="bi bi-truck me-1"></i>Praktis &amp; Bermanfaat
                            </span>
                            <h1 class="display-5 fw-bold mb-3">Bisa Antar ke Pos atau Jemput Kurir</h1>
                            <p class="lead mb-4">
                                Tidak sempat ke pos bank sampah? Pilih metode <strong>Jemput Kurir</strong> saat mengajukan setoran dan tukarkan poinmu menjadi paket bantuan sosial.
                            </p>
                            <div class="d-flex flex-column flex-sm-row justify-content-center justify-content-lg-start gap-2">
                                <?php if (isLoggedIn()): ?>
                                    <a href="tambah.php" class="btn btn-warning btn-lg fw-bold text-dark">Pesan Jemput Kurir</a>
                                <?php else: ?>
                                    <a href="register.php" class="btn btn-warning btn-lg fw-bold text-dark">Daftar Gratis Sekarang</a>
                                    <a href="login.php" class="btn btn-outline-light btn-lg">Masuk Akun</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-lg-4 d-none d-lg-block">
                            <div class="hero-slide-card rounded-4 p-4 text-center shadow-sm">
                                <i class="bi bi-bag-heart-fill text-warning" style="font-size: 5rem;"></i>
                                <h5 class="fw-bold mt-2 mb-1">1 Poin = Rp 50</h5>
                                <p class="small text-white-50 mb-0">Akumulasi poin otomatis tercatat dan siap dicetak sebagai bukti sah.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev" style="width: 5%;">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Sebelumnya</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next" style="width: 5%;">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Berikutnya</span>
        </button>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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