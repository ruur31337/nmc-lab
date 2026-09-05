"use strict";
const express  = require("express");
const Database = require("better-sqlite3");
const { DB_PATH } = require("../db/init");
const { verifyToken } = require("../middleware/auth");

const router = express.Router();

// GET /api/courses — own enrolled courses (student) or all courses (admin/instructor)
router.get("/", verifyToken, (req, res) => {
  const db = new Database(DB_PATH, { readonly: true });
  let rows;

  if (req.user.role === "student") {
    rows = db.prepare(`
      SELECT c.id, c.code, c.title, c.units, c.schedule, c.room,
             u.first_name||' '||u.last_name AS instructor,
             e.grade, e.status
      FROM enrollments e
      JOIN courses c ON e.course_id = c.id
      JOIN users u ON c.instructor_id = u.id
      WHERE e.user_id = ?
    `).all(req.user.sub);
  } else {
    rows = db.prepare(`
      SELECT c.id, c.code, c.title, c.units, c.schedule, c.room,
             u.first_name||' '||u.last_name AS instructor
      FROM courses c
      JOIN users u ON c.instructor_id = u.id
      ORDER BY c.code
    `).all();
  }
  db.close();
  res.json({ courses: rows });
});

// GET /api/courses/:id
router.get("/:id", verifyToken, (req, res) => {
  const db = new Database(DB_PATH, { readonly: true });
  const course = db.prepare(`
    SELECT c.*, u.first_name||' '||u.last_name AS instructor,
           u.email AS instructor_email
    FROM courses c JOIN users u ON c.instructor_id = u.id
    WHERE c.id = ?
  `).get(req.params.id);
  db.close();
  if (!course) return res.status(404).json({ error: "Course not found" });
  res.json({ course });
});

module.exports = router;
