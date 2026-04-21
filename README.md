# KeuanganKu — Website Pencatatan Keuangan Pribadi

**Kelompok Catatan Keuangan**
- Nyayu Jessi Putri Shafira (03041282530056)
- Resti Anggratiani (03041382530102)
- Arya Figo (03041382530136)
- Rakan Dirgantara (03041282530048)

---

## 🎨 Warna Tema
Mengikuti gradasi warna logo **UKS** dari biru → teal → hijau lime:
| Variabel        | Warna         | Hex       |
|----------------|---------------|-----------|
| --blue          | Biru Dalam    | #1565C0   |
| --teal          | Teal/Emerald  | #00C896   |
| --lime          | Hijau Lime    | #B2FF59   |
| --dark          | Navy Gelap    | #0A1628   |

---

## 🗂️ Struktur Folder

```
catatan_keuangan/
├── index.php              ← Halaman utama / dashboard
├── login.php              ← Halaman masuk
├── register.php           ← Halaman daftar
├── logout.php             ← Proses keluar
│
├── ajax/
│   ├── save_transaction.php   ← Simpan transaksi (AJAX)
│   └── delete_transaction.php ← Hapus transaksi (AJAX)
│
├── config/
│   ├── app.php            ← Konfigurasi aplikasi
│   └── db.php             ← Koneksi database (PDO)
│
├── includes/
│   ├── auth.php           ← Fungsi autentikasi & tamu
│   └── functions.php      ← Fungsi bantu transaksi
│
├── assets/
│   ├── css/
│   │   └── style.css      ← Stylesheet utama
│   └── js/
│       └── app.js         ← JavaScript frontend
│
└── database/
    └── catatan_keuangan.sql  ← Schema + data awal
```

---

## ⚙️ Cara Instalasi (XAMPP)

### 1. Salin folder ke htdocs
```
C:\xampp\htdocs\catatan_keuangan\
```

### 2. Buat database
- Buka **phpMyAdmin** → `http://localhost/phpmyadmin`
- Klik tab **Import**
- Pilih file: `database/catatan_keuangan.sql`
- Klik **Go**

### 3. Konfigurasi database (jika perlu)
Edit file `config/db.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');   // sesuaikan username MySQL
define('DB_PASS', '');       // sesuaikan password MySQL
define('DB_NAME', 'catatan_keuangan');
```

### 4. Jalankan aplikasi
- Pastikan Apache & MySQL di XAMPP sudah **Start**
- Buka browser → `http://localhost/catatan_keuangan`

---

## 🚀 Fitur Aplikasi

| Fitur | Tamu | Login |
|-------|------|-------|
| Lihat transaksi | ✅ | ✅ |
| Tambah transaksi | ✅ (5x) | ✅ unlimited |
| Hapus transaksi | ✅ | ✅ |
| Data tersimpan permanen | ❌ | ✅ |
| Navigasi bulan | ✅ | ✅ |
| Ringkasan saldo | ✅ | ✅ |

---

## 🗄️ Tabel Database

### `tabel_users`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | INT(11) PK | Auto increment |
| name | VARCHAR(100) | Nama pengguna |
| email | VARCHAR(150) UNIQUE | Email login |
| password_hash | VARCHAR(255) | Hash bcrypt |
| is_premium | BOOLEAN | Status premium |
| created_at | TIMESTAMP | Waktu daftar |

### `tabel_transactions`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | BIGINT(20) PK | Auto increment |
| user_id | INT(11) FK | Relasi ke users (null=tamu) |
| guest_token | VARCHAR(64) | Token sesi tamu |
| type | ENUM | pemasukan / pengeluaran |
| amount | DECIMAL(15,2) | Nominal uang |
| category | VARCHAR(100) | Kategori transaksi |
| description | TEXT | Keterangan |
| date | DATE | Tanggal transaksi |
| created_at | TIMESTAMP | Waktu input |

### `tabel_categories`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | INT(11) PK | Auto increment |
| name | VARCHAR(100) | Nama kategori |
| type | ENUM | pemasukan / pengeluaran |
| icon | VARCHAR(50) | Emoji ikon |
| color | VARCHAR(7) | Kode warna hex |
| is_default | BOOLEAN | Kategori bawaan |

### `tabel_sessions`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | VARCHAR(64) PK | Token sesi |
| user_id | INT(11) FK | Relasi ke users |
| created_at | TIMESTAMP | Waktu dibuat |
| expires_at | TIMESTAMP | Waktu kadaluarsa |
| ip_address | VARCHAR(45) | IP pengguna |
| user_agent | TEXT | Info browser |
| is_revoked | BOOLEAN | Status dicabut |

---

## 📱 Software & Hardware yang Digunakan

**Software:**
- XAMPP (Apache + MySQL + PHP)
- VS Code
- Canva (desain logo)
- Google Chrome (testing)

**Hardware:**
- Laptop (development)
- Gawai/HP (testing mobile)
