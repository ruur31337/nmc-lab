import os

class Config:
    SECRET_KEY          = os.environ.get("SECRET_KEY", "nmc-admission-s3cr3t-2026!")
    SQLALCHEMY_DATABASE_URI = os.environ.get(
        "DATABASE_URL", "sqlite:////data/admission.db"
    )
    SQLALCHEMY_TRACK_MODIFICATIONS = False
    JWT_SECRET_KEY      = os.environ.get("JWT_SECRET_KEY", "nmc-jwt-k3y-!2026")
    JWT_ACCESS_TOKEN_EXPIRES = 3600  # 1 hour
    RATELIMIT_DEFAULT   = "200 per day;50 per hour"
    RATELIMIT_STORAGE_URL = "memory://"
