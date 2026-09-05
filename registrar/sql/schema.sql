-- NMC Registrar — ORDIR (Online Request and Document Issuance Reporting System)
-- Schema v2 — no student accounts; tracking code-based like CTU ORDIR

CREATE DATABASE IF NOT EXISTS nmc_registrar CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nmc_registrar;

DROP TABLE IF EXISTS announcements;
DROP TABLE IF EXISTS document_requests;
DROP TABLE IF EXISTS grades;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS nmc_staff;

CREATE TABLE nmc_staff (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50)  NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name     VARCHAR(100),
    role          ENUM('registrar','staff') DEFAULT 'staff',
    is_active     TINYINT DEFAULT 1,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE students (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    student_id  VARCHAR(15)  NOT NULL UNIQUE,
    surname     VARCHAR(80)  NOT NULL,
    given_name  VARCHAR(80)  NOT NULL,
    middle_name VARCHAR(50),
    email       VARCHAR(120),
    course      VARCHAR(20),
    year_level  TINYINT,
    section     VARCHAR(10),
    status      ENUM('enrolled','active','inactive','leave') DEFAULT 'enrolled',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE grades (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    student_id    VARCHAR(15) NOT NULL,
    subject_code  VARCHAR(20),
    subject_name  VARCHAR(100),
    units         DECIMAL(3,1),
    grade         VARCHAR(5),
    semester      ENUM('1st','2nd','Summer'),
    academic_year VARCHAR(10),
    remarks       VARCHAR(30),
    FOREIGN KEY (student_id) REFERENCES students(student_id)
);

-- No student_id FK — applicant info stored directly (no student portal accounts)
CREATE TABLE document_requests (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    tracking_code  VARCHAR(20)  NOT NULL UNIQUE,
    applicant_name VARCHAR(100) NOT NULL,
    student_id_no  VARCHAR(20),
    email          VARCHAR(100),
    contact        VARCHAR(20),
    doc_type       ENUM(
                     'Transcript of Records',
                     'Certificate of Enrollment',
                     'Certificate of Good Moral Character',
                     'Diploma Authentication',
                     'Course Description',
                     'Honorable Dismissal'
                   ) NOT NULL,
    copies         TINYINT NOT NULL DEFAULT 1,
    purpose        TEXT NOT NULL,
    status         ENUM('pending','processing','ready','released') DEFAULT 'pending',
    requested_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    release_date   DATE,
    staff_notes    TEXT,
    processed_by   INT,
    FOREIGN KEY (processed_by) REFERENCES nmc_staff(id) ON DELETE SET NULL
);

CREATE TABLE announcements (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(200) NOT NULL,
    body         TEXT NOT NULL,
    posted_by    INT,
    is_published TINYINT DEFAULT 1,
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (posted_by) REFERENCES nmc_staff(id) ON DELETE SET NULL
);

CREATE TABLE internal_notes (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    author   VARCHAR(50),
    category VARCHAR(50),
    note     TEXT
);
