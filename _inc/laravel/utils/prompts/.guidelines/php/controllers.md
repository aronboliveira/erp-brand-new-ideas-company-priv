# Controller Conventions

## Abstract Controller Pattern

All module controllers extend an abstract base controller that wraps `callAction` for
automatic performance measurement. This ensures every controller action is timed without
repeating boilerplate in each controller.

### `callAction` Wrapper

The base controller overrides `callAction` to wrap each action in a timing envelope:

```php
public function callAction($method, $parameters)
{
    return $this->measure($method, fn () => parent::callAction($method, $parameters));
}
```

### `measure()` Method

Captures wall-clock time and delegates to `logExecutionTime`:

```php
protected function measure(string $label, \Closure $callback): mixed
{
    $start  = microtime(true);
    $result = $callback();
    $ms     = (microtime(true) - $start) * 1000;

    $this->logExecutionTime($label, $ms);

    return $result;
}
```

### `measureProfile()` Method

For sub-action profiling (e.g., measuring a specific query or service call within an action):

```php
protected function measureProfile(string $label, \Closure $callback): mixed
{
    return $this->measure("profile:{$label}", $callback);
}
```

## Execution Time Thresholds

| Elapsed (ms) | Log Level | Action                          |
| ------------ | --------- | ------------------------------- |
| ≤ 50         | `debug`   | Normal — log for tracing only   |
| 51 – 150     | `info`    | Acceptable — log for monitoring |
| 151 – 300    | `warning` | Slow — investigate              |
| > 300        | `error`   | Critical — immediate attention  |

### `logExecutionTime`

```php
protected function logExecutionTime(string $label, float $ms): void
{
    $context = ['action' => $label, 'ms' => round($ms, 2)];

    if ($ms <= 50) {
        Log::debug("Action timing", $context);
    } elseif ($ms <= 150) {
        Log::info("Action timing", $context);
    } elseif ($ms <= 300) {
        Log::warning("Slow action", $context);
    } else {
        Log::error("Critical slow action", $context);
    }
}
```

## Validation Methods

Validation logic lives in controller methods (not form requests) when it is tightly coupled
to the action's business logic. Keep validation methods `protected` and co-located with
the action that calls them.

## Missing Endpoints

When adding missing endpoints:

1. Follow the **same pattern** as existing endpoints in the module.
2. If no module pattern exists, follow **RESTful design** (`index`, `show`, `store`, `update`, `destroy`).
3. Match the naming convention of sibling controllers.

## Console Output for Timing

Use Symfony `ConsoleOutput` in artisan-triggered controllers for human-readable timing:

```php
$this->output->writeln("<comment>[{$ms}ms] {$label}</comment>");
```

## View Guards

Always check `View::exists()` before returning a view to prevent 500 errors from missing templates:

```php
if (!View::exists($viewName)) {
    abort(404, "View [{$viewName}] not found.");
}
return view($viewName, $data);
```
