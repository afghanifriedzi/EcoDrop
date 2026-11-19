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

