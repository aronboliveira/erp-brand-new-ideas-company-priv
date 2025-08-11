import os
from pathlib import Path

BASE_DIR = Path(__file__).resolve().parent.parent

# Environment values (defaulting as in Laravel)
LOG_CHANNEL = os.environ.get("LOG_CHANNEL", "stack")
LOG_LEVEL = os.environ.get("LOG_LEVEL", "DEBUG").upper()
LOG_SLACK_WEBHOOK_URL = os.environ.get("LOG_SLACK_WEBHOOK_URL", "")
PAPERTRAIL_URL = os.environ.get("PAPERTRAIL_URL", "")
PAPERTRAIL_PORT = os.environ.get("PAPERTRAIL_PORT", "")

# Django logging configuration
LOGGING = {
    "version": 1,
    "disable_existing_loggers": False,
    "formatters": {
        "verbose": {
            "format": "[%(asctime)s] %(levelname)s [%(name)s:%(lineno)s] %(message)s",
            "datefmt": "%Y-%m-%d %H:%M:%S",
        },
        "simple": {
            "format": "%(levelname)s %(message)s",
        },
    },
    "handlers": {
        # Single file handler (Laravel 'single')
        "file": {
            "level": LOG_LEVEL,
            "class": "logging.FileHandler",
            "filename": os.path.join(BASE_DIR, "storage", "logs", "laravel.log"),
            "formatter": "verbose",
        },
        # Daily rotating file handler (Laravel 'daily')
        "daily": {
            "level": LOG_LEVEL,
            "class": "logging.handlers.TimedRotatingFileHandler",
            "filename": os.path.join(BASE_DIR, "storage", "logs", "laravel.log"),
            "when": "D",       # Rotate daily
            "interval": 1,
            "backupCount": 14, # Keep logs for 14 days
            "formatter": "verbose",
        },
        # Slack handler – requires a custom handler or a third‑party package
        "slack": {
            "level": os.environ.get("LOG_LEVEL", "CRITICAL"),
            "class": "path.to.CustomSlackHandler",  # You need to implement or install a Slack handler.
            "webhook_url": LOG_SLACK_WEBHOOK_URL,
            "username": "Laravel Log",
            "emoji": ":boom:",
        },
        # STDERR handler (similar to Laravel 'stderr')
        "stderr": {
            "level": LOG_LEVEL,
            "class": "logging.StreamHandler",
            "stream": "ext://sys.stderr",
            "formatter": "simple",
        },
        # Syslog handler
        "syslog": {
            "level": LOG_LEVEL,
            "class": "logging.handlers.SysLogHandler",
            # On Linux, typically '/dev/log' is used. Adjust for your OS.
            "address": "/dev/log",
            "formatter": "verbose",
        },
        # Error log using stderr (Laravel 'errorlog')
        "errorlog": {
            "level": LOG_LEVEL,
            "class": "logging.StreamHandler",
            "stream": "ext://sys.stderr",
            "formatter": "simple",
        },
        # Null handler (Laravel 'null')
        "null": {
            "level": LOG_LEVEL,
            "class": "logging.NullHandler",
        },
        # Emergency channel writes to file as a fallback
        "emergency": {
            "level": "ERROR",
            "class": "logging.FileHandler",
            "filename": os.path.join(BASE_DIR, "storage", "logs", "laravel.log"),
            "formatter": "verbose",
        },
        # Custom PayTabs channel
        "PayTabs": {
            "level": "INFO",
            "class": "logging.FileHandler",
            "filename": os.path.join(BASE_DIR, "storage", "logs", "paytabs.log"),
            "formatter": "verbose",
        },
    },
    "loggers": {
        # The root logger will use a "stack" of handlers (here we use "file" as an example).
        "": {
            "handlers": ["file"],
            "level": LOG_LEVEL,
            "propagate": True,
        },
        # You can override Django's default logger if needed.
        "django": {
            "handlers": ["file"],
            "level": LOG_LEVEL,
            "propagate": False,
        },
        # For deprecation warnings you might set:
        "py.warnings": {
            "handlers": ["null"],
            "propagate": False,
        },
    },
}
