<?php
require_once 'config/app.php';
require_once 'includes/auth.php';
if (isLoggedIn()) { header('Location: index.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']    ?? '');
    $pass  = $_POST['password']      ?? '';
    if (!$email || !$pass)
        $error = 'Email dan kata sandi wajib diisi.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $error = 'Format email tidak valid.';
    elseif (!loginUser($email, $pass))
        $error = 'Email atau kata sandi salah.';
    else { header('Location: index.php'); exit; }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Masuk — Sisa Uangku</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="logo-wrap">
                <img src="assets/img/logo.png" alt="Logo" style="height:38px; width:38px; border-radius:10px; object-fit:cover;">
            </div>
            <h1>Sisa Uangku</h1>
            <p>Catatan Keuangan Pribadi</p>
        </div>

    <div class="auth-title">Selamat Datang Kembali 👋</div>

    <?php if ($error): ?>
      <div class="auth-error">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" placeholder="nama@email.com"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
               required autofocus>
      </div>
      <div class="form-group">
        <label>Kata Sandi</label>
        <input type="password" name="password" placeholder="••••••••" required>
      </div>
      <button type="submit" class="submit-btn income-btn">
        🔐 Masuk
      </button>
    </form>

    <div class="or-divider"><span>atau</span></div>
    <a href="index.php" class="guest-link-btn">
      👀 Lanjutkan sebagai Tamu (<?= GUEST_LIMIT ?> transaksi gratis)
    </a>

    <div class="auth-link">
      Belum punya akun? <a href="register.php">Daftar Gratis</a>
    </div>
  </div>
</body>
</html>
