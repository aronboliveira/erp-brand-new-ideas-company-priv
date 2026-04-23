# Módulo HRM / HRM Module Guide

> Employees, Payroll, Attendance, Leave, Awards, Benefits.

## Arquivos Chave

### Models (`app/Models/Individuals/` + `app/Models/Planning/`)

- `Employee`, `EmployeeAttendance`, `EmployeeAnnouncement`
- `User` — tem `notifications()` HasMany override (usa `user_id` ao invés de morphMany)
- `SetSalary` — payroll com `gross_salary` (não `basic_salary`)

### Models (`app/Models/Shapes/`)

- `Timesheet` — usa SoftDeletes (coluna `deleted_at` adicionada via migration)

### Controllers

- `SetSalaryController` — precisa de `JsonResponse` import
- `TimesheetController` — 7 guard patterns corrigidos (`!== true`)

## Problemas Conhecidos

### HRM Hidden Tables

- `/meetings` e `/award_types` — tabelas com `display:none` até JS carregar
- Playwright não pode `toBeVisible()` — testes pulam estas páginas
- Fix: CSS ou `waitForResponse` no teste

### Payslip

- Campo correto: `gross_salary` via `BC::COL_G_SLR`
- Overtime JSON pode exceder `CHAR(36)` — evitar em testes
- `employeeDetails()` double-hashes passwords — criar Employee diretamente

## Permissões Spatie

Permissões HRM definidas em `PermissionsConstants.php` e seedadas em `SeedersTemplating.php`. Faltantes causam HTTP 500 (training, zoom, reports).

## Convenções de Nomes (corrigidos)

- `AnnouncementEmployee` → `EmployeeAnnouncement` ✅
- `AttendanceEmployee` → `EmployeeAttendance` ✅
