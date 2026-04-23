# Módulo de Produtos / Products Module Guide

> Products, Services, Categories, Warehouses, Stock.

## Arquivos Chave

### Models (`app/Models/Products/`)

- `ProductService` — produto/serviço unificado
- `ProductServiceCategory` — categorias com `incomeCategoryRevenueAmount()` e `expenseCategoryAmount()`
- `WarehouseProduct` — composite unique key adicionada no schema

### Service

- `Utility::addProductStock()` — cria `StockReport` com campo `title` obrigatório
- Cuidado: `DB::transaction()` dentro de `addProductStock()` causa conflito SAVEPOINT com `RefreshDatabase`
- Em testes, criar `StockReport::create()` diretamente ao invés de chamar `addProductStock()`

## Testes

### ProductServiceCategoryTest

- Usa Mockery partial mocks — intelephense não resolve `incomeCategoryRevenueAmount()` / `expenseCategoryAmount()` (falso positivo)
- Usar `@var ProductServiceCategory $cat` annotation antes de chamadas Mockery

### Warehouse

- `warehouse_products` tem composite unique key — `insertOrIgnore` pode falhar silenciosamente
