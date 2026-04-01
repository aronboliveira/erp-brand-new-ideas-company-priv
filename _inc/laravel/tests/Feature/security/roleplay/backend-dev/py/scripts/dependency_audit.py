#!/usr/bin/env python3
# ▓ Roleplay: Backend Developer — Dependency Vulnerability Audit
# Dev backend. Verifica dependências do composer.lock para CVEs conhecidas.
# PULL REQUEST START
"""
Audita dependências PHP (composer.lock) para vulnerabilidades conhecidas.
Uso: python3 dependency_audit.py [caminho_do_composer.lock]
"""
import json
import os
import re
import subprocess
import sys

DEFAULT_LOCK = os.path.join(
    os.path.dirname(os.path.abspath(__file__)),
    "..", "..", "..", "..", "..", "..",
    "composer.lock",
)


def parse_composer_lock(lock_path: str) -> list:
    """Extrai pacotes do composer.lock."""
    with open(lock_path) as f:
        data = json.load(f)
    packages = []
    for pkg in data.get("packages", []) + data.get("packages-dev", []):
        packages.append({
            "name": pkg.get("name", "?"),
            "version": pkg.get("version", "?"),
            "type": pkg.get("type", "library"),
        })
    return packages


def check_known_vulnerable(name: str, version: str) -> list:
    """Verifica contra lista de padrões conhecidos como vulneráveis."""
    findings = []
    # Padrões simplificados — em produção usaria um advisory DB real
    patterns = [
        {
            "pattern": r"^laravel/framework$",
            "vuln_below": "10.48.0",
            "cve": "CVE-2024-laravel-example",
            "severity": "HIGH",
        },
        {
            "pattern": r"^guzzlehttp/guzzle$",
            "vuln_below": "7.8.0",
            "cve": "CVE-2024-guzzle-ssrf",
            "severity": "MEDIUM",
        },
        {
            "pattern": r"^symfony/http-kernel$",
            "vuln_below": "6.4.0",
            "cve": "CVE-2024-symfony-kernel",
            "severity": "HIGH",
        },
    ]

    for p in patterns:
        if re.match(p["pattern"], name):
            # Comparação simplificada de versão
            clean_ver = version.lstrip("v")
            clean_vuln = p["vuln_below"]
            try:
                ver_parts = [int(x) for x in clean_ver.split(".")[:3]]
                vuln_parts = [int(x) for x in clean_vuln.split(".")[:3]]
                if ver_parts < vuln_parts:
                    findings.append({
                        "package": name,
                        "version": version,
                        "cve": p["cve"],
                        "severity": p["severity"],
                        "fix": f"Atualizar para >= {p['vuln_below']}",
                    })
            except (ValueError, IndexError):
                pass

    return findings


def run_composer_audit(project_root: str) -> str:
    """Tenta executar composer audit (requer composer 2.4+)."""
    try:
        result = subprocess.run(
            ["composer", "audit", "--format=json"],
            cwd=project_root,
            capture_output=True,
            text=True,
            timeout=60,
        )
        return result.stdout
    except (FileNotFoundError, subprocess.TimeoutExpired):
        return ""


def main():
    lock_path = sys.argv[1] if len(sys.argv) > 1 else DEFAULT_LOCK
    print("[BACKEND-DEV] Dependency Audit v1.0")
    print(f"[BACKEND-DEV] Lock: {lock_path}")
    print("═" * 50)

    if not os.path.exists(lock_path):
        print(f"[ERRO] Arquivo não encontrado: {lock_path}")
        sys.exit(1)

    packages = parse_composer_lock(lock_path)
    print(f"[BACKEND-DEV] {len(packages)} pacotes encontrados")

    # Check local patterns
    all_findings = []
    for pkg in packages:
        findings = check_known_vulnerable(pkg["name"], pkg["version"])
        all_findings.extend(findings)

    if all_findings:
        print(f"\n[✗] {len(all_findings)} vulnerabilidades encontradas:")
        for f in all_findings:
            print(f"  [{f['severity']}] {f['package']} {f['version']}")
            print(f"    {f['cve']} — {f['fix']}")
    else:
        print("\n[✓] Nenhuma vulnerabilidade conhecida encontrada")

    # Tenta composer audit
    project_root = os.path.dirname(lock_path)
    print("\n[BACKEND-DEV] Tentando composer audit...")
    audit_output = run_composer_audit(project_root)
    if audit_output:
        try:
            audit_data = json.loads(audit_output)
            advisories = audit_data.get("advisories", {})
            if advisories:
                print(f"[✗] Composer audit: {len(advisories)} advisories")
            else:
                print("[✓] Composer audit: limpo")
        except json.JSONDecodeError:
            print("[·] Composer audit: output não parseável")
    else:
        print("[·] Composer audit: indisponível")

    print("\n" + "═" * 50)
    print(f"[BACKEND-DEV] Total: {len(all_findings)} findings")

    report_path = "/tmp/backend-dev-dependency-audit.json"
    with open(report_path, "w") as fp:
        json.dump(
            {
                "actor": "backend-dev",
                "tool": "dependency_audit",
                "packages": len(packages),
                "findings": all_findings,
            },
            fp,
            indent=2,
        )
    print(f"[BACKEND-DEV] Relatório: {report_path}")


if __name__ == "__main__":
    main()
# PULL REQUEST END
