<?php
require_once 'config/app.php';
require_once 'includes/auth.php';
if (isLoggedIn()) { header('Location: index.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name']     ?? '');
    $email = trim($_POST['email']    ?? '');
    $pass  = $_POST['password']      ?? '';
    $pass2 = $_POST['password2']     ?? '';
    if (!$name || !$email || !$pass)
        $error = 'Semua field wajib diisi.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $error = 'Format email tidak valid.';
    elseif (strlen($pass) < 6)
        $error = 'Kata sandi minimal 6 karakter.';
    elseif ($pass !== $pass2)
        $error = 'Konfirmasi kata sandi tidak cocok.';
    else {
        $result = registerUser($name, $email, $pass);
        if ($result === true) { header('Location: index.php'); exit; }
        else $error = $result;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar — Sisa Uangku</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">
      <div class="logo-wrap">💰</div>
      <h1>Sisa Uangku</h1>
      <p>Buat akun gratis sekarang!</p>
    </div>

    <div class="auth-title">Daftar Akun Baru ✨</div>

    <?php if ($error): ?>
      <div class="auth-error">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="form-group">
        <label>Nama Lengkap</label>
        <input type="text" name="name" placeholder="Nama kamu"
               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
               required autofocus>
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" placeholder="nama@email.com"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
      </div>
      <div class="form-group">
        <label>Kata Sandi</label>
        <input type="password" name="password" placeholder="Minimal 6 karakter" required>
      </div>
      <div class="form-group">
        <label>Konfirmasi Kata Sandi</label>
        <input type="password" name="password2" placeholder="Ulangi kata sandi" required>
      </div>
      <button type="submit" class="submit-btn income-btn">
        🚀 Daftar Sekarang — Gratis!
      </button>
    </form>

    <div class="auth-link">Sudah punya akun? <a href="login.php">Masuk di sini</a></div>
    <div class="or-divider"><span>atau</span></div>
    <a href="index.php" class="guest-link-btn">
      👀 Lanjutkan sebagai Tamu (<?= GUEST_LIMIT ?> transaksi gratis)
    </a>
  </div>
</body>
</html>
