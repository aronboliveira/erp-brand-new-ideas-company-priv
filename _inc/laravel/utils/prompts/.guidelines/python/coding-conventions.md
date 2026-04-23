# Python Coding Conventions

## Importing

Never use `importlib` unless explicitly and strictly demanded by a requirement. All imports must be standard `import` or `from ... import ...` statements. This ensures clarity, IDE support, and static analysis compatibility.

`get_redirect_url` must always be imported from `.._helpers.http`.

## Type Hinting

Every function and method **must** include complete type hints — parameters and return types. There are no exceptions. This applies to private methods, classmethods, staticmethods, lambdas (when assigned), and nested functions.

```python
# Correct
def calculate_total(items: list[dict], tax_rate: float) -> Decimal:
    ...

# Incorrect — missing return type
def calculate_total(items, tax_rate):
    ...
```

All methods must have **explicitly typed returns**. If a method returns nothing, annotate with `-> None`.

## String References to Functions and Methods

Hardcoded function/method name strings are **forbidden**. Instead, use runtime introspection:

- **Inside a function/method**: `inspect.currentframe().f_code.co_name`
- **From a traceback context**: `traceback.extract_stack()`

This ensures rename-safety and eliminates silent breakage when refactoring.

```python
import inspect

def process_order(self, order_id: int) -> None:
    fn = inspect.currentframe().f_code.co_name  # "process_order"
    logger.info(f"{self.__class__.__name__}::{fn} called with {order_id}")
```

## Classes

### Preservation

Never convert a class into a standalone function. Classes exist for a reason — they group state, behaviour, and identity. If a class seems too small, consider whether it will grow or whether it participates in polymorphism.

### Method Types

| Decorator       | When to use                                                                      |
| --------------- | -------------------------------------------------------------------------------- |
| (instance)      | Needs `self`, accesses instance state                                            |
| `@classmethod`  | Needs `cls`, performs auth checks, error handling, or factory logic              |
| `@staticmethod` | Pure procedure, no class/instance references needed, no auth/error_handler calls |

Only use `@staticmethod` when **no** class references are needed and the method does not call auth or error handler utilities.

### Class Name Strings

Never hardcode class name strings. Store `__class__.__name__` in an acronym constant and chain with `self`, `cls`, or `None` depending on method type:

```python
class OrderProcessor:
    _CN = None  # set dynamically

    def __init__(self) -> None:
        self._CN = self.__class__.__name__

    @classmethod
    def from_dict(cls, data: dict) -> "OrderProcessor":
        cn = cls.__name__
        logger.debug(f"{cn}::from_dict invoked")
        ...
```

## Replacement Patterns

### Permission Denial Blocks

All permission denial handling must use the standardized helper:

```python
# Before (incorrect)
except PermissionError as e:
    return HttpResponseForbidden(str(e))

# After (correct)
except PermissionError as e:
    return default_permission_denial(
        request,
        err=e,
        ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
        logger=logger
    )
```

### Undefined Exceptions

All catch-all exception blocks must use:

```python
except Exception as e:
    return default_undefined_exception(
        request,
        err=e,
        ref=f'{cls.__name__}::{inspect.currentframe().f_code.co_name}',
        logger=logger
    )
```

## Indentation & Formatting

- **Argument separation**: Never use more than one whitespace character to separate arguments.
- **Foldable indentation**: Maintain a pattern that allows IDE folding. Each continuation line aligns logically.
- **Multi-line strings**: Use triple-quoted strings (`"""..."""`) with consistent indentation. Do not use string concatenation for multi-line text.
- **Semicolons**: **DO NOT use semicolons**. Use newlines to separate statements — always.

```python
# Correct
result = some_function(
    arg_one,
    arg_two,
    arg_three,
)

# Incorrect — double spaces between args
result = some_function(arg_one,  arg_two,  arg_three)
```

## Strict Rules

1. **Type hinting**: Always. No exceptions.
2. **Server logging**: Detailed. Every method entry/exit, every branch, every error — logged with context.
3. **Multi-exception handling**: Handle each expected exception type separately, then add a final `except Exception` catch-all using `default_undefined_exception`.
4. **ACID / Rollback**: All financial operations must be wrapped in database transactions with explicit rollback on failure. Use `transaction.atomic()` or equivalent.
5. **Design patterns**: Use Strategy pattern for interchangeable algorithms and Observer pattern for event-driven flows in complex methods.

## Exception Handling

- All methods that raise non-base exceptions **must** also handle generic `Exception` as a fallback.
- Abstract repetitive procedures (like permission checks) into **private methods** to reduce duplication and improve testability.

```python
def _check_permission(self, request: HttpRequest, action: str) -> None:
    if not request.user.has_perm(action):
        raise PermissionError(f"User lacks permission: {action}")
```

## Utility Imports

- `get_redirect_url` → always from `.._helpers.http`
- `default_permission_denial` → from the project's standard error handler module
- `default_undefined_exception` → from the project's standard error handler module
