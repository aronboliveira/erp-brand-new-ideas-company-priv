import os
import platform
import subprocess
import importlib.util
from django.shortcuts import render

def get_permission_string(mode):
    """
    Convert permission bits (mode) to a string like 'u=rwx, g=r-x, o=r--'
    Similar to the original PHP getPermissionString().
    """
    # Mode is an int, e.g. from os.stat().st_mode
    # We'll mask with 0o777 to get just the permission bits
    perms = mode & 0o777

    def _rwx(bits):
        return (
            ('r' if (bits & 4) else '-')
            + ('w' if (bits & 2) else '-')
            + ('x' if (bits & 1) else '-')
        )

    user_bits = (perms & 0o700) >> 6
    group_bits = (perms & 0o070) >> 3
    other_bits = (perms & 0o007)

    return f"u={_rwx(user_bits)}, g={_rwx(group_bits)}, o={_rwx(other_bits)}"

def check_and_set_folder_permissions(folder_path, required_perms):
    """
    Checks if the folder has the required permission bits.
    If on Windows, we attempt a naive approach like in the original script.
    Otherwise, we try to chmod if needed.
    Returns True if final perms match required_perms, else False.
    """
    # If the folder doesn't exist, skip
    if not os.path.isdir(folder_path):
        return False

    current_mode = os.stat(folder_path).st_mode
    if platform.system().lower().startswith('win'):
        # On Windows, do something similar to the original
        # e.g. `icacls` or `cacls` calls:
        cmd = f'icacls "{folder_path}" /grant Everyone:F'
        try:
            subprocess.run(cmd, shell=True, check=True)
        except subprocess.CalledProcessError:
            pass
    else:
        # On Unix-like OS, compare & possibly chmod
        needed = required_perms & 0o777
        if (current_mode & 0o777) != needed:
            try:
                os.chmod(folder_path, needed)
            except PermissionError:
                pass

    # Re-check
    new_mode = os.stat(folder_path).st_mode & 0o777
    return (new_mode == (required_perms & 0o777))

def check_extension_loaded(extension_name):
    """
    Rough approximation of `extension_loaded($extensionName)` in PHP.
    We'll interpret "extension" as "Python module".
    If the module can be imported, we consider it "loaded".
    """
    spec = importlib.util.find_spec(extension_name)
    return spec is not None

def system_config_check_view(request):
    """
    A Django view replicating the logic of the original PHP script.
    Renders a table checking folder perms & certain modules.
    """
    folders_to_check = [
        "resources/lang",
        "bootstrap/cache",
        "storage",
    ]
    required_folder_perms = 0o777  # e.g. 0777 in Python

    # We’ll treat these as "modules" you want to check for in Python
    # There's no 1:1 mapping with the original (like 'mbstring', etc.),
    # but this shows the concept. Adjust as desired.
    extensions_to_check = [
        "requests",
        "PIL",        # stands for pillow, an example for 'gd' or 'imagick' in PHP
        "mysql",
        # etc...
    ]

    folder_results = []
    for folder in folders_to_check:
        ok = check_and_set_folder_permissions(folder, required_folder_perms)
        # We store both the final status & the textual perms
        current_mode = os.stat(folder).st_mode if os.path.isdir(folder) else 0
        folder_perm_str = get_permission_string(current_mode)
        folder_results.append({
            'folder': folder,
            'status': "OK" if ok else folder_perm_str,
            'required': "777 (or Everyone:F on Windows)",
        })

    extension_results = []
    for ext in extensions_to_check:
        loaded = check_extension_loaded(ext)
        extension_results.append({
            'extension': ext,
            'status': "OK" if loaded else "Not OK",
            'required': "OK",
            'css_class': "table-success" if loaded else "table-danger"
        })

    context = {
        'folder_results': folder_results,
        'extension_results': extension_results,
    }
    return render(request, 'system_config_check.html', context)
