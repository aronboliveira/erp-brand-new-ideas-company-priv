#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
VENV_DIR="${1:-$ROOT_DIR/.venv}"

python3 -m venv "$VENV_DIR"
"$VENV_DIR/bin/python" -m pip install --upgrade pip
"$VENV_DIR/bin/python" -m pip install -r "$ROOT_DIR/requirements.txt"

printf '\nEnvironment ready.\n'
printf 'Activate with: source %s/bin/activate\n' "$VENV_DIR"
printf 'Run tests with: pytest -c %s/pytest.ini %s\n' "$ROOT_DIR" "$ROOT_DIR"

