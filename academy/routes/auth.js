"use strict";
const express  = require("express");
const bcrypt   = require("bcryptjs");
const crypto   = require("crypto");
const Database = require("better-sqlite3");
const { DB_PATH } = require("../db/init");
const { signToken, verifyToken } = require("../middleware/auth");

const router = express.Router();

// POST /api/auth/login
router.post("/login", (req, res) => {
  const { email, password } = req.body || {};
  if (!email || !password)
    return res.status(400).json({ error: "email and password are required" });

  const db   = new Database(DB_PATH, { readonly: true });
  const user = db.prepare(
    "SELECT * FROM users WHERE email = ? AND is_active = 1"
  ).get(email.trim().toLowerCase());
  db.close();

  if (!user || !bcrypt.compareSync(password, user.password_hash))
    return res.status(401).json({ error: "Invalid credentials" });

  const token = signToken({
    sub:   user.id,
    uuid:  user.uuid,
    role:  user.role,
    email: user.email,
    name:  `${user.first_name} ${user.last_name}`,
  });

  res.json({
    access_token: token,
    role:         user.role,
    name:         `${user.first_name} ${user.last_name}`,
  });
});

// GET /api/auth/me  — decode token, return current user
router.get("/me", verifyToken, (req, res) => {
  const db   = new Database(DB_PATH, { readonly: true });
  const user = db.prepare(
    "SELECT id,uuid,email,first_name,last_name,role,student_id,department FROM users WHERE id=?"
  ).get(req.user.sub);
  db.close();
  if (!user) return res.status(404).json({ error: "User not found" });
  res.json({ user });
});

// POST /api/auth/forgot-password
router.post("/forgot-password", (req, res) => {
  const { email } = req.body || {};
  if (!email)
    return res.status(400).json({ error: "email is required" });

  const db   = new Database(DB_PATH);
  const user = db.prepare(
    "SELECT id,first_name,last_name FROM users WHERE email=? AND is_active=1"
  ).get(email.trim().toLowerCase());

  if (user) {
    const token   = crypto.randomBytes(24).toString("hex");
    const itAdmin = db.prepare(
      "SELECT id FROM users WHERE role='it_staff' AND is_active=1 LIMIT 1"
    ).get();

    if (itAdmin) {
      db.prepare(`
        INSERT INTO inbox (recipient_id,sender_name,subject,body,sent_at)
        VALUES (?,'NMC Academy System','Password Reset Request — Student Portal',?,CURRENT_TIMESTAMP)
      `).run(
        itAdmin.id,
        `A password reset was requested for the following student account:\n\n` +
        `Name:  ${user.first_name} ${user.last_name}\n` +
        `Email: ${email.trim().toLowerCase()}\n\n` +
        `Reset Token: ${token}\n\n` +
        `To reset the student's password, use the admin panel or apply the token via the reset endpoint.\n\n` +
        `This token expires in 1 hour. If this request was not made by the student, disregard this message.\n\n` +
        `— NMC Academy System`
      );
    }
  }
  db.close();

  // Always return generic response (no user enumeration)
  res.json({
    message: "If an account with that email exists, a password reset request has been sent to IT Services. You will be contacted within 1–2 business days.",
  });
});

// POST /api/auth/reset-password  (IT Admin uses token from inbox to set new password)
router.post("/reset-password", (req, res) => {
  const { token, email, password } = req.body || {};
  if (!token || !email || !password)
    return res.status(400).json({ error: "token, email, and password are required" });
  if (password.length < 8)
    return res.status(400).json({ error: "Password must be at least 8 characters" });

  const db      = new Database(DB_PATH);
  const itAdmin = db.prepare(
    "SELECT id FROM users WHERE role='it_staff' AND is_active=1 LIMIT 1"
  ).get();

  const msg = itAdmin
    ? db.prepare(
        "SELECT id FROM inbox WHERE recipient_id=? AND body LIKE ? AND sent_at >= datetime('now','-1 hour')"
      ).get(itAdmin.id, `%${token}%`)
    : null;

  if (!msg) {
    db.close();
    return res.status(400).json({ error: "Invalid or expired reset token" });
  }

  const user = db.prepare("SELECT id FROM users WHERE email=?").get(email.trim().toLowerCase());
  if (!user) {
    db.close();
    return res.status(404).json({ error: "User not found" });
  }

  db.prepare("UPDATE users SET password_hash=? WHERE id=?")
    .run(bcrypt.hashSync(password, 10), user.id);
  db.prepare("DELETE FROM inbox WHERE id=?").run(msg.id);
  db.close();

  res.json({ message: "Password updated successfully." });
});

module.exports = router;
