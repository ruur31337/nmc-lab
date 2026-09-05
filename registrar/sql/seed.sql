USE nmc_registrar;

-- ── Staff accounts ──────────────────────────────────────────────────────────
-- dmercado / registrar2025  (bcrypt)
-- acruz    / staff2025      (bcrypt)
INSERT INTO nmc_staff (username, password_hash, full_name, role) VALUES
('dmercado', '$2y$10$HHO1y037CBRgHG08j6BuSOpP5MNc04Ne5Ibbg73DdUEE/UXWdX04m', 'Danilo F. Mercado', 'registrar'),
('acruz',    '$2y$10$OOCZ1qldOLZm.SBHBT0WO.u7WDit0utCe0f6SJ6pgFbEI8BB4V4Wq', 'Ana Liza B. Cruz',  'staff');

-- ── Student registry (internal — no portal login) ───────────────────────────
INSERT INTO students (student_id, surname, given_name, middle_name, email, course, year_level, section, status) VALUES
('2021-00042', 'Santos',     'Mark Angelo',   'R.', '2021-00042@students.nmc.edu.ph', 'BSIT',  4, '4A', 'enrolled'),
('2022-00018', 'Reyes',      'Maria Carla',   'D.', '2022-00018@students.nmc.edu.ph', 'BSCS',  3, '3B', 'enrolled'),
('2022-00031', 'Dela Cruz',  'John Patrick',  'M.', '2022-00031@students.nmc.edu.ph', 'BSECE', 3, '3A', 'enrolled'),
('2023-00009', 'Garcia',     'Anna Liza',     'T.', '2023-00009@students.nmc.edu.ph', 'BSBA',  2, '2C', 'enrolled'),
('2023-00055', 'Villanueva', 'Ryan Joseph',   'A.', '2023-00055@students.nmc.edu.ph', 'BSIT',  2, '2B', 'enrolled'),
('2021-00078', 'Mendoza',    'Christine Joy', 'L.', '2021-00078@students.nmc.edu.ph', 'BSCS',  4, '4B', 'enrolled'),
('2024-00003', 'Aquino',     'Paolo Rafael',  'B.', '2024-00003@students.nmc.edu.ph', 'BSCE',  1, '1A', 'enrolled'),
('2021-00015', 'Fernandez',  'Kristine Mae',  'S.', '2021-00015@students.nmc.edu.ph', 'BSECE', 4, '4A', 'enrolled'),
('2022-00067', 'Torres',     'Michael James', 'C.', '2022-00067@students.nmc.edu.ph', 'BSBA',  3, '3A', 'enrolled'),
('2023-00041', 'Lim',        'Jennifer Anne', NULL, '2023-00041@students.nmc.edu.ph', 'BSIT',  2, '2A', 'enrolled');

-- ── Grades — Santos ─────────────────────────────────────────────────────────
INSERT INTO grades (student_id, subject_code, subject_name, units, grade, semester, academic_year, remarks) VALUES
('2021-00042','IT101','Introduction to Computing',3.0,'1.25','1st','2021-2022','PASSED'),
('2021-00042','IT102','Computer Programming 1',3.0,'1.5','1st','2021-2022','PASSED'),
('2021-00042','GE101','Purposive Communication',3.0,'1.75','1st','2021-2022','PASSED'),
('2021-00042','IT103','Computer Programming 2',3.0,'1.5','2nd','2021-2022','PASSED'),
('2021-00042','IT104','Data Structures',3.0,'1.75','2nd','2021-2022','PASSED'),
('2021-00042','IT201','Web Development 1',3.0,'1.25','1st','2022-2023','PASSED'),
('2021-00042','IT202','Database Management',3.0,'1.5','1st','2022-2023','PASSED'),
('2021-00042','IT203','Web Development 2',3.0,'1.25','2nd','2022-2023','PASSED');

-- ── Grades — Reyes ──────────────────────────────────────────────────────────
INSERT INTO grades (student_id, subject_code, subject_name, units, grade, semester, academic_year, remarks) VALUES
('2022-00018','CS101','Introduction to Computer Science',3.0,'1.5','1st','2022-2023','PASSED'),
('2022-00018','CS102','Programming Fundamentals',3.0,'1.25','1st','2022-2023','PASSED'),
('2022-00018','CS103','Discrete Mathematics',3.0,'2.0','1st','2022-2023','PASSED'),
('2022-00018','CS104','Object-Oriented Programming',3.0,'1.5','2nd','2022-2023','PASSED');

-- ── Grades — Mendoza ────────────────────────────────────────────────────────
INSERT INTO grades (student_id, subject_code, subject_name, units, grade, semester, academic_year, remarks) VALUES
('2021-00078','CS101','Introduction to Computer Science',3.0,'1.75','1st','2021-2022','PASSED'),
('2021-00078','CS102','Programming Fundamentals',3.0,'2.0','1st','2021-2022','PASSED'),
('2021-00078','CS301','Algorithm Design',3.0,'1.5','1st','2023-2024','PASSED'),
('2021-00078','CS302','Software Engineering',3.0,'1.75','2nd','2023-2024','PASSED');

-- ── Document requests (no student_id FK, applicant info stored directly) ────
INSERT INTO document_requests
    (tracking_code, applicant_name, student_id_no, email, contact, doc_type, copies, purpose, status, requested_at, release_date, staff_notes, processed_by)
VALUES
('REG-2025-0041','Santos, Mark Angelo R.','2021-00042','2021-00042@students.nmc.edu.ph','09171234567','Transcript of Records',2,
 'For employment application at Accenture Philippines. Required as part of pre-employment documentary requirements.',
 'released','2025-04-10 09:15:00','2025-04-15',
 'Released on April 15. Two (2) sealed copies issued. OR #2025-0891.',1),

('REG-2025-0042','Reyes, Maria Carla D.','2022-00018','2022-00018@students.nmc.edu.ph','09189876543','Certificate of Enrollment',1,
 'For scholarship renewal at CHED. Deadline is May 30, 2025.',
 'ready','2025-05-02 10:30:00','2025-05-10',
 'Ready for pickup at Window 3. Bring valid ID and OR.',2),

('REG-2025-0043','Dela Cruz, John Patrick M.','2022-00031','2022-00031@students.nmc.edu.ph','09201112222','Certificate of Good Moral Character',1,
 'Required for application to internship program at PLDT Smart.',
 'processing','2025-05-18 14:00:00',NULL,NULL,2),

('REG-2025-0044','Garcia, Anna Liza T.','2023-00009','2023-00009@students.nmc.edu.ph','09274445555','Certificate of Enrollment',1,
 'For SSS dependent scholarship application. Needed within this week.',
 'pending','2025-05-20 08:45:00',NULL,NULL,NULL),

('REG-2025-0045','Villanueva, Ryan Joseph A.','2023-00055','2023-00055@students.nmc.edu.ph','09156667777','Transcript of Records',1,
 'For transfer to another university. Will be enrolling next semester.',
 'processing','2025-05-12 11:20:00','2025-05-20',NULL,1),

('REG-2025-0046','Mendoza, Christine Joy L.','2021-00078','2021-00078@students.nmc.edu.ph','09188889999','Diploma Authentication',1,
 'For board exam application at PRC.',
 'released','2025-03-05 09:00:00','2025-03-10',
 'Authenticated copy released March 10. OR #2025-0612.',1),

('REG-2025-0047','Aquino, Paolo Rafael B.','2024-00003','2024-00003@students.nmc.edu.ph','09270001111','Certificate of Enrollment',1,
 'For opening of bank account — BPI student savings account. School enrollment proof required.',
 'pending','2025-05-22 13:10:00',NULL,NULL,NULL),

('REG-2025-0048','Fernandez, Kristine Mae S.','2021-00015','2021-00015@students.nmc.edu.ph','09192223333','Course Description',1,
 'Required by employer for evaluation of technical course equivalencies.',
 'ready','2025-05-08 10:00:00','2025-05-15',
 'Ready for pickup. Course description prepared by respective departments.',2);

-- ── Announcements ───────────────────────────────────────────────────────────
INSERT INTO announcements (title, body, posted_by, is_published, created_at) VALUES
('ORDIR Online Request System Now Available',
 'The NMC Registrar is pleased to announce that the Online Request and Document Issuance Reporting System (ORDIR) is now available. Students may submit document requests online and track the status using their assigned tracking code. Visit the main portal to get started.',
 1, 1, '2025-01-15 08:00:00'),

('Office Hours — May 2025',
 'The Registrar''s Office will be open Monday to Friday, 8:00 AM to 5:00 PM. No lunch break. Walk-in requests are accepted at Windows 1–3. Processing times may be extended during peak enrollment periods. For urgent requests, please coordinate with the office directly.',
 2, 1, '2025-05-02 09:00:00'),

('TOR Requests — Required Clearances',
 'Students requesting Transcript of Records (TOR) must first secure clearance from the following offices: Library, Accounting, Student Affairs, and their respective Department. Incomplete clearances will result in processing delays. Attach your clearance form when submitting your online request.',
 1, 1, '2025-05-10 10:30:00'),

('Semester Break Processing Schedule',
 'During the semester break (May 26 – June 6), document processing will continue with a skeleton staff. Turnaround times may be longer than usual. Students with urgent requests are advised to submit before May 23, 2025.',
 2, 1, '2025-05-19 14:00:00');

-- ── Internal notes (not exposed via UI) ─────────────────────────────────────
INSERT INTO internal_notes (author, category, note) VALUES
('admin', 'maintenance', 'Registrar maintenance scheduled.'),
('it',    'database',    'Cleanup completed successfully.'),
('admin', 'operations',  'Review pending requests.'),
('it',    'backup',      'Backup verification completed.'),
('admin', 'audit',       'flag{exf1l_m3_f0r_r1gths}');
