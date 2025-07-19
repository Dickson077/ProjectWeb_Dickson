<?php
session_start();
include 'config.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$ukuran_stok = [];
$total_stok = 0;
$ukuran_result = $koneksi->prepare("SELECT ukuran, stok FROM produk_ukuran WHERE produk_id = ?");
$ukuran_result->bind_param("i", $id);
$ukuran_result->execute();
$result = $ukuran_result->get_result();
while ($row = $result->fetch_assoc()) {
    $ukuran_stok[$row['ukuran']] = $row['stok'];
    $total_stok += $row['stok'];
}
$ukuran_result->close();

$query = $koneksi->prepare("SELECT * FROM produk WHERE id = ?");
$query->bind_param("i", $id);
$query->execute();
$result_produk = $query->get_result();
if ($result_produk->num_rows === 0) {
    echo "<p style='color:red; text-align:center; padding: 2rem;'>Produk tidak ditemukan.</p>";
    echo "<div style='text-align:center;'><a href='produk.php' style='text-decoration:none; color:#3b82f6;'>← Kembali ke Daftar Produk</a></div>";
    exit;
}
$data = $result_produk->fetch_assoc();
$query->close();

$cart_count = array_sum($_SESSION['keranjang'] ?? []);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($data['nama']) ?> - Velton</title>
    <link rel="stylesheet" href="https:verifikasi_pembayarancdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" /> 	
    <link rel="stylesheet" href="../design/deskripsi.css"></head>
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
            <i class="fas fa-info-circle"></i>
            Detail Produk
            <i class="fas fa-shoe-prints"></i>
        </h1>
        <p class="hero-subtitle">
            Lihat informasi lengkap tentang produk pilihan Anda
        </p>
        <div class="hero-badges">
            <div class="hero-badge">
                <i class="fas fa-star"></i>
                <span>High Quality</span>
            </div>
            <div class="hero-badge">
                <i class="fas fa-tag"></i>
                <span>Best Price</span>
            </div>
            <div class="hero-badge">
                <i class="fas fa-box-open"></i>
                <span>Ready Stock</span>
            </div>
        </div>
    </div>
</div>
<div class="container-deskripsi">
    <div class="product-image-wrapper">
        <img src="img/<?= htmlspecialchars($data['gambar']) ?>" alt="<?= htmlspecialchars($data['nama']) ?>">
    </div>
        <div class="product-details-container">
                <p class="product-name"><?= htmlspecialchars($data['nama']) ?></p>
        <p class="product-price">Rp <?= number_format($data['harga'], 0, ',', '.') ?></p>
        <div class="sisa-stok-container">
            <span class="sisa-stok">Sisa Stok Total: <?= $total_stok ?> pcs</span>
            <div class="size-badge-list">
                <?php if (!empty($ukuran_stok)): ?>
                    <?php foreach ($ukuran_stok as $uk => $stok): ?>
                        <span class="size-badge">Ukuran <?= htmlspecialchars($uk) ?>: <?= htmlspecialchars($stok) ?> pcs</span>
                    <?php endforeach; ?>
                <?php else: ?>
                    <span class="size-badge">Tidak ada informasi stok per ukuran.</span>
                <?php endif; ?>
            </div> 	
        </div>
        <p><?= nl2br(htmlspecialchars($data['deskripsi'])) ?></p>
        <p>Mengenai Ukuran: Selisih 1-2 cm mungkin terjadi dikarenakan proses pengembangan dan produksi.</p>
        <p>Mengenai Warna: Warna sesungguhnya mungkin dapat berbeda. Hal ini disebabkan oleh perbedaan cahaya dan resolusi layar.</p>
        <form method="POST" action="tambah_keranjang.php">
            <input type="hidden" name="id_produk" value="<?= htmlspecialchars($id) ?>">
            <label for="ukuran">Pilih Ukuran:</label>
            <select name="ukuran" id="ukuran" required>
                <option value="">-- Pilih Ukuran --</option>
                <?php foreach ($ukuran_stok as $uk => $stok): ?>
                    <?php if ($stok > 0): ?><option value="<?= htmlspecialchars($uk) ?>" data-stok="<?= $stok ?>"><?= htmlspecialchars($uk) ?> (<?= htmlspecialchars($stok) ?> pcs)</option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
            <label for="jumlah">Jumlah:</label>
            <input type="number" name="jumlah" id="jumlah" value="1" min="1" required>
            <button type="submit"><i class="fas fa-shopping-cart"></i> Tambah ke Keranjang</button>
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
                heroHeader.style.transform = `translateY(${scrolled * 0.5}px)`;
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
        document.getElementById('jumlah').addEventListener('change', function() {
            if (this.value < 1) {
                this.value = 1;
            }
        });
        document.addEventListener('DOMContentLoaded', () => {
    const ukuranSelect = document.getElementById('ukuran');
    const jumlahInput = document.getElementById('jumlah');
    function updateMaxJumlah() {
        const selectedOption = ukuranSelect.options[ukuranSelect.selectedIndex];
        const maxStok = parseInt(selectedOption.dataset.stok) || 1;
        jumlahInput.max = maxStok;
        if (parseInt(jumlahInput.value) > maxStok) {
            jumlahInput.value = maxStok;
        }
    }
    ukuranSelect.addEventListener('change', updateMaxJumlah);
    jumlahInput.addEventListener('input', () => {
        const max = parseInt(jumlahInput.max);
        if (parseInt(jumlahInput.value) > max) {
            jumlahInput.value = max;
        }
        if (parseInt(jumlahInput.value) < 1) {
            jumlahInput.value = 1;
        }
    });
});
    });
</script>
</body>
</html>
