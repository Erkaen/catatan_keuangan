<?php
define('APP_NAME',    'KeuanganKu');
define('APP_VERSION', '1.0.0');
define('BASE_URL',    'http://localhost/catatan_keuangan');
define('GUEST_LIMIT', 5);
define('SESSION_DURATION', 60 * 60 * 24 * 7);
date_default_timezone_set('Asia/Jakarta');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
