<?php
header('Content-Type: application/json');

require_once '../config/app.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan.']);
    exit;
}

if (guestLimitReached()) {
    echo json_encode([
        'success' => false,
        'message' => 'Batas tamu tercapai. Silakan login atau daftar.',
        'redirect' => '../login.php'
    ]);
    exit;
}

$type   = $_POST['type']   ?? '';
$amount = $_POST['amount'] ?? '';
$date   = $_POST['date']   ?? '';
$desc   = trim($_POST['description'] ?? '');
$cat    = trim($_POST['category']    ?? '');

if (!in_array($type, ['pemasukan', 'pengeluaran'])) {
    echo json_encode(['success' => false, 'message' => 'Tipe transaksi tidak valid.']);
    exit;
}

$amount = preg_replace('/\D/', '', $amount); 
$amount = (float)$amount;

if ($amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Jumlah uang harus lebih dari 0.']);
    exit;
}

if (!$date || !strtotime($date)) {
    echo json_encode(['success' => false, 'message' => 'Tanggal tidak valid.']);
    exit;
}

$ok = saveTransaction([
    'type'        => $type,
    'amount'      => $amount,
    'category'    => $cat    ?: null,
    'description' => $desc   ?: null,
    'date'        => $date,
]);

if ($ok) {
    $label  = $type === 'pemasukan' ? 'Pemasukan' : 'Pengeluaran';
    $remain = getRemainingGuest();
    $msg    = "$label berhasil disimpan!";
    if (!isLoggedIn() && $remain > 0) {
        $msg .= " (Sisa $remain penggunaan tamu)";
    } elseif (!isLoggedIn() && $remain === 0) {
        $msg .= " — Batas tamu tercapai, silakan daftar untuk lanjut.";
    }
    echo json_encode(['success' => true, 'message' => $msg]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan transaksi.']);
}
