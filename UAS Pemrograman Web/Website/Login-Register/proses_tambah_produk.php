<?php
session_start();
include 'config.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'] ?? '';
    $harga_beli = floatval($_POST['harga_beli'] ?? 0);
    $harga_jual = floatval($_POST['harga_jual'] ?? 0);
    $deskripsi = $_POST['deskripsi'] ?? '';
    $stok_ukuran = $_POST['stok_ukuran'] ?? [];
    if (empty($nama) || $harga_beli <= 0 || $harga_jual <= 0 || empty($stok_ukuran) || !isset($_FILES['gambar'])) {
        $_SESSION['error_message'] = "Semua kolom wajib diisi dan harga harus lebih dari 0. Pastikan Anda menambahkan stok untuk setidaknya satu ukuran.";
        header("Location: tambah_produk.php");
        exit;
    }
    foreach ($stok_ukuran as $ukuran => $stok) {
        if (!is_numeric($stok) || $stok < 0) {
            $_SESSION['error_message'] = "Stok untuk ukuran " . htmlspecialchars($ukuran) . " harus berupa angka non-negatif.";
            header("Location: tambah_produk.php");
            exit;
        }
    }
    $target_dir = "img/";
    $gambar_nama = basename($_FILES["gambar"]["name"]);
    $target_file = $target_dir . $gambar_nama;
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $check = getimagesize($_FILES["gambar"]["tmp_name"]);
    if ($check !== false) {
        $uploadOk = 1;
    } else {
        $_SESSION['error_message'] = "File bukan gambar.";
        $uploadOk = 0;
    }
    if (file_exists($target_file)) {
        $gambar_nama = pathinfo($gambar_nama, PATHINFO_FILENAME) . '_' . time() . '.' . $imageFileType;
        $target_file = $target_dir . $gambar_nama;
    }
    if ($_FILES["gambar"]["size"] > 5000000) {
        $_SESSION['error_message'] = "Ukuran file terlalu besar. Maksimal 5MB.";
        $uploadOk = 0;
    }
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
        $_SESSION['error_message'] = "Hanya file JPG, JPEG, PNG & GIF yang diizinkan.";
        $uploadOk = 0;
    }
    if ($uploadOk == 0) {
        header("Location: tambah_produk.php");
        exit;
    } else {
        if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
            $koneksi->begin_transaction();
            try {
                $stmt_produk = $koneksi->prepare("INSERT INTO produk (nama, harga_beli, harga, gambar, deskripsi) VALUES (?, ?, ?, ?, ?)");
              
                $stmt_produk->bind_param("sddss", $nama, $harga_beli, $harga_jual, $gambar_nama, $deskripsi);
                $stmt_produk->execute();
                $produk_id = $koneksi->insert_id;
                $stmt_ukuran = $koneksi->prepare("INSERT INTO produk_ukuran (produk_id, ukuran, stok) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE stok = VALUES(stok)");
                foreach ($stok_ukuran as $ukuran => $stok) {
                    $stmt_ukuran->bind_param("isi", $produk_id, $ukuran, $stok);
                    $stmt_ukuran->execute();
                }
                $koneksi->commit();
                $_SESSION['success_message'] = "Produk baru berhasil ditambahkan!";
                header("Location: produk.php");
                exit;
            } catch (Exception $e) {
                $koneksi->rollback();
                if (file_exists($target_file)) {
                    unlink($target_file);
                }
                $_SESSION['error_message'] = "Gagal menambahkan produk: " . $e->getMessage();
                header("Location: tambah_produk.php");
                exit;
            }
        } else {
            $_SESSION['error_message'] = "Maaf, terjadi kesalahan saat mengunggah file Anda.";
            header("Location: tambah_produk.php");
            exit;
        }
    }
} else {
    header("Location: tambah_produk.php");
    exit;
}
?>
