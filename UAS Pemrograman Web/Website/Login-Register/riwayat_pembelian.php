<?php
session_start();
include 'config.php'; 
echo "<!-- Debug: User ID from session: " . ($_SESSION['id_login'] ?? 'Not Set') . " -->";

if (!isset($_SESSION['role'])) {
  $_SESSION['role'] = 'pengunjung';
}

$cart_count = 0;
foreach ($_SESSION['keranjang'] ?? [] as $item_data) {
  if (is_array($item_data) && isset($item_data['jumlah'])) {
      $cart_count += $item_data['jumlah'];
  }
}

$user_id = $_SESSION['id_login'] ?? 0;
$purchase_history = [];

if ($user_id > 0) {

  $stmt_penjualan = $koneksi->prepare("SELECT id, total_harga, metode_pembayaran, status, bukti_pembayaran, tanggal FROM penjualan WHERE id_login = ? ORDER BY tanggal DESC");
  $stmt_penjualan->bind_param("i", $user_id);
  $stmt_penjualan->execute();
  $result_penjualan = $stmt_penjualan->get_result();

  echo "<!-- Debug: Number of purchase history records found: " . $result_penjualan->num_rows . " -->";

  while ($penjualan = $result_penjualan->fetch_assoc()) {
      $id_penjualan = $penjualan['id'];
      $penjualan['items'] = [];

      $stmt_detail = $koneksi->prepare("SELECT pd.kuantitas, pd.subtotal, pd.ukuran, p.nama AS nama_produk, p.gambar AS gambar_produk FROM penjualan_detail pd JOIN produk p ON pd.id_produk = p.id WHERE pd.id_penjualan = ?");
      $stmt_detail->bind_param("i", $id_penjualan);
      $stmt_detail->execute();
      $result_detail = $stmt_detail->get_result();

      while ($item = $result_detail->fetch_assoc()) {
          $item['harga_satuan_item'] = $item['kuantitas'] > 0 ? $item['subtotal'] / $item['kuantitas'] : 0;
          $penjualan['items'][] = $item;
      }
      $stmt_detail->close();
      $purchase_history[] = $penjualan;
  }
  $stmt_penjualan->close();
}

echo "<!-- Debug: Purchase History Array: " . print_r($purchase_history, true) . " -->";

$success_message_from_session = '';
if (isset($_SESSION['success_message'])) {
  $success_message_from_session = $_SESSION['success_message'];
  unset($_SESSION['success_message']);
}

$error_message_from_session = '';
if (isset($_SESSION['error_message'])) {
  $error_message_from_session = $_SESSION['error_message'];
  unset($_SESSION['error_message']);
}

$logo_path = 'img/logo-velton.jpg';
$display_logo_src = file_exists($logo_path) ? htmlspecialchars($logo_path) : '/placeholder.svg?height=48&width=48';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Riwayat Pembelian - Velton</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <link rel="stylesheet" href="../design/riwayat.css">
</head>
<body>
  <div class="navbar">
      <a href="#" class="logo">
          <div class="logo-icon">
              <img src="<?= htmlspecialchars($display_logo_src) ?>" alt="Velton Logo">
          </div>
      </a>
      <div class="menu-utama">
          <ul>
              <li><a href="produk.php"><i class="fas fa-box"></i> Produk</a></li>
              <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
              <li><a href="laporan_penjualan.php"><i class="fas fa-chart-bar"></i> Laporan Penjualan</a></li>
              <li><a href="admin_verifikasi_pembayaran.php"><i class="fas fa-check-double"></i> Verifikasi Pembayaran</a></li>
              <?php endif; ?>
              <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'pelanggan'): ?>
              <li><a href="riwayat_pembelian.php" class="active"><i class="fas fa-history"></i> Riwayat Pembelian</a></li>
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
              <i class="fas fa-history"></i>
              Riwayat Pembelian
              <i class="fas fa-receipt"></i>
          </h1>
          <p class="hero-subtitle">
              Lihat semua transaksi pembelian Anda sebelumnya
          </p>
          <div class="hero-badges">
              <div class="hero-badge">
                  <i class="fas fa-check-circle"></i>
                  <span>Order Tracking</span>
              </div>
              <div class="hero-badge">
                  <i class="fas fa-box-open"></i>
                  <span>Past Orders</span>
              </div>
              <div class="hero-badge">
                  <i class="fas fa-star"></i>
                  <span>Review Products</span>
              </div>
          </div>
      </div>
  </div>
  <div class="container-riwayat">
      <?php if (!empty($success_message_from_session)): ?>
          <div class="message success-message">
              <i class="fas fa-check-circle"></i>
              <span><?= htmlspecialchars($success_message_from_session) ?></span>
          </div>
      <?php endif; ?>
      <?php if (!empty($error_message_from_session)): ?>
          <div class="message error-message">
              <i class="fas fa-exclamation-circle"></i>
              <span><?= htmlspecialchars($error_message_from_session) ?></span>
          </div>
      <?php endif; ?>
      <?php if (empty($purchase_history)): ?>
          <div class="empty-state">
              <div class="empty-state-inner">
                  <div class="empty-icon">
                      <i class="fas fa-box-open"></i>
                  </div>
                  <h3>Belum Ada Riwayat Pembelian</h3>
                  <p>Anda belum melakukan pembelian apapun. Mari mulai berbelanja dan isi riwayat Anda!</p>
                  <a href="produk.php" class="btn-shop">
                      <i class="fas fa-shopping-bag"></i>
                      Mulai Belanja Sekarang
                  </a>
              </div>
          </div>
      <?php else: ?>
          <?php foreach ($purchase_history as $purchase): ?>
          <div class="purchase-card">
              <div class="purchase-card-inner">
                  <div class="purchase-header">
                      <h3>
                          <i class="fas fa-receipt"></i>
                          Pesanan #<?= str_pad($purchase['id'], 6, '0', STR_PAD_LEFT) ?>
                      </h3>
                      <span class="date">
                          <i class="fas fa-calendar-alt"></i>
                          <?= date('d M Y, H:i', strtotime($purchase['tanggal'])) ?>
                      </span>
                      <?php
                          $status_text = '';
                          $status_class = '';
                          switch ($purchase['status']) {
                              case 'pending':
                                  $status_text = 'Belum Dicek oleh Admin';
                                  $status_class = 'pending';
                                  break;
                              case 'verified':
                                  $status_text = 'Berhasil, Pesanan akan Segera Diproses!';
                                  $status_class = 'verified';
                                  break;
                              case 'rejected':
                                  $status_text = 'Ditolak';
                                  $status_class = 'rejected';
                                  break;
                              default:
                                  $status_text = 'Status Tidak Diketahui';
                                  $status_class = '';
                          }
                      ?>
                      <span class="status-badge <?= $status_class ?>">
                          <?php if ($purchase['status'] == 'pending'): ?>
                              <i class="fas fa-hourglass-half"></i>
                          <?php elseif ($purchase['status'] == 'verified'): ?>
                              <i class="fas fa-check-circle"></i>
                          <?php elseif ($purchase['status'] == 'rejected'): ?>
                              <i class="fas fa-times-circle"></i>
                          <?php endif; ?>
                          <?= $status_text ?>
                      </span>
                  </div>
                  <div class="purchase-details">
                      <div class="purchase-detail-row">
                          <span class="label">
                              <i class="fas fa-money-bill-wave"></i>
                              Metode Pembayaran:
                          </span>
                          <span class="value"><?= htmlspecialchars($purchase['metode_pembayaran']) ?></span>
                      </div>
                      <div class="purchase-detail-row total">
                          <span class="label">
                              <i class="fas fa-dollar-sign"></i>
                              Total Pembayaran:
                          </span>
                          <span class="value">Rp <?= number_format($purchase['total_harga'], 0, ',', '.') ?></span>
                      </div>
                      <?php if ($purchase['metode_pembayaran'] === 'Transfer' && !empty($purchase['bukti_pembayaran'])): ?>
                          <div class="purchase-detail-row">
                              <span class="label">
                                  <i class="fas fa-image"></i>
                                  Bukti Pembayaran:
                              </span>
                              <span class="value">
                                  <a href="<?= htmlspecialchars($purchase['bukti_pembayaran']) ?>" target="_blank" class="bukti-pembayaran-link">
                                      Lihat Bukti
                                  </a>
                              </span>
                          </div>
                      <?php endif; ?>
                  </div>
                  <div class="purchase-items-list">
                      <h4>
                          <i class="fas fa-boxes"></i>
                          Detail Produk:
                      </h4>
                      <?php foreach ($purchase['items'] as $item):
                          $item_image_path = 'img/' . basename($item['gambar_produk']);
                          $display_item_image_src = file_exists($item_image_path) ? $item_image_path : '/placeholder.svg?height=90&width=90';
                      ?>
                      <div class="purchase-item">
                          <img src="<?= htmlspecialchars($display_item_image_src) ?>" alt="<?= htmlspecialchars($item['nama_produk']) ?>">
                          <div class="item-info">
                              <div class="name"><?= htmlspecialchars($item['nama_produk']) ?></div>
                              <div class="details">
                                  Ukuran: <?= htmlspecialchars($item['ukuran']) ?> | Jumlah: <?= htmlspecialchars($item['kuantitas']) ?> pcs
                              </div>
                              <div class="price">Rp <?= number_format($item['harga_satuan_item'], 0, ',', '.') ?> / pcs</div>
                          </div>
                      </div>
                      <?php endforeach; ?>
                  </div>
              </div>
          </div>
          <?php endforeach; ?>
      <?php endif; ?>
      <a href="produk.php" class="kembali">
          <i class="fas fa-arrow-left"></i>
          Kembali ke Produk
      </a>
  </div>
  <script>
      document.addEventListener('DOMContentLoaded', () => {
          const cards = document.querySelectorAll('.purchase-card');
          cards.forEach((card, index) => {
              card.style.animationDelay = `${index * 150}ms`;
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
