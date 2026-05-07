# Python toolchain run — 2026-05-07

CWD: `_inc/laravel/`
Tools: pytest 9.0.2, flake8 7.3.0 (pyflakes 3.4.0, pycodestyle 2.14.0), mypy 1.19.1
Python: 3.13.3

## Commands (matching `.github/workflows/ci.yml`)

```bash
python3 -m flake8 tests/python/ --max-line-length=120
python3 -m mypy tests/python/ --ignore-missing-imports
python3 -m pytest tests/python/
```

(CI also lists `resources/python/` but that path doesn't exist in this fork — flake8 errors on it; dropped.)

## Results

| Tool | Result |
|---|---|
| flake8 | 0 problems |
| mypy | Success: no issues found in 14 source files |
| pytest | 268 passed, 171 skipped (server-dependent SQLi suite), 0 failed |

## One fix applied
The nested `tests/python/pytest.ini` overrode the parent
`_inc/laravel/pytest.ini` rootdir resolution, dropping the `markers` section
and producing 5× `PytestUnknownMarkWarning: pytest.mark.timeout`. Re-added
the marker registration to the nested file. Re-run shows 0 warnings.
