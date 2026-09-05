-- ============================================================
-- setup.sql — Run this in phpMyAdmin to create all tables
-- ============================================================

CREATE DATABASE IF NOT EXISTS mindfulspace CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mindfulspace;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)        NOT NULL,
    email       VARCHAR(191)        NOT NULL UNIQUE,
    password    VARCHAR(255)        NOT NULL,
    role        ENUM('user','admin') DEFAULT 'user',
    created_at  TIMESTAMP           DEFAULT CURRENT_TIMESTAMP
);

-- Mood history table (server-side, syncs across devices)
CREATE TABLE IF NOT EXISTS mood_history (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT                 NOT NULL,
    mood        VARCHAR(20)         NOT NULL,
    label       VARCHAR(30)         NOT NULL,
    emoji       VARCHAR(10)         NOT NULL,
    created_at  TIMESTAMP           DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Resources table (admin-managed)
CREATE TABLE IF NOT EXISTS resources (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    icon        VARCHAR(10)         NOT NULL DEFAULT '💚',
    title       VARCHAR(150)        NOT NULL,
    description TEXT                NOT NULL,
    link        VARCHAR(500)        NOT NULL,
    link_text   VARCHAR(100)        NOT NULL DEFAULT 'Learn More',
    is_active   TINYINT(1)          DEFAULT 1,
    sort_order  INT                 DEFAULT 0,
    created_at  TIMESTAMP           DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP           DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- User favorites table
CREATE TABLE IF NOT EXISTS favorites (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT                 NOT NULL,
    resource_id INT                 NOT NULL,
    created_at  TIMESTAMP           DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_fav (user_id, resource_id),
    FOREIGN KEY (user_id)     REFERENCES users(id)     ON DELETE CASCADE,
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE
);

-- Public mood wall table
CREATE TABLE IF NOT EXISTS mood_wall (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    emoji       VARCHAR(10)         NOT NULL,
    mood        VARCHAR(20)         NOT NULL,
    message     VARCHAR(280)        DEFAULT NULL,
    ip_hash     VARCHAR(64)         DEFAULT NULL,
    created_at  TIMESTAMP           DEFAULT CURRENT_TIMESTAMP
);

-- Contact messages table
CREATE TABLE IF NOT EXISTS contact_messages (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)        NOT NULL,
    email       VARCHAR(191)        NOT NULL,
    subject     VARCHAR(200)        NOT NULL,
    message     TEXT                NOT NULL,
    is_read     TINYINT(1)          DEFAULT 0,
    created_at  TIMESTAMP           DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- Seed: Default Pakistani Mental Health Resources
-- ============================================================
INSERT INTO resources (icon, title, description, link, link_text, sort_order) VALUES
('🆘', 'Umang Helpline (Pakistan)',       'Pakistan\'s first free, confidential mental health helpline. Call 0317-4288665 (Mon–Sat, 12pm–12am) for emotional support and crisis counseling.',             'https://www.umang.com.pk',                       'Visit umang.com.pk',       1),
('📞', 'Rozan Counseling Center',          'Islamabad-based NGO offering free psychological counseling, awareness programs, and trauma support for individuals and families across Pakistan.',               'https://rozan.org',                              'Visit rozan.org',          2),
('🧠', 'Institute of Psychiatry — BBH',   'Government psychiatric facility in Rawalpindi providing free mental health assessment, inpatient care, and outpatient counseling services.',                    'https://www.pims.gov.pk',                        'Visit pims.gov.pk',        3),
('💚', 'Pakistan Assoc. for Mental Health','National organization working to promote mental health awareness, reduce stigma, and improve psychiatric services throughout Pakistan.',                        'https://www.pamh.org.pk',                        'Visit pamh.org.pk',        4),
('🤝', 'Fountain House Lahore',            'Non-profit rehabilitation center in Lahore supporting people with serious mental illness through community programs and social reintegration.',                 'https://fountainhouselahore.org',                'Visit fountainhouselahore.org', 5),
('🌿', 'OlaDoc — Find a Psychologist',    'Online directory of licensed Pakistani psychologists and therapists offering affordable in-person and telecounseling sessions in Urdu and English.',            'https://www.oladoc.com/pakistan/psychologist',   'Find a Psychologist',      6),
('🎧', 'Headspace',                        'Globally available meditation and mindfulness app with breathing exercises, sleep programs, and anxiety support — accessible from Pakistan.',                   'https://www.headspace.com',                      'Try Headspace',            7),
('🏥', 'Aga Khan University — Psychiatry', 'AKU\'s Department of Psychiatry in Karachi offers outpatient consultations, therapy, and research-backed psychiatric care.',                                  'https://www.aku.edu/mcpk/psychiatry',            'Visit AKU Psychiatry',     8);

-- ============================================================
-- Seed: Default Admin Account
-- Password: Admin@1234  (change immediately after setup!)
-- ============================================================
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@mindfulspace.pk', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uFpXfelC/', 'admin');
