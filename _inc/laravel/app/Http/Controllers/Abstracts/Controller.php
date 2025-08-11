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
use Illuminate\Support\Facades\{Log, Route};
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
}
