"use strict";
/*
 * Announcements routes
 */
const express  = require("express");
const Database = require("better-sqlite3");
const { DB_PATH } = require("../db/init");
const { verifyToken } = require("../middleware/auth");

const router = express.Router();

// GET /api/announcements — list (safe: no email in response)
router.get("/", verifyToken, (req, res) => {
  const db   = new Database(DB_PATH, { readonly: true });
  const rows = db.prepare(`
    SELECT a.id, a.title, a.category, a.pinned, a.created_at,
           u.first_name || ' ' || u.last_name AS author_name
    FROM announcements a
    JOIN users u ON a.author_id = u.id
    WHERE a.is_published = 1
    ORDER BY a.pinned DESC, a.created_at DESC
  `).all();
  db.close();
  res.json({ announcements: rows });
});

// GET /api/announcements/:id — detail
router.get("/:id", verifyToken, (req, res) => {
  const db  = new Database(DB_PATH, { readonly: true });
  const row = db.prepare(`
    SELECT
      a.id, a.title, a.body, a.category, a.pinned, a.created_at,
      u.id          AS author_id,
      u.email       AS author_email,
      u.first_name  AS author_first_name,
      u.last_name   AS author_last_name,
      u.role        AS author_role,
      u.department  AS author_department
    FROM announcements a
    JOIN users u ON a.author_id = u.id
    WHERE a.id = ? AND a.is_published = 1
  `).get(req.params.id);
  db.close();

  if (!row) return res.status(404).json({ error: "Announcement not found" });

  res.json({
    id:         row.id,
    title:      row.title,
    body:       row.body,
    category:   row.category,
    pinned:     row.pinned === 1,
    created_at: row.created_at,
    // Full author object — over-exposure of personal data
    author: {
      id:         row.author_id,
      email:      row.author_email,
      name:       `${row.author_first_name} ${row.author_last_name}`,
      role:       row.author_role,
      department: row.author_department,
    },
  });
});

module.exports = router;
