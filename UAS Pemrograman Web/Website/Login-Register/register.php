<?php
session_start();
include 'config.php';
$success = '';
$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama     = $_POST['nama'] ?? '';
    $email    = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $no_telp  = $_POST['no_telp'] ?? '';
    $alamat   = $_POST['alamat'] ?? '';
    if (empty($nama) || empty($email) || empty($password) || empty($no_telp) || empty($alamat)) {
        $error = "Semua kolom harus diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } else {
        $stmt_check = $koneksi->prepare("SELECT id_login FROM pelanggan WHERE email = ?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $stmt_check->store_result();
        if ($stmt_check->num_rows > 0) {
            $error = "Email ini sudah terdaftar. Silakan gunakan email lain atau login.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'pelanggan';
            $stmt_insert = $koneksi->prepare("INSERT INTO pelanggan (nama, email, password, no_telp, alamat, role) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_insert->bind_param("ssssss", $nama, $email, $hashed_password, $no_telp, $alamat, $role);
            if ($stmt_insert->execute()) {
                $success = "Registrasi berhasil. <a href='login.php'>Login di sini</a>";
            } else {
                $error = "❌ Error: " . $stmt_insert->error;
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    }
}
$cart_count = array_sum($_SESSION['keranjang'] ?? []);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Velton</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="../design/register.css"></head>
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
                <li><a href="laporan_penjualan.php"><i class="fas fa-chart-bar"></i> Laporan</a></li>
                <?php endif; ?>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'pelanggan'): ?>
                <li><a href="riwayat_pembelian.php"><i class="fas fa-history"></i> Riwayat</a></li>
                <?php endif; ?>
            </ul>
        </div>
        <div class="menu-kanan">
            <ul>
                <li><a href="login.php"><i class="fas fa-user"></i></a></li>
                <li class="cart-badge">
                    <a href="keranjang.php"><i class="fas fa-shopping-cart"></i></a>
                    <?php if ($cart_count > 0): ?>
                    <span class="cart-count" id="cartCount"><?= $cart_count ?></span>
                    <?php endif; ?>
                </li>
                <?php if (isset($_SESSION['role']) && ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'pelanggan')): ?>
                <li><a href="logout.php" onclick="return confirm('Yakin ingin logout?')"><i class="fas fa-sign-out-alt"></i></a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    <div class="main-content">
        <div class="register-container">
            <h2>Register</h2>
            <?php if (!empty($success)) : ?>
            <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if (!empty($error)) : ?>
            <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="nama">Nama:</label>
                    <input type="text" id="nama" name="nama" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="no_telp">No. Telp:</label>
                    <input type="text" id="no_telp" name="no_telp" required>
                </div>
                <div class="form-group">
                    <label for="alamat">Alamat:</label>
                    <textarea id="alamat" name="alamat" required></textarea>
                </div>
                <button type="submit" class="btn-register">
                    <i class="fas fa-user-plus"></i> Register
                </button>
            </form>
            <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
        </div>
    </div>
    <script>
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
