<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_OFF);

// Paksa cookie sesi hanya berlaku selama browser terbuka + batas idle 15 menit
ini_set('session.cookie_lifetime', 0);
session_set_cookie_params(0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Auto-logout jika tidak ada aktivitas selama 15 menit (900 detik)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 900)) {
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION['last_activity'] = time();

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

// Auto-logout otomatis jika tab ditutup lalu dibuka kembali di tab baru
if (isLoggedIn() && basename($_SERVER['PHP_SELF']) !== 'logout.php') {
    $logoutUrl = (basename($_SERVER['PHP_SELF']) === 'index.php') ? 'logout.php?to=index' : 'logout.php';
    ob_start(function ($html) use ($logoutUrl) {
        if (stripos($html, '</head>') !== false) {
            $initScript = '';
            if (!empty($_SESSION['init_tab'])) {
                unset($_SESSION['init_tab']);
                $initScript = "sessionStorage.setItem('ecodrop_tab', '1');";
            }
            $guard = "<script>{$initScript}if(!sessionStorage.getItem('ecodrop_tab')){window.location.replace('{$logoutUrl}');}</script>\n</head>";
            return str_ireplace('</head>', $guard, $html);
        }
        return $html;
    });
}
?>