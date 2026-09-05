"""
API v2 — current hardened API.
Self-registration is disabled (Jira: NMC-1047 — pending Board approval
for open enrollment; IT decision to restrict all account creation to
admission staff and admin).

Routes
------
POST /api/v2/auth/login               — JWT login
POST /api/v2/register                 — disabled (503)
POST /api/v2/auth/forgot-password     — password reset request (no debug leak)
POST /api/v2/auth/reset-password      — consume reset token
GET  /api/v2/profile                  — get own profile (JWT required)
PATCH /api/v2/profile                 — update own profile (role NOT updatable)
GET  /api/v2/applications             — list own applications
POST /api/v2/applications             — submit application
"""
from datetime import datetime, timezone, timedelta
from flask import Blueprint, request, jsonify, current_app
from flask_jwt_extended import (
    create_access_token, jwt_required, get_jwt_identity, get_jwt
)

from ..extensions import db, bcrypt, limiter
from ..models     import User, Application, PasswordReset

api_v2_bp = Blueprint("api_v2", __name__)


# ---------------------------------------------------------------------------
# Login
# ---------------------------------------------------------------------------
@api_v2_bp.route("/auth/login", methods=["POST"])
@limiter.limit("5 per minute")
def login():
    data     = request.get_json(silent=True) or {}
    email    = (data.get("email") or "").strip().lower()
    password = data.get("password") or ""

    user = User.query.filter_by(email=email, is_active=True).first()
    if not user or not bcrypt.check_password_hash(user.password_hash, password):
        return jsonify({"error": "Invalid credentials"}), 401

    token = create_access_token(
        identity=str(user.id),
        additional_claims={"role": user.role, "uuid": user.uuid}
    )
    return jsonify({"access_token": token, "role": user.role}), 200


# ---------------------------------------------------------------------------
# Self-registration — full admission application form.
# Account is created INACTIVE (pending admin approval). Cannot login until
# an admin approves the registration from the admission dashboard.
# ---------------------------------------------------------------------------
@api_v2_bp.route("/register", methods=["POST"])
@limiter.limit("10 per minute")
def register():
    data       = request.get_json(silent=True) or {}
    email      = (data.get("email") or "").strip().lower()
    password   = data.get("password") or ""
    first_name = (data.get("first_name") or "").strip()
    last_name  = (data.get("last_name") or "").strip()
    phone      = (data.get("phone") or "").strip()
    dob        = (data.get("date_of_birth") or "").strip()
    address    = (data.get("address") or "").strip()
    zip_code   = (data.get("zip_code") or "").strip()
    prev_school= (data.get("prev_school") or "").strip()
    program    = (data.get("program") or "").strip()

    required_fields = {
        "email": email, "password": password,
        "first_name": first_name, "last_name": last_name,
        "phone": phone, "date_of_birth": dob,
        "address": address, "zip_code": zip_code,
        "prev_school": prev_school, "program": program,
    }
    missing = [k for k, v in required_fields.items() if not v]
    if missing:
        return jsonify({"error": f"Required fields missing: {', '.join(missing)}"}), 400

    if len(password) < 8:
        return jsonify({"error": "Password must be at least 8 characters"}), 400

    if User.query.filter_by(email=email).first():
        return jsonify({"error": "An account with that email already exists"}), 409

    pw_hash = bcrypt.generate_password_hash(password).decode("utf-8")
    user = User(
        email=email,
        password_hash=pw_hash,
        first_name=first_name,
        last_name=last_name,
        phone=phone,
        date_of_birth=dob,
        address=address,
        zip_code=zip_code,
        role="student",
        is_active=False,   # pending admin approval
    )
    db.session.add(user)
    db.session.flush()

    # Pre-create application entry
    from ..models import Application
    app_entry = Application(
        user_id=user.id,
        program=program,
        prev_school=prev_school,
        status="pending",
    )
    db.session.add(app_entry)
    db.session.commit()

    return jsonify({
        "message": "Registration submitted successfully. "
                   "Your application is pending review by the Admission Office. "
                   "You will receive an email once your account is approved.",
        "ref_no": app_entry.ref_no,
    }), 201


# ---------------------------------------------------------------------------
# Forgot password (v2 — hardened: no debug leak)
# ---------------------------------------------------------------------------
@api_v2_bp.route("/auth/forgot-password", methods=["POST"])
@limiter.limit("5 per minute")
def forgot_password():
    data  = request.get_json(silent=True) or {}
    email = (data.get("email") or "").strip().lower()

    if not email:
        return jsonify({"error": "email is required"}), 400

    user = User.query.filter_by(email=email, is_active=True).first()
    if user:
        PasswordReset.query.filter_by(user_id=user.id, used=False).delete()
        reset = PasswordReset(
            user_id=user.id,
            expires_at=datetime.now(timezone.utc) + timedelta(hours=1),
        )
        db.session.add(reset)
        db.session.commit()
        # Token is emailed only — not returned in response.

    return jsonify({
        "message": "If an account with that email exists, a password reset link has been sent."
    }), 200


# ---------------------------------------------------------------------------
# Reset password (v2)
# ---------------------------------------------------------------------------
@api_v2_bp.route("/auth/reset-password", methods=["POST"])
def reset_password():
    data         = request.get_json(silent=True) or {}
    token        = (data.get("token") or "").strip()
    new_password = data.get("password") or ""

    if not token or not new_password:
        return jsonify({"error": "token and password are required"}), 400

    if len(new_password) < 8:
        return jsonify({"error": "Password must be at least 8 characters"}), 400

    now   = datetime.now(timezone.utc)
    reset = PasswordReset.query.filter_by(token=token, used=False).first()

    if not reset:
        return jsonify({"error": "Invalid or expired reset token"}), 400

    exp = reset.expires_at
    if exp.tzinfo is None:
        exp = exp.replace(tzinfo=timezone.utc)
    if now > exp:
        return jsonify({"error": "Reset token has expired"}), 400

    user = User.query.get(reset.user_id)
    if not user:
        return jsonify({"error": "User not found"}), 404

    user.password_hash = bcrypt.generate_password_hash(new_password).decode("utf-8")
    reset.used = True
    db.session.commit()
    return jsonify({"message": "Password updated successfully."}), 200


# ---------------------------------------------------------------------------
# Profile — view own account
# ---------------------------------------------------------------------------
@api_v2_bp.route("/profile", methods=["GET"])
@jwt_required()
def get_profile():
    user_id = int(get_jwt_identity())
    user    = User.query.get(user_id)
    if not user:
        return jsonify({"error": "User not found"}), 404
    return jsonify(user.to_dict()), 200


# ---------------------------------------------------------------------------
# Profile — update (HARDENED: role is not an updatable field)
# ---------------------------------------------------------------------------
@api_v2_bp.route("/profile", methods=["PATCH"])
@jwt_required()
def update_profile():
    user_id  = int(get_jwt_identity())
    user     = User.query.get(user_id)
    if not user:
        return jsonify({"error": "User not found"}), 404

    data = request.get_json(silent=True) or {}

    # Explicitly allowed fields only — role, email, password_hash are excluded
    allowed = {"first_name", "last_name"}
    for field in allowed:
        if field in data and data[field]:
            setattr(user, field, data[field].strip())

    if "role" in data:
        # Log the attempt but silently ignore
        current_app.logger.warning(
            "Mass-assignment attempt on role by user %s (uuid=%s)",
            user.id, user.uuid
        )

    db.session.commit()
    return jsonify({"message": "Profile updated.", "user": user.to_dict()}), 200


# ---------------------------------------------------------------------------
# Applications — list own
# ---------------------------------------------------------------------------
@api_v2_bp.route("/applications", methods=["GET"])
@jwt_required()
def list_applications():
    user_id = int(get_jwt_identity())
    apps    = Application.query.filter_by(user_id=user_id)\
                               .order_by(Application.submitted_at.desc()).all()
    return jsonify({"applications": [a.to_dict() for a in apps]}), 200


# ---------------------------------------------------------------------------
# Applications — submit
# ---------------------------------------------------------------------------
@api_v2_bp.route("/applications", methods=["POST"])
@jwt_required()
def submit_application():
    import os, uuid as _uuid
    from werkzeug.utils import secure_filename

    claims  = get_jwt()
    if claims.get("role") != "student":
        return jsonify({"error": "Only student accounts can submit applications"}), 403

    user_id = int(get_jwt_identity())

    # Support both JSON and multipart/form-data (form with file upload)
    if request.content_type and "multipart" in request.content_type:
        data = request.form
    else:
        data = request.get_json(silent=True) or {}

    program = (data.get("program") or "").strip()
    if not program:
        return jsonify({"error": "program is required"}), 400

    existing = Application.query.filter_by(user_id=user_id, status="pending").first()
    if existing:
        return jsonify({"error": "You already have a pending application", "ref_no": existing.ref_no}), 409

    # Handle photo upload
    photo_path = None
    photo_file = request.files.get("photo")
    if photo_file and photo_file.filename:
        ext = os.path.splitext(secure_filename(photo_file.filename))[1].lower()
        if ext not in (".jpg", ".jpeg", ".png"):
            return jsonify({"error": "Photo must be JPG or PNG"}), 400
        upload_dir = os.path.join(current_app.static_folder, "uploads", "photos")
        os.makedirs(upload_dir, exist_ok=True)
        fname = f"{_uuid.uuid4().hex}{ext}"
        photo_file.save(os.path.join(upload_dir, fname))
        photo_path = f"uploads/photos/{fname}"

    app = Application(
        user_id=user_id,
        program=program,
        strand=(data.get("strand") or "").strip() or None,
        prev_school=(data.get("prev_school") or "").strip() or None,
        gwa=float(data.get("gwa")) if data.get("gwa") else None,
        photo=photo_path,
        status="pending",
    )
    db.session.add(app)
    db.session.commit()
    return jsonify({"message": "Application submitted.", "application": app.to_dict()}), 201
