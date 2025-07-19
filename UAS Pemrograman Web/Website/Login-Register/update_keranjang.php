<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: keranjang.php');
    exit;
}

$index = $_POST['index'] ?? null;
$produk_id = $_POST['id_produk'] ?? null;
$ukuran = $_POST['ukuran'] ?? '';
$jumlah_baru = (int)($_POST['jumlah'] ?? 1);

if ($index === null || $produk_id === null || empty($ukuran) || $jumlah_baru < 1) {
    $_SESSION['error_message'] = "Input tidak valid untuk pembaruan keranjang.";
    header('Location: keranjang.php');
    exit;
}

if (!isset($_SESSION['keranjang']) || !is_array($_SESSION['keranjang']) || !isset($_SESSION['keranjang'][$index])) {
    $_SESSION['error_message'] = "Item keranjang tidak ditemukan.";
    header('Location: keranjang.php');
    exit;
}

$item_keranjang = $_SESSION['keranjang'][$index];

if ($item_keranjang['id_produk'] != $produk_id || $item_keranjang['ukuran'] != $ukuran) {
    $_SESSION['error_message'] = "Data item tidak cocok dengan yang ada di keranjang.";
    header('Location: keranjang.php');
    exit;
}

$stmt_stok = $koneksi->prepare("SELECT stok FROM produk_ukuran WHERE produk_id = ? AND ukuran = ?");
$stmt_stok->bind_param("is", $produk_id, $ukuran);
$stmt_stok->execute();
$result_stok = $stmt_stok->get_result();
$stok_data = $result_stok->fetch_assoc();
$stmt_stok->close();

if (!$stok_data) {
    $_SESSION['error_message'] = "Stok produk atau ukuran tidak ditemukan di database.";
    header('Location: keranjang.php');
    exit;
}

$stok_tersedia = $stok_data['stok'];

if ($jumlah_baru > $stok_tersedia) {
    $_SESSION['error_message'] = "Stok untuk " . htmlspecialchars($item_keranjang['nama_produk']) . " ukuran " . htmlspecialchars($ukuran) . " tidak mencukupi. Tersedia: " . $stok_tersedia . " pcs.";
    header('Location: keranjang.php');
    exit;
}

$_SESSION['keranjang'][$index]['jumlah'] = $jumlah_baru;
$_SESSION['success_message'] = "Jumlah produk '" . htmlspecialchars($item_keranjang['nama_produk']) . "' ukuran '" . htmlspecialchars($ukuran) . "' berhasil diperbarui.";

header('Location: keranjang.php');
exit;
?>