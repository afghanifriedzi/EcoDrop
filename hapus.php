<?php
require 'config.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

$stmt = mysqli_prepare($conn, "SELECT user_id, status FROM setoran WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if ($data && ($role === 'admin' || ($data['user_id'] == $user_id && $data['status'] === 'Menunggu'))) {
    $del = mysqli_prepare($conn, "DELETE FROM setoran WHERE id = ?");
    mysqli_stmt_bind_param($del, "i", $id);
    mysqli_stmt_execute($del);
}

header("Location: dashboard.php?msg=deleted");
exit;
?>