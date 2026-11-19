<?php
require 'config.php';

// Statistik dampak lingkungan (dari setoran terverifikasi)
$qStats = mysqli_query($conn, "SELECT COALESCE(SUM(berat_kg),0) AS total_kg, COALESCE(SUM(poin),0) AS total_poin, COUNT(*) AS total_transaksi FROM setoran WHERE status = 'Terverifikasi'");
$stats = mysqli_fetch_assoc($qStats);

// Leaderboard Top 5 Warga
$qLeader = mysqli_query($conn, "
    SELECT u.nama, u.alamat, COALESCE(SUM(s.berat_kg),0) AS total_kg, COALESCE(SUM(s.poin),0) AS total_poin
    FROM users u
    JOIN setoran s ON u.id = s.user_id
    WHERE u.role = 'warga' AND s.status = 'Terverifikasi'
    GROUP BY u.id
    ORDER BY total_poin DESC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoDrop - Bank Sampah Digital & Poin Sosial</title>
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

<header class="bg-success text-white py-5">
    <div class="container text-center">
        <span class="badge bg-warning text-dark mb-2 px-3 py-2 rounded-pill">Layanan Publik & Lingkungan</span>
        <h1 class="display-5 fw-bold">Ubah Sampah Terpilah Jadi Poin Sosial</h1>
        <p class="lead mx-auto" style="max-width: 650px;">
            Setorkan sampah anorganik dari rumah, kumpulkan poin sosial yang dapat ditukar kebutuhan pokok, dan pantau dampak nyata bagi lingkungan sekitar.
        </p>
        <div class="mt-4">
            <?php if (isLoggedIn()): ?>
                <a href="tambah.php" class="btn btn-warning btn-lg fw-bold px-4"><i class="bi bi-plus-circle me-2"></i>Ajukan Setor Sampah</a>
            <?php else: ?>
                <a href="register.php" class="btn btn-warning btn-lg fw-bold px-4 me-2">Mulai Setor Sekarang</a>
                <a href="login.php" class="btn btn-outline-light btn-lg px-4">Masuk Akun</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<main class="container my-5">
    <!-- Statistik Dampak -->
    <div class="row g-4 mb-5 text-center">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 py-3">
                <div class="card-body">
                    <i class="bi bi-box-seam fs-1 text-success"></i>
                    <h2 class="fw-bold mt-2"><?= number_format($stats['total_kg'], 1) ?> Kg</h2>
                    <p class="text-muted mb-0">Sampah Terkelola & Terverifikasi</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 py-3">
                <div class="card-body">
                    <i class="bi bi-coin fs-1 text-warning"></i>
                    <h2 class="fw-bold mt-2"><?= number_format($stats['total_poin']) ?> Poin</h2>
                    <p class="text-muted mb-0">Total Poin Sosial Tersalurkan</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 py-3">
                <div class="card-body">
                    <i class="bi bi-check2-circle fs-1 text-primary"></i>
                    <h2 class="fw-bold mt-2"><?= number_format($stats['total_transaksi']) ?> Kali</h2>
                    <p class="text-muted mb-0">Aksi Penyetoran Berhasil</p>
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
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Peringkat</th>
                                    <th>Nama Warga</th>
                                    <th>Total Berat</th>
                                    <th class="text-end pe-3">Poin Sosial</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $rank = 1; while ($row = mysqli_fetch_assoc($qLeader)): ?>
                                <tr>
                                    <td class="ps-3 fw-bold">
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
                                    <td class="text-end pe-3 fw-bold text-success"><?= number_format($row['total_poin']) ?> pts</td>
                                </tr>
                                <?php $rank++; endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Tarif Konversi -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-success"><i class="bi bi-calculator me-2"></i>Tarif Konversi Sampah</h5>
                </div>
                <div class="card-body">
                    <p class="small text-muted">Setiap <strong>1 Poin = Rp 50</strong> saldo bantuan sosial / sembako.</p>
                    <ul class="list-group list-group-flush">
                        <?php foreach (getTarifPoin() as $kat => $nilai): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><?= e($kat) ?></span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill"><?= $nilai ?> Poin / Kg</span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</main>

<footer class="bg-white border-top py-3 text-center text-muted small">
    &copy; <?= date('Y') ?> EcoDrop — Sistem Layanan Bank Sampah Digital & Poin Sosial
</footer>
</body>
</html>