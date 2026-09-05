"use strict";
const express  = require("express");
const path     = require("path");
const fs       = require("fs");
const Database = require("better-sqlite3");
const { DB_PATH, DATA_DIR } = require("../../db/init");
const { verifyToken } = require("../../middleware/auth");

const router = express.Router();

router.get("/", verifyToken, (req, res) => {
  const db   = new Database(DB_PATH, { readonly: true });
  const rows = db.prepare(`
    SELECT id, recipient_id, sender_name, subject, attachment IS NOT NULL AS has_attachment,
           is_read, sent_at
    FROM inbox
    ORDER BY sent_at DESC
  `).all();
  db.close();
  res.json({ messages: rows });
});

router.get("/download/:filename", verifyToken, (req, res) => {
  const db     = new Database(DB_PATH, { readonly: true });
  const exists = db.prepare(
    "SELECT 1 FROM inbox WHERE attachment = ? LIMIT 1"
  ).get(req.params.filename);
  db.close();

  if (!exists) return res.status(404).json({ error: "Attachment not found" });

  const filePath = path.join(DATA_DIR, "inbox", req.params.filename);
  if (!fs.existsSync(filePath))
    return res.status(404).json({ error: "File not on server" });

  res.download(filePath, req.params.filename);
});

router.get("/:id", verifyToken, (req, res) => {
  const db  = new Database(DB_PATH);
  const msg = db.prepare(
    "SELECT * FROM inbox WHERE id = ?"
  ).get(req.params.id);

  if (!msg) {
    db.close();
    return res.status(404).json({ error: "Message not found" });
  }

  db.prepare("UPDATE inbox SET is_read = 1 WHERE id = ?").run(msg.id);
  db.close();
  res.json({ message: msg });
});

module.exports = router;
