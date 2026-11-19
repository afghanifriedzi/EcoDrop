<?php
require 'config.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$role    = $_SESSION['role'];

// Ringkasan Kartu Statistik
if ($role === 'admin') {
    $qSummary = mysqli_query($conn, "
        SELECT
            COALESCE(SUM(CASE WHEN status='Terverifikasi' THEN berat_kg ELSE 0 END), 0) AS total_kg,
            COALESCE(SUM(CASE WHEN status='Terverifikasi' THEN poin ELSE 0 END), 0) AS total_poin,
            SUM(CASE WHEN status='Menunggu' THEN 1 ELSE 0 END) AS jml_menunggu,
            COUNT(*) AS total_data
        FROM setoran
    ");
} else {
    $stmtSum = mysqli_prepare($conn, "
        SELECT
            COALESCE(SUM(CASE WHEN status='Terverifikasi' THEN berat_kg ELSE 0 END), 0) AS total_kg,
            COALESCE(SUM(CASE WHEN status='Terverifikasi' THEN poin ELSE 0 END), 0) AS total_poin,
            SUM(CASE WHEN status='Menunggu' THEN 1 ELSE 0 END) AS jml_menunggu,
            COUNT(*) AS total_data
        FROM setoran WHERE user_id = ?
    ");
    mysqli_stmt_bind_param($stmtSum, "i", $user_id);
    mysqli_stmt_execute($stmtSum);
    $qSummary = mysqli_stmt_get_result($stmtSum);
}
$summary = mysqli_fetch_assoc($qSummary);

// Filter & Search
$search   = trim($_GET['search'] ?? '');
$kategori = $_GET['kategori'] ?? '';
$status   = $_GET['status'] ?? '';

$sql = "SELECT s.*, u.nama AS nama_warga FROM setoran s JOIN users u ON s.user_id = u.id WHERE 1=1";
$types = "";
$params = [];

if ($role !== 'admin') {
    $sql .= " AND s.user_id = ?";
    $types .= "i";
    $params[] = $user_id;
}
if ($search !== '') {
    $sql .= " AND (s.kode_setoran LIKE ? OR u.nama LIKE ? OR s.catatan LIKE ?)";
    $like = "%$search%";
    $types .= "sss";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}
if ($kategori !== '') {
    $sql .= " AND s.kategori = ?";
    $types .= "s";
    $params[] = $kategori;
}
if ($status !== '') {
    $sql .= " AND s.status = ?";
    $types .= "s";
    $params[] = $status;
}

$sql .= " ORDER BY s.created_at DESC";
$stmt = mysqli_prepare($conn, $sql);
if ($types !== "") {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$listSetoran = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - EcoDrop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php"><i class="bi bi-recycle me-2"></i>EcoDrop</a>
        <div class="d-flex align-items-center gap-3">
            <span class="text-white small d-none d-md-inline">
                Halo, <strong><?= e($_SESSION['nama']) ?></strong>
                <span class="badge bg-warning text-dark ms-1 text-uppercase"><?= e($role) ?></span>
            </span>
            <a href="index.php" class="btn btn-outline-light btn-sm">Beranda</a>
            <a href="logout.php" class="btn btn-light btn-sm text-danger fw-semibold">Logout</a>
        </div>
    </div>
</nav>

<div class="container my-4">
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show small" role="alert">
            <?php
                if ($_GET['msg'] === 'added') echo "Pengajuan setoran sampah berhasil ditambahkan!";
                elseif ($_GET['msg'] === 'updated') echo "Data setoran berhasil diperbarui!";
                elseif ($_GET['msg'] === 'deleted') echo "Data setoran berhasil dihapus!";
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Kartu Ringkasan -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <small class="text-muted"><?= $role === 'admin' ? 'Total Sampah Terkumpul' : 'Sampah Terverifikasi Saya' ?></small>
                    <h4 class="fw-bold text-success mb-0"><?= number_format($summary['total_kg'], 1) ?> Kg</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <small class="text-muted"><?= $role === 'admin' ? 'Total Poin Diterbitkan' : 'Total Poin Sosial Saya' ?></small>
                    <h4 class="fw-bold text-warning mb-0"><?= number_format($summary['total_poin']) ?> Pts</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <small class="text-muted"><?= $role === 'admin' ? 'Nilai Konversi Bantuan' : 'Estimasi Saldo Sosial' ?></small>
                    <h4 class="fw-bold text-primary mb-0">Rp <?= number_format($summary['total_poin'] * 50, 0, ',', '.') ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <small class="text-muted">Menunggu Verifikasi</small>
                    <h4 class="fw-bold text-secondary mb-0"><?= (int)$summary['jml_menunggu'] ?> Transaksi</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Header & Tombol Tambah -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h5 class="fw-bold mb-0"><?= $role === 'admin' ? 'Daftar Seluruh Setoran Sampah Warga' : 'Riwayat Setoran Sampah Saya' ?></h5>
        <a href="tambah.php" class="btn btn-success fw-semibold">
            <i class="bi bi-plus-lg me-1"></i><?= $role === 'admin' ? 'Input Setoran Baru' : 'Ajukan Setor Sampah' ?>
        </a>
    </div>

    <!-- Fitur Tambahan: Search & Filter -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari kode setoran / nama / catatan..." value="<?= e($search) ?>">
                </div>
                <div class="col-md-3">
                    <select name="kategori" class="form-select form-select-sm">
                        <option value="">-- Semua Kategori --</option>
                        <?php foreach (array_keys(getTarifPoin()) as $kat): ?>
                            <option value="<?= e($kat) ?>" <?= $kategori === $kat ? 'selected' : '' ?>><?= e($kat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">-- Semua Status --</option>
                        <option value="Menunggu" <?= $status === 'Menunggu' ? 'selected' : '' ?>>Menunggu</option>
                        <option value="Terverifikasi" <?= $status === 'Terverifikasi' ? 'selected' : '' ?>>Terverifikasi</option>
                        <option value="Ditolak" <?= $status === 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-success btn-sm w-100">Filter</button>
                    <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel CRUD Setoran -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kode & Tanggal</th>
                        <?php if ($role === 'admin'): ?><th>Penyetor</th><?php endif; ?>
                        <th>Kategori</th>
                        <th>Berat</th>
                        <th>Poin</th>
                        <th>Metode</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($listSetoran) === 0): ?>
                        <tr>
                            <td colspan="<?= $role === 'admin' ? 8 : 7 ?>" class="text-center py-4 text-muted">Belum ada data setoran sampah.</td>
                        </tr>
                    <?php endif; ?>

                    <?php while ($row = mysqli_fetch_assoc($listSetoran)): ?>
                    <tr>
                        <td>
                            <div class="fw-bold text-dark"><?= e($row['kode_setoran']) ?></div>
                            <small class="text-muted"><?= date('d M Y', strtotime($row['tanggal_setor'])) ?></small>
                        </td>
                        <?php if ($role === 'admin'): ?>
                            <td><?= e($row['nama_warga']) ?></td>
                        <?php endif; ?>
                        <td><?= e($row['kategori']) ?></td>
                        <td><?= number_format($row['berat_kg'], 1) ?> Kg</td>
                        <td class="fw-bold text-success">+<?= number_format($row['poin']) ?></td>
                        <td><small><?= e($row['metode_jemput']) ?></small></td>
                        <td>
                            <?php if ($row['status'] === 'Terverifikasi'): ?>
                                <span class="badge bg-success">Terverifikasi</span>
                            <?php elseif ($row['status'] === 'Ditolak'): ?>
                                <span class="badge bg-danger">Ditolak</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Menunggu</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <a href="cetak_struk.php?id=<?= $row['id'] ?>" class="btn btn-outline-secondary btn-sm" title="Cetak Struk">
                                <i class="bi bi-printer"></i>
                            </a>
                            <?php if ($role === 'admin' || $row['status'] === 'Menunggu'): ?>
                                <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-outline-primary btn-sm" title="<?= $role === 'admin' ? 'Verifikasi / Edit' : 'Edit' ?>">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <a href="hapus.php?id=<?= $row['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Yakin ingin menghapus data setoran ini?')" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>