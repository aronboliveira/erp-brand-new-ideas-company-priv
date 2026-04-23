# Agent Context Directory

> **Last updated:** 2026-02-28

## Structure

Organized by **focus area** to enable focused agent sessions:

```
agents/
├── frontend/          ← Blade views, JS, CSS, i18n, UI components
├── backend/           ← Controllers, models, seeders, services, DB-related
│   ├── crm/           ← CRM module (chat, leads, deals, pipelines)
│   ├── financial/     ← Finance module (invoices, bills, payments, reports)
│   ├── hrm/           ← HRM module (employees, attendance, payroll, guards)
│   ├── sales/         ← Sales module (commerce, POS)
│   ├── proposal/      ← Proposals module
│   ├── user/          ← User management (auth, 2FA, encryption)
│   └── other/         ← Cross-cutting concerns (dashboards, exports, seeders)
└── infrastructure/    ← DevOps, testing, deployment, migrations, config
```

## How to use

- **Frontend agent:** Attach all files from `frontend/` plus `erp_context.md`
- **Backend agent:** Attach all files from `backend/{module}/` plus `erp_context.md`
- **Infrastructure agent:** Attach all files from `infrastructure/` plus `erp_context.md`

## Adding new notes

All files here are symlinks pointing to `_inc/laravel/utils/.llms/notes/`. To add a new note:

```bash
# From agents/ directory
ln -s ../../../../notes/YYYYMMDD/copilot/note_name.md backend/module/NOTE_name.md
```

Use `NOTE_` prefix for consistency.
