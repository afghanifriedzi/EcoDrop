<?php
require 'config.php';
requireLogin();

$role = $_SESSION['role'];
$wargaList = [];
if ($role === 'admin') {
    $qWarga = mysqli_query($conn, "SELECT id, nama, email FROM users WHERE role = 'warga' ORDER BY nama ASC");
    while ($w = mysqli_fetch_assoc($qWarga)) {
        $wargaList[] = $w;
    }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target_user_id = ($role === 'admin' && !empty($_POST['user_id'])) ? (int)$_POST['user_id'] : $_SESSION['user_id'];
    $kategori       = $_POST['kategori'] ?? 'Plastik';
    $berat_kg       = (float)($_POST['berat_kg'] ?? 0);
    $metode_jemput  = $_POST['metode_jemput'] ?? 'Antar ke Pos';
    $tanggal_setor  = $_POST['tanggal_setor'] ?? date('Y-m-d');
    $catatan        = trim($_POST['catatan'] ?? '');
    $status         = ($role === 'admin') ? 'Terverifikasi' : 'Menunggu';

    if ($berat_kg <= 0) {
        $error = "Berat sampah harus lebih dari 0 Kg.";
    } else {
        $poin = hitungPoin($kategori, $berat_kg);
        $kode_setoran = 'ECO-' . date('Ymd') . '-' . rand(100, 999);

        $stmt = mysqli_prepare($conn, "INSERT INTO setoran (kode_setoran, user_id, kategori, berat_kg, poin, metode_jemput, catatan, status, tanggal_setor) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sisdissss", $kode_setoran, $target_user_id, $kategori, $berat_kg, $poin, $metode_jemput, $catatan, $status, $tanggal_setor);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: dashboard.php?msg=added");
            exit;
        } else {
            $error = "Gagal menyimpan data setoran.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajukan Setoran - EcoDrop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container my-5" style="max-width: 600px;">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-success text-white py-3">
            <h5 class="mb-0 fw-bold">Form Pengajuan Setor Sampah</h5>
        </div>
        <div class="card-body p-4">
            <?php if ($error): ?>
                <div class="alert alert-danger small"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <?php if ($role === 'admin'): ?>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Pilih Warga Penyetor</label>
                    <select name="user_id" class="form-select" required>
                        <?php foreach ($wargaList as $w): ?>
                            <option value="<?= $w['id'] ?>"><?= e($w['nama']) ?> (<?= e($w['email']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label fw-semibold small">Kategori Sampah Terpilah</label>
                    <select name="kategori" id="kategori" class="form-select" required onchange="kalkulasiPoin()">
                        <?php foreach (getTarifPoin() as $kat => $tarif): ?>
                            <option value="<?= e($kat) ?>" data-rate="<?= $tarif ?>"><?= e($kat) ?> (<?= $tarif ?> Poin/Kg)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small">Perkiraan Berat Sampah (Kg)</label>
                    <input type="number" step="0.1" min="0.1" name="berat_kg" id="berat_kg" class="form-control" required placeholder="Contoh: 2.5" oninput="kalkulasiPoin()">
                </div>

                <!-- Box Kalkulator Poin Real-Time -->
                <div class="alert alert-success d-flex justify-content-between align-items-center">
                    <div>
                        <small class="d-block text-muted">Estimasi Poin Didapat:</small>
                        <strong class="fs-5" id="preview_poin">0 Poin</strong>
                    </div>
                    <div class="text-end">
                        <small class="d-block text-muted">Setara Saldo Sosial:</small>
                        <strong class="fs-5 text-success" id="preview_rupiah">Rp 0</strong>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Metode Penyerahan</label>
                        <select name="metode_jemput" class="form-select">
                            <option value="Antar ke Pos">Antar ke Pos Bank Sampah</option>
                            <option value="Jemput Kurir">Jemput Kurir EcoDrop</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Tanggal Setor</label>
                        <input type="date" name="tanggal_setor" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold small">Catatan Tambahan (Opsional)</label>
                    <textarea name="catatan" class="form-control" rows="2" placeholder="Contoh: Sudah dipilah dalam 2 karung bersih"></textarea>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="dashboard.php" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-success fw-semibold px-4">Simpan Pengajuan</button>
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
    const rupiah = poin * 50;

    document.getElementById('preview_poin').innerText = poin.toLocaleString('id-ID') + ' Poin';
    document.getElementById('preview_rupiah').innerText = 'Rp ' + rupiah.toLocaleString('id-ID');
}
</script>
</body>
</html>