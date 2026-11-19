<?php
require 'config.php';

session_unset();
session_destroy();

$tujuan = (isset($_GET['to']) && $_GET['to'] === 'index') ? 'index.php' : 'login.php';
header("Location: " . $tujuan);
exit;