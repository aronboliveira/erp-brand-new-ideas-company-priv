# Agent Context Directory

> **Last updated:** 2026-03-01

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

All files here are symlinks pointing to `_inc/utils/.llms/notes/`. To add a new note:

```bash
# From agents/ directory
ln -s ../../../../notes/YYYYMMDD/copilot/note_name.md backend/module/NOTE_name.md
```

Use `NOTE_` prefix for consistency.

## Format-Based Context Files (../ctx/)

In addition to Markdown agent notes, structured data is available as format-typed
files in the parent `ctx/` directory. Reference these for fast lookups instead
of scanning the long `erp_context.md`:

| File                         | Use when…                                           |
| ---------------------------- | --------------------------------------------------- |
| `../project.yml`             | Stack versions, paths, SA credentials, git config   |
| `../server.toml`             | Artisan commands, composer, supervisor config        |
| `../db_state.json`           | Table counts, UUID pitfalls, priority SQL queries    |
| `../constants_map.json`      | VW::*, PMC::*, MWC::* constant aliases and values   |
| `../middleware_pipeline.xml` | Full request pipeline and guard flow logic           |
| `../test_suites.xml`         | Test suite hierarchy, browser projects, commands     |
| `../notes/context/ci.yml`    | CI commands, worker counts, timeouts, skip patterns  |
