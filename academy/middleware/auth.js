"use strict";
const jwt = require("jsonwebtoken");

const JWT_SECRET      = process.env.JWT_SECRET      || "nmc-academy-jwt-k3y-2026!";
const INTERNAL_SECRET = process.env.INTERNAL_SECRET || "nmc-admission-s3cr3t-2026!";

function verifyToken(req, res, next) {
  const auth = req.headers.authorization || "";
  const token = auth.startsWith("Bearer ") ? auth.slice(7) : null;
  if (!token) return res.status(401).json({ error: "Authentication required" });
  try {
    req.user = jwt.verify(token, JWT_SECRET);
    next();
  } catch {
    return res.status(401).json({ error: "Invalid or expired token" });
  }
}

function requireRole(...roles) {
  return (req, res, next) => {
    if (!roles.includes(req.user?.role)) {
      return res.status(403).json({ error: "Insufficient privileges" });
    }
    next();
  };
}

function verifyInternal(req, res, next) {
  const token = req.headers["x-internal-token"];
  if (!token || token !== INTERNAL_SECRET) {
    return res.status(401).json({ error: "Unauthorized" });
  }
  next();
}

function signToken(payload) {
  return jwt.sign(payload, JWT_SECRET, { expiresIn: "8h" });
}

module.exports = { verifyToken, requireRole, verifyInternal, signToken, JWT_SECRET };
