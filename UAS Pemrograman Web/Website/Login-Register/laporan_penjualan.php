<?php
session_start();
include 'config.php'; 
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: login.php"); 
}

$tanggal_awal = $_GET['tanggal_awal'] ?? '';
$tanggal_akhir = $_GET['tanggal_akhir'] ?? '';
$filter_sql = " WHERE pj.status = 'verified'"; 
$params = [];
$types = '';

if ($tanggal_awal && $tanggal_akhir) {
  $filter_sql .= " AND DATE(pj.tanggal) BETWEEN ? AND ?";
  $params = [$tanggal_awal, $tanggal_akhir];
  $types = 'ss';
} elseif ($tanggal_awal) {
  $filter_sql .= " AND DATE(pj.tanggal) >= ?";
  $params = [$tanggal_awal];
  $types = 's';
} elseif ($tanggal_akhir) {
  $filter_sql .= " AND DATE(pj.tanggal) <= ?";
  $params = [$tanggal_akhir];
  $types = 's';
}

$query = "SELECT
  pr.nama AS nama_produk,
  pd.ukuran,
  SUM(pd.kuantitas) AS total_kuantitas,
  pr.harga AS harga_satuan,
  SUM(pd.subtotal) AS total_pendapatan
FROM penjualan_detail pd
JOIN produk pr ON pd.id_produk = pr.id
JOIN penjualan pj ON pd.id_penjualan = pj.id
$filter_sql
GROUP BY pd.id_produk, pd.ukuran, pr.harga
ORDER BY total_pendapatan DESC";

$stmt = $koneksi->prepare($query);
if ($stmt === false) {
  die('Prepare failed: ' . htmlspecialchars($koneksi->error));
}

if (!empty($params)) {
  $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$penjualan = [];
$total_kuantitas = 0;
$total_harga = 0;

while ($row = $result->fetch_assoc()) {
  $penjualan[] = $row;
  $total_kuantitas += $row['total_kuantitas'];
  $total_harga += $row['total_pendapatan'];
}
$stmt->close();

$logo_path = 'img/logo-velton.jpg';
$display_logo_src = file_exists($logo_path) ? htmlspecialchars($logo_path) : '/placeholder.svg?height=48&width=48';

$cart_count = 0; 
foreach ($_SESSION['keranjang'] ?? [] as $item_data) {
  if (is_array($item_data) && isset($item_data['jumlah'])) {
      $cart_count += $item_data['jumlah'];
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Laporan Penjualan - Velton</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <link rel="stylesheet" href="../design/laporan.css">
</head>
<body>
<div class="navbar">
  <a href="#" class="logo">
      <div class="logo-icon">
          <img src="<?= $display_logo_src ?>" alt="Velton Logo">
      </div>
  </a>
  <div class="menu-utama">
      <ul>
          <li><a href="produk.php"><i class="fas fa-box"></i> Produk</a></li>
          <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
          <li><a href="laporan_penjualan.php" class="active"><i class="fas fa-chart-bar"></i> Laporan Penjualan</a></li>
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
          <i class="fas fa-chart-line"></i>
          Laporan Penjualan
      </h1>
      <p class="hero-subtitle">
          Analisis performa penjualan produk berdasarkan periode waktu
      </p>
      <div class="hero-badges">
          <div class="hero-badge">
              <i class="fas fa-calendar-alt"></i>
              <span>Filter Tanggal</span>
          </div>
          <div class="hero-badge">
              <i class="fas fa-boxes"></i>
              <span>Total Kuantitas</span>
          </div>
          <div class="hero-badge">
              <i class="fas fa-money-bill-wave"></i>
              <span>Total Pendapatan</span>
          </div>
      </div>
  </div>
</div>
<div class="container-laporan">
  <h2>Laporan Penjualan Produk</h2>
  <form method="GET" class="form-tanggal">
      <div class="form-row">
          <div class="form-group">
              <label for="tanggal_awal">Dari Tanggal:</label>
              <input type="date" id="tanggal_awal" name="tanggal_awal" value="<?= htmlspecialchars($tanggal_awal) ?>">
          </div>
          <div class="form-group">
              <label for="tanggal_akhir">Sampai Tanggal:</label>
              <input type="date" id="tanggal_akhir" name="tanggal_akhir" value="<?= htmlspecialchars($tanggal_akhir) ?>">
          </div>
      </div>
      <div class="form-submit">
          <button type="submit">
              <i class="fas fa-filter"></i> Tampilkan Laporan
          </button>
      </div>
  </form>
  <div class="overflow-x-auto">
      <table class="tabel-laporan">
          <thead>
              <tr>
                  <th>Nama Produk</th>
                  <th>Ukuran</th>
                  <th>Kuantitas</th>
                  <th>Harga Satuan</th>
                  <th>Total Pendapatan</th>
              </tr>
          </thead>
          <tbody>
              <?php if (!empty($penjualan)): ?>
                  <?php foreach ($penjualan as $row): ?>
                  <tr>
                      <td data-label="Nama Produk"><?= htmlspecialchars($row['nama_produk']) ?></td>
                      <td data-label="Ukuran"><?= htmlspecialchars($row['ukuran']) ?></td>
                      <td data-label="Kuantitas"><?= $row['total_kuantitas'] ?></td>
                      <td data-label="Harga Satuan">Rp <?= number_format($row['harga_satuan'], 0, ',', '.') ?></td>
                      <td data-label="Total Pendapatan">Rp <?= number_format($row['total_pendapatan'], 0, ',', '.') ?></td>
                  </tr>
                  <?php endforeach; ?>
              <?php else: ?>
                  <tr>
                      <td colspan="5" style="text-align: center; color: #64748b;">Tidak ada data penjualan untuk periode ini.</td>
                  </tr>
              <?php endif; ?>
          </tbody>
          <tfoot>
              <tr>
                  <th colspan="2" style="text-align: right;">Total Keseluruhan:</th>
                  <th><?= $total_kuantitas ?></th>
                  <th>-</th>
                  <th>Rp <?= number_format($total_harga, 0, ',', '.') ?></th>
              </tr>
          </tfoot>
      </table>
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
  });
</script>
</body>
</html>
