import os

# Get the default queue connection from the environment (default: 'sync')
QUEUE_CONNECTION = os.environ.get('QUEUE_CONNECTION', 'sync')

# When using 'sync' (i.e. synchronous execution) in Celery:
if QUEUE_CONNECTION == 'sync':
    CELERY_TASK_ALWAYS_EAGER = True
else:
    CELERY_TASK_ALWAYS_EAGER = False

# Configure the Celery broker and result backend based on the chosen connection.
# Note: You may need to install additional packages (like django-celery-results or kombu transports)
if QUEUE_CONNECTION == 'database':
    # Celery does not have a built-in "database" queue driver.
    # One option is to use SQLAlchemy transport with SQLite, for example:
    CELERY_BROKER_URL = 'sqla+sqlite:///celerydb.sqlite'
    CELERY_RESULT_BACKEND = 'db+sqlite:///celerydb.sqlite'
elif QUEUE_CONNECTION == 'beanstalkd':
    # Requires a beanstalkd transport (e.g., kombu-beanstalkd)
    CELERY_BROKER_URL = os.environ.get('BEANSTALKD_URL', 'beanstalk://localhost:11300')
    CELERY_RESULT_BACKEND = 'rpc://'
elif QUEUE_CONNECTION == 'sqs':
    # Using SQS as the broker (make sure to install the necessary transport)
    # The following is an example; you may need to adjust based on your SQS setup.
    SQS_PREFIX = os.environ.get('SQS_PREFIX', 'https://sqs.us-east-1.amazonaws.com/your-account-id')
    SQS_QUEUE = os.environ.get('SQS_QUEUE', 'default')
    CELERY_BROKER_URL = f'{SQS_PREFIX}/{SQS_QUEUE}'
    # SQS does not support a result backend directly; you might use RPC or another backend.
    CELERY_RESULT_BACKEND = 'rpc://'
elif QUEUE_CONNECTION == 'redis':
    # Default to Redis (e.g., on localhost)
    CELERY_BROKER_URL = os.environ.get('REDIS_URL', 'redis://localhost:6379/0')
    CELERY_RESULT_BACKEND = os.environ.get('REDIS_URL', 'redis://localhost:6379/0')
else:
    # Fallback (or if using 'sync', no broker is needed)
    CELERY_BROKER_URL = None
    CELERY_RESULT_BACKEND = None

# Failed Queue Jobs:
# Laravel uses a dedicated table to log failed jobs.
# In Celery, you can inspect failed tasks via the result backend or use monitoring tools.
# If using django-celery-results, you might set:
CELERY_RESULT_EXTENDED = True
