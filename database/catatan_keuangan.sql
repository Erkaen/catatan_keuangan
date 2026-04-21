
CREATE DATABASE IF NOT EXISTS catatan_keuangan
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE catatan_keuangan;

CREATE TABLE IF NOT EXISTS tabel_users (
    id          INT(11)      NOT NULL AUTO_INCREMENT,
    name        VARCHAR(100) NOT NULL,
    email       VARCHAR(150) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    is_premium  BOOLEAN      NOT NULL DEFAULT FALSE,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS tabel_categories (
    id          INT(11)      NOT NULL AUTO_INCREMENT,
    name        VARCHAR(100) NOT NULL,
    type        ENUM('pemasukan','pengeluaran') NOT NULL,
    icon        VARCHAR(50)  DEFAULT NULL,
    color       VARCHAR(7)   DEFAULT NULL,
    is_default  BOOLEAN      NOT NULL DEFAULT FALSE,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tabel_transactions (
    id          BIGINT(20)   NOT NULL AUTO_INCREMENT,
    user_id     INT(11)      DEFAULT NULL,         
    guest_token VARCHAR(64)  DEFAULT NULL,         
    type        ENUM('pemasukan','pengeluaran') NOT NULL,
    amount      DECIMAL(15,2) NOT NULL,
    category    VARCHAR(100) DEFAULT NULL,
    party       VARCHAR(150) DEFAULT NULL,
    description TEXT         DEFAULT NULL,
    date        DATE         NOT NULL,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_user_id   (user_id),
    KEY idx_guest     (guest_token),
    KEY idx_date      (date),
    CONSTRAINT fk_trans_user
        FOREIGN KEY (user_id) REFERENCES tabel_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tabel_sessions (
    id          VARCHAR(64)  NOT NULL,
    user_id     INT(11)      NOT NULL,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at  TIMESTAMP    NOT NULL,
    ip_address  VARCHAR(45)  DEFAULT NULL,
    user_agent  TEXT         DEFAULT NULL,
    is_revoked  BOOLEAN      NOT NULL DEFAULT FALSE,
    PRIMARY KEY (id),
    KEY idx_user (user_id),
    CONSTRAINT fk_sess_user
        FOREIGN KEY (user_id) REFERENCES tabel_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO tabel_categories (name, type, icon, color, is_default) VALUES
  ('Gaji',          'pemasukan',   '💼', '#00C896', TRUE),
  ('Bonus',         'pemasukan',   '🎁', '#00C896', TRUE),
  ('Freelance',     'pemasukan',   '💻', '#1565C0', TRUE),
  ('Investasi',     'pemasukan',   '📈', '#43A047', TRUE),
  ('Dari Orang Tua','pemasukan',   '👨‍👩‍👧', '#7E57C2', TRUE),
  ('Lainnya',       'pemasukan',   '➕', '#78909C', TRUE),

  ('Makan & Minum', 'pengeluaran', '🍽️', '#FF5252', TRUE),
  ('Transportasi',  'pengeluaran', '🚗', '#FF9800', TRUE),
  ('Belanja',       'pengeluaran', '🛒', '#E91E63', TRUE),
  ('Hiburan',       'pengeluaran', '🎬', '#9C27B0', TRUE),
  ('Pendidikan',    'pengeluaran', '📚', '#2196F3', TRUE),
  ('Kesehatan',     'pengeluaran', '💊', '#F44336', TRUE),
  ('Tagihan',       'pengeluaran', '📄', '#FF5722', TRUE),
  ('Lainnya',       'pengeluaran', '➖', '#78909C', TRUE);
