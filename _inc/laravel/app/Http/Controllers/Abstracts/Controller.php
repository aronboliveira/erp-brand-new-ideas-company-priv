<?php

namespace App\Http\Controllers;


use App\Config\Constants\SettingsConstants;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Foundation\{
    Auth\Access\AuthorizesRequests,
    Bus\DispatchesJobs,
    Precognition,
};
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\{Log, Route, View};
use Illuminate\Support\Str;
use Illuminate\Validation\{ValidationException, Validator};

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs;
    private const FAILURES = 'validation_failures';
    protected const NOTICE_THRESHOLD_MS   = 250;
    protected const WARNING_THRESHOLD_MS  = 1000;
    protected const CRITICAL_THRESHOLD_MS = 4000;

    /**
     * Validate the given request with the given rules.1
     *
     * @param  Request  $request
     * @param  array  $rules
     * @param  array  $messages
     * @param  array  $attributes
     * @return ?array
     *
     * @throws ValidationException
     */
    public function validate(Request $request, array $rules, array $messages = [], array $attributes = [], ?bool $shouldRedirect = true)
    {
        $method = __CLASS__ . '::' . __FUNCTION__;
        Log::debug("$method - start", [
            'input_keys' => array_keys($request->all()),
            'rules'      => $rules,
            'messages'   => $messages,
            'attributes' => $attributes,
        ]);
        $validator = $this->getValidationFactory()->make(
            $request->all(),
            $rules,
            $messages,
            $attributes
        );
        if ($request->isPrecognitive() && $validator instanceof Validator) {
            Log::debug("$method - applying precognitive hooks", [
                'rules_before' => $validator->getRulesWithoutPlaceholders()
            ]);
            $validator->after(Precognition::afterValidationHook($request))
                ->setRules($request->filterPrecognitiveRules(
                    $validator->getRulesWithoutPlaceholders()
                ));
        }
        try {
            $result = $validator->validate();
            Log::debug("$method - validation passed", ['validated' => $result]);
            session()->forget(self::FAILURES);
            return $result;
        } catch (ValidationException $e) {
            $attempts = session()->increment(self::FAILURES, 1);
            Log::warning("$method - validation failed", [
                'main_error' => $e->getMessage(),
                'errors' => $e->validator->errors()->all(),
                'input_keys' => array_keys($request->all()),
                'rules'      => $rules,
                'status'    => '401'
            ]);
            if (!$shouldRedirect || Route::is('login')) {
                if ($attempts >= 5) {
                    Log::error("$method - too many validation attempts ({$attempts}), aborting");
                    abort(429, 'Too many attempts. Please try again later.');
                }
                $uuid = Str::uuid();
                $msg = implode('\n', $e->validator->errors()->all());
                $snippet = <<<HTML
                <script id="err-login-{$uuid}">
                  alert("Validation failed:\\n{$msg}");c
                  console.error('Validation failed: {$msg}');
                  setTimeout(function(){
                    var el = document.getElementById("err-{$uuid}");
                    if(el) el.remove();
                  }, 15000);
                </script>
                HTML;
                return response()->json([
                    'error'   => "View [{$method}] failed to load",
                    'snippet' => $snippet,
                    'status'  => 401,
                ]);
            }
            return redirect()->route('login')
                ->withErrors($e->validator)->withInput($request->except('passwords'));
        } catch (\Throwable $e) {
            $errCtx = [
                'error' => $e->getMessage(),
                'status' => '500',
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ];
            $attempts = session()->increment(self::FAILURES, 1);
            Log::error("$method - unexpected error during validation", $errCtx);
            Log::channel(SettingsConstants::CRT_TRACE)->debug(
                "$method - unexpected error during validation",
                array_merge($errCtx, [
                    'input_keys' => array_keys($request->all()),
                    'rules'      => $rules,
                    'trace' => $e->getTraceAsString(),
                ])
            );
            if (!$shouldRedirect || Route::is('login')) {
                if ($attempts >= 5) {
                    Log::error("$method - too many validation attempts ({$attempts}), aborting");
                    abort(429, 'Too many attempts. Please try again later.');
                }
                $uuid = Str::uuid();
                $snippet = <<<HTML
                <script id="err-login-{$uuid}">
                    alert('An unexpected error occurred: {$e->getMessage()}');
                    console.error('Validation failed: {$e->getMessage()}');
                    setTimeout(function(){
                    var el = document.getElementById("err-{$uuid}");
                    if(el) el.remove();
                  }, 15000);
                </script>
                HTML;
                return response()->json([
                    'error'   => "View [{$method}] failed to load",
                    'snippet' => $snippet,
                    'status'  => 500,
                ]);
            }
            return redirect()->route('login')
                ->with('error', 'An unexpected error occurred. Please try again later.');
        }
    }

    /**
     * Validate the given request with the given rules.
     *
     * @param  string  $errorBag
     * @param  Request  $request
     * @param  array  $rules
     * @param  array  $messages
     * @param  array  $attributes
     * @return ?array
     *
     * @throws ValidationException
     */
    public function validateWithBag($errorBag, Request $request, array $rules, array $messages = [], array $attributes = [], ?bool $shouldRedirect = true)
    {
        $method = __CLASS__ . '::' . __FUNCTION__;
        Log::debug("$method - start", [
            'input_keys' => array_keys($request->all()),
            'rules'      => $rules,
            'messages'   => $messages,
            'attributes' => $attributes,
        ]);
        $validator = $this->getValidationFactory()->make(
            $request->all(),
            $rules,
            $messages,
            $attributes
        );
        if ($request->isPrecognitive() && $validator instanceof Validator) {
            Log::debug("$method - applying precognitive hooks", [
                'rules_before' => $validator->getRulesWithoutPlaceholders()
            ]);
            $validator->after(Precognition::afterValidationHook($request))
                ->setRules($request->filterPrecognitiveRules(
                    $validator->getRulesWithoutPlaceholders()
                ));
        }
        try {
            $result = $validator->validate();
            Log::debug("$method - validation passed", ['validated' => $result]);
            session()->forget(self::FAILURES);
            return $result;
        } catch (ValidationException $e) {
            $attempts = session()->increment(self::FAILURES, 1);
            Log::warning("$method - validation failed", [
                'main_error' => $e->getMessage(),
                'errors' => $e->validator->errors()->all(),
                'input_keys' => array_keys($request->all()),
                'rules'      => $rules,
                'status'    => $e->getCode() ?? 'n/a',
                'error_bag' => $errorBag,
            ]);
            if (!$shouldRedirect || Route::is('login')) {
                if ($attempts >= 5) {
                    Log::error("$method - too many validation attempts ({$attempts}), aborting");
                    abort(429, 'Too many attempts. Please try again later.');
                }
                $uuid = Str::uuid();
                $msg = implode('\n', $e->validator->errors()->all());
                $snippet = <<<HTML
                <script id="err-login-{$uuid}">
                  alert("Validation failed:\\n{$msg}");c
                  console.error('Validation failed: {$msg}');
                  setTimeout(function(){
                    var el = document.getElementById("err-{$uuid}");
                    if(el) el.remove();
                  }, 15000);
                </script>
                HTML;
                return response()->json([
                    'error'   => "View [{$method}] failed to load",
                    'snippet' => $snippet,
                    'status'  => 500,
                    'bag'     => $errorBag,
                ]);
            }
            return redirect()->route('login')
                ->withErrors($e->validator)->withInput($request->except('passwords'));
        } catch (\Throwable $e) {
            $errCtx = [
                'error' => $e->getMessage(),
                'status' => '500',
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
            ];
            $attempts = session()->increment(self::FAILURES, 1);
            Log::error("$method - unexpected error during validation", $errCtx);
            Log::channel(SettingsConstants::CRT_TRACE)->debug(
                "$method - unexpected error during validation",
                array_merge($errCtx, [
                    'input_keys' => array_keys($request->all()),
                    'rules'      => $rules,
                    'trace' => $e->getTraceAsString(),
                ])
            );
            if (!$shouldRedirect || Route::is('login')) {
                if ($attempts >= 5) {
                    Log::error("$method - too many validation attempts ({$attempts}), aborting");
                    abort(429, 'Too many attempts. Please try again later.');
                }
                $uuid = Str::uuid();
                $snippet = <<<HTML
                <script id="err-login-{$uuid}">
                    alert('An unexpected error occurred: {$e->getMessage()}');
                    console.error('Validation failed: {$e->getMessage()}');
                    setTimeout(function(){
                    var el = document.getElementById("err-{$uuid}");
                    if(el) el.remove();
                  }, 15000);
                </script>
                HTML;
                return response()->json([
                    'error'   => "View [{$method}] failed to load",
                    'snippet' => $snippet,
                    'status'  => 500,
                    'bag'     => $errorBag,
                ]);
            }
            return redirect()->route('login')
                ->with('error', 'An unexpected error occurred. Please try again later.');
        }
    }

    /**
     * Get a validation factory instance.
     *
     * @return \Illuminate\Contracts\Validation\Factory
     */
    protected function getValidationFactory()
    {
        return app(Factory::class);
    }

    /**
     * Run the validation routine against the given validator.
     *
     * @param  \Illuminate\Contracts\Validation\Validator|array  $validator
     * @param  \Illuminate\Http\Request|null  $request
     * @return ?array
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validateWith($validator, ?Request $request = null)
    {
        Log::info('Called ' . __FUNCTION__, [
            'validator' => is_array($validator) ? 'array' : get_class($validator),
            'request' => $request ? $request->all() : 'null'
        ]);
        $request = $request ?: request();
        if (is_array($validator))
            $validator = $this->getValidationFactory()->make($request->all(), $validator);
        if ($request->isPrecognitive() && $validator instanceof Validator) {
            $validator->after(Precognition::afterValidationHook($request))
                ->setRules(
                    $request->filterPrecognitiveRules($validator->getRulesWithoutPlaceholders())
                );
        }
        return $validator->validate();
    }

    /**
     * Measure and log the given callback as a controller action.
     *
     * @param  string   $action     The method name being executed
     * @param  \Closure $callback   The actual action invocation
     * @param  array    $parameters The action parameters
     * @return mixed
     */
    public function measureProfile(string $action, \Closure $callback, array $parameters = [])
    {
        $class = static::class;
        $start = microtime(true);
        try {
            $response = $callback();
        } catch (\Throwable $e) {
            $this->logExecutionTime($start, $action, 'exception');
            Log::error("{$class}::{$action} threw exception to measureProfile. Bubbling up... ", [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                'params'    => $parameters
            ]);
            throw $e;
        }
        $this->logExecutionTime($start, $action, 'completed');
        return $response;
    }

    /**
     * Perform the threshold checks and emit notice/warning/critical.
     */
    protected function logExecutionTime(float $startTime, string $action, string $result): void
    {
        $class         = static::class;
        $executionTime = round((microtime(true) - $startTime) * 1000, 2);
        $memoryNow     = memory_get_usage();
        $memoryPeak    = memory_get_peak_usage();
        $context = [
            'action'                   => $action,
            'execution_time_ms'        => $executionTime,
            'memory_usage_bytes'       => $memoryNow,
            'memory_peak_usage_bytes'  => $memoryPeak,
            'result'                   => $result,
            'thresholds_ms'            => [
                'notice'   => self::NOTICE_THRESHOLD_MS,
                'warning'  => self::WARNING_THRESHOLD_MS,
                'critical' => self::CRITICAL_THRESHOLD_MS,
            ],
        ];
        if ($executionTime > self::CRITICAL_THRESHOLD_MS) {
            Log::warning("{$class}::{$action} extremely slow (> {$context['thresholds_ms']['critical']} ms)", $context);
            return;
        }
        if ($executionTime > self::WARNING_THRESHOLD_MS) {
            Log::notice("{$class}::{$action} slow (> {$context['thresholds_ms']['warning']} ms)", $context);
            return;
        }
        if ($executionTime > self::NOTICE_THRESHOLD_MS) {
            Log::info("{$class}::{$action} above expected (> {$context['thresholds_ms']['notice']} ms)", $context);
        }
    }

    /**
     * Get the first existing view from provided path(s)
     * 
     * @param string|array $viewPath Single view path or array of view paths
     * @return string|null The first existing view path or null if none found
     * @throws \InvalidArgumentException If input validation fails
     */
    protected static function getFirstExistingView(string|array $viewPath, array $data = []): ?string
    {
        if (is_string($viewPath)) {
            $viewPath = static::sanitizeViewPath($viewPath);
            $bases = ['landing_page', 'landing-page', 'landingpage', 'LandingPage', 'landingPage', 'Landingpage', 'LANDINGPAGE'];
            $viewPaths = [$viewPath];
            $trimmedPath = ltrim($viewPath, '.');
            foreach ($bases as $base1) {
                $viewPaths[] = "$base1::$trimmedPath";
                foreach ($bases as $base2)
                    $viewPaths[] = "$base1::$base2.$trimmedPath";
            }
        } elseif (is_array($viewPath)) {
            if (empty($viewPath))
                throw new \InvalidArgumentException('View path array cannot be empty');
            $viewPaths = array_map(function ($path) {
                if (!is_string($path))
                    throw new \InvalidArgumentException('All view paths must be strings, ' . gettype($path) . ' given');
                return static::sanitizeViewPath($path);
            }, $viewPath);
        } else
            throw new \InvalidArgumentException(
                'Argument must be a string or an array of strings, ' . gettype($viewPath) . ' given'
            );
        $viewPaths = array_values(array_filter(array_unique($viewPaths), fn($path) => !empty($path)));
        if (count($viewPaths) > 254)
            throw new \InvalidArgumentException('Too many view paths provided (max 50)');
        foreach ($viewPaths as $path) {
            try {
                if (View::exists($path)) {
                    Log::debug('View resolved', ['path' => $path]);
                    return $path;
                }
            } catch (\Throwable $e) {
                Log::warning('Error checking view existence', [
                    'path' => $path,
                    'error' => $e->getMessage()
                ]);
                continue;
            }
        }
        Log::warning('No existing view found', ['attempted_paths' => $viewPaths]);
        return null;
    }

    /**
     * Sanitize view path to prevent path traversal and injection attacks
     * 
     * @param string $path The view path to sanitize
     * @return string Sanitized view path
     * @throws \InvalidArgumentException If path is invalid
     */
    protected static function sanitizeViewPath(string $path): string
    {
        $path = trim($path);
        if (strlen($path) > 255)
            throw new \InvalidArgumentException('View path too long (max 255 characters)');
        if (empty($path))
            throw new \InvalidArgumentException('View path cannot be empty');
        if (preg_match('#(\.\.|/\.|\\\\)#', $path))
            throw new \InvalidArgumentException('Invalid view path: path traversal detected');
        if (!preg_match('/^[a-zA-Z0-9._:\-]+$/', $path))
            throw new \InvalidArgumentException('Invalid view path: contains illegal characters');
        if (substr_count($path, '::') > 1)
            throw new \InvalidArgumentException('Invalid view path: multiple namespace separators');
        if (str_contains($path, '::')) {
            $parts = explode('::', $path, 2);
            if (empty($parts[0]) || empty($parts[1]))
                throw new \InvalidArgumentException('Invalid view path: malformed namespace');
        }
        if (str_contains($path, '..'))
            throw new \InvalidArgumentException('Invalid view path: consecutive dots not allowed');
        $path = str_replace(['/', '\\'], '.', $path);
        $path = preg_replace('/\.+/', '.', $path);
        $path = trim($path, '.');
        return $path;
    }
}
