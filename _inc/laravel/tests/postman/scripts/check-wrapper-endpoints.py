#!/usr/bin/env python3
from __future__ import annotations

import json
import re
import sys
from pathlib import Path


POSTMAN_ROOT = Path(__file__).resolve().parents[1]
LARAVEL_ROOT = Path(__file__).resolve().parents[3]
EXPORT_WRAPPERS = LARAVEL_ROOT / "app" / "Exports" / "_withPython"
IMPORT_WRAPPERS = LARAVEL_ROOT / "app" / "Imports" / "_withPython"


def collect_wrapper_endpoints(directory: Path) -> dict[str, str]:
    endpoints: dict[str, str] = {}
    for path in sorted(directory.glob("*.php")):
        content = path.read_text(encoding="utf-8")
        match = re.search(r"(/api(?:/python)?/[a-z_]+)", content)
        if match:
            endpoints[path.name] = match.group(1)
    return endpoints


def load_routes(route_inventory: Path) -> set[str]:
    payload = json.loads(route_inventory.read_text(encoding="utf-8"))
    routes = payload.get("interesting_routes", [])
    uris = set()
    for route in routes:
        uri = str(route.get("uri", "")).strip("/")
        if uri:
            uris.add("/" + uri)
    return uris


def main() -> int:
    route_inventory = (
        Path(sys.argv[1]).expanduser().resolve()
        if len(sys.argv) > 1
        else (POSTMAN_ROOT / "route-surface.json").resolve()
    )
    if not route_inventory.exists():
        print(
            json.dumps(
                {
                    "status": "error",
                    "message": f"Route inventory not found: {route_inventory}",
                    "hint": "Generate it with php scripts/export-route-surface.php route-surface.json",
                },
                indent=2,
            )
        )
        return 1

    routes = load_routes(route_inventory)
    endpoints = {}
    endpoints.update(collect_wrapper_endpoints(EXPORT_WRAPPERS))
    endpoints.update(collect_wrapper_endpoints(IMPORT_WRAPPERS))

    missing = {
        wrapper: endpoint
        for wrapper, endpoint in endpoints.items()
        if endpoint not in routes
    }

    report = {
        "status": "ok" if not missing else "warning",
        "route_inventory": str(route_inventory),
        "wrapper_count": len(endpoints),
        "missing_route_count": len(missing),
        "missing_routes": missing,
    }
    print(json.dumps(report, indent=2, sort_keys=True))
    return 0 if not missing else 2


if __name__ == "__main__":
    raise SystemExit(main())
