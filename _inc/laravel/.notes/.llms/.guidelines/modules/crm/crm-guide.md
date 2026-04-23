# Módulo CRM / CRM Module Guide

> Leads, Deals, Pipelines, Stages, Labels.

## Arquivos Chave

### Models (`app/Models/Planning/`)

- `Deal`, `DealTask` — cuidado com `__get()` collision (OOM fix: `$this->getAttributes()['col']`)
- `Lead`, `LeadStage` — `created_by` movido de `$guarded` para `$fillable`
- `Pipeline` — `created_by` em `$fillable`
- `Label`, `JobStage`, `TaskStage` — idem

### Constants

- `CrmPipelineConstants as CPC` — aliases para pipelines/stages

## Problemas Conhecidos

### Circular Redirect Loops

- `/deals/create`, `/deals/{id}/tasks/create` — redirect loops por middleware de permissão
- Root cause: guards usam `back()` que encadeia com outras rotas guardadas
- Fix necessário: per-module fallback targets

### Deal Routes (HTTP 500 — data-dependent)

- `/deals/1/tasks`, `/deals/1/users` — seeder cria 0 deals
- São `ModelNotFoundException` dentro do controller, não bugs de código

## Convenções Específicas

- Deals/Leads usam UUID PKs — nunca usar IDs inteiros em URLs
- Pipeline relations podem colidir com column names — usar `getAttributes()['col']`
