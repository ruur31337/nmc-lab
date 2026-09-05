"""
Seed the database with realistic NMC users and sample applications.
Runs on first startup only (guarded by presence of seed-done flag).
"""
import os
from datetime import datetime, timezone
from .extensions import db, bcrypt
from .models import User, Application

SEED_FLAG = "/data/.admission-seed-done"


def seed():
    if os.path.exists(SEED_FLAG):
        print("[admission-seed] Already seeded — skipping.")
        return

    print("[admission-seed] Seeding database...")

    users = [
        # System admin — manages admission portal, also has IT staff role on Academy
        dict(
            email="it.rdelacruz@nmc.local",
            password="NMCit@2026",
            first_name="Ricardo",
            last_name="dela Cruz",
            role="it_staff",
        ),
        # Admission office admin
        dict(
            email="admin.jbautista@nmc.local",
            password="NMCAdm!n2026",
            first_name="Jose",
            last_name="Bautista",
            role="admin",
        ),
        # Admission staff
        dict(
            email="m.santos@nmc.local",
            password="NMCStaff@2026",
            first_name="Maria",
            last_name="Santos",
            role="admission_staff",
        ),
        dict(
            email="r.garcia@nmc.local",
            password="NMCStaff@2026",
            first_name="Roberto",
            last_name="Garcia",
            role="admission_staff",
        ),
        # Sample student applicants
        dict(
            email="juan.reyes@student.nmc.local",
            password="Student@2026!",
            first_name="Juan",
            last_name="Reyes",
            role="student",
        ),
        dict(
            email="ana.lim@student.nmc.local",
            password="Student@2026!",
            first_name="Ana",
            last_name="Lim",
            role="student",
        ),
    ]

    created_users = {}
    for u in users:
        pw_hash = bcrypt.generate_password_hash(u["password"]).decode("utf-8")
        user = User(
            email=u["email"],
            password_hash=pw_hash,
            first_name=u["first_name"],
            last_name=u["last_name"],
            role=u["role"],
        )
        db.session.add(user)
        db.session.flush()   # get user.id before commit
        created_users[u["email"]] = user

    # Sample applications for student accounts
    applications = [
        dict(
            email="juan.reyes@student.nmc.local",
            program="Bachelor of Science in Information Technology",
            strand="ICT",
            prev_school="Paranaque National High School",
            gwa=87.5,
            status="under_review",
            remarks="",
        ),
        dict(
            email="ana.lim@student.nmc.local",
            program="Bachelor of Science in Computer Science",
            strand="STEM",
            prev_school="De La Salle Zobel",
            gwa=93.2,
            status="pending",
            remarks="",
        ),
    ]

    for a in applications:
        user = created_users.get(a["email"])
        if user:
            app = Application(
                user_id=user.id,
                program=a["program"],
                strand=a.get("strand"),
                prev_school=a.get("prev_school"),
                gwa=a.get("gwa"),
                status=a["status"],
                remarks=a.get("remarks", ""),
            )
            db.session.add(app)

    db.session.commit()

    # Create seed flag
    os.makedirs("/data", exist_ok=True)
    open(SEED_FLAG, "w").close()
    print("[admission-seed] Done.")
