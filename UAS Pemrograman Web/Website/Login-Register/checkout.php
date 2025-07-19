<?php
session_start();
include 'config.php'; 
error_reporting(E_ALL);
ini_set('display_errors', 1); 
ini_set('log_errors', 1);
ini_set('error_log', 'checkout_errors.log');

if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = 'pengunjung';
}

if (!isset($_SESSION['id_login'])) {
    header("Location: login.php?redirect=checkout.php");
    exit;
}
$keranjang = $_SESSION['keranjang'] ?? [];

if (empty($keranjang)) {
    header("Location: produk.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['metode'])) {
    $metode = $_POST['metode'];
    $id_login = $_SESSION['id_login'] ?? 0;
    $bukti_pembayaran_path = NULL;
    $status_pesanan = 'pending'; 

    echo "Debug: Memulai proses checkout untuk ID Login: " . $id_login . "<br>";
    echo "Debug: Keranjang: " . print_r($keranjang, true) . "<br>";

    if ($id_login <= 0 || empty($keranjang)) {
        $errorMessage = "Terjadi kesalahan saat memproses pesanan. ID Login tidak valid atau keranjang kosong.";
        $_SESSION['error_message'] = $errorMessage;
        echo "Debug: Error sebelum transaksi: " . $errorMessage . "<br>"; 
        header("Location: checkout.php");
        exit;
    }

    if ($metode === 'Transfer' && isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_name = $_FILES['bukti_pembayaran']['tmp_name'];
        $file_name = $_FILES['bukti_pembayaran']['name'];
        $file_size = $_FILES['bukti_pembayaran']['size'];
        $file_type = $_FILES['bukti_pembayaran']['type'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed_ext = ['jpg', 'jpeg', 'png', 'pdf'];
        $max_file_size = 5 * 1024 * 1024; 

        if (!in_array($file_ext, $allowed_ext)) {
            $errorMessage = "Format file tidak diizinkan. Hanya JPG, JPEG, PNG, dan PDF yang diizinkan.";
            $_SESSION['error_message'] = $errorMessage;
            echo "Debug: Error upload file (format): " . $errorMessage . "<br>"; 
            header("Location: checkout.php");
            exit;
        }

        if ($file_size > $max_file_size) {
            $errorMessage = "Ukuran file terlalu besar. Maksimal 5 MB.";
            $_SESSION['error_message'] = $errorMessage;
            echo "Debug: Error upload file (ukuran): " . $errorMessage . "<br>"; 
            header("Location: checkout.php");
            exit;
        }

        $upload_dir = 'uploads/bukti_pembayaran/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true); 
        }

        $new_file_name = uniqid('proof_', true) . '.' . $file_ext;
        $destination = $upload_dir . $new_file_name;

        if (move_uploaded_file($file_tmp_name, $destination)) {
            $bukti_pembayaran_path = $destination;
            $status_pesanan = 'pending';
            echo "Debug: Bukti pembayaran berhasil diunggah ke: " . $bukti_pembayaran_path . "<br>";
        } else {
            $errorMessage = "Gagal mengunggah bukti pembayaran.";
            $_SESSION['error_message'] = $errorMessage;
            echo "Debug: Error upload file (gagal move): " . $errorMessage . "<br>"; 
            header("Location: checkout.php");
            exit;
        }
    } elseif ($metode === 'Transfer') {
        $errorMessage = "Bukti pembayaran wajib diunggah untuk metode Transfer Bank.";
        $_SESSION['error_message'] = $errorMessage;
        echo "Debug: Error upload file (tidak ada file): " . $errorMessage . "<br>"; 
        header("Location: checkout.php");
        exit;
    } else {

        $status_pesanan = 'verified';
        echo "Debug: Metode COD, status langsung 'verified'.<br>";
    }

    $koneksi->autocommit(false);
    echo "Debug: Autocommit dinonaktifkan, memulai transaksi.<br>";
    try {
        $total = 0;
        foreach ($keranjang as $item) {
            if (!is_array($item) || !isset($item['harga_satuan']) || !isset($item['jumlah'])) {
                throw new Exception("Item keranjang tidak valid: " . print_r($item, true));
            }
            $total += $item['harga_satuan'] * $item['jumlah'];
        }
        echo "Debug: Total harga dihitung: " . $total . "<br>";

        $stmt_penjualan = $koneksi->prepare("INSERT INTO penjualan (id_login, total_harga, metode_pembayaran, status, bukti_pembayaran, tanggal) VALUES (?, ?, ?, ?, ?, NOW())");
        if (!$stmt_penjualan) {
            throw new Exception("Prepare failed for penjualan: " . $koneksi->error);
        }
        echo "Debug: Prepare statement penjualan berhasil.<br>";
        $stmt_penjualan->bind_param("idsss", $id_login, $total, $metode, $status_pesanan, $bukti_pembayaran_path);
        if (!$stmt_penjualan->execute()) {
            throw new Exception("Execute failed for penjualan: " . $stmt_penjualan->error);
        }
        $id_penjualan = $koneksi->insert_id;
        $stmt_penjualan->close();
        echo "Debug: Data penjualan berhasil dimasukkan, ID Penjualan: " . $id_penjualan . "<br>";

        $stmt_detail = $koneksi->prepare("INSERT INTO penjualan_detail (id_penjualan, id_produk, ukuran, kuantitas, subtotal) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt_detail) {
            throw new Exception("Prepare failed for penjualan_detail: " . $koneksi->error);
        }
        echo "Debug: Prepare statement penjualan_detail berhasil.<br>";

        foreach ($keranjang as $item) {
            echo "Debug: Memproses item keranjang: " . htmlspecialchars($item['nama_produk'] ?? 'N/A') . "<br>";
            if (!isset($item['id_produk']) || !isset($item['ukuran']) || !isset($item['jumlah']) || !isset($item['nama_produk'])) {
                throw new Exception("Item produk tidak lengkap untuk update stok: " . print_r($item, true));
            }

            $stmt_check = $koneksi->prepare("SELECT stok FROM produk_ukuran WHERE produk_id = ? AND ukuran = ? FOR UPDATE");
            if (!$stmt_check) {
                throw new Exception("Prepare failed for stock check: " . $koneksi->error);
            }
            $stmt_check->bind_param("is", $item['id_produk'], $item['ukuran']);
            if (!$stmt_check->execute()) {
                throw new Exception("Execute failed for stock check: " . $stmt_check->error);
            }
            $result_check = $stmt_check->get_result();
            $stock_data = $result_check->fetch_assoc();
            $stmt_check->close();

            $current_stock = $stock_data['stok'] ?? 0;
            echo "Debug: Stok saat ini untuk " . htmlspecialchars($item['nama_produk']) . " ukuran " . htmlspecialchars($item['ukuran']) . ": " . $current_stock . ", Diminta: " . $item['jumlah'] . "<br>";
            if (!$stock_data || $current_stock < $item['jumlah']) {
                throw new Exception("Stok tidak mencukupi untuk produk " . htmlspecialchars($item['nama_produk']) . " ukuran " . htmlspecialchars($item['ukuran']) . ". Stok tersedia: " . $current_stock . ", Diminta: " . $item['jumlah']);
            }

            $subtotal = $item['harga_satuan'] * $item['jumlah'];
            $stmt_detail->bind_param("iisid", $id_penjualan, $item['id_produk'], $item['ukuran'], $item['jumlah'], $subtotal);
            if (!$stmt_detail->execute()) {
                throw new Exception("Execute failed for penjualan_detail: " . $stmt_detail->error);
            }
            echo "Debug: Detail penjualan untuk produk " . htmlspecialchars($item['nama_produk']) . " berhasil dimasukkan.<br>";

            if ($status_pesanan === 'verified') {
                $stmt_update = $koneksi->prepare("UPDATE produk_ukuran SET stok = stok - ? WHERE produk_id = ? AND ukuran = ?");
                if (!$stmt_update) {
                    throw new Exception("Prepare failed for stock update: " . $koneksi->error);
                }
                $stmt_update->bind_param("iis", $item['jumlah'], $item['id_produk'], $item['ukuran']);
                if (!$stmt_update->execute()) {
                    throw new Exception("Execute failed for stock update: " . $stmt_update->error);
                }
                $affected_rows = $stmt_update->affected_rows;
                if ($affected_rows === 0) {
                    throw new Exception("No rows affected in stock update for product " . htmlspecialchars($item['nama_produk']) . " size " . htmlspecialchars($item['ukuran']) . ". Pastikan produk dan ukuran ada.");
                }
                $stmt_update->close();
                echo "Debug: Stok untuk produk " . htmlspecialchars($item['nama_produk']) . " ukuran " . htmlspecialchars($item['ukuran']) . " berhasil diperbarui.<br>";
            }
        }
        $stmt_detail->close();

        if (!$koneksi->commit()) {
            throw new Exception("Commit failed: " . $koneksi->error);
        }
        echo "Debug: Transaksi berhasil di-commit.<br>";

        $_SESSION['keranjang'] = [];
        if ($status_pesanan === 'pending') {
            $_SESSION['success_message'] = "Pesanan Anda telah dibuat! Menunggu verifikasi pembayaran oleh admin.";
        } else {
            $_SESSION['success_message'] = "Pesanan Anda telah dikonfirmasi! Stok telah diperbarui.";
        }
        echo "Debug: Redirecting to riwayat_pembelian.php with success message.<br>";
        header("Location: riwayat_pembelian.php"); 
        exit;
    } catch (Exception $e) {
        $koneksi->rollback();
        $errorMessage = "Checkout gagal: " . $e->getMessage();
        $_SESSION['error_message'] = $errorMessage;
        echo "Debug: Exception caught, rolling back: " . $errorMessage . "<br>"; 
        header("Location: checkout.php");
        exit;
    } finally {
        $koneksi->autocommit(true);
        echo "Debug: Autocommit diaktifkan kembali.<br>";
    }
}

$totalBayar = 0;
foreach ($keranjang as $item) {
    if (is_array($item) && isset($item['harga_satuan']) && isset($item['jumlah'])) {
        $totalBayar += $item['harga_satuan'] * $item['jumlah'];
    }
}

$cart_count = 0;
foreach ($_SESSION['keranjang'] ?? [] as $item) {
    if (is_array($item) && isset($item['jumlah'])) {
        $cart_count += $item['jumlah'];
    }
}

$error_message_from_session = '';
if (isset($_SESSION['error_message'])) {
    $error_message_from_session = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout - Velton</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <link rel="stylesheet" href="../design/checkout.css">
</head>
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
              <?php if ($_SESSION['role'] == 'admin'): ?>
              <li><a href="laporan_penjualan.php"><i class="fas fa-chart-bar"></i> Laporan Penjualan</a></li>
              <li><a href="admin_verifikasi_pembayaran.php"><i class="fas fa-check-double"></i> Verifikasi Pembayaran</a></li>
              <?php endif; ?>
              <?php if ($_SESSION['role'] == 'pelanggan'): ?>
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
              <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'pelanggan'): ?>
              <li><a href="logout.php" onclick="return confirm('Yakin ingin logout?')"><i class="fas fa-sign-out-alt"></i></a></li>
              <?php endif; ?>
          </ul>
      </div>
  </div>
  <div class="hero-header">
      <div class="hero-content">
          <h1 class="hero-title">
              <i class="fas fa-credit-card"></i>
              Checkout Pesanan
              <i class="fas fa-check-circle"></i>
          </h1>
          <p class="hero-subtitle">
              Konfirmasi pesanan Anda dan pilih metode pembayaran yang diinginkan
          </p>
          <div class="hero-badges">
              <div class="hero-badge">
                  <i class="fas fa-shield-alt"></i>
                  <span>Secure Payment</span>
              </div>
              <div class="hero-badge">
                  <i class="fas fa-truck"></i>
                  <span>Fast Delivery</span>
              </div>
              <div class="hero-badge">
                  <i class="fas fa-headset"></i>
                  <span>24/7 Support</span>
              </div>
          </div>
      </div>
  </div>
  <div class="container-checkout">
      <?php if (!empty($error_message_from_session)): ?>
          <div class="message error-message">
              <i class="fas fa-exclamation-circle"></i>
              <span><?= htmlspecialchars($error_message_from_session) ?></span>
          </div>
      <?php endif; ?>
      <div class="checkout-items-list">
          <?php foreach ($keranjang as $item):
              if (!is_array($item)) continue;
              $subtotal = $item['harga_satuan'] * $item['jumlah'];
              $image_path = 'img/' . basename($item['gambar_produk'] ?? '');
              $display_image_src = file_exists($image_path) ? htmlspecialchars($image_path) : '/placeholder.svg?height=120&width=120';
          ?>
          <div class="checkout-item-card">
              <div class="checkout-item-inner">
                  <div class="checkout-item-header">
                      <img src="<?= $display_image_src ?>" alt="<?= htmlspecialchars($item['nama_produk'] ?? 'Produk') ?>" class="item-image-checkout">
                      <div class="item-details-checkout">
                          <div class="item-name-checkout"><?= htmlspecialchars($item['nama_produk'] ?? 'Produk') ?></div>
                          <?php if (!empty($item['ukuran'])): ?>
                          <div class="item-size-checkout">
                              <i class="fas fa-ruler"></i> Ukuran: <?= htmlspecialchars($item['ukuran']) ?>
                          </div>
                          <?php endif; ?>
                          <div class="item-qty-checkout">
                              <i class="fas fa-sort-numeric-up"></i> Kuantitas: <?= $item['jumlah'] ?>
                          </div>
                          <div class="item-price-checkout">Rp <?= number_format($item['harga_satuan'], 0, ',', '.') ?></div>
                      </div>
                  </div>
                  <div class="item-subtotal-checkout">
                      <i class="fas fa-calculator"></i>
                      Subtotal: Rp <?= number_format($subtotal, 0, ',', '.') ?>
                  </div>
              </div>
          </div>
          <?php endforeach; ?>
      </div>
      <div class="form-checkout-container">
          <div class="form-checkout-inner">
              <h3>
                  <i class="fas fa-credit-card"></i>
                  Metode Pembayaran
              </h3>
              <form method="POST" action="checkout.php" class="form-checkout" onsubmit="return confirmOrder()" enctype="multipart/form-data">
                  <label for="metode">
                      <i class="fas fa-money-bill-wave"></i> Pilih Metode Pembayaran:
                  </label>
                  <select name="metode" id="metode" required onchange="togglePaymentProof()">
                      <option value="">-- Pilih Metode Pembayaran --</option>
                      <option value="COD">💰 COD (Bayar di Tempat)</option>
                      <option value="Transfer">🏦 Transfer Bank</option>
                  </select>

                  <div id="paymentProofSection" style="display: none;">
                      <div class="bank-info">
                          <h4><i class="fas fa-university"></i> Informasi Rekening Bank</h4>
                          <p>Silakan transfer ke rekening berikut:</p>
                          <p>Bank: <strong>BCA</strong></p>
                          <p>Nomor Rekening: <strong>1234567890</strong></p>
                          <p>Atas Nama: <strong>PT. Velton E-commerce</strong></p>
                          <p>Jumlah yang harus dibayar: <strong>Rp <?= number_format($totalBayar, 0, ',', '.') ?></strong></p>
                      </div>
                      <div class="file-upload-container">
                          <label for="bukti_pembayaran"><i class="fas fa-upload"></i> Unggah Bukti Pembayaran:</label>
                          <input type="file" name="bukti_pembayaran" id="bukti_pembayaran" accept="image/*,application/pdf">
                          <small>Format: JPG, JPEG, PNG, PDF. Maksimal 5MB.</small>
                      </div>
                  </div>

                  <div class="total-section">
                      <div class="total-bayar">
                          <span>
                              <i class="fas fa-receipt"></i>
                              Total Pembayaran:
                          </span>
                          <span>Rp <?= number_format($totalBayar, 0, ',', '.') ?></span>
                      </div>
                  </div>
                  <button type="submit" class="btn-simpan">
                      <i class="fas fa-check-circle"></i>
                      Konfirmasi Pesanan
                  </button>
              </form>
          </div>
      </div>
      <a href="keranjang.php" class="kembali">
          <i class="fas fa-arrow-left"></i>
          Kembali ke Keranjang
      </a>
  </div>
  <script>
      document.addEventListener('DOMContentLoaded', () => {
          const items = document.querySelectorAll('.checkout-item-card');
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
          togglePaymentProof();
      });

      function togglePaymentProof() {
          const metodeSelect = document.getElementById('metode');
          const paymentProofSection = document.getElementById('paymentProofSection');
          const buktiPembayaranInput = document.getElementById('bukti_pembayaran');

          if (metodeSelect.value === 'Transfer') {
              paymentProofSection.style.display = 'block';
              buktiPembayaranInput.setAttribute('required', 'required');
          } else {
              paymentProofSection.style.display = 'none';
              buktiPembayaranInput.removeAttribute('required');
              buktiPembayaranInput.value = '';
          }
      }

      function confirmOrder() {
          const totalPaymentElement = document.querySelector('.total-bayar span:last-child');
          const totalPayment = totalPaymentElement ? totalPaymentElement.textContent : 'Rp 0';
          const paymentMethodElement = document.getElementById('metode');
          const paymentMethod = paymentMethodElement ? paymentMethodElement.options[paymentMethodElement.selectedIndex].text : 'Tidak diketahui';
          const confirmationMessage = `Anda akan membuat pesanan dengan total ${totalPayment} menggunakan metode pembayaran ${paymentMethod}. Lanjutkan?`;

          if (paymentMethodElement.value === 'Transfer') {
              const buktiPembayaranInput = document.getElementById('bukti_pembayaran');
              if (!buktiPembayaranInput.files.length) {
                  alert("Mohon unggah bukti pembayaran untuk metode Transfer Bank.");
                  return false;
              }
          }

          return confirm(confirmationMessage);
      }

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
  </script>
</body>
</html>
