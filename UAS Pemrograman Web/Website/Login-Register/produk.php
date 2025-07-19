<?php
session_start();
include 'config.php';
if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = 'pengunjung';
}

$query = "SELECT p.*, SUM(pu.stok) as total_stok_per_produk
           FROM produk p
           LEFT JOIN produk_ukuran pu ON p.id = pu.produk_id
           WHERE p.is_deleted = FALSE
           GROUP BY p.id";
$result = $koneksi->query($query);
$produks = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['gambar'] = basename($row['gambar']);
        $produks[] = $row;
    }
}
$cart_count = 0;
if (isset($_SESSION['keranjang']) && is_array($_SESSION['keranjang'])) {
    foreach ($_SESSION['keranjang'] as $item) {
        if (is_array($item) && isset($item['jumlah'])) {
            $cart_count += $item['jumlah'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk - Velton</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="../design/beranda.css"></head>
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
                <i class="fas fa-store"></i>
                Koleksi Produk Velton
                <i class="fas fa-shoe-prints"></i>
            </h1>
            <p class="hero-subtitle">
                Temukan Berbagai sepatu terbaik dengan gaya dan kenyamanan tak tertandingi hanya di toko kami
            </p>
            <div class="hero-badges">
                <div class="hero-badge">
                    <i class="fas fa-star"></i>
                    <span>Kualitas Premium</span>
                </div>
                <div class="hero-badge">
                    <i class="fas fa-truck"></i>
                    <span>Pengiriman Cepat</span>
                </div>
                <div class="hero-badge">
                    <i class="fas fa-shield-alt"></i>
                    <span>Garansi Resmi</span>
                </div>
            </div>
        </div>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
        <a href="tambah_produk.php" class="btn-add-new-product">
            <i class="fas fa-plus-circle"></i> Tambah Produk Baru
        </a>
        <?php endif; ?>
    </div>
    <div class="container-produk">
        <?php if (empty($produks)): ?>
            <p style="text-align: center; grid-column: 1 / -1; font-size: 1.2rem; color: #64748b;">Belum ada produk yang tersedia.</p>
        <?php else: ?>
            <?php foreach ($produks as $produk): ?>
                <div class="product-card">
                    <div class="product-card-inner">
                        <div class="product-image-container">
                            <img src="<?= htmlspecialchars(file_exists('img/' . $produk['gambar']) ? 'img/' . $produk['gambar'] : '/placeholder.svg?height=250&width=250') ?>" alt="<?= htmlspecialchars($produk['nama']) ?>">
                            <?php if ($produk['total_stok_per_produk'] <= 0): ?>
                                <span class="stock-badge">Stok Kosong</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <a href="deskripsi.php?id=<?= htmlspecialchars($produk['id']) ?>" class="product-name-link">
                                <h3><?= htmlspecialchars($produk['nama']) ?></h3>
                            </a>
                            <div class="price">Rp <?= number_format($produk['harga'], 0, ',', '.') ?></div>
                        </div>
                        <div class="product-actions">
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                                <a href="edit_produk.php?id=<?= htmlspecialchars($produk['id']) ?>" class="btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            <?php else: ?>
                                <?php if ($produk['total_stok_per_produk'] > 0): ?>
                                    <a href="deskripsi.php?id=<?= htmlspecialchars($produk['id']) ?>" class="btn-add-to-cart">
                                        <i class="fas fa-shopping-cart"></i> Beli Sekarang
                                    </a>
                                <?php else: ?>
                                    <button class="btn-stock-out" disabled>
                                        <i class="fas fa-shopping-cart"></i> Stok Habis
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <footer>
        <div class="container-footer">
            <div class="isi-footer">
                <div>
                    <img src="img/logo-velton.jpg" alt="Velton Logo">
                    <h4>Contact</h4>
                    <p><strong>Alamat:</strong> Ruko Mega Legenda Blok B3 No. 15</p>
                    <p><strong>Nomor Telepon:</strong> +62 895 7182 9125</p>
                    <p><strong>Jam Buka:</strong> Setiap hari, pukul 10.00 - 21.00</p>
                    <div class="follow">
                        <h4>Follow Us</h4>
                        <div class="icon">
                            <i class="fab fa-facebook"></i>
                            <i class="fab fa-instagram"></i>
                            <i class="fab fa-twitter"></i>
                        </div>
                    </div>
                </div>
                                <div>
                    <h4>Tentang</h4>
                    <a href="#">Tentang Kami</a>
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms & Condition</a>
                    <a href="#">Contact Us</a>
                </div>
                <div>
                    <h4>Akun</h4>
                    <a href="#">Login</a>
                    <a href="#">Keranjang</a>
                    <a href="#">Help</a>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; Copyright 2025 | Pembuatan Website E-Commerce</p>
            </div>
        </div>
    </footer>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const cards = document.querySelectorAll('.product-card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 100}ms`;
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
