<?php

namespace App\Http\Middleware;

use Closure;
use App\Config\Constants\{PermissionsConstants, SettingsConstants, UsersConstants};
use App\Models\{User, Utility};
use App\Traits\ChecksLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{App, Log};
use RachidLaasri\LaravelInstaller\Helpers\MigrationsHelper;
use App\Helpers\SafeConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

final class XSS
{
    use ChecksLogin, MigrationsHelper, MeasuresPerformance;

    private const SCRIPT_CLASS       = 'xss-auth-fail-script';
    private const STATEFUL_METHODS   = ['POST', 'PUT', 'PATCH'];
    private const FAILURES           = 'xss_failures';

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $class    = class_basename(static::class);
        $location = "{$request->getMethod()} {$request->getPathInfo()}";
        Log::debug("{$class}::handle start", [
            'ip'       => $request->ip(),
            'referrer'  => $request->header('Referer') ?? $request->headers->get('referer') ?? request()->server('HTTP_REFERER') ?? '# UNIDENTIFIED' . " - Previous: " . url()->previous(),
            'route' => $request->route()?->getName() ?? '# UNIDENTIFIED',
            'action_method' => $request->route()?->getActionMethod() ?? '# UNIDENTIFIED',
            'location' => $location,
            'params'   => $request->route()?->parameters() ?? [],
            'bearer_present' => (bool)$request->bearerToken(),
        ]);
        $output = SafeConsoleOutput::make();
        $output->writeln("[{$class}] start {$location}");
        $authStart = microtime(true);
        $user = null;
        try {
            if (
                in_array($request->getMethod(), self::STATEFUL_METHODS, true)
                && !in_array(
                    str_replace('fortify-', '', $request->getPathInfo()),
                    ['/login', '/register', '/forgot-password', '/reset-password'],
                    true
                )
            ) {
                $userOrRedirect = self::_checkLogin(haltRedirect: true);
                if (!($userOrRedirect instanceof User)) {
                    Log::warning("{$class} auth failed", ['uri' => $request->getPathInfo(), 'status' => 401]);
                    $output->writeln("[{$class}] auth failed – halting");
                    session()->increment(self::FAILURES, 1);
                    $this->logExecutionTime($authStart, 'authentication_error');
                    if ($request->expectsJson()) {
                        return response()->json(['error' => 'Not authenticated'], 401);
                    }
                    return redirect()->route('login')->withErrors(['error' => __('auth.unauthenticated')]);
                }
                $user = $userOrRedirect;
            } else if (!in_array(
                str_replace('fortify-', '', $request->getPathInfo()),
                ['/login', '/register', '/forgot-password', '/reset-password'],
                true
            )) {
                $userOrFalse = self::_checkLogin(haltRedirect: true);
                if ($userOrFalse === false) {
                    Log::info("{$class} guest access", ['uri' => $request->getPathInfo()]);
                    $output->writeln("[{$class}] guest access for {$request->getMethod()}");
                    $user = null;
                } else $user = $userOrFalse;
            }
            $this->logExecutionTime($authStart, 'authentication');
            if ($user instanceof User) {
                Log::info("{$class} auth succeeded", ['uri' => $request->getPathInfo()]);
                Log::debug("{$class} auth succeeded", [
                    UsersConstants::COL_USER_ID => $user->id,
                    SettingsConstants::LCL      => $user[UsersConstants::COL_LG],
                    'status'                   => 100
                ]);
                $output->writeln("[{$class}] user {$user->id} validated");
                App::setLocale($user[UsersConstants::COL_LG]);
                Log::debug("{$class} locale set", [SettingsConstants::LCL => $user[UsersConstants::COL_LG]]);
                if ($user[UsersConstants::COL_TP] === PermissionsConstants::SA) {
                    $pending = $this->pendingMigrationsCount();
                    if ($pending > 0) {
                        Log::info("{$class} pending migrations", ['count' => $pending]);
                        return redirect()->route('LaravelUpdater::welcome');
                    }
                }
            }
            $sanitStart = microtime(true);
            $raw = $request->all();
            $count = count($raw, COUNT_RECURSIVE);
            Log::debug("{$class} sanitization start", ['fields' => $count]);
            array_walk_recursive($raw, fn(&$v) => is_string($v) ? $v = strip_tags($v) : null);
            $request->merge($raw);
            Log::debug("{$class} sanitization complete", ['url' => $request->fullUrl()]);
            Log::debug("{$class} sanitization done", [
                'uri' => $request->getPathInfo(),
                'fields' => $count,
                'next'   => $this->searchForNext($request)
            ]);
            session()->forget(self::FAILURES);
            $output->writeln("[{$class}] sanitization complete");
            $this->logExecutionTime($sanitStart, 'sanitization');
            return $next($request);
        } catch (\Throwable $e) {
            Log::error("{$class}::handle error", [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
                'uri'       => $request->getPathInfo(),
                'status'    => 500
            ]);
            $output->writeln("[{$class}] error: {$e->getMessage()}");
            $this->logExecutionTime($authStart, 'handle_error');
            if ($request->expectsJson())
                return response()->json(['error' => "Unexpected error: {$e->getMessage()}"], 500);
            Log::debug("{$class} ingested a throwable. Aborting.");
            abort(500, 'XSS Sanitization failed.');
        }
    }

    private function writeConsole(OutputInterface $out, string $message): void
    {
        app()->runningInConsole()
            ? $out->writeln("<comment> $message </comment>")
            : $out->writeln($message);
    }

    private function pendingMigrationsCount(): int
    {
        $core     = $this->getMigrations();
        $msgTotal = Utility::getMessengerPackagesMigration();
        $executed = $this->getExecutedMigrations();
        return (count($core) + $msgTotal) - count($executed);
    }
}
