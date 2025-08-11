import os

BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))

# Choose the storage driver from the environment (default 'local')
FILESYSTEM_DRIVER = os.environ.get('FILESYSTEM_DRIVER', 'local')

if FILESYSTEM_DRIVER == 'local':
    DEFAULT_FILE_STORAGE = 'django.core.files.storage.FileSystemStorage'
    MEDIA_ROOT = os.path.join(BASE_DIR, 'storage')
    MEDIA_URL = os.environ.get('APP_URL', 'http://localhost') + '/storage'
elif FILESYSTEM_DRIVER == 's3':
    DEFAULT_FILE_STORAGE = 'storages.backends.s3boto3.S3Boto3Storage'
    AWS_ACCESS_KEY_ID = os.environ.get('AWS_ACCESS_KEY_ID')
    AWS_SECRET_ACCESS_KEY = os.environ.get('AWS_SECRET_ACCESS_KEY')
    AWS_STORAGE_BUCKET_NAME = os.environ.get('AWS_BUCKET')
    AWS_S3_REGION_NAME = os.environ.get('AWS_DEFAULT_REGION')
    AWS_S3_ENDPOINT_URL = os.environ.get('AWS_ENDPOINT')
    AWS_S3_CUSTOM_DOMAIN = os.environ.get('AWS_URL')
    AWS_S3_FORCE_PATH_STYLE = os.environ.get('AWS_USE_PATH_STYLE_ENDPOINT', 'false').lower() in ['true', '1']
elif FILESYSTEM_DRIVER == 'wasabi':
    DEFAULT_FILE_STORAGE = 'storages.backends.s3boto3.S3Boto3Storage'
    AWS_ACCESS_KEY_ID = os.environ.get('WAS_ACCESS_KEY_ID')
    AWS_SECRET_ACCESS_KEY = os.environ.get('WAS_SECRET_ACCESS_KEY')
    AWS_STORAGE_BUCKET_NAME = os.environ.get('WAS_BUCKET')
    AWS_S3_REGION_NAME = os.environ.get('WAS_DEFAULT_REGION')
    AWS_S3_ENDPOINT_URL = os.environ.get('WAS_URL')
    # Optionally, set a custom domain if provided:
    AWS_S3_CUSTOM_DOMAIN = os.environ.get('WAS_URL')

# Note on symbolic links:
# To mimic Laravel’s "storage:link", you can create a symbolic link from your public directory
# to your media directory manually (or via a custom management command):
# e.g. public_path('storage') -> storage_path('app/public')
