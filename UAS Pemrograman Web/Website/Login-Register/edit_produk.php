<?php
session_start();
include 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$id_produk = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$produk = null;
$ukuran_stok = [];
$total_stok_produk = 0;
$success_message = '';
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
$error_message = '';
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_product') {
    if ($id_produk <= 0) {
        $_SESSION['error_message'] = "ID produk tidak valid untuk penghapusan.";
        header("Location: produk.php");
        exit;
    }

    $koneksi->begin_transaction();
    try {
       
        $stmt_soft_delete_produk = $koneksi->prepare("UPDATE produk SET is_deleted = TRUE WHERE id = ?");
        if (!$stmt_soft_delete_produk) {
            throw new Exception("Prepare failed for soft delete produk: " . $koneksi->error);
        }
        $stmt_soft_delete_produk->bind_param("i", $id_produk);
        if (!$stmt_soft_delete_produk->execute()) {
            throw new Exception("Execute failed for soft delete produk: " . $stmt_soft_delete_produk->error);
        }
        $stmt_soft_delete_produk->close();

        $koneksi->commit();
        $_SESSION['success_message'] = "Produk berhasil dihapus (soft delete)! Riwayat penjualan tetap terjaga.";
        header("Location: produk.php");
        exit;

    } catch (Exception $e) {
        $koneksi->rollback();
        $_SESSION['error_message'] = "Gagal menghapus produk: " . $e->getMessage();
        header("Location: edit_produk.php?id=" . $id_produk); 
        exit;
    }
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($id_produk <= 0) {
        $_SESSION['error_message'] = "ID produk tidak valid untuk pembaruan.";
        header("Location: produk.php");
        exit;
    }
    $nama = $_POST['nama'] ?? '';
    $harga_beli = floatval($_POST['harga_beli'] ?? 0);
    $harga_jual = floatval($_POST['harga_jual'] ?? 0);
    $deskripsi = $_POST['deskripsi'] ?? '';
    $stok_ukuran = $_POST['stok_ukuran'] ?? [];
    $gambar_lama = $_POST['gambar_lama'] ?? '';
    $gambar_baru = $gambar_lama;

    if (empty($nama) || $harga_beli <= 0 || $harga_jual <= 0 || empty($stok_ukuran)) {
        $_SESSION['error_message'] = "Semua kolom wajib diisi dan harga harus lebih dari 0. Pastikan Anda menambahkan stok untuk setidaknya satu ukuran.";
    } else {
        foreach ($stok_ukuran as $ukuran => $stok) {
            if (!is_numeric($stok) || $stok < 0) {
                $_SESSION['error_message'] = "Stok untuk ukuran " . htmlspecialchars($ukuran) . " harus berupa angka non-negatif.";
                break;
            }
        }
    }

    if (empty($_SESSION['error_message'])) {
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $target_dir = "img/";
            $gambar_nama_file = basename($_FILES["gambar"]["name"]);
            $target_file = $target_dir . $gambar_nama_file;
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
                $gambar_nama_file = pathinfo($gambar_nama_file, PATHINFO_FILENAME) . '_' . time() . '.' . $imageFileType;
                $target_file = $target_dir . $gambar_nama_file;
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
            } else {
                if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
                    $gambar_baru = basename($gambar_nama_file); 
                    if ($gambar_lama && $gambar_lama !== $gambar_baru && file_exists($target_dir . $gambar_lama) && $gambar_lama !== 'default-product.jpg') {
                        unlink($target_dir . $gambar_lama);
                    }
                } else {
                    $_SESSION['error_message'] = "Maaf, terjadi kesalahan saat mengunggah file Anda.";
                }
            }
        }
    }

    if (empty($_SESSION['error_message'])) {
        $koneksi->begin_transaction();
        try {
            $stmt_produk = $koneksi->prepare("UPDATE produk SET nama = ?, harga_beli = ?, harga = ?, gambar = ?, deskripsi = ? WHERE id = ?");
            $stmt_produk->bind_param("sddssi", $nama, $harga_beli, $harga_jual, $gambar_baru, $deskripsi, $id_produk);
            $stmt_produk->execute();
            $stmt_ukuran = $koneksi->prepare("INSERT INTO produk_ukuran (produk_id, ukuran, stok) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE stok = VALUES(stok)");
            foreach ($stok_ukuran as $ukuran => $stok) {
                $stmt_ukuran->bind_param("isi", $id_produk, $ukuran, $stok);
                $stmt_ukuran->execute();
            }
            $koneksi->commit();
            $_SESSION['success_message'] = "Produk berhasil diperbarui!";
            header("Location: produk.php");
            exit;
        } catch (Exception $e) {
            $koneksi->rollback();
            if (isset($target_file) && file_exists($target_file) && $target_file !== $gambar_lama) {
                unlink($target_file);
            }
            $_SESSION['error_message'] = "Gagal memperbarui produk: " . $e->getMessage();
        }
    }
}

if ($id_produk > 0) {
    $stmt_produk = $koneksi->prepare("SELECT id, nama, harga_beli, harga, deskripsi, gambar FROM produk WHERE id = ?");
    $stmt_produk->bind_param("i", $id_produk);
    $stmt_produk->execute();
    $result_produk = $stmt_produk->get_result();
    $produk = $result_produk->fetch_assoc();
    if ($produk && strpos($produk['gambar'], 'img/') === 0) {
        $produk['gambar'] = basename($produk['gambar']);
    }
    $stmt_produk->close();

    if (!$produk) {
        $_SESSION['error_message'] = "Produk tidak ditemukan.";
        header("Location: produk.php");
        exit;
    }

    $stmt_ukuran = $koneksi->prepare("SELECT ukuran, stok FROM produk_ukuran WHERE produk_id = ?");
    $stmt_ukuran->bind_param("i", $id_produk);
    $stmt_ukuran->execute();
    $result_ukuran = $stmt_ukuran->get_result();
    while ($row = $result_ukuran->fetch_assoc()) {
        $ukuran_stok[$row['ukuran']] = $row['stok'];
        $total_stok_produk += $row['stok'];
    }
    $stmt_ukuran->close();
} else {
    $_SESSION['error_message'] = "ID produk tidak valid.";
    header("Location: produk.php");
    exit;
}

$cart_count = array_sum($_SESSION['keranjang'] ?? []);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - Velton Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="../design/edit-style.css"></head>
<body>
    <div class="navbar">
        <a href="#" class="logo">
            <div class="logo-icon">
                <img src="img/logo-velton.jpg" alt="Velton Logo">
            </div>
        </a>
        <div class="menu-utama">
            <ul>
                <li><a href="produk.php" class="active"><i class="fas fa-box"></i> Produk</a></li>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                <li><a href="laporan_penjualan.php"><i class="fas fa-chart-bar"></i> Laporan Penjualan</a></li>
                <li><a href="verifikasi_pembayaran.php"><i class="fas fa-check-double"></i> Verifikasi Pembayaran</a></li>
                <?php endif; ?>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'pelanggan'): ?>
                <li><a href="riwayat_pembelian.php"><i class="fas fa-history"></i> Riwayat Pembelian</a></li>
                <?php endif; ?>
            </ul>
        </div>
                <div class="menu-kanan">
            <ul>
                <li><a href="login.php"><i class="fas fa-user"></i></a></li>
                <li>
                    <a href="keranjang.php"><i class="fas fa-shopping-cart"></i></a>
                    <?php if ($cart_count > 0): ?>
                    <span class="cart-count"><?= $cart_count ?></span>
                    <?php endif; ?>
                </li>
                <?php if (isset($_SESSION['role']) && ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'pelanggan')): ?>
                <li><a href="logout.php" onclick="return confirm('Yakin ingin logout?')"><i class="fas fa-sign-out-alt"></i></a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    <div class="hero-header">
        <div class="hero-content">
            <h1 class="hero-title">
                <i class="fas fa-edit"></i>
                Edit Produk
                <i class="fas fa-box-open"></i>
            </h1>
            <p class="hero-subtitle">
                Perbarui detail produk yang sudah ada di toko Anda
            </p>
            <div class="hero-badges">
                <div class="hero-badge">
                    <i class="fas fa-cubes"></i>
                    <span>Manajemen Stok</span>
                </div>
                <div class="hero-badge">
                    <i class="fas fa-upload"></i>
                    <span>Unggah Gambar</span>
                </div>
            </div>
        </div>
    </div>
    <div class="form-container">
        <div class="form-inner">
            <h2>Form Edit Produk</h2>
            <?php if (!empty($success_message)): ?>
                <div class="message success-message">
                    <i class="fas fa-check-circle"></i>
                    <span><?= htmlspecialchars($success_message) ?></span>
                </div>
            <?php endif; ?>
            <?php if (!empty($error_message)): ?>
                <div class="message error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?= htmlspecialchars($error_message) ?></span>
                </div>
            <?php endif; ?>
            <form action="edit_produk.php?id=<?= $id_produk ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="gambar_lama" value="<?= htmlspecialchars($produk['gambar']) ?>">
                                <div class="form-group">
                    <label for="nama">Nama Produk:</label>
                    <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($produk['nama']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="harga_beli">Harga Beli:</label>
                    <input type="number" id="harga_beli" name="harga_beli" min="0" step="1000" value="<?= htmlspecialchars($produk['harga_beli']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="harga_jual">Harga Jual:</label>
                    <input type="number" id="harga_jual" name="harga_jual" min="0" step="1000" value="<?= htmlspecialchars($produk['harga']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="stok_total">Stok Tersedia:</label>
                    <input type="number" id="stok_total" name="stok_total" min="0" readonly value="<?= $total_stok_produk ?>">
                </div>
                <div class="size-stock-section">
                    <h3>Stok Per Ukuran:</h3>
                    <div class="size-stock-group">
                        <div class="form-group">
                            <label for="stok_38">Ukuran 38:</label>
                            <input type="number" name="stok_ukuran[38]" id="stok_38" min="0" placeholder="Stok ukuran 38" value="<?= htmlspecialchars($ukuran_stok['38'] ?? 0) ?>">
                        </div>
                    </div>
                    <div class="size-stock-group">
                        <div class="form-group">
                            <label for="stok_39">Ukuran 39:</label>
                            <input type="number" name="stok_ukuran[39]" id="stok_39" min="0" placeholder="Stok ukuran 39" value="<?= htmlspecialchars($ukuran_stok['39'] ?? 0) ?>">
                        </div>
                    </div>
                    <div class="size-stock-group">
                        <div class="form-group">
                            <label for="stok_40">Ukuran 40:</label>
                            <input type="number" name="stok_ukuran[40]" id="stok_40" min="0" placeholder="Stok ukuran 40" value="<?= htmlspecialchars($ukuran_stok['40'] ?? 0) ?>">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="deskripsi">Deskripsi Produk:</label>
                    <textarea id="deskripsi" name="deskripsi" rows="5"><?= htmlspecialchars($produk['deskripsi']) ?></textarea>
                </div>
                <div class="form-group">
                    <label for="gambar">Gambar Produk:</label>
                    <input type="file" id="gambar" name="gambar" accept="image/*">
                    <?php if ($produk['gambar']): ?>
                        <div class="current-image-preview">
                            <p>Gambar Saat Ini:</p>
                            <img src="img/<?= htmlspecialchars($produk['gambar']) ?>" alt="Current Product Image">
                        </div>
                    <?php endif; ?>
                </div>
                                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </form>
            <form action="edit_produk.php?id=<?= $id_produk ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini? Produk akan disembunyikan dari daftar produk, tetapi riwayat penjualan akan tetap ada.')" style="margin-top: 20px;">
                <input type="hidden" name="action" value="delete_product">
                <button type="submit" class="btn-delete">
                    <i class="fas fa-trash-alt"></i> Hapus Produk
                </button>
            </form>
            <a href="produk.php" class="kembali">
                <i class="fas fa-arrow-left"></i> Kembali ke Daftar Produk
            </a>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.addEventListener('scroll', () => {
                const scrolled = window.pageYOffset;
                const heroHeader = document.querySelector('.hero-header');
                if (heroHeader) {
                    heroHeader.style.transform = `translate3d(0, ${scrolled * 0.5}px, 0)`;
                }
            });
            document.querySelectorAll('.icon i').forEach(icon => {
                icon.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-3px) scale(1.1) rotate(5deg)';
                });
                                icon.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1) rotate(0deg)';
                });
            });
            function calculateTotalStock() {
                let total = 0;
                const sizes = ['38', '39', '40'];
                sizes.forEach(size => {
                    const input = document.getElementById(`stok_${size}`);
                    if (input) {
                        total += parseInt(input.value) || 0;
                    }
                });
                document.getElementById('stok_total').value = total;
            }
            const sizeInputs = document.querySelectorAll('input[name^="stok_ukuran"]');
            sizeInputs.forEach(input => {
                input.addEventListener('input', calculateTotalStock);
            });
            calculateTotalStock();
        });
    </script>
</body>
</html>
