"use strict";
const express  = require("express");
const Database = require("better-sqlite3");
const { DB_PATH } = require("../db/init");
const { verifyToken, requireRole } = require("../middleware/auth");

const router = express.Router();

// GET /api/users/me — own profile
router.get("/me", verifyToken, (req, res) => {
  const db   = new Database(DB_PATH, { readonly: true });
  const user = db.prepare(`
    SELECT id,uuid,email,first_name,last_name,role,student_id,department,created_at
    FROM users WHERE id = ?
  `).get(req.user.sub);
  const courses = db.prepare(`
    SELECT c.code, c.title, c.units, c.schedule, c.room,
           u.first_name||' '||u.last_name AS instructor, e.grade, e.status
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    JOIN users u ON c.instructor_id = u.id
    WHERE e.user_id = ?
  `).all(req.user.sub);
  db.close();
  if (!user) return res.status(404).json({ error: "User not found" });
  res.json({ user, courses });
});

// GET /api/users/:id — view profile by ID

// Student can hit /api/users/1 → gets IT Admin email
router.get("/:id", verifyToken, (req, res) => {
  const db   = new Database(DB_PATH, { readonly: true });
  const user = db.prepare(`
    SELECT id,uuid,email,first_name,last_name,role,student_id,department,created_at
    FROM users WHERE id = ?
  `).get(req.params.id);
  db.close();
  if (!user) return res.status(404).json({ error: "User not found" });

  res.json({ user });
});

// GET /api/users — admin only
router.get("/", verifyToken, requireRole("admin", "it_staff"), (req, res) => {
  const db   = new Database(DB_PATH, { readonly: true });
  const rows = db.prepare(`
    SELECT id,uuid,email,first_name,last_name,role,student_id,department,is_active,created_at
    FROM users ORDER BY role, last_name
  `).all();
  db.close();
  res.json({ users: rows, total: rows.length });
});

module.exports = router;
