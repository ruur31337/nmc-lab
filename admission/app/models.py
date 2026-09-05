import uuid
from datetime import datetime, timezone
from .extensions import db

VALID_ROLES = {"student", "admission_staff", "it_staff", "admin"}


class User(db.Model):
    __tablename__ = "users"

    id            = db.Column(db.Integer, primary_key=True)
    uuid          = db.Column(db.String(36), unique=True, nullable=False,
                              default=lambda: str(uuid.uuid4()))
    email         = db.Column(db.String(120), unique=True, nullable=False)
    password_hash = db.Column(db.String(128), nullable=False)
    first_name    = db.Column(db.String(60), nullable=False)
    last_name     = db.Column(db.String(60), nullable=False)
    phone         = db.Column(db.String(20))
    date_of_birth = db.Column(db.String(20))
    address       = db.Column(db.String(200))
    zip_code      = db.Column(db.String(10))
    # Roles: student | admission_staff | it_staff | admin | None (no role yet)
    role          = db.Column(db.String(20), nullable=True, default=None)
    # is_active=False = pending admin approval (v2 registrations)
    # is_active=True  = approved / staff / API-created accounts
    is_active     = db.Column(db.Boolean, default=True)
    created_at    = db.Column(db.DateTime, default=lambda: datetime.now(timezone.utc))

    applications  = db.relationship("Application", backref="applicant", lazy=True)
    reset_tokens  = db.relationship("PasswordReset", backref="user", lazy=True)

    def to_dict(self):
        return {
            "uuid":       self.uuid,
            "email":      self.email,
            "first_name": self.first_name,
            "last_name":  self.last_name,
            "role":       self.role,
        }

    def to_public_dict(self):
        """Subset returned to non-admin callers."""
        return {
            "uuid":       self.uuid,
            "first_name": self.first_name,
            "last_name":  self.last_name,
        }


class Application(db.Model):
    __tablename__ = "applications"

    id           = db.Column(db.Integer, primary_key=True)
    ref_no       = db.Column(db.String(12), unique=True, nullable=False,
                             default=lambda: "NMC-" + uuid.uuid4().hex[:8].upper())
    user_id      = db.Column(db.Integer, db.ForeignKey("users.id"), nullable=False)
    program      = db.Column(db.String(80), nullable=False)
    strand       = db.Column(db.String(80))
    prev_school  = db.Column(db.String(120))
    gwa          = db.Column(db.Float)
    photo        = db.Column(db.String(200))   # relative path to uploaded 2x2 photo
    status       = db.Column(db.String(20), default="pending")
    remarks      = db.Column(db.Text)
    submitted_at = db.Column(db.DateTime, default=lambda: datetime.now(timezone.utc))
    updated_at   = db.Column(db.DateTime, default=lambda: datetime.now(timezone.utc),
                             onupdate=lambda: datetime.now(timezone.utc))

    def to_dict(self):
        return {
            "ref_no":      self.ref_no,
            "program":     self.program,
            "strand":      self.strand,
            "prev_school": self.prev_school,
            "gwa":         self.gwa,
            "photo":       self.photo,
            "status":      self.status,
            "remarks":     self.remarks,
            "submitted_at": self.submitted_at.isoformat() if self.submitted_at else None,
        }


class PasswordReset(db.Model):
    __tablename__ = "password_resets"

    id         = db.Column(db.Integer, primary_key=True)
    user_id    = db.Column(db.Integer, db.ForeignKey("users.id"), nullable=False)
    token      = db.Column(db.String(64), unique=True, nullable=False,
                           default=lambda: uuid.uuid4().hex + uuid.uuid4().hex[:16])
    expires_at = db.Column(db.DateTime, nullable=False)
    used       = db.Column(db.Boolean, default=False)
    created_at = db.Column(db.DateTime, default=lambda: datetime.now(timezone.utc))
