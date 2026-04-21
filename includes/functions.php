<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/auth.php';
function rupiah(float $amount): string {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function getTransactions(int $year, int $month): array {
    $db = getDB();
    if (isLoggedIn()) {
        $stmt = $db->prepare(
            "SELECT * FROM tabel_transactions
             WHERE user_id = ? AND YEAR(date) = ? AND MONTH(date) = ?
             ORDER BY date DESC, created_at DESC"
        );
        $stmt->execute([$_SESSION['user_id'], $year, $month]);
    } else {
        $token = getGuestToken();
        $stmt = $db->prepare(
            "SELECT * FROM tabel_transactions
             WHERE guest_token = ? AND YEAR(date) = ? AND MONTH(date) = ?
             ORDER BY date DESC, created_at DESC"
        );
        $stmt->execute([$token, $year, $month]);
    }
    return $stmt->fetchAll();
}

function getMonthlySummary(int $year, int $month): array {
    $transactions = getTransactions($year, $month);
    $pemasukan = 0;
    $pengeluaran = 0;
    foreach ($transactions as $t) {
        if ($t['type'] === 'pemasukan') $pemasukan += (float)$t['amount'];
        else $pengeluaran += (float)$t['amount'];
    }
    return [
        'pemasukan'   => $pemasukan,
        'pengeluaran' => $pengeluaran,
        'saldo'       => $pemasukan - $pengeluaran,
        'transactions'=> $transactions,
    ];
}

function saveTransaction(array $data): bool {
    $db = getDB();
    $userId    = isLoggedIn() ? $_SESSION['user_id'] : null;
    $guestToken = isLoggedIn() ? null : getGuestToken();

    $stmt = $db->prepare(
        "INSERT INTO tabel_transactions
         (user_id, guest_token, type, amount, category, description, date)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $ok = $stmt->execute([
        $userId,
        $guestToken,
        $data['type'],
        $data['amount'],
        $data['category'] ?? null,
        $data['description'] ?? null,
        $data['date'],
    ]);
    if ($ok && !isLoggedIn()) {
        incrementGuestCount();
    }
    return $ok;
}

function deleteTransaction(int $id): bool {
    $db = getDB();
    if (isLoggedIn()) {
        $stmt = $db->prepare("DELETE FROM tabel_transactions WHERE id = ? AND user_id = ?");
        return $stmt->execute([$id, $_SESSION['user_id']]);
    } else {
        $token = getGuestToken();
        $stmt = $db->prepare("DELETE FROM tabel_transactions WHERE id = ? AND guest_token = ?");
        return $stmt->execute([$id, $token]);
    }
}

function getCategories(string $type): array {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM tabel_categories WHERE type = ? ORDER BY is_default DESC, name ASC");
    $stmt->execute([$type]);
    return $stmt->fetchAll();
}

function namaBulan(int $m): string {
    $bulan = ['','Januari','Februari','Maret','April','Mei','Juni',
              'Juli','Agustus','September','Oktober','November','Desember'];
    return $bulan[$m] ?? '';
}

function groupByDate(array $transactions): array {
    $grouped = [];
    foreach ($transactions as $t) {
        $grouped[$t['date']][] = $t;
    }
    return $grouped;
}
