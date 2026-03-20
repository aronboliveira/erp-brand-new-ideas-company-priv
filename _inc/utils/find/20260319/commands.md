# Find Commands — 2026-03-19

## Blade i18n replacement targets

- `find resources/views Modules -name '*.blade.php' -exec grep -l 'aria-label="Close"' {} \;`: blade files with untranslated Close
- `find resources/views Modules -name '*.blade.php' -exec grep -l '"me-auto">Notice<' {} \;`: blade files with untranslated Notice
- `find resources/views Modules -name '*.blade.php' -exec grep -l 'aria-label="Toggle navigation"' {} \;`: blade files with untranslated Toggle
