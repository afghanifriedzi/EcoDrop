<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'db_ecodrop';

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

function getTarifPoin() {
    return [
        'Plastik'         => 100,
        'Kertas & Kardus' => 60,
        'Logam & Kaleng'  => 150,
        'Kaca'            => 80,
        'Elektronik'      => 250
    ];
}

function hitungPoin($kategori, $berat_kg) {
    $tarif = getTarifPoin();
    $rate = $tarif[$kategori] ?? 50;
    return (int) round($berat_kg * $rate);
}
?>