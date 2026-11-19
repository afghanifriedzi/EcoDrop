<?php
require 'config.php';
requireLogin();

$id      = (int)($_GET['id'] ?? 0);
$user_id = (int)$_SESSION['user_id'];
$role    = $_SESSION['role'];

// Jika dibuka langsung tanpa ?id=, otomatis ambil transaksi terakhir
if ($id <= 0) {
    if ($role === 'admin') {
        $qLast = mysqli_query($conn, "SELECT id FROM setoran ORDER BY id DESC LIMIT 1");
    } else {
        $qLast = mysqli_query($conn, "SELECT id FROM setoran WHERE user_id = $user_id ORDER BY id DESC LIMIT 1");
    }
    $lastRow = $qLast ? mysqli_fetch_assoc($qLast) : null;
    $id = $lastRow ? (int)$lastRow['id'] : 0;
}

$sql = "SELECT s.*, u.nama, u.email, u.alamat 
        FROM setoran s 
        JOIN users u ON s.user_id = u.id 
        WHERE s.id = $id LIMIT 1";
$result = mysqli_query($conn, $sql);
$data   = $result ? mysqli_fetch_assoc($result) : null;

if (!$data) {
    echo "<div style='font-family:sans-serif;padding:2rem;text-align:center;'>
            <h3>Data setoran belum tersedia untuk dicetak.</h3>
            <a href='dashboard.php'>Kembali ke Dashboard</a>
          </div>";
    exit;
}

if ($role !== 'admin' && (int)$data['user_id'] !== $user_id) {
    die("Akses ditolak.");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Setoran - <?= e($data['kode_setoran']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .struk-box {
            max-width: 520px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 24px;
        }
        @media print {
            .no-print { display: none !important; }
            body { background: #ffffff !important; padding: 0 !important; }
            .struk-box {
                border: 1px solid #000 !important;
                box-shadow: none !important;
                width: 100% !important;
                max-width: 100% !important;
            }
        }
    </style>
</head>
<body class="bg-light py-4">

<div class="container">
    <div class="struk-box shadow-sm">
        <div class="text-center border-bottom pb-3 mb-3">
            <h4 class="fw-bold text-success mb-0">ECODROP BANK SAMPAH</h4>
            <small class="text-muted">Bukti Setoran Digital &amp; Konversi Poin Sosial</small>
        </div>

        <table class="table table-sm table-borderless mb-3">
            <tr>
                <td class="text-muted">Kode Transaksi</td>
                <td class="text-end fw-bold"><?= e($data['kode_setoran']) ?></td>
            </tr>
            <tr>
                <td class="text-muted">Tanggal Setor</td>
                <td class="text-end"><?= date('d F Y', strtotime($data['tanggal_setor'])) ?></td>
            </tr>
            <tr>
                <td class="text-muted">Nama Penyetor</td>
                <td class="text-end fw-semibold"><?= e($data['nama']) ?></td>
            </tr>
            <tr>
                <td class="text-muted">Alamat</td>
                <td class="text-end small"><?= e($data['alamat']) ?></td>
            </tr>
            <tr>
                <td class="text-muted">Status</td>
                <td class="text-end fw-bold"><?= strtoupper(e($data['status'])) ?></td>
            </tr>
        </table>

        <div class="border-top border-bottom py-3 mb-3">
            <div class="d-flex justify-content-between mb-1">
                <span>Kategori Sampah:</span>
                <strong><?= e($data['kategori']) ?></strong>
            </div>
            <div class="d-flex justify-content-between mb-1">
                <span>Berat Timbangan:</span>
                <strong><?= number_format($data['berat_kg'], 2) ?> Kg</strong>
            </div>
            <div class="d-flex justify-content-between mb-1">
                <span>Metode Penyerahan:</span>
                <span><?= e($data['metode_jemput']) ?></span>
            </div>
            <div class="d-flex justify-content-between mt-3 pt-2 border-top">
                <span class="fw-bold">Total Poin Diperoleh:</span>
                <strong class="text-success fs-5">+<?= number_format($data['poin']) ?> Poin</strong>
            </div>
            <div class="d-flex justify-content-between">
                <span class="small text-muted">Nilai Konversi Bantuan:</span>
                <span class="fw-semibold">Rp <?= number_format($data['poin'] * 50, 0, ',', '.') ?></span>
            </div>
        </div>

        <p class="small text-muted text-center mb-0">
            Simpan bukti digital ini sebagai tanda terima penukaran poin bantuan sosial di Pos EcoDrop.
        </p>
    </div>

    <div class="d-flex justify-content-between mt-3 no-print" style="max-width: 520px; margin: 0 auto;">
        <a href="dashboard.php" class="btn btn-outline-secondary">&larr; Kembali ke Dashboard</a>
        <button type="button" onclick="window.print()" class="btn btn-success fw-semibold">Cetak / Simpan PDF</button>
    </div>
</div>

</body>
</html>