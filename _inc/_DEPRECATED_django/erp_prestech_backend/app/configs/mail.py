import os
from pathlib import Path

BASE_DIR = Path(__file__).resolve().parent.parent

# 1) Determine the mail “backend” from environment
MAIL_MAILER = os.getenv('MAIL_MAILER', 'smtp')

if MAIL_MAILER == 'smtp':
    EMAIL_BACKEND = 'django.core.mail.backends.smtp.EmailBackend'
elif MAIL_MAILER == 'console':
    EMAIL_BACKEND = 'django.core.mail.backends.console.EmailBackend'
elif MAIL_MAILER == 'file':
    EMAIL_BACKEND = 'django.core.mail.backends.filebased.EmailBackend'
    EMAIL_FILE_PATH = os.path.join(BASE_DIR, 'tmp_emails')  # example
elif MAIL_MAILER == 'sendmail':
    EMAIL_BACKEND = 'django.core.mail.backends.sendmail.EmailBackend'
    SENDMAIL_PATH = os.getenv('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i')
elif MAIL_MAILER == 'ses':
    EMAIL_BACKEND = 'django_ses.SESBackend'  # if using django-ses
elif MAIL_MAILER == 'mailgun':
    EMAIL_BACKEND = 'anymail.backends.mailgun.EmailBackend'  # if using django-anymail
elif MAIL_MAILER == 'postmark':
    EMAIL_BACKEND = 'anymail.backends.postmark.EmailBackend'
elif MAIL_MAILER == 'log':
    EMAIL_BACKEND = 'myproject.mail_backends.LoggingBackend'
elif MAIL_MAILER == 'array':
    # "array" is somewhat like "locmem" in Django
    EMAIL_BACKEND = 'django.core.mail.backends.locmem.EmailBackend'
else:
    # fallback
    EMAIL_BACKEND = 'django.core.mail.backends.smtp.EmailBackend'

# 2) SMTP Settings (only relevant if using smtp)
EMAIL_HOST = os.getenv('MAIL_HOST', 'smtp.mailgun.org')
EMAIL_PORT = int(os.getenv('MAIL_PORT', 587))
EMAIL_HOST_USER = os.getenv('MAIL_USERNAME', '')
EMAIL_HOST_PASSWORD = os.getenv('MAIL_PASSWORD', '')
ENCRYPTION = os.getenv('MAIL_ENCRYPTION', 'tls').lower()

if ENCRYPTION == 'tls':
    EMAIL_USE_TLS = True
    EMAIL_USE_SSL = False
elif ENCRYPTION == 'ssl':
    EMAIL_USE_SSL = True
    EMAIL_USE_TLS = False
else:
    EMAIL_USE_SSL = False
    EMAIL_USE_TLS = False

# Optional
EMAIL_TIMEOUT = None  # or int
# local_domain is not used in Django by default

# 3) Global "From" address
MAIL_FROM_NAME = os.getenv('MAIL_FROM_NAME', 'Example')
MAIL_FROM_ADDRESS = os.getenv('MAIL_FROM_ADDRESS', 'hello@example.com')
DEFAULT_FROM_EMAIL = f"{MAIL_FROM_NAME} <{MAIL_FROM_ADDRESS}>"

# 4) (Optional) Markdown mail settings
EMAIL_MARKDOWN_THEME = 'default'
EMAIL_MARKDOWN_TEMPLATE_PATHS = [
    os.path.join(BASE_DIR, 'templates', 'vendor', 'mail'),
]
