
<?php
require 'config.php';

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = mysqli_prepare($conn, "SELECT id, nama, email, password, role FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama']    = $user['nama'];
        $_SESSION['role']    = $user['role'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Email atau password tidak sesuai!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EcoDrop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center min-vh-100">

<div class="container" style="max-width: 440px;">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h4 class="fw-bold text-success text-center mb-1">Masuk ke EcoDrop</h4>
            <p class="text-muted text-center small mb-4">Kelola setoran sampah &amp; poin sosialmu</p>

            <?php if (isset($_GET['registered'])): ?>
                <div class="alert alert-success small">Registrasi berhasil! Silakan login dengan akun barumu.</div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger small"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Email</label>
                    <input type="email" name="email" class="form-control" required placeholder="Masukkan email terdaftar">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="Masukkan password">
                </div>
                <button type="submit" class="btn btn-success w-100 fw-semibold">Login</button>
            </form>

            <div class="text-center mt-3 small">
                Belum punya akun warga? <a href="register.php" class="text-success fw-semibold">Daftar di sini</a><br>
                <a href="index.php" class="text-muted">&larr; Kembali ke Beranda</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>

