"use strict";
const path    = require("path");
const fs      = require("fs");
const bcrypt  = require("bcryptjs");
const { v4: uuidv4 } = require("uuid");
const Database = require("better-sqlite3");

const DB_PATH  = process.env.DB_PATH  || "/data/academy.db";
const DATA_DIR = process.env.DATA_DIR || "/data";

function initDb() {
  fs.mkdirSync(DATA_DIR, { recursive: true });
  fs.mkdirSync(path.join(DATA_DIR, "inbox"), { recursive: true });

  const db = new Database(DB_PATH);
  db.pragma("journal_mode = WAL");
  db.pragma("foreign_keys = ON");

  // ── Schema ────────────────────────────────────────────────────────────────
  db.exec(`
    CREATE TABLE IF NOT EXISTS users (
      id           INTEGER PRIMARY KEY AUTOINCREMENT,
      uuid         TEXT UNIQUE NOT NULL,
      email        TEXT UNIQUE NOT NULL,
      password_hash TEXT NOT NULL,
      first_name   TEXT,
      last_name    TEXT,
      role         TEXT DEFAULT 'student',
      student_id   TEXT,
      department   TEXT,
      photo        TEXT,
      is_active    INTEGER DEFAULT 1,
      created_at   DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS announcements (
      id           INTEGER PRIMARY KEY AUTOINCREMENT,
      title        TEXT NOT NULL,
      body         TEXT NOT NULL,
      category     TEXT DEFAULT 'general',
      author_id    INTEGER REFERENCES users(id),
      is_published INTEGER DEFAULT 1,
      pinned       INTEGER DEFAULT 0,
      created_at   DATETIME DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS courses (
      id           INTEGER PRIMARY KEY AUTOINCREMENT,
      code         TEXT UNIQUE NOT NULL,
      title        TEXT NOT NULL,
      units        INTEGER DEFAULT 3,
      instructor_id INTEGER REFERENCES users(id),
      schedule     TEXT,
      room         TEXT
    );

    CREATE TABLE IF NOT EXISTS enrollments (
      id        INTEGER PRIMARY KEY AUTOINCREMENT,
      user_id   INTEGER REFERENCES users(id),
      course_id INTEGER REFERENCES courses(id),
      grade     TEXT,
      status    TEXT DEFAULT 'enrolled',
      UNIQUE(user_id, course_id)
    );

    CREATE TABLE IF NOT EXISTS inbox (
      id           INTEGER PRIMARY KEY AUTOINCREMENT,
      recipient_id INTEGER REFERENCES users(id),
      sender_name  TEXT DEFAULT 'NMC System',
      subject      TEXT,
      body         TEXT,
      attachment   TEXT,
      is_read      INTEGER DEFAULT 0,
      sent_at      DATETIME DEFAULT CURRENT_TIMESTAMP
    );
  `);

  // ── Seed (idempotent) ─────────────────────────────────────────────────────
  const existing = db.prepare("SELECT COUNT(*) as n FROM users").get();
  if (existing.n > 0) {
    db.close();
    return;
  }

  const hash = (p) => bcrypt.hashSync(p, 10);

  const ins = db.prepare(`
    INSERT INTO users (uuid,email,password_hash,first_name,last_name,role,student_id,department)
    VALUES (?,?,?,?,?,?,?,?)
  `);

  // Students — seeded first
  ins.run(uuidv4(), "m.garcia@student.nmc.local", hash("Student@2026!"),
    "Maria", "Garcia", "student", "2025-00091", "College of Engineering");

  ins.run(uuidv4(), "c.mendoza@student.nmc.local", hash("Student@2026!"),
    "Carlo", "Mendoza", "student", "2025-00112", "College of Computing");

  ins.run(uuidv4(), "p.cruz@student.nmc.local", hash("Student@2026!"),
    "Paolo", "Cruz", "student", "2025-00134", "College of Computing");

  ins.run(uuidv4(), "s.tan@student.nmc.local", hash("Student@2026!"),
    "Sofia", "Tan", "student", "2025-00158", "College of Engineering");

  ins.run(uuidv4(), "l.ocampo@student.nmc.local", hash("Student@2026!"),
    "Liza", "Ocampo", "student", "2026-00003", "College of Computing");

  const juanId = ins.run(uuidv4(), "juan.reyes@student.nmc.local", hash("Student@2026!"),
    "Juan", "Reyes", "student", "2026-00001", "College of Computing").lastInsertRowid;

  const anaId = ins.run(uuidv4(), "ana.lim@student.nmc.local", hash("Student@2026!"),
    "Ana", "Lim", "student", "2026-00002", "College of Computing").lastInsertRowid;

  ins.run(uuidv4(), "r.buenaventura@student.nmc.local", hash("Student@2026!"),
    "Ramon", "Buenaventura", "student", "2026-00005", "College of Engineering");

  // Faculty — instructors
  const santosId = ins.run(uuidv4(), "prof.msantos@nmc.local", hash("NMCprof@2026"),
    "Maria", "Santos", "instructor", null, "College of Computing").lastInsertRowid;

  const jreyesId = ins.run(uuidv4(), "prof.jreyes@nmc.local", hash("NMCprof@2026"),
    "Jose", "Reyes", "instructor", null, "College of Engineering").lastInsertRowid;

  // Administration — LMS Admin, then IT Staff
  const lmsAdminId = ins.run(uuidv4(), "lms.admin@nmc.local", hash("LMSadm!n2026"),
    "Veronica", "Bautista", "admin", null, "Academic Affairs").lastInsertRowid;

  // IT Staff — synced from Admission portal via /internal/sync-password
  const itAdminId = ins.run(uuidv4(), "it.rdelacruz@nmc.local", hash("NMCit@2026"),
    "Ricardo", "dela Cruz", "it_staff", null, "IT Services").lastInsertRowid;

  // ── Courses ───────────────────────────────────────────────────────────────
  const insCourse = db.prepare(`
    INSERT INTO courses (code,title,units,instructor_id,schedule,room) VALUES (?,?,?,?,?,?)
  `);
  // Computing / IT
  const c1  = insCourse.run("CS101",  "Introduction to Computing",         3, santosId, "MWF 07:30–08:30", "Lab 201").lastInsertRowid;
  const c2  = insCourse.run("CS102",  "Programming Fundamentals",          3, santosId, "TTH 09:00–10:30", "Lab 202").lastInsertRowid;
  const c3  = insCourse.run("CS201",  "Data Structures and Algorithms",    3, santosId, "MWF 09:30–10:30", "Lab 203").lastInsertRowid;
  const c4  = insCourse.run("CS202",  "Web Development",                   3, santosId, "TTH 13:00–14:30", "Lab 201").lastInsertRowid;
  const c5  = insCourse.run("CS203",  "Database Management Systems",       3, santosId, "MWF 13:00–14:00", "Lab 202").lastInsertRowid;
  const c6  = insCourse.run("IT301",  "Network Administration",            3, jreyesId, "TTH 07:30–09:00", "Lab 301").lastInsertRowid;
  const c7  = insCourse.run("IT302",  "Systems Analysis and Design",       3, jreyesId, "MWF 15:00–16:00", "Rm 302").lastInsertRowid;
  // Engineering
  const c8  = insCourse.run("CE101",  "Engineering Drawing",               3, jreyesId, "TTH 10:30–12:00", "Studio A").lastInsertRowid;
  const c9  = insCourse.run("CE201",  "Statics of Rigid Bodies",           3, jreyesId, "MWF 10:30–11:30", "Rm 401").lastInsertRowid;
  // General Education
  const c10 = insCourse.run("GE001",  "Purposive Communication",           3, jreyesId, "MWF 10:30–11:30", "Rm 301").lastInsertRowid;
  const c11 = insCourse.run("GE002",  "Readings in Philippine History",    3, jreyesId, "TTH 14:30–16:00", "Rm 302").lastInsertRowid;
  const c12 = insCourse.run("GE003",  "Mathematics in the Modern World",   3, santosId, "MWF 08:30–09:30", "Rm 201").lastInsertRowid;
  const c13 = insCourse.run("GE004",  "Art Appreciation",                  3, jreyesId, "TTH 11:30–13:00", "Rm 303").lastInsertRowid;
  const c14 = insCourse.run("PE101",  "Physical Education 1",              2, jreyesId, "SAT 07:00–09:00", "Gymnasium").lastInsertRowid;
  const c15 = insCourse.run("NSTP101","National Service Training Program", 3, jreyesId, "SAT 09:00–12:00", "Auditorium").lastInsertRowid;

  // Enroll demo students in a realistic load (6 subjects each)
  const insEnroll = db.prepare("INSERT OR IGNORE INTO enrollments (user_id,course_id,grade) VALUES (?,?,?)");
  // Juan Reyes — BSIT student
  [[c1,null],[c2,null],[c6,null],[c7,null],[c10,null],[c14,null]].forEach(([c,g]) => insEnroll.run(juanId,c,g));
  // Ana Lim — BSCS student
  [[c1,'1.25'],[c2,'1.50'],[c3,null],[c4,null],[c10,null],[c12,null]].forEach(([c,g]) => insEnroll.run(anaId,c,g));

  // ── Announcements ─────────────────────────────────────────────────────────
  const insAnn = db.prepare(`
    INSERT INTO announcements (title,body,category,author_id,pinned,created_at) VALUES (?,?,?,?,?,?)
  `);

  insAnn.run(
    "Welcome to NMC Academy — AY 2026–2027",
    `Dear Students,\n\nWelcome to the new academic year! The NMC Academy portal is now live for AY 2026–2027.\n\nPlease log in to view your class schedules, course materials, and grades. If you experience any technical issues accessing the portal, please contact the IT Services department directly.\n\nWarm regards,\nOffice of Academic Affairs`,
    "general", lmsAdminId, 1, "2026-08-01 08:00:00"
  );

  insAnn.run(
    "LMS System Maintenance — August 10, 2026",
    `Please be informed that the NMC Academy portal will undergo scheduled maintenance on August 10, 2026 from 12:00 AM to 4:00 AM.\n\nDuring this window, the portal will be unavailable. Please download any materials you need beforehand.\n\nFor urgent technical concerns during the maintenance window, you may reach the IT Services team directly.\n\nThank you for your understanding.`,
    "system", itAdminId, 0, "2026-08-05 09:00:00"
  );

  insAnn.run(
    "Enrollment Guidelines for First Semester",
    `To all enrolled students:\n\nPlease review your enrolled subjects under the Courses section. If you see any discrepancies in your schedule or enrolled units, coordinate with your respective department registrar immediately.\n\nDeadline for adding/dropping subjects: August 20, 2026.\n\nFor system-related enrollment issues, coordinate with IT Services.`,
    "academic", lmsAdminId, 0, "2026-08-08 10:00:00"
  );

  insAnn.run(
    "Midterm Examination Schedule — First Semester AY 2026–2027",
    `The midterm examination period is scheduled for October 6–10, 2026.\n\nDetailed room assignments will be posted on the bulletin board and uploaded to each course page one week before the exam.\n\nStudents must present their Student ID to the proctor. No ID, no entry.\n\nGood luck!`,
    "academic", santosId, 0, "2026-08-15 14:00:00"
  );

  // ── IT Admin inbox ─────────────────────────────────────────────────────────
  const insInbox = db.prepare(`
    INSERT INTO inbox (recipient_id, sender_name, subject, body, attachment, sent_at)
    VALUES (?,?,?,?,?,?)
  `);

  insInbox.run(
    itAdminId,
    "NMC Academy System",
    "Password Reset Request — Student Portal",
    `This is an automated notification.\n\nA password reset was requested for the following account:\n\nName:  Maria Garcia\nEmail: m.garcia@student.nmc.local\n\nIf this was not initiated by the student, no action is required. Reset tokens expire after 1 hour.\n\n— NMC Academy System`,
    null,
    "2026-01-10 08:43:00"
  );

  insInbox.run(
    itAdminId,
    "Patricia Reyes (HR)",
    "New Faculty Account Request",
    `Hi Ricardo,\n\nWe have a new faculty member joining the College of Engineering next week — Prof. Jose Reyes. Please provision his NMC Academy account at your earliest convenience.\n\nDetails:\nName: Jose Reyes\nDepartment: College of Engineering\nRole: Instructor\n\nLet me know once it's done so we can send him the onboarding email.\n\nThanks,\nPatricia`,
    null,
    "2026-01-28 09:15:00"
  );

  insInbox.run(
    itAdminId,
    "Alma Cruz (Registrar)",
    "Printer in Records Room Still Not Working",
    `Hi Ricardo,\n\nThe printer in the records room (3rd floor, Rm 304) is still not printing even after I restarted it. It's showing "offline" on the computer but it's turned on.\n\nWe have a batch of document requests to print today. Can someone from IT take a look?\n\nThanks,\nAlma`,
    null,
    "2026-02-14 10:02:00"
  );

  insInbox.run(
    itAdminId,
    "Rodrigo Lim (Finance)",
    "Microsoft 365 License Renewal — Action Required",
    `Hi Ricardo,\n\nOur Microsoft 365 Business subscription is up for renewal on March 31. Finance needs the current license count and any additions before we process the PO.\n\nPlease send me the updated headcount for IT-managed accounts by March 10.\n\nThanks,\nRodrigo`,
    null,
    "2026-03-03 14:30:00"
  );

  insInbox.run(
    itAdminId,
    "Cynthia Dela Rosa (Admissions)",
    "Account Locked — Cannot Log In",
    `Hi,\n\nI've been locked out of my Admission portal account since this morning. I tried resetting the password but it says the link expired.\n\nCan you unlock it or reset it manually? I have applicants to process today.\n\nEmail: c.delarosa@nmc.local\n\nThanks,\nCynthia`,
    null,
    "2026-03-19 08:58:00"
  );

  // Q1 backup — password stated here
  insInbox.run(
    itAdminId,
    "Veronica Bautista (LMS Admin)",
    "Q1 Academy Backup Completed",
    `Hi Ricardo,\n\nJust a heads up — Q1 backup of the Academy portal is done.\n\nArchive: NMC_Academy_Backup_Q1.zip\nPassword: NMC@2026\n\nIt's in the usual network folder. Let me know if you can't access it.\n\nThanks,\nVeronica`,
    null,
    "2026-04-02 10:15:00"
  );

  insInbox.run(
    itAdminId,
    "NMC Systems",
    "SSL Certificate Expiry Notice — www.nmc.local",
    `This is an automated alert from the NMC web infrastructure monitoring system.\n\nThe SSL certificate for www.nmc.local will expire in 30 days.\n\nPlease renew the certificate before May 22, 2026 to avoid service interruption.\n\nFor assistance, contact the hosting provider or refer to the IT runbook.\n\n— NMC Infrastructure Monitor`,
    null,
    "2026-04-22 06:00:00"
  );

  insInbox.run(
    itAdminId,
    "Ramon Aquino (IT)",
    "Scheduled Server Maintenance — This Saturday",
    `Hi Ricardo,\n\nReminder: we have scheduled maintenance this Saturday (May 9) from 12 AM to 4 AM for the main server and network switches.\n\nAll campus-hosted services will be unavailable during the window. I'll send the advisory email to department heads before Friday EOD.\n\nLet me know if anything else needs to be included in the maintenance window.\n\n— Ramon`,
    null,
    "2026-05-07 16:45:00"
  );

  insInbox.run(
    itAdminId,
    "Patricia Reyes (HR)",
    "New Employee Account — Admissions Officer",
    `Hi Ricardo,\n\nWe have a new Admissions Officer starting June 16:\n\nName: Donna Mae Santos\nEmail: dm.santos@nmc.local\nRole: Admission Staff\n\nPlease provision her portal accounts (Admission and Academy) and send login credentials to her personal email on file.\n\nThanks,\nPatricia`,
    null,
    "2026-06-05 11:20:00"
  );

  insInbox.run(
    itAdminId,
    "Veronica Bautista (LMS Admin)",
    "Academy Portal — DB Migration Complete",
    `Hi Ricardo,\n\nJust wanted to confirm the database migration for the Academy portal is done. Moved to the new server last night without issues.\n\nAll data intact. Students and faculty can now log in normally.\n\nLet me know if you notice anything off on your end.\n\nVeronica`,
    null,
    "2026-06-28 09:05:00"
  );

  insInbox.run(
    itAdminId,
    "Cynthia Dela Rosa (Admissions)",
    "Laptop Replacement Request",
    `Hi Ricardo,\n\nMy office laptop (Asset tag: NMC-LT-047) has been running very slowly for the past few weeks and the battery only lasts about 30 minutes now. I submitted a replacement request through the HR form last week.\n\nCan IT evaluate it this week? Midterm season is coming up and I can't afford downtime.\n\nThanks,\nCynthia`,
    null,
    "2026-07-18 14:10:00"
  );

  // Onboarding — no attachment
  insInbox.run(
    itAdminId,
    "Veronica Bautista (LMS Admin)",
    "Portal Onboarding Complete",
    `Hi Ricardo,\n\nThe NMC Academy portal has been configured and is now live. All faculty and student accounts have been provisioned.\n\nPlease verify that the IT Services account is correctly set up and notify me if there are any access issues.\n\nThanks,\nVeronica`,
    null,
    "2026-08-01 08:00:00"
  );

  // August backup — references same password, has attachment
  insInbox.run(
    itAdminId,
    "Veronica Bautista (LMS Admin)",
    "Academy Portal Backup — August 2026",
    `Hi Ricardo,\n\nLatest backup has been uploaded. Same password as the previous backup.\n\nPlease verify the archive is intact on your end and store a copy in the IT vault.\n\nThanks,\nVeronica`,
    "NMC_Academy_Backup.zip",
    "2026-08-01 09:30:00"
  );

  insInbox.run(
    itAdminId,
    "NMC Enrollment System",
    "Error Log — Duplicate Student ID Detected",
    `This is an automated error report from the NMC Enrollment System.\n\nTimestamp: 2026-08-22 03:14:07\nError: Duplicate entry for student_id '2026-00003'\nTable: students\nAction: INSERT ignored\n\nThis may indicate a data entry issue during batch enrollment upload. Please review and correct the record in the registrar database.\n\n— NMC Enrollment System`,
    null,
    "2026-08-22 03:14:00"
  );


  db.close();
  console.log("[academy] Database initialized and seeded.");
}

module.exports = { initDb, DB_PATH, DATA_DIR };
