# tests/python/conftest.py
"""Root conftest — adds both Exports/py and Imports/py directories to sys.path."""

import sys
from pathlib import Path

_LARAVEL = Path(__file__).resolve().parents[2]  # _inc/laravel
_EXPORTS_PY = _LARAVEL / "app" / "Exports" / "py"
_IMPORTS_PY = _LARAVEL / "app" / "Imports" / "py"

for _dir in (_EXPORTS_PY, _IMPORTS_PY):
    if str(_dir) not in sys.path:
        sys.path.insert(0, str(_dir))
