<?php
require 'config.php';

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $alamat   = trim($_POST['alamat'] ?? '');
    $password = $_POST['password'] ?? '';

    if (strlen($password) < 6) {
        $error = "Password minimal 6 karakter.";
    } else {
        $cek = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($cek, "s", $email);
        mysqli_stmt_execute($cek);
        mysqli_stmt_store_result($cek);

        if (mysqli_stmt_num_rows($cek) > 0) {
            $error = "Email sudah terdaftar. Gunakan email lain.";
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = mysqli_prepare($conn, "INSERT INTO users (nama, email, password, role, alamat) VALUES (?, ?, ?, 'warga', ?)");
            mysqli_stmt_bind_param($stmt, "ssss", $nama, $email, $hash, $alamat);

            if (mysqli_stmt_execute($stmt)) {
                header("Location: login.php?registered=1");
                exit;
            } else {
                $error = "Terjadi kesalahan saat menyimpan data.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Warga - EcoDrop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center min-vh-100">

<div class="container" style="max-width: 460px;">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h4 class="fw-bold text-success text-center mb-1">Registrasi Warga EcoDrop</h4>
            <p class="text-muted text-center small mb-4">Gabung dan mulai kumpulkan poin dari sampah terpilah</p>

            <?php if ($error): ?>
                <div class="alert alert-danger small"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control" required placeholder="Nama lengkap Anda">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Email</label>
                    <input type="email" name="email" class="form-control" required placeholder="nama@email.com">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Alamat Domisili</label>
                    <input type="text" name="alamat" class="form-control" required placeholder="Contoh: Jl. Merdeka No. 10, RT 02">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Password (Min. 6 Karakter)</label>
                    <input type="password" name="password" class="form-control" required minlength="6">
                </div>
                <button type="submit" class="btn btn-success w-100 fw-semibold">Daftar Sekarang</button>
            </form>

            <div class="text-center mt-3 small">
                Sudah punya akun? <a href="login.php" class="text-success fw-semibold">Login di sini</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>