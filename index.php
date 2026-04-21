<?php
require_once 'config/app.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$year  = (int)($_GET['y'] ?? date('Y'));
$month = (int)($_GET['m'] ?? date('n'));
if ($month < 1)  { $month = 12; $year--; }
if ($month > 12) { $month = 1;  $year++; }

$summary      = getMonthlySummary($year, $month);
$grouped      = groupByDate($summary['transactions']);
$cats_masuk   = getCategories('pemasukan');
$cats_keluar  = getCategories('pengeluaran');
$user         = getCurrentUser();
$limitReached = guestLimitReached();
$remaining    = getRemainingGuest();
$bulan        = namaBulan($month);
$initial      = $user ? strtoupper(substr($user['name'], 0, 1)) : 'T';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sisa Uangku — Catatan Keuangan Pribadi</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="app-layout">
  <aside class="sidebar">
        <div class="sidebar-logo">
            <img src="assets/img/logo.png" alt="Logo" style="height:38px; width:38px; border-radius:10px; object-fit:cover;">
        </div>
        <a href="index.php" class="sidebar-icon active" title="Dashboard">🏠</a>
    </aside>

  <div class="main-area">
    <header class="topbar">
      <div class="month-group">
        <button class="month-arrow-btn" id="btn-prev-month" title="Bulan sebelumnya">&#8592;</button>

        <button class="month-trigger" onclick="openMonthPicker()" title="Klik untuk pilih bulan &amp; tahun">
          <span class="year-text"><?= $year ?></span>
          <span class="month-text"><?= $bulan ?></span>
        </button>

        <button class="month-arrow-btn" id="btn-next-month" title="Bulan berikutnya">&#8594;</button>
      </div>

      <div class="topbar-right">
        <?php if ($user): ?>
          <div class="user-chip">
            <div class="avatar"><?= $initial ?></div>
            <?= htmlspecialchars(explode(' ', $user['name'])[0]) ?>
          </div>
          <a href="logout.php" class="btn-top danger">Keluar</a>
        <?php else: ?>
          <a href="login.php"    class="btn-top outline">Masuk</a>
          <a href="register.php" class="btn-top solid">Daftar Gratis</a>
        <?php endif; ?>
      </div>
    </header>

    <?php if (!$user && !$limitReached): ?>
    <div class="guest-banner">
      🎉 Mode Tamu — Sisa gratis:
      <span class="count-pill"><?= $remaining ?>/<?= GUEST_LIMIT ?></span>
      <a href="register.php">Daftar sekarang</a> untuk penggunaan tak terbatas!
    </div>
    <?php elseif ($limitReached): ?>
    <div class="limit-banner">
      ⚠️ Batas tamu tercapai!
      <a href="login.php">Masuk</a> atau
      <a href="register.php">Daftar Gratis</a> untuk melanjutkan.
    </div>
    <?php endif; ?>

    <div class="page-body">
      <div class="summary-cards">
        <div class="summary-card card-income">
          <div class="card-label">Pemasukan</div>
          <div class="card-amount"><?= rupiah($summary['pemasukan']) ?></div>
          <div class="card-icon">👛</div>
        </div>
        <div class="summary-card card-expense">
          <div class="card-label">Pengeluaran</div>
          <div class="card-amount"><?= rupiah($summary['pengeluaran']) ?></div>
          <div class="card-icon">📅</div>
        </div>
        <div class="summary-card card-balance">
          <div class="card-label">Saldo</div>
          <div class="card-amount"><?= rupiah($summary['saldo']) ?></div>
          <div class="card-icon">📓</div>
        </div>
      </div>

      <div class="table-section">
        <div class="table-thead">
          <div>Transaksi</div>
          <div>Keterangan</div>
          <div>Kategori</div>
          <div>Jumlah</div>
          <div></div>
        </div>

        <div class="table-body">
          <?php if (empty($summary['transactions'])): ?>
            <div class="empty-state">
              <div class="empty-icon">📋</div>
              <h3>Belum ada transaksi</h3>
              <p>Mulai catat keuanganmu di <?= $bulan ?> <?= $year ?></p>
            </div>

          <?php else: ?>
            <?php foreach ($grouped as $date => $txList):
              $dayIn  = array_sum(array_map(fn($t) => $t['type']==='pemasukan'   ? (float)$t['amount'] : 0, $txList));
              $dayOut = array_sum(array_map(fn($t) => $t['type']==='pengeluaran' ? (float)$t['amount'] : 0, $txList));
              $dt     = new DateTime($date);
              $dlabel = $dt->format('d') . ' ' . namaBulan((int)$dt->format('n'));
            ?>

            <div class="day-header-row">
              <div class="day-date-label">📅 <?= $dlabel ?></div>
              <div></div>
              <div></div>
              <div style="display:flex; gap:1.2rem;">
                <?php if ($dayIn  > 0): ?>
                  <span class="day-income-total">+<?= rupiah($dayIn) ?></span>
                <?php endif; ?>
                <?php if ($dayOut > 0): ?>
                  <span class="day-expense-total">-<?= rupiah($dayOut) ?></span>
                <?php endif; ?>
              </div>
              <div></div>
            </div>

            <?php foreach ($txList as $t):
              $isIn    = $t['type'] === 'pemasukan';
              $allCats = array_merge($cats_masuk, $cats_keluar);
              $icon    = $isIn ? '💰' : '💸';
              foreach ($allCats as $c) {
                if ($c['name'] === $t['category']) { $icon = $c['icon']; break; }
              }
              $label = $t['description'] ?: ($t['category'] ?: ($isIn ? 'Pemasukan' : 'Pengeluaran'));
            ?>
            <div class="tx-row">
              <div class="tx-desc-cell">
                <div class="tx-icon-wrap <?= $isIn ? 'income-ico' : 'expense-ico' ?>">
                  <?= $icon ?>
                </div>
                <div class="tx-name"><?= htmlspecialchars($label) ?></div>
              </div>

              <div class="tx-keterangan">
                <?= htmlspecialchars($t['description'] ?? '—') ?>
              </div>

              <div class="tx-kategori">
                <?= htmlspecialchars($t['category'] ?? '—') ?>
              </div>

              <div class="tx-amount <?= $isIn ? 'income-amt' : 'expense-amt' ?>">
                <?= ($isIn ? '+' : '-') . rupiah((float)$t['amount']) ?>
              </div>

              <div>
                <button class="tx-delete-btn" data-id="<?= $t['id'] ?>" title="Hapus">🗑</button>
              </div>
            </div>
            <?php endforeach; ?>

            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="bottom-bar">
      <?php if (!$limitReached): ?>
        <button class="bottom-btn btn-income" onclick="openModal('modal-masuk')">
          <div class="btn-icon-wrap">👛</div>
          + Pemasukan
        </button>
        <button class="bottom-btn btn-expense" onclick="openModal('modal-keluar')">
          <div class="btn-icon-wrap">👛</div>
          + Pengeluaran
        </button>
      <?php else: ?>
        <a href="login.php" class="bottom-btn btn-income bottom-btn-full">
          🔐 Masuk untuk Tambah Transaksi
        </a>
      <?php endif; ?>
    </div>

  </div>
</div>

<div class="mypicker-overlay" id="mypicker-overlay">
  <div class="mypicker-box">
    <div class="myp-header">
      <div class="myp-year-nav">
        <button class="myp-nav-btn" type="button" onclick="mypNavYear(-1)">&#8249;</button>
        <span class="myp-year-display" id="myp-year-lbl"><?= $year ?></span>
        <button class="myp-nav-btn" type="button" onclick="mypNavYear(1)">&#8250;</button>
      </div>
      <button class="myp-close-btn" type="button" onclick="closeMYP()">&#10005;</button>
    </div>

    <div class="myp-month-grid" id="myp-month-grid">
    </div>

    <div class="myp-footer">
      <button class="myp-btn myp-cancel"  type="button" onclick="closeMYP()">Batal</button>
      <button class="myp-btn myp-confirm" type="button" onclick="confirmMYP()">Terapkan</button>
    </div>
  </div>
</div>

<div class="modal-overlay" id="modal-masuk">
  <div class="modal-box">
    <div class="modal-header">
      <div class="modal-title-wrap">
        <h2>Tambah Pemasukan</h2>
        <span class="modal-badge badge-income">👛 Masuk</span>
      </div>
      <button class="modal-close-btn" type="button" onclick="closeModal('modal-masuk')">&#10005;</button>
    </div>

    <form id="form-masuk">
      <input type="hidden" name="type"     value="pemasukan">
      <input type="hidden" name="category" id="cat-hidden-masuk">
      <input type="hidden" name="date"     id="date-val-masuk">

      <div class="form-group">
        <label>Tanggal</label>
        <div class="date-input-row">
          <div class="date-display-box" id="date-disp-masuk"
               onclick="openCal('masuk')">Pilih tanggal...</div>
          <button type="button" class="date-cal-btn" onclick="openCal('masuk')">📅</button>
        </div>
      </div>

      <div class="form-group">
        <label>Jumlah Uang (Rp)</label>
        <div class="amount-wrapper">
          <span class="amount-prefix">Rp</span>
          <input type="text" class="amount-field" data-raw="raw-masuk"
                 placeholder="0" autocomplete="off">
          <input type="hidden" id="raw-masuk" name="amount">
        </div>
      </div>

      <div class="form-group">
        <label>Kategori</label>
        <div class="category-grid">
          <?php foreach ($cats_masuk as $c): ?>
          <button type="button" class="cat-option-btn"
                  data-value="<?= htmlspecialchars($c['name']) ?>"
                  onclick="selectCategory(this, 'cat-hidden-masuk')">
            <span class="cat-icon"><?= $c['icon'] ?></span>
            <?= htmlspecialchars($c['name']) ?>
          </button>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="form-group">
        <label>Keterangan</label>
        <input type="text" name="description"
               placeholder="Mis: Gaji bulan April, dari orang tua..."
               maxlength="255">
      </div>

      <button type="submit" class="submit-btn income-btn">
        💾 Simpan Pemasukan
      </button>
    </form>
  </div>
</div>

<div class="modal-overlay" id="modal-keluar">
  <div class="modal-box">
    <div class="modal-header">
      <div class="modal-title-wrap">
        <h2>Tambah Pengeluaran</h2>
        <span class="modal-badge badge-expense">👛 Keluar</span>
      </div>
      <button class="modal-close-btn" type="button" onclick="closeModal('modal-keluar')">&#10005;</button>
    </div>

    <form id="form-keluar">
      <input type="hidden" name="type"     value="pengeluaran">
      <input type="hidden" name="category" id="cat-hidden-keluar">
      <input type="hidden" name="date"     id="date-val-keluar">

      <div class="form-group">
        <label>Tanggal</label>
        <div class="date-input-row">
          <div class="date-display-box" id="date-disp-keluar"
               onclick="openCal('keluar')">Pilih tanggal...</div>
          <button type="button" class="date-cal-btn" onclick="openCal('keluar')">📅</button>
        </div>
      </div>

      <div class="form-group">
        <label>Jumlah Uang (Rp)</label>
        <div class="amount-wrapper">
          <span class="amount-prefix">Rp</span>
          <input type="text" class="amount-field" data-raw="raw-keluar"
                 placeholder="0" autocomplete="off">
          <input type="hidden" id="raw-keluar" name="amount">
        </div>
      </div>

      <div class="form-group">
        <label>Kategori</label>
        <div class="category-grid">
          <?php foreach ($cats_keluar as $c): ?>
          <button type="button" class="cat-option-btn"
                  data-value="<?= htmlspecialchars($c['name']) ?>"
                  onclick="selectCategory(this, 'cat-hidden-keluar')">
            <span class="cat-icon"><?= $c['icon'] ?></span>
            <?= htmlspecialchars($c['name']) ?>
          </button>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="form-group">
        <label>Keterangan</label>
        <input type="text" name="description"
               placeholder="Mis: Makan siang, isi bensin, bayar listrik..."
               maxlength="255">
      </div>

      <button type="submit" class="submit-btn expense-btn">
        💾 Simpan Pengeluaran
      </button>
    </form>
  </div>
</div>

<div class="cal-overlay" id="cal-overlay">
  <div class="cal-box">
    <div class="cal-header">
      <div class="cal-month-label" id="cal-month-label">Januari 2026</div>
      <div class="cal-nav-group">
        <button class="cal-nav-btn" type="button" onclick="calNavMonth(-1)">&#8249;</button>
        <button class="cal-nav-btn" type="button" onclick="calNavMonth(1)">&#8250;</button>
      </div>
    </div>

    <div class="cal-weekdays">
      <div class="cal-wd">S</div><div class="cal-wd">S</div>
      <div class="cal-wd">S</div><div class="cal-wd">R</div>
      <div class="cal-wd">K</div><div class="cal-wd">J</div>
      <div class="cal-wd">S</div>
    </div>

    <div class="cal-days-grid" id="cal-days-grid"></div>

    <div class="cal-footer">
      <button class="cal-footer-btn" type="button" onclick="closeCal()">Batalkan</button>
      <button class="cal-footer-btn" type="button" onclick="confirmCal()">Konfirmasi</button>
    </div>
  </div>
</div>


<div id="toast" class="toast"></div>
<script src="assets/js/app.js"></script>
</body>
</html>
