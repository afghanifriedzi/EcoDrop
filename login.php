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

    if ($email === '' || $password === '') {
        $error = 'Email dan password wajib diisi.';
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id, nama, email, password, role FROM users WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user   = mysqli_fetch_assoc($result);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['nama']     = $user['nama'];
            $_SESSION['role']     = $user['role'];
            $_SESSION['init_tab'] = true; // <-- TAMBAHKAN BARIS INI
            header("Location: dashboard.php");
            exit;
        } else {
            $error = 'Email atau password tidak sesuai.';
        }
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center min-vh-100 py-4">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="text-center mb-4">
                <a href="index.php" class="text-decoration-none text-success fw-bold fs-3">
                    <i class="bi bi-recycle me-1"></i>EcoDrop
                </a>
                <p class="text-muted small mb-0">Bank Sampah Digital &amp; Poin Sosial</p>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 text-center">Masuk ke Akun</h5>

                    <?php if ($error): ?>
                        <div class="alert alert-danger py-2 small"><?= e($error) ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="email" class="form-label small fw-semibold">Alamat Email</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="nama@email.com" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label small fw-semibold">Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100 fw-semibold">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Masuk
                        </button>
                    </form>

                    <hr class="my-3">
                    <div class="text-center">
                        <span class="d-block text-muted small mb-2">Isi Cepat Akun Demo Pengujian:</span>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-success btn-sm w-50" onclick="fillDemo('admin@ecodrop.id', 'password')">
                                <i class="bi bi-shield-lock me-1"></i>Admin
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm w-50" onclick="fillDemo('raka@gmail.com', 'password')">
                                <i class="bi bi-person me-1"></i>Warga
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-3 small">
                Belum punya akun warga? <a href="register.php" class="text-success fw-semibold text-decoration-none">Daftar di sini</a><br>
                <a href="index.php" class="text-muted text-decoration-none mt-2 d-inline-block">&larr; Kembali ke Beranda</a>
            </div>
        </div>
    </div>
</div>

<script>
function fillDemo(email, password) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = password;
}
</script>

</body>
</html>