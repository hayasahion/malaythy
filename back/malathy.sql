-- ========================================
-- قاعدة بيانات مشروع ملاذي
-- ========================================

CREATE DATABASE IF NOT EXISTS malathy_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE malathy_db;

-- ========================================
-- جدول 1: أولياء الأمور (المستخدمون الرئيسيون)
-- ========================================
CREATE TABLE parents (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    full_name    VARCHAR(100)  NOT NULL,
    email        VARCHAR(100)  UNIQUE NOT NULL,
    password     VARCHAR(255)  NOT NULL,          -- مشفّر بـ password_hash
    phone        VARCHAR(20)   DEFAULT NULL,
    avatar       VARCHAR(255)  DEFAULT NULL,       -- مسار صورة البروفايل
    is_active    TINYINT(1)    DEFAULT 1,          -- 1=نشط، 0=موقوف
    created_at   TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ========================================
-- جدول 2: الأطفال (مرتبط بولي الأمر)
-- ========================================
CREATE TABLE children (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    parent_id    INT           NOT NULL,
    child_name   VARCHAR(100)  NOT NULL,
    birth_year   YEAR          DEFAULT NULL,       -- سنة الميلاد لتحديد العمر
    gender       ENUM('male','female') DEFAULT NULL,
    avatar       VARCHAR(255)  DEFAULT NULL,
    created_at   TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,

    -- ربط الطفل بوالده: إذا حُذف الوالد تُحذف بياناته تلقائياً
    FOREIGN KEY (parent_id) REFERENCES parents(id) ON DELETE CASCADE
);

-- ========================================
-- جدول 3: القصص المولّدة
-- ========================================
CREATE TABLE stories (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    parent_id       INT           NOT NULL,
    child_id        INT           DEFAULT NULL,    -- القصة قد تكون عامة بدون طفل محدد
    title           VARCHAR(255)  NOT NULL,
    character_type  VARCHAR(50)   NOT NULL,        -- rabbit, cat, lion ...
    environment     VARCHAR(50)   NOT NULL,        -- forest, sea, space ...
    emotion         VARCHAR(50)   NOT NULL,        -- fear, sadness, anger ...
    content         TEXT          NOT NULL,        -- نص القصة الكامل (JSON أو نص عادي)
    analysis        TEXT          DEFAULT NULL,    -- التحليل النفسي
    advice          TEXT          DEFAULT NULL,    -- النصيحة للوالدين
    cover_image     VARCHAR(255)  DEFAULT NULL,    -- صورة غلاف القصة
    is_favorite     TINYINT(1)    DEFAULT 0,       -- 1=مفضّلة
    created_at      TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (parent_id)  REFERENCES parents(id)  ON DELETE CASCADE,
    FOREIGN KEY (child_id)   REFERENCES children(id) ON DELETE SET NULL
);

-- ========================================
-- جدول 4: تقييمات القصص
-- ========================================
CREATE TABLE story_ratings (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    story_id    INT         NOT NULL,
    parent_id   INT         NOT NULL,
    rating      TINYINT     NOT NULL CHECK (rating BETWEEN 1 AND 5), -- من 1 إلى 5 نجوم
    comment     TEXT        DEFAULT NULL,
    created_at  TIMESTAMP   DEFAULT CURRENT_TIMESTAMP,

    -- كل والد يقيّم القصة مرة واحدة فقط
    UNIQUE KEY unique_rating (story_id, parent_id),

    FOREIGN KEY (story_id)  REFERENCES stories(id)  ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES parents(id)  ON DELETE CASCADE
);

-- ========================================
-- جدول 5: المشرفون (مستقل تماماً)
-- ========================================
CREATE TABLE admins (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50)   UNIQUE NOT NULL,
    email       VARCHAR(100)  UNIQUE NOT NULL,
    password    VARCHAR(255)  NOT NULL,
    created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
);

-- ========================================
-- إدراج أدمن افتراضي للاختبار
-- كلمة المرور: Admin@1234 (مشفّرة)
-- ========================================
INSERT INTO admins (username, email, password) VALUES (
    'admin',
    'admin@malathy.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
);