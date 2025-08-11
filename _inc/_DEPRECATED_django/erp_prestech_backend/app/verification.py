import os
import platform
from django.conf import settings
from django.http import JsonResponse

def get_python_version_info():
    """
    Returns a dict with Python's current major.minor (short) and full version string.
    For example:
      {
        'full': '3.9.10',
        'version': '3.9'
      }
    """
    version_full = platform.python_version()  # e.g. '3.9.10'
    # Extract major.minor:
    short = ".".join(version_full.split('.')[:2])  # '3.9'
    return {
        'full': version_full,
        'version': short
    }

def get_permission(path: str) -> str:
    """
    Return the numeric permission of the given path as a string, e.g. '777', '755', etc.
    If the path doesn’t exist, return '000'.
    """
    if not os.path.exists(path):
        return '000'
    mode = os.stat(path).st_mode
    # Convert to octal string, e.g. '0o755' => '755'
    return oct(mode)[-3:]  # e.g. '755'

def check_permissions_and_python(required_python='3.7'):
    """
    The main logic. We check:
      - A set of directories/files that need "777" permissions (Unix style).
      - The current Python version against a required version.

    Returns a dict with the data needed to render results on the frontend:
      {
        'requiredPython': '3.7',
        'pythonAllowed': True or False,
        'pythonCurrentVersion': '3.9',
        'folderResults': [ { 'folderKey': str, 'permission': str, 'ok': bool }, ... ],
        'hasError': bool
      }
    """
    # Which folders/files to check for "777" (or whichever you prefer)
    arr_permissions = {
        "storage/framework/":        os.path.join(settings.BASE_DIR, "storage", "framework"),
        "storage/logs/":             os.path.join(settings.BASE_DIR, "storage", "logs"),
        "bootstrap/cache/":          os.path.join(settings.BASE_DIR, "bootstrap", "cache"),
        "resources/lang/":           os.path.join(settings.BASE_DIR, "resources", "lang"),
        ".env":                      os.path.join(settings.BASE_DIR, ".env"),
    }

    folder_results = []
    has_error = False

    for folder_key, folder_path in arr_permissions.items():
        perm_str = get_permission(folder_path)  # e.g. '755'
        is_ok = (perm_str == '777')  # or perm_str >= '777' if you want at least 777
        if not is_ok:
            has_error = True
        folder_results.append({
            'folderKey': folder_key,
            'permission': perm_str,
            'ok': is_ok
        })

    # Check Python version
    py_info = get_python_version_info()
    # We compare short versions, e.g. '3.7' vs '3.9'
    def parse_version(v):
        try:
            return [int(x) for x in v.split('.')]
        except ValueError:
            return [0]

    python_allowed = parse_version(py_info['version']) >= parse_version(required_python)
    if not python_allowed:
        has_error = True

    return {
        'requiredPython': required_python,
        'pythonAllowed': python_allowed,
        'pythonCurrentVersion': py_info['version'],
        'folderResults': folder_results,
        'hasError': has_error
    }

def system_check_api(request):
    """
    A Django view that returns JSON about folder permissions and Python version status.
    The Next.js frontend can call this endpoint to display the results.
    """
    results = check_permissions_and_python(required_python='3.7')
    return JsonResponse(results, safe=False)
