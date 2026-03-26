# Guia de Migração — laravelcollective/html

**De:** v6.4.1 (ABANDONADO) → **Para:** Alternativa ativa  
**Laravel:** 10.49.0 (framework ^10.10) | **PHP:** 8.4.5  
**Data:** 2026-03-25  
**Risco:** 🔴 Alto — ~200+ usos de `Form::` em Blade; migração requer rewrite extensivo de views

---

## ⚠ Contexto Crítico

`laravelcollective/html` está **oficialmente abandonado** desde 2023. Não receberá mais atualizações de segurança, correções de bugs, ou suporte a novas versões do Laravel/PHP.

O autor recomenda migração para **`spatie/laravel-html`** ou substituição por Blade nativo.

## 1. Opções de Migração

### Opção A: `spatie/laravel-html` (Recomendada pelo autor)

| Aspecto    | Detalhe                                        |
| ---------- | ---------------------------------------------- |
| Pacote     | `spatie/laravel-html`                          |
| Manutenção | Ativa (Spatie)                                 |
| Laravel    | 10.x, 11.x                                     |
| PHP        | 8.1+                                           |
| API        | Diferente — builder chain, não façade estática |
| Esforço    | 🔴 Alto — rewrite de ~200+ chamadas            |

**Exemplo de conversão:**

```php
{{-- laravelcollective/html (atual) --}}
{!! Form::open(['route' => 'invoices.store', 'method' => 'POST']) !!}
{!! Form::label('customer_id', 'Cliente') !!}
{!! Form::select('customer_id', $customers, null, ['class' => 'form-control']) !!}
{!! Form::text('amount', null, ['class' => 'form-control', 'placeholder' => 'Valor']) !!}
{!! Form::submit('Salvar', ['class' => 'btn btn-primary']) !!}
{!! Form::close() !!}

{{-- spatie/laravel-html (alternativa) --}}
{{ html()->form('POST', route('invoices.store'))->open() }}
{{ html()->label('Cliente', 'customer_id') }}
{{ html()->select('customer_id', $customers)->class('form-control') }}
{{ html()->text('amount')->class('form-control')->placeholder('Valor') }}
{{ html()->submit('Salvar')->class('btn btn-primary') }}
{{ html()->form()->close() }}
```

### Opção B: Blade nativo (Sem dependência extra)

| Aspecto    | Detalhe                                   |
| ---------- | ----------------------------------------- |
| Pacote     | Nenhum                                    |
| Manutenção | Parte do Laravel                          |
| Esforço    | 🔴 Alto — rewrite para HTML puro em Blade |
| Vantagem   | Zero dependência adicional                |

**Exemplo de conversão:**

```php
{{-- laravelcollective/html (atual) --}}
{!! Form::open(['route' => 'invoices.store', 'method' => 'POST']) !!}
{!! Form::text('amount', null, ['class' => 'form-control']) !!}
{!! Form::close() !!}

{{-- Blade nativo --}}
<form action="{{ route('invoices.store') }}" method="POST">
    @csrf
    <input type="text" name="amount" value="{{ old('amount') }}" class="form-control" />
</form>
```

### Opção C: Manter com patch (Temporária)

| Aspecto    | Detalhe                                                           |
| ---------- | ----------------------------------------------------------------- |
| Pacote     | `laravelcollective/html` v6.4.1                                   |
| Manutenção | ❌ Abandonado                                                     |
| Patch      | `patches/laravelcollective-html-implicit-nullable.patch` aplicado |
| Risco      | Sem correções futuras; pode quebrar em Laravel 11+                |
| Esforço    | 🟢 Zero — já funciona                                             |

**Recomendação:** Aceitável a curto prazo enquanto Laravel 10 é mantido. Obrigatório migrar antes de atualizar para Laravel 11+.

## 2. Inventário de Uso

### 2.1 Configuração

| Arquivo          | Uso                                                 |
| ---------------- | --------------------------------------------------- |
| `config/app.php` | Alias `'Form' => Collective\Html\FormFacade::class` |

### 2.2 Views principais (~200+ chamadas `Form::`)

| Diretório                                                       | Padrão                                        | Estimativa |
| --------------------------------------------------------------- | --------------------------------------------- | ---------- |
| `resources/views/proposals/`                                    | `Form::open`, `Form::close`, `Form::select`   | ~8         |
| `Modules/LandingPage/Resources/views/landingpage/features/`     | `Collective\Html\FormFacade::` (full qualify) | ~50+       |
| `Modules/LandingPage/Resources/views/landingpage/faqs/`         | `Form::open`, `Form::text`, `Form::textarea`  | ~15+       |
| `Modules/LandingPage/Resources/views/landingpage/screenshots/`  | `Form::open`, `Form::file`, `Form::close`     | ~8+        |
| `Modules/LandingPage/Resources/views/landingpage/testimonials/` | `Form::`                                      | ~10+       |
| `Modules/LandingPage/Resources/views/landingpage/discover/`     | `Form::`                                      | ~10+       |
| `Modules/LandingPage/Resources/views/landingpage/pricing_plan/` | `Form::`                                      | ~15+       |
| `Modules/LandingPage/Resources/views/landingpage/join_us/`      | `Form::`                                      | ~8+        |
| `Modules/LandingPage/Resources/views/landingpage/menubar/`      | `Form::`                                      | ~8+        |
| Outros views                                                    | Espalhados                                    | ~50+       |

### 2.3 Métodos utilizados

| Método             | Frequência Estimada | Conversão Blade                       |
| ------------------ | ------------------- | ------------------------------------- |
| `Form::open()`     | ~40                 | `<form action="" method="">@csrf`     |
| `Form::close()`    | ~40                 | `</form>`                             |
| `Form::model()`    | ~10                 | `<form>` com `old()` / `$model->attr` |
| `Form::text()`     | ~30                 | `<input type="text">`                 |
| `Form::select()`   | ~20                 | `<select>` com `@foreach`             |
| `Form::textarea()` | ~10                 | `<textarea>`                          |
| `Form::label()`    | ~20                 | `<label>`                             |
| `Form::file()`     | ~5                  | `<input type="file">`                 |
| `Form::submit()`   | ~15                 | `<button type="submit">`              |
| `Form::hidden()`   | ~5                  | `<input type="hidden">`               |

## 3. Estratégia de Migração Recomendada

### Fase 1: Manter com patch (Agora)

Status atual — funcional com patch de implicit-nullable. Não requer ação imediata.

### Fase 2: Migrar views do LandingPage (Próximo sprint)

O módulo LandingPage concentra **~70% dos usos** de `Form::`. Como é um módulo isolado, é o melhor candidato para migração incremental.

```bash
# Listar todos os arquivos com Form:: no LandingPage
grep -rl "Form::" Modules/LandingPage/Resources/views/ | wc -l

# Converter um arquivo por vez, testando cada conversão
```

### Fase 3: Migrar views do core (Antes do Laravel 11)

As views em `resources/views/` que usam `Form::` devem ser migradas antes de qualquer upgrade para Laravel 11+.

### Fase 4: Remover o pacote

```bash
# Após todas as views convertidas:
composer remove laravelcollective/html

# Remover alias do config/app.php
# Remover patch do composer.json
# Deletar patches/laravelcollective-html-implicit-nullable.patch
```

## 4. Script de Auditoria

Para encontrar todas as chamadas `Form::` no projeto:

```bash
#!/bin/bash
# Auditoria de uso do laravelcollective/html
echo "=== Contagem por tipo de chamada ==="
for method in "Form::open" "Form::close" "Form::model" "Form::text" "Form::select" "Form::textarea" "Form::label" "Form::file" "Form::submit" "Form::hidden" "Form::number" "Form::email" "Form::password" "Form::checkbox" "Form::radio"; do
  count=$(grep -rl "$method" resources/views/ Modules/ 2>/dev/null | wc -l)
  echo "  $method: $count arquivo(s)"
done

echo ""
echo "=== Arquivos com qualquer Form:: ==="
grep -rl "Form::\|Collective\\\\Html" resources/views/ Modules/ 2>/dev/null | sort
```

## 5. Conversão — Referência Rápida

### Form::open → HTML

```php
{{-- DE --}}
{!! Form::open(['route' => 'resource.store', 'method' => 'POST', 'class' => 'form-horizontal', 'enctype' => 'multipart/form-data']) !!}

{{-- PARA --}}
<form action="{{ route('resource.store') }}" method="POST" class="form-horizontal" enctype="multipart/form-data">
    @csrf
```

### Form::model → HTML

```php
{{-- DE --}}
{!! Form::model($item, ['route' => ['resource.update', $item->id], 'method' => 'PUT']) !!}

{{-- PARA --}}
<form action="{{ route('resource.update', $item->id) }}" method="POST">
    @csrf
    @method('PUT')
```

### Form::select → HTML

```php
{{-- DE --}}
{!! Form::select('category_id', $categories, null, ['class' => 'form-control', 'required' => true]) !!}

{{-- PARA --}}
<select name="category_id" class="form-control" required>
    <option value="">Selecione...</option>
    @foreach($categories as $id => $name)
        <option value="{{ $id }}" {{ old('category_id', $item->category_id ?? '') == $id ? 'selected' : '' }}>
            {{ $name }}
        </option>
    @endforeach
</select>
```

### Form::text → HTML

```php
{{-- DE --}}
{!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Nome', 'required' => true]) !!}

{{-- PARA --}}
<input type="text" name="name" value="{{ old('name', $item->name ?? '') }}" class="form-control" placeholder="Nome" required />
```

## 6. Riscos de Não Migrar

| Risco                                      | Probabilidade | Impacto    |
| ------------------------------------------ | ------------- | ---------- |
| Vulnerabilidade de segurança não corrigida | Média         | Alto       |
| Incompatibilidade com Laravel 11           | Alta          | Bloqueante |
| Incompatibilidade com PHP 9.x              | Alta          | Bloqueante |
| Falha em CI/CD por deprecation warnings    | Baixa         | Médio      |

## 7. Referências

- [Abandono oficial](https://github.com/LaravelCollective/html) — README indica abandono
- [spatie/laravel-html](https://github.com/spatie/laravel-html) — substituto recomendado
- [Laravel Blade Forms](https://laravel.com/docs/10.x/blade#forms) — documentação oficial
- [Migration Tool](https://github.com/protonemedia/laravel-form-components) — componentes Blade alternativos
