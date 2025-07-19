<?php
session_start();
include 'config.php';
if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = 'pengunjung';
}
$keranjang = $_SESSION['keranjang'] ?? [];
$totalBayar = 0;
$cart_count = 0;
foreach ($keranjang as $item) {
    if (is_array($item) && isset($item['harga_satuan']) && isset($item['jumlah'])) {
        $totalBayar += $item['harga_satuan'] * $item['jumlah'];
        $cart_count += $item['jumlah'];
    }
}
if (isset($_GET['action']) && $_GET['action'] == 'hapus' && isset($_GET['id_produk']) && isset($_GET['ukuran'])) {
    $id_produk_hapus = $_GET['id_produk'];
    $ukuran_hapus = $_GET['ukuran'];
    foreach ($keranjang as $key => $item) {
        if ($item['id_produk'] == $id_produk_hapus && $item['ukuran'] == $ukuran_hapus) {
            unset($keranjang[$key]);
            break;
        }
    }
    $_SESSION['keranjang'] = array_values($keranjang);
    header("Location: keranjang.php");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_qty'])) {
    $id_produk_update = $_POST['id_produk'];
    $ukuran_update = $_POST['ukuran'];
    $new_qty = (int)$_POST['new_qty'];
    foreach ($keranjang as $key => $item) {
        if ($item['id_produk'] == $id_produk_update && $item['ukuran'] == $ukuran_update) {
            if ($new_qty > 0) {
                $stmt_stok = $koneksi->prepare("SELECT stok FROM produk_ukuran WHERE produk_id = ? AND ukuran = ?");
                $stmt_stok->bind_param("is", $id_produk_update, $ukuran_update);
                $stmt_stok->execute();
                $result_stok = $stmt_stok->get_result();
                $stok_data = $result_stok->fetch_assoc();
                $stmt_stok->close();
                $stok_tersedia = $stok_data['stok'] ?? 0;
                if ($new_qty <= $stok_tersedia) {
                    $keranjang[$key]['jumlah'] = $new_qty;
                    $_SESSION['success_message'] = "Kuantitas produk berhasil diperbarui.";
                } else {
                    $_SESSION['error_message'] = "Stok tidak mencukupi untuk kuantitas yang diminta. Stok tersedia: " . $stok_tersedia;
                }
            } else {
                unset($keranjang[$key]);
                $_SESSION['success_message'] = "Produk berhasil dihapus dari keranjang.";
            }
            break;
        }
    }
    $_SESSION['keranjang'] = array_values($keranjang);
    header("Location: keranjang.php");
    exit;
}
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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Velton</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="../design/keranjang.css"></head>
<body>
    <div class="navbar">
        <a href="#" class="logo">
            <div class="logo-icon">
                <img src="img/logo-velton.jpg" alt="Velton Logo">
            </div>
        </a>
        <div class="menu-utama">
            <ul>
                <li><a href="produk.php"><i class="fas fa-box"></i> Produk</a></li>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                <li><a href="laporan_penjualan.php"><i class="fas fa-chart-bar"></i> Laporan Penjualan</a></li>
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
                    <a href="keranjang.php" class="active"><i class="fas fa-shopping-cart"></i></a>
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
                <i class="fas fa-shopping-cart"></i>
                Keranjang Belanja Anda
                <i class="fas fa-basket-shopping"></i>
            </h1>
            <p class="hero-subtitle">
                Periksa kembali item pilihan Anda sebelum melanjutkan ke pembayaran
            </p>
            <div class="hero-badges">
                <div class="hero-badge">
                    <i class="fas fa-check-circle"></i>
                    <span>Review Pesanan</span>
                </div>
                <div class="hero-badge">
                    <i class="fas fa-truck-fast"></i>
                    <span>Siap Dikirim</span>
                </div>
            </div>
        </div>
    </div>
    <div class="container-keranjang">
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
        <?php if (empty($keranjang)): ?>
            <div class="empty-cart-message">
                <i class="fas fa-box-open"></i>
                <p>Keranjang belanja Anda kosong.</p>
                <a href="produk.php" class="btn-shop-now">
                    <i class="fas fa-arrow-left"></i> Mulai Belanja Sekarang
                </a>
            </div>
        <?php else: ?>
            <div class="keranjang-items-list">
                <?php foreach ($keranjang as $key => $item):
                    $subtotal_item = $item['harga_satuan'] * $item['jumlah'];
                    $image_path = 'img/' . basename($item['gambar_produk'] ?? '');
                    $display_image_src = file_exists($image_path) ? htmlspecialchars($image_path) : '/placeholder.svg?height=120&width=120';
                ?>
                <div class="keranjang-item-card">
                    <div class="keranjang-item-inner">
                        <div class="item-header">
                            <img src="<?= $display_image_src ?>" alt="<?= htmlspecialchars($item['nama_produk'] ?? 'Produk') ?>" class="item-image">
                            <div class="item-details">
                                <div class="item-name"><?= htmlspecialchars($item['nama_produk'] ?? 'Produk') ?></div>
                                <?php if (!empty($item['ukuran'])): ?>
                                <div class="item-size">
                                    <i class="fas fa-ruler"></i> Ukuran: <?= htmlspecialchars($item['ukuran']) ?>
                                </div>
                                <?php endif; ?>
                                <div class="item-price">Rp <?= number_format($item['harga_satuan'], 0, ',', '.') ?></div>
                            </div>
                        </div>
                        <div class="item-actions">
                            <form action="keranjang.php" method="POST" class="update-qty-form">
                                <input type="hidden" name="id_produk" value="<?= htmlspecialchars($item['id_produk']) ?>">
                                <input type="hidden" name="ukuran" value="<?= htmlspecialchars($item['ukuran']) ?>">
                                <label for="qty_<?= $key ?>">Kuantitas:</label>
                                <input type="number" id="qty_<?= $key ?>" name="new_qty" value="<?= htmlspecialchars($item['jumlah']) ?>" min="1" class="qty-input">
                                <button type="submit" name="update_qty" class="btn-update-qty">
                                    <i class="fas fa-sync-alt"></i> Update
                                </button>
                            </form>
                            <a href="keranjang.php?action=hapus&id_produk=<?= htmlspecialchars($item['id_produk']) ?>&ukuran=<?= htmlspecialchars($item['ukuran']) ?>" class="btn-hapus" onclick="return confirm('Yakin ingin menghapus produk ini dari keranjang?')">
                                <i class="fas fa-trash-alt"></i> Hapus
                            </a>
                        </div>
                        <div class="item-subtotal">
                            <i class="fas fa-calculator"></i>
                            Subtotal: Rp <?= number_format($subtotal_item, 0, ',', '.') ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="keranjang-summary">
                <div class="total-keranjang">
                    <span>Total Belanja:</span>
                    <span>Rp <?= number_format($totalBayar, 0, ',', '.') ?></span>
                </div>
                <a href="checkout.php" class="btn-checkout">
                    <i class="fas fa-credit-card"></i> Lanjutkan ke Checkout
                </a>
                <a href="produk.php" class="kembali-belanja">
                    <i class="fas fa-arrow-left"></i> Lanjutkan Belanja
                </a>
            </div>
        <?php endif; ?>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const items = document.querySelectorAll('.keranjang-item-card');
            items.forEach((item, index) => {
                item.style.animationDelay = `${index * 100}ms`;
            });
            window.addEventListener('scroll', () => {
                const scrolled = window.pageYOffset;
                const heroHeader = document.querySelector('.hero-header');
                if (heroHeader) {
                    heroHeader.style.transform = `translateY(${scrolled * 0.5}px)`;
                }
            });
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
            document.querySelectorAll('.icon i').forEach(icon => {
                icon.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-3px) scale(1.1) rotate(5deg)';
                });
                icon.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1) rotate(0deg)';
                });
            });
        });
    </script>
</body>
</html>
