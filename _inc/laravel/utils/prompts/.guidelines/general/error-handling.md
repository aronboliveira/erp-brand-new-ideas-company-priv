# Error Handling Guidelines

All languages in the codebase follow a uniform error-handling philosophy: be defensive, log thoroughly, never expose internals to end users.

## Logging

- Every `catch`/`except` block must include a logging call (`console.error` in JS, `Log::error` in PHP, `logger.error` in Python).
- Log messages must describe the **method that failed** and the **task it was performing**, not a description of the function itself.
  - Good: `Failed to PUT for employees: {error.message}`
  - Bad: `This function updates employees`
- For PHP `Log::` calls that log exceptions, always include the exception's file and line for easier debugging.
- User-facing messages (toasts, modals) must show **only** the error message and, when essential, the status code. Never expose stack traces or internal details.

## Try/Catch Discipline

- Always wrap API calls (fetch, axios, HTTP clients) inside `try`/`catch` or `try`/`except`.
- Add additional `try`/`catch` blocks wherever necessary for defensive programming.
- Do NOT re-throw errors in `catch` blocks unless explicitly asked to.
- All methods that raise non-base exceptions must also handle a fallback case for the base `Exception`/`Error` type.

## Defensive Patterns

- Destructuring must NEVER happen immediately on a response. The response variable must be tested for truthiness first.
- Use optional chaining (`?.`) and nullish coalescing (`??`) aggressively to prevent null-pointer errors.
- Every method must have an explicitly typed return.

## Critical Methods

- Financial or critical procedures require rollback and ACID transaction strategies with associated logging.
- Methods with severe complexity should consider design patterns like Strategy or Observer.
