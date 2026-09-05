"use strict";
const express  = require("express");
const Database = require("better-sqlite3");
const { DB_PATH } = require("../../db/init");
const { verifyToken } = require("../../middleware/auth");

const router = express.Router();

router.get("/profile", verifyToken, (req, res) => {
  const id = req.query.user_id;
  if (!id) return res.status(400).json({ error: "user_id required" });
  const db   = new Database(DB_PATH, { readonly: true });
  const user = db.prepare(`
    SELECT id,uuid,email,first_name,last_name,role,student_id,department,created_at
    FROM users WHERE id = ?
  `).get(id);
  db.close();
  if (!user) return res.status(404).json({ error: "User not found" });
  res.json({ user });
});

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

module.exports = router;
