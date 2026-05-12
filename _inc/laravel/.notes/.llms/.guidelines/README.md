# Subagent Guidelines Tree

> Estruturado para subagentes de IA operarem em domínios específicos do ERP.
> Cada subdiretório contém regras, convenções e contexto por camada, módulo ou papel.

## Estrutura / Structure

```
.guidelines/
├── README.md                    ← Você está aqui / You are here
├── project.yml                  ← Configurações globais do projeto / Global project config
├── constraints.md               ← Restrições gerais / General constraints
│
├── backend/                     ← Camada backend PHP/Laravel
│   ├── constants_map.json       ← Mapa de aliases de constantes (11 classes)
│   ├── erp-architecture.md      ← Visão geral da arquitetura
│   ├── reliability-outbox-ledger.md ← Outbox/inbox + operation ledger policy
│   ├── middleware_pipeline.xml  ← Pipeline de middlewares
│   ├── naming-conventions.yml   ← Convenções de nomes
│   ├── services/                ← Padrões de serviços (delegation, DI)
│   │   └── delegation-patterns.md
│   ├── models/                  ← Convenções de modelos Eloquent
│   │   └── model-conventions.md
│   └── controllers/             ← Padrões de controllers
│       └── controller-patterns.md
│
├── database/                    ← Banco de dados, migrations, seeders
│   └── database-guide.md
│
├── frontend/                    ← Frontend JS/TS/Blade
│   ├── esm-iife-strategy.md    ← Estratégia de build ESM→IIFE
│   ├── template-literal-testing.md
│   ├── blade/                   ← Templates Blade
│   │   └── blade-conventions.md
│   ├── typescript/              ← Migração TypeScript
│   │   └── typescript-guide.md
│   └── assets/                  ← Assets JS, singletons, rotas
│       └── js-singletons.md
│
├── infrastructure/              ← Docker, Nginx, CI/CD
│   └── server.toml
│
├── modules/                     ← Módulos do ERP (por domínio)
│   ├── billing/                 ← Faturamento (invoices, bills, payments)
│   │   └── billing-guide.md
│   ├── hrm/                     ← Gestão de RH (employees, payroll)
│   │   └── hrm-guide.md
│   ├── crm/                     ← CRM (leads, deals, pipelines)
│   │   └── crm-guide.md
│   ├── planning/                ← Projetos, tarefas, contratos
│   │   └── planning-guide.md
│   ├── accounting/              ← Contabilidade (CoA, journal, balance)
│   │   └── accounting-guide.md
│   └── products/                ← Produtos, estoque, warehouse
│       └── products-guide.md
│
├── roles/                       ← Perfis de subagentes por papel
│   └── agent-roles.md
│
├── security/                    ← Padrões de segurança
│   └── security-patterns.xml
│
└── testing/                     ← Testes (PHPUnit, Jest, Playwright)
    ├── ci.yml
    ├── test-architecture.json
    ├── test_suites.xml
    └── typescript-test-harness.md
```

## Como usar / How to use

1. **Subagentes de backend**: Leia `backend/` + módulo específico em `modules/`
2. **Subagentes de frontend**: Leia `frontend/` + `modules/` relevante
3. **Subagentes de teste**: Leia `testing/` + módulo sendo testado
4. **Subagentes de banco**: Leia `database/` + módulo relevante
5. **Perfis compostos**: Veja `roles/agent-roles.md` para combinações predefinidas
6. **Resiliência**: Leia `backend/reliability-outbox-ledger.md` e
   `.tmp/codex/20260512/final-resilience-readiness-scan.md` antes de adicionar
   novas proteções; em dev mode, a camada genérica já está no limite
   responsável sem segredos reais de provedores.

## Arquivos complementares / Complementary files

- `_inc/laravel/utils/prompts/.guidelines/` — Regras de codificação por linguagem (PHP, JS, CSS, Python, React, TS)
- `_inc/laravel/.notes/.llms/` — Logs de sessões e relatórios históricos
- `_inc/laravel/.notes/.llms/.history/` — Arquivo de relatórios antigos
