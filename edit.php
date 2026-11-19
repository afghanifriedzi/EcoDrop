<?php
require 'config.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

$stmt = mysqli_prepare($conn, "SELECT s.*, u.nama AS nama_warga FROM setoran s JOIN users u ON s.user_id = u.id WHERE s.id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$data) {
    die("Data tidak ditemukan.");
}

// Proteksi hak akses: Warga hanya boleh edit miliknya yang statusnya masih Menunggu
if ($role !== 'admin' && ($data['user_id'] != $user_id || $data['status'] !== 'Menunggu')) {
    die("Akses ditolak: Anda tidak dapat mengubah data yang sudah diverifikasi.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kategori      = $_POST['kategori'] ?? $data['kategori'];
    $berat_kg      = (float)($_POST['berat_kg'] ?? $data['berat_kg']);
    $metode_jemput = $_POST['metode_jemput'] ?? $data['metode_jemput'];
    $tanggal_setor = $_POST['tanggal_setor'] ?? $data['tanggal_setor'];
    $catatan       = trim($_POST['catatan'] ?? '');
    $status        = ($role === 'admin' && isset($_POST['status'])) ? $_POST['status'] : $data['status'];
    $poin          = hitungPoin($kategori, $berat_kg);

    $upd = mysqli_prepare($conn, "UPDATE setoran SET kategori = ?, berat_kg = ?, poin = ?, metode_jemput = ?, catatan = ?, status = ?, tanggal_setor = ? WHERE id = ?");
    mysqli_stmt_bind_param($upd, "sdissssi", $kategori, $berat_kg, $poin, $metode_jemput, $catatan, $status, $tanggal_setor, $id);

    if (mysqli_stmt_execute($upd)) {
        header("Location: dashboard.php?msg=updated");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit / Verifikasi Setoran - EcoDrop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container my-5" style="max-width: 600px;">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-primary text-white py-3">
            <h5 class="mb-0 fw-bold"><?= $role === 'admin' ? 'Verifikasi & Koreksi Timbangan' : 'Edit Pengajuan Setoran' ?> (<?= e($data['kode_setoran']) ?>)</h5>
        </div>
        <div class="card-body p-4">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Penyetor</label>
                    <input type="text" class="form-control bg-light" value="<?= e($data['nama_warga']) ?>" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small">Kategori Sampah</label>
                    <select name="kategori" id="kategori" class="form-select" onchange="kalkulasiPoin()">
                        <?php foreach (getTarifPoin() as $kat => $tarif): ?>
                            <option value="<?= e($kat) ?>" data-rate="<?= $tarif ?>" <?= $data['kategori'] === $kat ? 'selected' : '' ?>>
                                <?= e($kat) ?> (<?= $tarif ?> Poin/Kg)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small">Berat Sampah Aktual (Kg)</label>
                    <input type="number" step="0.1" min="0.1" name="berat_kg" id="berat_kg" class="form-control" value="<?= e($data['berat_kg']) ?>" required oninput="kalkulasiPoin()">
                </div>

                <div class="alert alert-info d-flex justify-content-between align-items-center">
                    <span>Poin Terhitung:</span>
                    <strong id="preview_poin"><?= number_format($data['poin']) ?> Poin</strong>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Metode Penyerahan</label>
                        <select name="metode_jemput" class="form-select">
                            <option value="Antar ke Pos" <?= $data['metode_jemput'] === 'Antar ke Pos' ? 'selected' : '' ?>>Antar ke Pos</option>
                            <option value="Jemput Kurir" <?= $data['metode_jemput'] === 'Jemput Kurir' ? 'selected' : '' ?>>Jemput Kurir</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Tanggal Setor</label>
                        <input type="date" name="tanggal_setor" class="form-control" value="<?= e($data['tanggal_setor']) ?>" required>
                    </div>
                </div>

                <?php if ($role === 'admin'): ?>
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-success">Status Verifikasi Petugas</label>
                    <select name="status" class="form-select border-success">
                        <option value="Menunggu" <?= $data['status'] === 'Menunggu' ? 'selected' : '' ?>>Menunggu</option>
                        <option value="Terverifikasi" <?= $data['status'] === 'Terverifikasi' ? 'selected' : '' ?>>Terverifikasi (Cairkan Poin)</option>
                        <option value="Ditolak" <?= $data['status'] === 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>
                    </select>
                </div>
                <?php endif; ?>

                <div class="mb-4">
                    <label class="form-label fw-semibold small">Catatan</label>
                    <textarea name="catatan" class="form-control" rows="2"><?= e($data['catatan']) ?></textarea>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="dashboard.php" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary fw-semibold px-4">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function kalkulasiPoin() {
    const selectKat = document.getElementById('kategori');
    const rate = parseFloat(selectKat.options[selectKat.selectedIndex].getAttribute('data-rate')) || 0;
    const berat = parseFloat(document.getElementById('berat_kg').value) || 0;
    const poin = Math.round(rate * berat);
    document.getElementById('preview_poin').innerText = poin.toLocaleString('id-ID') + ' Poin';
}
</script>
</body>
</html>