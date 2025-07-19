<?php
session_start();
include 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$cart_count = array_sum($_SESSION['keranjang'] ?? []);

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
    <title>Tambah Produk - Velton Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="../design/tambah-produk.css"></head>
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
                <i class="fas fa-plus-square"></i>
                Tambah Produk Baru
                <i class="fas fa-box-open"></i>
            </h1>
            <p class="hero-subtitle">
                Isi detail produk baru yang akan ditambahkan ke toko Anda
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
            <h2>Form Tambah Produk</h2>
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
            <form action="proses_tambah_produk.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="nama">Nama Produk:</label>
                    <input type="text" id="nama" name="nama" required>
                </div>
                <div class="form-group">
                    <label for="harga_beli">Harga Beli:</label>
                    <input type="number" id="harga_beli" name="harga_beli" min="0" step="1000" required>
                </div>
                <div class="form-group">
                    <label for="harga_jual">Harga Jual:</label>
                    <input type="number" id="harga_jual" name="harga_jual" min="0" step="1000" required>
                </div>
                <div class="form-group">
                    <label for="stok_total">Stok Total:</label>
                    <input type="number" id="stok_total" name="stok_total" min="0" readonly>
                </div>
                <div class="size-stock-section">
                    <h3>Stok Per Ukuran:</h3>
                    <div class="size-stock-group">
                        <div class="form-group">
                            <label for="stok_38">Ukuran 38:</label>
                            <input type="number" name="stok_ukuran[38]" id="stok_38" min="0" placeholder="Stok ukuran 38" value="0">
                        </div>
                    </div>
                    <div class="size-stock-group">
                        <div class="form-group">
                            <label for="stok_39">Ukuran 39:</label>
                            <input type="number" name="stok_ukuran[39]" id="stok_39" min="0" placeholder="Stok ukuran 39" value="0">
                        </div>
                    </div>
                    <div class="size-stock-group">
                        <div class="form-group">
                            <label for="stok_40">Ukuran 40:</label>
                            <input type="number" name="stok_ukuran[40]" id="stok_40" min="0" placeholder="Stok ukuran 40" value="0">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="deskripsi">Deskripsi Produk:</label>
                    <textarea id="deskripsi" name="deskripsi" rows="5"></textarea>
                </div>
                <div class="form-group">
                    <label for="gambar">Gambar Produk:</label>
                    <input type="file" id="gambar" name="gambar" accept="image/*" required>
                </div>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Simpan Produk
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
