from flask import (
    Blueprint, render_template, redirect, url_for,
    session, request, flash
)

views_bp = Blueprint("views", __name__)


def _current_user():
    return session.get("user")


@views_bp.route("/")
def index():
    return render_template("index.html", user=_current_user())


@views_bp.route("/login")
def login():
    if _current_user():
        return redirect(url_for("views.dashboard"))
    return render_template("login.html")


@views_bp.route("/logout")
def logout():
    session.clear()
    return redirect(url_for("views.index"))


@views_bp.route("/register")
def register():
    return render_template("register.html")


@views_bp.route("/forgot-password")
def forgot_password():
    return render_template("forgot_password.html")


@views_bp.route("/dashboard")
def dashboard():
    user = _current_user()
    if not user:
        return redirect(url_for("views.login"))
    if user.get("role") in ("admin", "admission_staff", "it_staff"):
        return redirect(url_for("views.admin_dashboard"))
    return render_template("dashboard.html", user=user)


@views_bp.route("/apply")
def apply():
    user = _current_user()
    if not user:
        return redirect(url_for("views.login"))
    if user.get("role") != "student":
        return redirect(url_for("views.admin_dashboard"))
    return render_template("apply.html", user=user)


@views_bp.route("/admin")
def admin_dashboard():
    user = _current_user()
    if not user:
        return redirect(url_for("views.login"))
    if user.get("role") not in ("admin", "admission_staff", "it_staff"):
        return redirect(url_for("views.dashboard"))
    return render_template("admin/dashboard.html", user=user)


# ---------------------------------------------------------------------------
# Session bridge — called by JS after successful JWT login so the HTML
# pages also know who is logged in.
# ---------------------------------------------------------------------------
@views_bp.route("/session/init", methods=["POST"])
def session_init():
    from flask import jsonify
    from flask_jwt_extended import decode_token
    from app.extensions import db
    from app.models import User

    data  = request.get_json(silent=True) or {}
    token = data.get("access_token", "")
    try:
        decoded = decode_token(token)
        user_id = int(decoded["sub"])
        user    = User.query.get(user_id)
        if user:
            session["user"] = {
                "id":         user.id,
                "uuid":       user.uuid,
                "email":      user.email,
                "first_name": user.first_name,
                "last_name":  user.last_name,
                "role":       user.role,
            }
            return jsonify({"ok": True, "role": user.role}), 200
    except Exception:
        pass
    return jsonify({"ok": False}), 401
