<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: keranjang.php');
    exit;
}
$index = $_POST['index'] ?? null;

if ($index === null || !isset($_SESSION['keranjang']) || !is_array($_SESSION['keranjang']) || !isset($_SESSION['keranjang'][$index])) {
    $_SESSION['error_message'] = "Item keranjang tidak ditemukan.";
    header('Location: keranjang.php');
    exit;
}
$item_name = $_SESSION['keranjang'][$index]['nama_produk'] ?? 'Produk';
$item_size = $_SESSION['keranjang'][$index]['ukuran'] ?? '';

unset($_SESSION['keranjang'][$index]);

$_SESSION['keranjang'] = array_values($_SESSION['keranjang']);
$success_msg = "Produk '" . htmlspecialchars($item_name) . "'";
if (!empty($item_size)) {
    $success_msg .= " ukuran '" . htmlspecialchars($item_size) . "'";
}
$success_msg .= " telah dihapus dari keranjang.";
$_SESSION['success_message'] = $success_msg;
header('Location: keranjang.php');
exit;
?>
