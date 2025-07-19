<?php
session_start();
include 'config.php';
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    if (empty($email) || empty($password)) {
        $error_message = "Email dan password harus diisi.";
    } else {
        $stmt = $koneksi->prepare("SELECT id_login, nama, email, password, role FROM pelanggan WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['id_login'] = $user['id_login'];
            $_SESSION['username'] = $user['nama'];
            $_SESSION['role'] = $user['role'];
                        $redirect_url = $_GET['redirect'] ?? 'produk.php';
            header("Location: " . $redirect_url);
            exit;
        } else {
            $error_message = "Email atau password salah.";
        }
    }
}
$cart_count = array_sum($_SESSION['keranjang'] ?? []);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Velton</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="../design/login.css"></head>
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
                <li><a href="login.php" class="active"><i class="fas fa-user"></i></a></li>
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
        <div class="login-container">
            <h2>Login</h2>
            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>
            <form action="login.php<?= isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : '' ?>" method="POST">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
            <a href="register.php" class="register-link">Belum punya akun? Daftar di sini.</a>
        </div>
    </div>
    <script>
        function updateMainCartCount(count) {
            const cartBadge = document.querySelector('.cart-badge');
            if (!cartBadge) return;
            let cartCountSpan = cartBadge.querySelector('.cart-count');
                        if (count > 0) {
                if (!cartCountSpan) {
                    cartCountSpan = document.createElement('span');
                    cartCountSpan.className = 'cart-count';
                    cartCountSpan.id = 'cartCount';
                    cartBadge.appendChild(cartCountSpan);
                }
                cartCountSpan.textContent = count;
                cartCountSpan.style.animation = 'bounce 0.5s ease';
            } else if (cartCountSpan) {
                cartCountSpan.remove();
            }
        }
        document.addEventListener('DOMContentLoaded', () => {
            const initialCartCount = <?= $cart_count ?>;
            updateMainCartCount(initialCartCount);
        });
    </script>
</body>
</html>
