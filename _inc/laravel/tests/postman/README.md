# Postman and Newman tests

This folder covers the routed Laravel HTTP surface with Postman/Newman assets and maintenance scripts.

## What is here

- `brand-new-ideas-company.collection.json`: anonymous security probes and authenticated API checks
- `local.environment.json`: local defaults for `http://127.0.0.1:18081`
- `run-postman.sh`: shell wrapper for Newman
- `package.json`: local Newman toolchain pinned for this folder
- `scripts/run-newman.cjs`: credential-aware Newman runner
- `scripts/export-route-surface.php`: dumps `artisan route:list --json` into a filtered JSON inventory
- `scripts/check-wrapper-endpoints.py`: compares `_withPython` wrapper endpoints against the exported route inventory

## Quick start

Anonymous checks only:

```bash
cd _inc/laravel/tests/postman
npm install
./run-postman.sh
```

Full API run with credentials:

```bash
cd _inc/laravel/tests/postman
npm install
POSTMAN_API_EMAIL='user@example.test' \
POSTMAN_API_PASSWORD='secret' \
./run-postman.sh
```

Optional route inventory and wrapper contract diff:

```bash
cd _inc/laravel/tests/postman
php scripts/export-route-surface.php route-surface.json
python3 scripts/check-wrapper-endpoints.py route-surface.json
```

## Notes

- The collection defaults to `{{baseUrl}} = http://127.0.0.1:18081` and `{{apiBase}} = {{baseUrl}}/apis`.
- When API credentials are missing, `scripts/run-newman.cjs` runs only the anonymous security folder.
- `npm install` in this folder installs Newman locally in `node_modules/`.
- The wrapper endpoint diff is intentionally separate from Newman because many `_withPython` wrapper contracts appear to exist in PHP without matching routed endpoints in this tree.
