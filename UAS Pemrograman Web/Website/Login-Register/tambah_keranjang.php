<?php
session_start();
include 'config.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_produk = (int)($_POST['id_produk'] ?? 0); 
    $ukuran = trim($_POST['ukuran'] ?? ''); 
    $jumlah = (int)($_POST['jumlah'] ?? 1); 


    if ($id_produk <= 0 || empty($ukuran) || $jumlah <= 0) {
        $_SESSION['error_message'] = "Data produk tidak lengkap atau tidak valid. Pastikan produk, ukuran, dan jumlah dipilih dengan benar.";
        header('Location: keranjang.php');
        exit();
    }

    $stmt = $koneksi->prepare("SELECT p.nama, p.gambar, p.harga, pu.stok FROM produk p JOIN produk_ukuran pu ON p.id = pu.produk_id WHERE p.id = ? AND pu.ukuran = ?");
    $stmt->bind_param("is", $id_produk, $ukuran);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    if ($product) {
        $nama_produk = $product['nama'];
        $gambar_produk = $product['gambar'];
        $harga_satuan = $product['harga'];
        $stok_tersedia = $product['stok'];

        if (!isset($_SESSION['keranjang'])) {
            $_SESSION['keranjang'] = [];
        }

        $item_found = false;
        foreach ($_SESSION['keranjang'] as $key => $item) {
            if (is_array($item) && isset($item['id_produk']) && isset($item['ukuran']) && $item['id_produk'] == $id_produk && $item['ukuran'] == $ukuran) {
                $new_quantity = $item['jumlah'] + $jumlah;
                if ($new_quantity <= $stok_tersedia) {
                    $_SESSION['keranjang'][$key]['jumlah'] = $new_quantity;
                    $_SESSION['success_message'] = "Jumlah produk '" . htmlspecialchars($nama_produk) . "' ukuran '" . htmlspecialchars($ukuran) . "' berhasil diperbarui di keranjang!";
                } else {
                    $_SESSION['error_message'] = "Stok tidak cukup untuk menambahkan '" . htmlspecialchars($nama_produk) . "' ukuran '" . htmlspecialchars($ukuran) . "'. Stok tersedia: " . $stok_tersedia . " pcs.";
                }
                $item_found = true;
                break;
            }
        }

        if (!$item_found) {
            if ($jumlah <= $stok_tersedia) {
                $_SESSION['keranjang'][] = [
                    'id_produk' => $id_produk,
                    'nama_produk' => $nama_produk,
                    'gambar_produk' => $gambar_produk,
                    'ukuran' => $ukuran,
                    'harga_satuan' => $harga_satuan,
                    'jumlah' => $jumlah,
                    'stok_tersedia' => $stok_tersedia 
                ];
                $_SESSION['success_message'] = "Produk '" . htmlspecialchars($nama_produk) . "' ukuran '" . htmlspecialchars($ukuran) . "' berhasil ditambahkan ke keranjang!";
            } else {
                $_SESSION['error_message'] = "Stok tidak cukup untuk menambahkan '" . htmlspecialchars($nama_produk) . "' ukuran '" . htmlspecialchars($ukuran) . "'. Stok tersedia: " . $stok_tersedia . " pcs.";
            }
        }
    } else {
        $_SESSION['error_message'] = "Produk atau ukuran tidak ditemukan dalam database.";
    }
} else {
    $_SESSION['error_message'] = "Metode request tidak valid.";
}

header('Location: keranjang.php'); 
exit();
?>
