"""
Admin API v1 — legacy internal endpoints at /admin/api/v1/

Routes
------
POST  /admin/api/v1/register              — create account
PATCH /admin/api/v1/profile               — update profile
POST  /admin/api/v1/auth/login            — JWT login
POST  /admin/api/v1/academy/users         — provision Academy student (admin JWT)
GET   /admin/api/v1/applicants            — list applicants (admin/staff JWT)
"""
import requests
from datetime import datetime, timezone, timedelta
from flask import Blueprint, request, jsonify, current_app
from flask_jwt_extended import (
    create_access_token, jwt_required, get_jwt_identity, get_jwt
)

from ..extensions import db, bcrypt, limiter
from ..models     import User, VALID_ROLES

api_v1_bp = Blueprint("api_v1", __name__)

ACADEMY_BASE = "http://academy:4000"


@api_v1_bp.route("/register", methods=["POST"])
def v1_register():
    data       = request.get_json(silent=True) or {}
    email      = (data.get("email") or "").strip().lower()
    password   = data.get("password") or ""
    first_name = (data.get("first_name") or "").strip()
    last_name  = (data.get("last_name") or "").strip()

    if not all([email, password, first_name, last_name]):
        return jsonify({"error": "email, password, first_name, and last_name are required"}), 400

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
        role=None,
    )
    db.session.add(user)
    db.session.commit()

    return jsonify({
        "message": "Account created.",
        "user": {"uuid": user.uuid, "email": user.email,
                 "first_name": user.first_name, "last_name": user.last_name},
    }), 201


@api_v1_bp.route("/profile", methods=["PATCH"])
@jwt_required()
def v1_update_profile():
    user_id = int(get_jwt_identity())
    user    = User.query.get(user_id)
    if not user:
        return jsonify({"error": "User not found"}), 404

    data = request.get_json(silent=True) or {}

    updatable = {"first_name", "last_name", "email", "role", "department"}
    for field in updatable:
        if field in data and data[field] is not None:
            if field == "role":
                if data[field] not in VALID_ROLES:
                    return jsonify({"error": "Invalid field value"}), 400
            val = data[field]
            setattr(user, field, val.strip() if isinstance(val, str) else val)

    db.session.commit()

    new_token = create_access_token(
        identity=str(user.id),
        additional_claims={"role": user.role, "uuid": user.uuid}
    )
    return jsonify({
        "message": "Profile updated successfully.",
        "access_token": new_token,
    }), 200


# ---------------------------------------------------------------------------
# Login (v1)
# ---------------------------------------------------------------------------
@api_v1_bp.route("/auth/login", methods=["POST"])
@limiter.limit("10 per minute")
def v1_login():
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
# Provision Academy student — admin JWT required
# ---------------------------------------------------------------------------
@api_v1_bp.route("/academy/users", methods=["POST"])
@jwt_required()
def create_academy_user():
    claims = get_jwt()
    if claims.get("role") != "admin":
        return jsonify({"error": "Admin access required"}), 403

    data     = request.get_json(silent=True) or {}
    required = ["email", "password", "first_name", "last_name"]
    if not all(data.get(f) for f in required):
        return jsonify({"error": f"Required: {', '.join(required)}"}), 400

    try:
        resp = requests.post(
            f"{ACADEMY_BASE}/internal/provision-student",
            json={
                "email":      data["email"],
                "password":   data["password"],
                "first_name": data["first_name"],
                "last_name":  data["last_name"],
                "student_id": data.get("student_id"),
            },
            timeout=5,
            headers={"X-Internal-Token": current_app.config["SECRET_KEY"]},
        )
        return jsonify(resp.json()), resp.status_code
    except requests.exceptions.ConnectionError:
        return jsonify({"error": "Academy service unreachable"}), 502
    except requests.exceptions.Timeout:
        return jsonify({"error": "Academy service timed out"}), 504


# ---------------------------------------------------------------------------
# List Academy students — proxies to Academy internal endpoint
# ---------------------------------------------------------------------------
@api_v1_bp.route("/academy/users", methods=["GET"])
@jwt_required()
def list_academy_users():
    claims = get_jwt()
    if claims.get("role") not in ("admin", "admission_staff"):
        return jsonify({"error": "Insufficient privileges"}), 403

    try:
        resp = requests.get(
            f"{ACADEMY_BASE}/internal/students",
            timeout=5,
            headers={"X-Internal-Token": current_app.config["SECRET_KEY"]},
        )
        return jsonify(resp.json()), resp.status_code
    except requests.exceptions.ConnectionError:
        return jsonify({"error": "Academy service unreachable", "students": []}), 502
    except requests.exceptions.Timeout:
        return jsonify({"error": "Academy service timed out", "students": []}), 504


# ---------------------------------------------------------------------------
# Approve pending registration — admin / admission_staff JWT
# ---------------------------------------------------------------------------
@api_v1_bp.route("/registrations/<string:user_uuid>/approve", methods=["POST"])
@jwt_required()
def approve_registration(user_uuid):
    claims = get_jwt()
    if claims.get("role") not in ("admin", "admission_staff"):
        return jsonify({"error": "Insufficient privileges"}), 403

    user = User.query.filter_by(uuid=user_uuid).first()
    if not user:
        return jsonify({"error": "User not found"}), 404
    if user.is_active:
        return jsonify({"message": "Account is already active"}), 200

    user.is_active = True
    from ..models import Application
    app_entry = Application.query.filter_by(user_id=user.id).first()
    if app_entry:
        app_entry.status = "accepted"
    db.session.commit()

    # Auto-provision Academy student account
    student_id   = f"NMC-{datetime.now(timezone.utc).year}-{str(user.id).zfill(4)}"
    temp_password = "Welcome@NMC1!"
    department    = app_entry.program if app_entry else None

    academy = {"provisioned": False}
    try:
        resp = requests.post(
            f"{ACADEMY_BASE}/internal/provision-student",
            json={
                "email":      user.email,
                "password":   temp_password,
                "first_name": user.first_name,
                "last_name":  user.last_name,
                "student_id": student_id,
                "department": department,
            },
            timeout=5,
            headers={"X-Internal-Token": current_app.config["SECRET_KEY"]},
        )
        if resp.status_code in (200, 201):
            academy = {
                "provisioned":   True,
                "student_id":    student_id,
                "temp_password": temp_password,
                "email":         user.email,
                "portal_url":    "http://localhost:5200",
            }
        else:
            academy = {"provisioned": False, "error": (resp.json() or {}).get("error", "Unknown")}
    except requests.exceptions.ConnectionError:
        academy = {"provisioned": False, "error": "Academy service unreachable"}
    except Exception as e:
        academy = {"provisioned": False, "error": str(e)}

    return jsonify({
        "message": f"Account for {user.email} approved.",
        "academy": academy,
    }), 200


# ---------------------------------------------------------------------------
# Reject pending registration — admin / admission_staff JWT
# ---------------------------------------------------------------------------
@api_v1_bp.route("/registrations/<string:user_uuid>/reject", methods=["POST"])
@jwt_required()
def reject_registration(user_uuid):
    claims = get_jwt()
    if claims.get("role") not in ("admin", "admission_staff"):
        return jsonify({"error": "Insufficient privileges"}), 403

    data   = request.get_json(silent=True) or {}
    reason = (data.get("reason") or "Does not meet admission requirements").strip()

    user = User.query.filter_by(uuid=user_uuid).first()
    if not user:
        return jsonify({"error": "User not found"}), 404

    user.is_active = False
    from ..models import Application
    app_entry = Application.query.filter_by(user_id=user.id).first()
    if app_entry:
        app_entry.status = "rejected"
        app_entry.remarks = reason
    db.session.commit()
    return jsonify({"message": f"Application for {user.email} rejected.", "reason": reason}), 200


# ---------------------------------------------------------------------------
# List applicants — admin / admission_staff JWT
# ---------------------------------------------------------------------------
@api_v1_bp.route("/applicants", methods=["GET"])
@jwt_required()
def list_applicants():
    claims = get_jwt()
    if claims.get("role") not in ("admin", "admission_staff"):
        return jsonify({"error": "Insufficient privileges"}), 403

    from ..models import Application
    apps = Application.query.order_by(Application.submitted_at.desc()).all()
    result = []
    for a in apps:
        applicant = User.query.get(a.user_id)
        result.append({
            **a.to_dict(),
            "applicant": {
                "uuid":       applicant.uuid       if applicant else None,
                "first_name": applicant.first_name if applicant else "",
                "last_name":  applicant.last_name  if applicant else "",
                "email":      applicant.email      if applicant else "",
            }
        })
    return jsonify({"applicants": result, "total": len(result)}), 200
