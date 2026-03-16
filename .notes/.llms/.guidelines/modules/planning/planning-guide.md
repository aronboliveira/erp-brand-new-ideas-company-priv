# Módulo de Planejamento / Planning Module Guide

> Projects, Tasks, Contracts, Goals, Milestones.

## Arquivos Chave

### Models (`app/Models/Planning/`)

- `Project`, `ProjectTask` — UUIDs, `created_by` em `$fillable`
- `Contract`, `ContractAttachment` (renomeado de `Contract_attachment`)
- `Proposal`, `ProposalProduct`
- `Goal`, `ProjectStage` (renomeado de `Projectstages`, singular)

### Controllers (`app/Http/Controllers/Planning/`)

- `ProjectController` — `PRJ_CPY_LNK` const, UUID params
- `ProjectReportController` — `ajaxData` e `ajaxTasksReport` (renomeados de snake_case)
- `ContractController` — `CL_WS_PRJ` const para `clientWiseProject`
- `SetSalaryController` — precisa de `JsonResponse` import

## Problemas Conhecidos

### Project UUID vs Integer

- 5 rotas falham com integer `1` — projects usam UUID PKs
- `/projects/1/users/1/permission`, `/projects/copies/1`, etc.
- São erros de dados, não de código

### Proposal — Namespace

- `Proposal.php` referencia `Utility::` (não `\Utility::`) — mesmo namespace `App\Models`
- Funciona em runtime via alias em `config/app.php` + classmap autoloading
- Intelephense mostra warning (falso positivo) por Utility estar em subdiretório `utils/`

## Constants

- `ProjectConstants as PJC` — aliases para projeto, milestones, stages
