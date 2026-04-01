#!/usr/bin/env python3
# ▓ Roleplay: QA — Boundary Value Generator
# QA tester. Gera valores de fronteira para campos de formulário.
# PULL REQUEST START
"""
Gera valores de fronteira (boundary values) para diferentes tipos de campo.
Uso: python3 boundary_value_gen.py
"""
import json


# ── Definições de campos do sistema ──────────────────────
FIELD_DEFINITIONS = {
    "name": {
        "type": "string",
        "min_length": 1,
        "max_length": 255,
        "charset": "alphanumeric_space",
    },
    "email": {
        "type": "email",
        "min_length": 5,
        "max_length": 255,
    },
    "password": {
        "type": "string",
        "min_length": 8,
        "max_length": 128,
    },
    "phone": {
        "type": "phone",
        "min_length": 10,
        "max_length": 15,
    },
    "price": {
        "type": "decimal",
        "min": 0.01,
        "max": 999999.99,
        "precision": 2,
    },
    "quantity": {
        "type": "integer",
        "min": 1,
        "max": 9999,
    },
    "description": {
        "type": "text",
        "min_length": 0,
        "max_length": 65535,
    },
}


def gen_string_boundaries(field: dict) -> list:
    """Gera valores de fronteira para campos string."""
    min_len = field.get("min_length", 0)
    max_len = field.get("max_length", 255)

    values = [
        {"label": "vazio", "value": "", "expected": "reject" if min_len > 0 else "accept"},
        {"label": f"min ({min_len})", "value": "a" * min_len, "expected": "accept"},
        {"label": f"min-1 ({min_len - 1})", "value": "a" * max(0, min_len - 1), "expected": "reject"},
        {"label": f"max ({max_len})", "value": "a" * max_len, "expected": "accept"},
        {"label": f"max+1 ({max_len + 1})", "value": "a" * (max_len + 1), "expected": "reject"},
        {"label": "espaços", "value": "   ", "expected": "reject"},
        {"label": "unicode", "value": "Ñoño àéîõü", "expected": "accept"},
        {"label": "emoji", "value": "👨‍💻🔐🛡️", "expected": "accept"},
        {"label": "null byte", "value": "test\x00value", "expected": "reject"},
        {"label": "HTML tags", "value": "<b>bold</b>", "expected": "sanitize"},
    ]
    return values


def gen_numeric_boundaries(field: dict) -> list:
    """Gera valores de fronteira para campos numéricos."""
    min_val = field.get("min", 0)
    max_val = field.get("max", 999999)
    is_int = field.get("type") == "integer"

    values = [
        {"label": f"min ({min_val})", "value": str(min_val), "expected": "accept"},
        {"label": "min-1", "value": str(min_val - 1), "expected": "reject"},
        {"label": f"max ({max_val})", "value": str(max_val), "expected": "accept"},
        {"label": "max+1", "value": str(max_val + 1), "expected": "reject"},
        {"label": "zero", "value": "0", "expected": "reject" if min_val > 0 else "accept"},
        {"label": "negativo", "value": "-1", "expected": "reject" if min_val >= 0 else "accept"},
        {"label": "string", "value": "abc", "expected": "reject"},
        {"label": "vazio", "value": "", "expected": "reject"},
    ]

    if not is_int:
        precision = field.get("precision", 2)
        values.extend([
            {"label": "precisão+1", "value": f"1.{'0' * precision}1", "expected": "reject"},
            {"label": "float grande", "value": "1.7976931348623157e+308", "expected": "reject"},
        ])

    return values


def gen_email_boundaries(field: dict) -> list:
    """Gera valores de fronteira para campos de email."""
    return [
        {"label": "válido", "value": "test@example.com", "expected": "accept"},
        {"label": "sem @", "value": "testexample.com", "expected": "reject"},
        {"label": "sem domínio", "value": "test@", "expected": "reject"},
        {"label": "sem local", "value": "@example.com", "expected": "reject"},
        {"label": "duplo @", "value": "test@@example.com", "expected": "reject"},
        {"label": "espaço", "value": "test @example.com", "expected": "reject"},
        {"label": "max length", "value": "a" * 243 + "@example.com", "expected": "accept"},
        {"label": "max+1 length", "value": "a" * 244 + "@example.com", "expected": "reject"},
        {"label": "caracteres especiais", "value": "test+tag@example.com", "expected": "accept"},
        {"label": "unicode local", "value": "tëst@example.com", "expected": "reject"},
    ]


def generate_all() -> dict:
    """Gera todos os boundary values para todos os campos."""
    results = {}
    for name, field in FIELD_DEFINITIONS.items():
        ftype = field["type"]
        if ftype in ("string", "text"):
            results[name] = gen_string_boundaries(field)
        elif ftype in ("integer", "decimal"):
            results[name] = gen_numeric_boundaries(field)
        elif ftype == "email":
            results[name] = gen_email_boundaries(field)
        elif ftype == "phone":
            results[name] = gen_string_boundaries(field)
        else:
            results[name] = gen_string_boundaries(field)
    return results


def main():
    print("[QA] Boundary Value Generator v1.0")
    print("═" * 50)

    all_values = generate_all()
    total = sum(len(v) for v in all_values.values())
    print(f"[QA] {len(all_values)} campos, {total} valores gerados\n")

    for field_name, values in all_values.items():
        print(f"[FIELD] {field_name} ({FIELD_DEFINITIONS[field_name]['type']})")
        for v in values:
            display = v["value"][:50] if len(v["value"]) <= 50 else v["value"][:47] + "..."
            print(f"  [{v['expected']:8}] {v['label']:20} → {repr(display)}")
        print()

    print("═" * 50)
    print(f"[QA] {total} boundary values prontos para teste")

    report_path = "/tmp/qa-boundary-values.json"
    with open(report_path, "w") as fp:
        json.dump(
            {"actor": "qa", "tool": "boundary_value_gen", "fields": all_values},
            fp,
            indent=2,
        )
    print(f"[QA] Relatório: {report_path}")


if __name__ == "__main__":
    main()
# PULL REQUEST END
