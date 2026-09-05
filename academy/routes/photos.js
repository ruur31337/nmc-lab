"use strict";
/*
 * Profile photo upload/serve
 *
 * POST /api/users/me/photo   — upload own photo (authenticated)
 * GET  /api/users/:id/photo  — serve photo by user ID
 *
 */
const express  = require("express");
const multer   = require("multer");
const path     = require("path");
const fs       = require("fs");
const Database = require("better-sqlite3");
const { DB_PATH, DATA_DIR } = require("../db/init");
const { verifyToken } = require("../middleware/auth");

const router    = express.Router();
const PHOTO_DIR = path.join(DATA_DIR, "photos");
fs.mkdirSync(PHOTO_DIR, { recursive: true });

const storage = multer.diskStorage({
  destination: PHOTO_DIR,
  filename: (req, file, cb) => {
    const ext = path.extname(file.originalname).toLowerCase();
    cb(null, `user_${req.user.sub}${ext}`);
  },
});
const upload = multer({
  storage,
  limits: { fileSize: 2 * 1024 * 1024 },
  fileFilter: (req, file, cb) => {
    const ok = [".jpg", ".jpeg", ".png"].includes(
      path.extname(file.originalname).toLowerCase()
    );
    cb(ok ? null : new Error("Only JPG/PNG allowed"), ok);
  },
});

// POST /api/users/me/photo
router.post("/me/photo", verifyToken, upload.single("photo"), (req, res) => {
  if (!req.file) return res.status(400).json({ error: "No photo uploaded" });

  const db = new Database(DB_PATH);
  db.prepare("UPDATE users SET photo = ? WHERE id = ?")
    .run(req.file.filename, req.user.sub);
  db.close();

  res.json({
    message: "Photo updated.",
    photo:   `/api/users/photos/${req.file.filename}`,
  });
});

// GET /api/users/photos/:filename — serve a stored photo file
router.get("/photos/:filename", verifyToken, (req, res) => {
  const filename = path.basename(req.params.filename); // strip traversal
  const filePath = path.join(PHOTO_DIR, filename);
  if (!fs.existsSync(filePath))
    return res.status(404).json({ error: "Photo not found" });
  res.sendFile(filePath);
});

// GET /api/users/:id/photo — redirect to actual photo file

router.get("/:id/photo", verifyToken, (req, res) => {
  const db   = new Database(DB_PATH, { readonly: true });
  const user = db.prepare("SELECT photo FROM users WHERE id = ?").get(req.params.id);
  db.close();

  if (!user || !user.photo)
    return res.status(404).json({ error: "No photo on file" });

  res.redirect(`/api/users/photos/${user.photo}`);
});

module.exports = router;
