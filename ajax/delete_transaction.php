<?php
header('Content-Type: application/json');

require_once '../config/app.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan.']);
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID tidak valid.']);
    exit;
}

$ok = deleteTransaction($id);
echo json_encode([
    'success' => $ok,
    'message' => $ok ? 'Transaksi berhasil dihapus.' : 'Gagal menghapus transaksi.',
]);
