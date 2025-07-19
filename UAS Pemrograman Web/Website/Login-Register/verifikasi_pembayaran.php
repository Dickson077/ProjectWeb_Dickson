<?php
session_start();
include 'config.php'; 

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: login.php"); 
  exit;
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id_penjualan'])) {
  $id_penjualan = (int)$_POST['id_penjualan'];
  $action = $_POST['action']; 

  $koneksi->autocommit(false);
  try {
      if ($action === 'verify') {
          
          $stmt_update_penjualan = $koneksi->prepare("UPDATE penjualan SET status = 'verified' WHERE id = ? AND status = 'pending'");
          if (!$stmt_update_penjualan) {
              throw new Exception("Prepare failed for update penjualan status: " . $koneksi->error);
          }
          $stmt_update_penjualan->bind_param("i", $id_penjualan);
          if (!$stmt_update_penjualan->execute()) {
              throw new Exception("Execute failed for update penjualan status: " . $stmt_update_penjualan->error);
          }
          $stmt_update_penjualan->close();

    
          $stmt_get_details = $koneksi->prepare("SELECT id_produk, ukuran, kuantitas FROM penjualan_detail WHERE id_penjualan = ?");
          if (!$stmt_get_details) {
              throw new Exception("Prepare failed for get penjualan details: " . $koneksi->error);
          }
          $stmt_get_details->bind_param("i", $id_penjualan);
          if (!$stmt_get_details->execute()) {
              throw new Exception("Execute failed for get penjualan details: " . $stmt_get_details->error);
          }
          $result_details = $stmt_get_details->get_result();

          $stmt_update_stock = $koneksi->prepare("UPDATE produk_ukuran SET stok = stok - ? WHERE produk_id = ? AND ukuran = ?");
          if (!$stmt_update_stock) {
              throw new Exception("Prepare failed for update stock: " . $koneksi->error);
          }

          while ($detail = $result_details->fetch_assoc()) {
              $kuantitas = $detail['kuantitas'];
              $id_produk = $detail['id_produk'];
              $ukuran = $detail['ukuran'];

              $stmt_update_stock->bind_param("iis", $kuantitas, $id_produk, $ukuran);
              if (!$stmt_update_stock->execute()) {
                  throw new Exception("Execute failed for stock update for product ID " . $id_produk . ": " . $stmt_update_stock->error);
              }
          }
          $stmt_get_details->close();
          $stmt_update_stock->close();

          $message = "Pesanan #" . str_pad($id_penjualan, 6, '0', STR_PAD_LEFT) . " berhasil diverifikasi dan stok diperbarui.";
          $message_type = 'success';

      } elseif ($action === 'reject') {
          
          $stmt_update_penjualan = $koneksi->prepare("UPDATE penjualan SET status = 'rejected' WHERE id = ? AND status = 'pending'");
          if (!$stmt_update_penjualan) {
              throw new Exception("Prepare failed for update penjualan status: " . $koneksi->error);
          }
          $stmt_update_penjualan->bind_param("i", $id_penjualan);
          if (!$stmt_update_penjualan->execute()) {
              throw new Exception("Execute failed for update penjualan status: " . $stmt_update_penjualan->error);
          }
          $stmt_update_penjualan->close();
          $message = "Pesanan #" . str_pad($id_penjualan, 6, '0', STR_PAD_LEFT) . " berhasil ditolak.";
          $message_type = 'error';
      } else {
          throw new Exception("Aksi tidak valid.");
      }

      if (!$koneksi->commit()) {
          throw new Exception("Commit failed: " . $koneksi->error);
      }
  } catch (Exception $e) {
      $koneksi->rollback();
      $message = "Gagal memproses pesanan: " . $e->getMessage();
      $message_type = 'error';
  } finally {
      $koneksi->autocommit(true);
  }
}

$pending_payments = [];
$query_pending = "SELECT
  pj.id,
  pj.total_harga,
  pj.metode_pembayaran,
  pj.bukti_pembayaran,
  pj.tanggal,
  p.nama AS nama_pelanggan,
  p.email AS email_pelanggan
FROM penjualan pj
JOIN pelanggan p ON pj.id_login = p.id_login
WHERE pj.status = 'pending'
ORDER BY pj.tanggal ASC";

$result_pending = $koneksi->query($query_pending);
if ($result_pending) {
  while ($row = $result_pending->fetch_assoc()) {
      $pending_payments[] = $row;
  }
} else {
  $message = "Error fetching pending payments: " . $koneksi->error;
  $message_type = 'error';
}

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
  <title>Verifikasi Pembayaran Admin - Velton</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <link rel="stylesheet" href="../design/laporan.css"> 
  <link rel="stylesheet" href="../design/verifikasi.css">
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
              <li><a href="laporan_penjualan.php"><i class="fas fa-chart-bar"></i> Laporan Penjualan</a></li>
              <li><a href="verifikasi_pembayaran.php" class="active"><i class="fas fa-check-double"></i> Verifikasi Pembayaran</a></li>
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
              <i class="fas fa-check-double"></i>
              Verifikasi Pembayaran
          </h1>
          <p class="hero-subtitle">
              Konfirmasi atau tolak pembayaran yang tertunda dari pelanggan
          </p>
          <div class="hero-badges">
              <div class="hero-badge">
                  <i class="fas fa-hourglass-half"></i>
                  <span>Pending Orders</span>
              </div>
              <div class="hero-badge">
                  <i class="fas fa-file-invoice-dollar"></i>
                  <span>Payment Proofs</span>
              </div>
              <div class="hero-badge">
                  <i class="fas fa-user-shield"></i>
                  <span>Admin Control</span>
              </div>
          </div>
      </div>
  </div>
  <div class="container-verifikasi">
      <?php if (!empty($message)): ?>
          <div class="message <?= $message_type ?>">
              <?php if ($message_type === 'success'): ?>
                  <i class="fas fa-check-circle"></i>
              <?php else: ?>
                  <i class="fas fa-exclamation-circle"></i>
              <?php endif; ?>
              <span><?= htmlspecialchars($message) ?></span>
          </div>
      <?php endif; ?>

      <h2>Pesanan Menunggu Verifikasi</h2>
      <?php if (empty($pending_payments)): ?>
          <div class="empty-state">
              <div class="empty-icon">
                  <i class="fas fa-clipboard-check"></i>
              </div>
              <h3>Tidak Ada Pesanan Menunggu Verifikasi</h3>
              <p>Semua pembayaran telah diverifikasi atau tidak ada pesanan yang tertunda.</p>
          </div>
      <?php else: ?>
          <?php foreach ($pending_payments as $payment): ?>
          <div class="verifikasi-card">
              <div class="verifikasi-card-header">
                  <h3>Pesanan #<?= str_pad($payment['id'], 6, '0', STR_PAD_LEFT) ?></h3>
                  <span class="date">
                      <i class="fas fa-calendar-alt"></i>
                      <?= date('d M Y, H:i', strtotime($payment['tanggal'])) ?>
                  </span>
              </div>
              <div class="verifikasi-details">
                  <p><strong>Pelanggan:</strong> <?= htmlspecialchars($payment['nama_pelanggan']) ?> (<?= htmlspecialchars($payment['email_pelanggan']) ?>)</p>
                  <p><strong>Total Harga:</strong> Rp <?= number_format($payment['total_harga'], 0, ',', '.') ?></p>
                  <p><strong>Metode Pembayaran:</strong> <?= htmlspecialchars($payment['metode_pembayaran']) ?></p>
                  <p><strong>Status:</strong> <span style="color: orange; font-weight: bold;">Menunggu Verifikasi</span></p>
              </div>
              <div>
                  <?php if (!empty($payment['bukti_pembayaran'])):
                      $file_ext = strtolower(pathinfo($payment['bukti_pembayaran'], PATHINFO_EXTENSION));
                      if (in_array($file_ext, ['jpg', 'jpeg', 'png'])): ?>
                          <img src="<?= htmlspecialchars($payment['bukti_pembayaran']) ?>" alt="Bukti Pembayaran" class="bukti-pembayaran-img">
                      <?php elseif ($file_ext === 'pdf'): ?>
                          <a href="<?= htmlspecialchars($payment['bukti_pembayaran']) ?>" target="_blank" class="bukti-pembayaran-pdf">
                              <i class="fas fa-file-pdf"></i>
                              Lihat PDF
                          </a>
                      <?php else: ?>
                          <p>Tidak ada pratinjau untuk jenis file ini.</p>
                          <a href="<?= htmlspecialchars($payment['bukti_pembayaran']) ?>" target="_blank" class="bukti-pembayaran-link">
                              Unduh Bukti
                          </a>
                      <?php endif; ?>
                  <?php else: ?>
                      <p>Tidak ada bukti pembayaran diunggah.</p>
                  <?php endif; ?>
              </div>
              <div class="verifikasi-actions">
                  <form method="POST" onsubmit="return confirm('Yakin ingin memverifikasi pesanan ini? Stok akan diperbarui.')">
                      <input type="hidden" name="id_penjualan" value="<?= $payment['id'] ?>">
                      <button type="submit" name="action" value="verify" class="btn-verify">
                          <i class="fas fa-check"></i> Verifikasi
                      </button>
                  </form>
                  <form method="POST" onsubmit="return confirm('Yakin ingin menolak pesanan ini? Stok tidak akan diperbarui.')">
                      <input type="hidden" name="id_penjualan" value="<?= $payment['id'] ?>">
                      <button type="submit" name="action" value="reject" class="btn-reject">
                          <i class="fas fa-times"></i> Tolak
                      </button>
                  </form>
              </div>
          </div>
          <?php endforeach; ?>
      <?php endif; ?>
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
