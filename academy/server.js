"use strict";
const express = require("express");
const path    = require("path");

// Init DB first (creates tables + seeds if empty)
const { initDb } = require("./db/init");
initDb();

const app = express();
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Static assets (CSS, JS, images)
app.use("/static", express.static(path.join(__dirname, "public")));

// ── API routes ──────────────────────────────────────────────────────────────
app.use("/api/auth",          require("./routes/auth"));
app.use("/api/announcements", require("./routes/announcements"));
app.use("/api/users",         require("./routes/photos"));   // photo upload/serve (mounted first — specific paths)
app.use("/api/users",         require("./routes/users"));
app.use("/api/courses",       require("./routes/courses"));

// Inbox — v1 (legacy) and v2 (current)
app.use("/api/v1/inbox",      require("./routes/v1/inbox"));
app.use("/api/v2/inbox",      require("./routes/v2/inbox"));

// ── Internal routes (X-Internal-Token required) ─────────────────────────────
app.use("/internal",          require("./routes/internal"));

// ── HTML page routes ────────────────────────────────────────────────────────
const views = path.join(__dirname, "views");
app.get("/",              (_, res) => res.sendFile(path.join(views, "index.html")));
app.get("/login",         (_, res) => res.sendFile(path.join(views, "index.html")));
app.get("/dashboard",     (_, res) => res.sendFile(path.join(views, "dashboard.html")));
app.get("/announcements", (_, res) => res.sendFile(path.join(views, "announcements.html")));
app.get("/announcements/:id",(_, res) => res.sendFile(path.join(views, "announcement.html")));
app.get("/inbox",         (_, res) => res.sendFile(path.join(views, "inbox.html")));
app.get("/profile",       (_, res) => res.sendFile(path.join(views, "profile.html")));

// 404 for unknown API calls
app.use("/api/*", (_, res) => res.status(404).json({ error: "Not found" }));

const PORT = process.env.PORT || 4000;
app.listen(PORT, () => {
  console.log(`[academy] NMC Academy running on port ${PORT}`);
});
