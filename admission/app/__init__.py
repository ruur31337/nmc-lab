from flask import Flask
from .config    import Config
from .extensions import db, bcrypt, jwt, limiter


def create_app(config_class=Config):
    app = Flask(__name__)
    app.config.from_object(config_class)

    db.init_app(app)
    bcrypt.init_app(app)
    jwt.init_app(app)
    limiter.init_app(app)

    from .routes.views  import views_bp
    from .routes.api_v1 import api_v1_bp
    from .routes.api_v2 import api_v2_bp

    app.register_blueprint(views_bp)
    app.register_blueprint(api_v1_bp, url_prefix="/admin/api/v1")
    app.register_blueprint(api_v2_bp, url_prefix="/api/v2")

    with app.app_context():
        db.create_all()
        from .seed import seed
        seed()

    return app
