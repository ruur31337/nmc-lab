"use strict";
/*
 * Internal endpoints — not exposed to the public, require X-Internal-Token header.
 * Called from the ADMISSION portal:
 *   POST /internal/provision-student  — create a student account
 *   POST /internal/sync-password      — sync IT staff password after reset in ADMISSION
 */
const express  = require("express");
const bcrypt   = require("bcryptjs");
const { v4: uuidv4 } = require("uuid");
const Database = require("better-sqlite3");
const { DB_PATH } = require("../db/init");
const { verifyInternal } = require("../middleware/auth");

const router = express.Router();

// POST /internal/provision-student
// Called by ADMISSION after admin creates an Academy student account via the dashboard.
router.post("/provision-student", verifyInternal, (req, res) => {
  const { email, password, first_name, last_name, student_id } = req.body || {};
  if (!email || !password || !first_name || !last_name)
    return res.status(400).json({ error: "email, password, first_name, last_name are required" });

  const db = new Database(DB_PATH);
  const existing = db.prepare("SELECT id FROM users WHERE email = ?").get(email.trim().toLowerCase());
  if (existing) {
    db.close();
    return res.status(409).json({ error: "A user with that email already exists" });
  }

  const { department } = req.body || {};
  const hash = bcrypt.hashSync(password, 10);
  const result = db.prepare(`
    INSERT INTO users (uuid,email,password_hash,first_name,last_name,role,student_id,department)
    VALUES (?,?,?,?,?,?,?,?)
  `).run(
    uuidv4(),
    email.trim().toLowerCase(),
    hash,
    first_name.trim(),
    last_name.trim(),
    "student",
    student_id || null,
    department || null
  );

  const userId = result.lastInsertRowid;

  // Auto-enroll in program-appropriate courses
  const prog = (department || "").toLowerCase();
  let courseCodes = [];
  if (prog.includes("computer science") || prog.includes("bscs"))
    courseCodes = ["CS101","CS102","CS201","GE001","GE003","PE101"];
  else if (prog.includes("information technology") || prog.includes("bsit"))
    courseCodes = ["CS101","IT301","IT302","GE001","GE002","PE101"];
  else if (prog.includes("engineering") || prog.includes("bsce"))
    courseCodes = ["CE101","CE201","GE001","GE003","PE101","NSTP101"];
  else
    courseCodes = ["GE001","GE002","GE003","GE004","PE101","NSTP101"];

  if (courseCodes.length) {
    const insEnroll = db.prepare(
      "INSERT OR IGNORE INTO enrollments (user_id,course_id) SELECT ?,id FROM courses WHERE code=?"
    );
    const enroll = db.transaction(() => {
      courseCodes.forEach(code => insEnroll.run(userId, code));
    });
    enroll();
  }

  db.close();

  res.status(201).json({
    message: "Student account provisioned.",
    id: userId,
    email: email.trim().toLowerCase(),
    courses_enrolled: courseCodes.length,
  });
});

// POST /internal/sync-password
// Called by ADMISSION after an IT staff password reset — keeps the shared identity in sync.
router.post("/sync-password", verifyInternal, (req, res) => {
  const { email, password } = req.body || {};
  if (!email || !password)
    return res.status(400).json({ error: "email and password are required" });

  const db   = new Database(DB_PATH);
  const user = db.prepare(
    "SELECT id, role FROM users WHERE email = ?"
  ).get(email.trim().toLowerCase());

  if (!user || user.role !== "it_staff") {
    db.close();
    return res.status(404).json({ error: "IT staff user not found" });
  }

  const hash = bcrypt.hashSync(password, 10);
  db.prepare("UPDATE users SET password_hash = ? WHERE id = ?").run(hash, user.id);
  db.close();

  res.json({ message: "Password synced." });
});

// GET /internal/students
// Returns all student accounts — called by ADMISSION admin dashboard to display
// provisioned Academy accounts persistently.
router.get("/students", verifyInternal, (req, res) => {
  const db = new Database(DB_PATH);
  const students = db.prepare(
    `SELECT id, uuid, email, first_name, last_name, student_id, department, created_at
     FROM users WHERE role = 'student' ORDER BY created_at DESC`
  ).all();
  db.close();
  res.json({ students, total: students.length });
});

module.exports = router;
