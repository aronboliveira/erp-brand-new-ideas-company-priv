# Regex Commands — 2026-03-19

## Blade i18n sed replacements
- `sed -i 's/aria-label="Close"/aria-label="{{ __('"'"'Close'"'"') }}"/g'`: translate Close attributes
- `sed -i 's/"me-auto">Notice</"me-auto">{{ __('"'"'Notice'"'"') }}</g'`: translate Notice text
- `sed -i 's/aria-label="Toggle navigation"/aria-label="{{ __('"'"'Toggle navigation'"'"') }}"/g'`: translate Toggle navigation

## ViewsConstants extraction regexes
- `grep -oP "const\s+\K[A-Z_]+"`: extract PHP constant names from class
- `grep -oP 'ViewsConstants::\K[A-Z_]+'`: extract ViewsConstants references
- `grep -oP "'[a-z_]+_route_unavailable'"`: extract route unavailable lang keys
- `grep -oP "'(index|store|update|create|edit|destroy|delete|show|generate|export|import|csv|apply|reset|settings|fallback|setup)_"`: CRUD action prefix keys
- `grep -oP "const ${code}\s*=\s*['\"]?\K[^'\";]+"`: extract constant value by name
