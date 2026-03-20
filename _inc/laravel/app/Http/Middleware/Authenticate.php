<?php

namespace App\Http\Middleware;
use App\Helpers\SafeConsoleOutput;

use Closure;
use App\Config\Constants\{SettingsConstants, UsersConstants};
use Illuminate\Auth\{
    Middleware\Authenticate as Middleware,
    AuthenticationException,
    Access\AuthorizationException
};
use Illuminate\Http\{Request};
use Illuminate\Support\Facades\Log;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpFoundation\Response;

final class Authenticate extends Middleware
{
    use MeasuresPerformance;
    protected const PERF_ENABLED = true;
    private const REDIRECT_ROUTE = 'login';

    /**
     * Ensure the user is authenticated for one of the given guards.
     *
     * @param  Request   $request
     * @param  string[]  $guards
     * @return void
     *
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    protected function _authenticate($request, $guards)
    {
        $start = microtime(true);
        $method = $request->getMethod();
        $uri    = $request->getRequestUri();
        Log::debug(__METHOD__ . ' start', ['method' => $method, 'uri' => $uri, 'guards' => $guards]);
        if (empty($guards)) {
            $guards = [null];
            Log::debug(__METHOD__ . ' - no guards provided, defaulting to [null]');
        }
        foreach ($guards as $guard) {
            $checker = $this->auth->guard($guard);
            if ($checker->check()) {
                $user = $checker->user();
                Log::debug(__METHOD__ . ' guard authenticated', ['guard' => $guard, 'user_id' => $user?->id]);
                if (!$user) {
                    Log::warning(__METHOD__ . ' user instance missing after guard check', ['guard' => $guard]);
                    $this->logExecutionTime($start, __METHOD__ . ' user_missing');
                    throw new AuthenticationException('Authenticated guard did not yield a user instance.', $guards, $this->redirectTo($request));
                }
                if (!empty($user[UsersConstants::COL_IB])) {
                    Log::warning(__METHOD__ . ' user is banned', ['user_id' => $user->id, 'guard' => $guard]);
                    $this->logExecutionTime($start, __METHOD__ . ' user_banned');
                    throw new AuthorizationException('User is banned.');
                }
                if (isset($user[UsersConstants::COL_IA]) && !$user[UsersConstants::COL_IA]) {
                    Log::warning(__METHOD__ . ' user is not active', ['user_id' => $user->id, 'guard' => $guard]);
                    $this->logExecutionTime($start, __METHOD__ . ' user_not_active');
                    throw new AuthorizationException('User is not active.');
                }
                Log::debug(__METHOD__ . ' success', ['guard' => $guard, 'user_id' => $user->id]);
                $this->auth->shouldUse($guard);
                $this->logExecutionTime($start, __METHOD__ . ' success');
                return;
            }
            Log::notice(__METHOD__ . ' guard did not authenticate', ['guard' => $guard]);
        }
        Log::notice(__METHOD__ . ' authentication failed for all guards', ['guards' => $guards, 'method' => $method, 'uri' => $uri]);
        $this->logExecutionTime($start, __METHOD__ . ' guards_all_failed');
        throw new AuthenticationException('Unauthenticated.', $guards, $this->redirectTo($request));
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure                  $next
     * @param  string[]                  ...$guards
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, $next, ...$guards)
    {
        $start = microtime(true);
        $output = SafeConsoleOutput::make();
        $base = class_basename(static::class);
        $sessionId = session()->getId();
        $ctx = [
            'ip'        => $request->ip(),
            'referrer'  => $request->header('Referer') ?? $request->headers->get('referer') ?? request()->server('HTTP_REFERER') ?? '# UNIDENTIFIED' . " - Previous: " . url()->previous(),
            'method'    => $request->getMethod(),
            'full_path' => $request->fullUrl(),
            'route' => $request->route()?->getName() ?? '# UNIDENTIFIED',
            'action_method' => $request->route()?->getActionMethod() ?? '# UNIDENTIFIED',
            'params'    => $request->route()?->parameters() ?? [],
            'session_id'   => $sessionId,
        ];
        Log::debug("{$base}::" . __FUNCTION__ . " start", $ctx);
        $msg = "[Auth] {$base} Starting authentication for {$ctx['method']} {$request->getRequestUri()}";
        app()->runningInConsole() ? $output->writeln("<question> {$msg} </question>") : $output->writeln($msg);
        try {
            $msg = "[Auth] {$base} Attempting to authenticate…";
            app()->runningInConsole() ? $output->writeln("<question> {$msg} </question>") : $output->writeln($msg);
            $this->_authenticate($request, $guards);
            $user = $request->user();
            Log::info("{$base}::" . __FUNCTION__ . " user_authenticated");
            Log::debug("{$base}::" . __FUNCTION__ . " user_authenticated", [
                'referrer'  => $request->header('Referer') ?? $request->headers->get('referer') ?? request()->server('HTTP_REFERER') ?? '# UNIDENTIFIED' . " - Previous: " . url()->previous(),
                'route' => $request->route()?->getName() ?? '# UNIDENTIFIED',
                'action_method' => $request->route()?->getActionMethod() ?? '# UNIDENTIFIED',
                'user_id' => $user?->id ?? 'guest',
                'user_email' => $user?->email ?? 'guest',
                'user_name' => $user?->name ?? 'guest',
                'guards' => $guards,
                'status' => '100',
                'next'   => $this->searchForNext($request),
            ]);
            $msg = "[Auth] {$base} Authentication successful for user ID {$user?->id}";
            app()->runningInConsole() ? $output->writeln("<info> {$msg} </info>") : $output->writeln($msg);
            $this->logExecutionTime($start, __METHOD__ . '::success');
        } catch (AuthenticationException $e) {
            Log::notice(__METHOD__ . ' AuthenticationException', ['message' => $e->getMessage(), 'guards' => $guards, 'uri' => $request->getRequestUri()]);
            $this->logExecutionTime($start, __METHOD__ . '::AuthenticationException');
            session()->flash('error', 'Authentication required.');
            return redirect()->route(self::REDIRECT_ROUTE)->with('error', __('Authentication error occurred.'));
        } catch (AuthorizationException $e) {
            Log::warning(__METHOD__ . ' AuthorizationException', ['message' => $e->getMessage(), 'user_id' => $request->user()?->id]);
            $this->logExecutionTime($start, __METHOD__ . '::AuthorizationException');
            app()->runningInConsole() ? $output->writeln("<error> [Auth] {$base} Unauthorized </error>") : $output->writeln("## AUTH ERROR: Unauthorized");
            abort(Response::HTTP_FORBIDDEN, 'You do not have permission.');
        } catch (TokenMismatchException $e) {
            Log::warning(__METHOD__ . ' TokenMismatchException', ['message' => $e->getMessage(), 'uri' => $request->getRequestUri()]);
            $this->logExecutionTime($start, __METHOD__ . '::TokenMismatchException');
            app()->runningInConsole() ? $output->writeln("<error> [Auth] {$base} Session expired </error>") : $output->writeln("## AUTH ERROR: Session expired");
            return redirect()->route(self::REDIRECT_ROUTE)->with('error', __('Session expired, please try again.'));
        }
        # PULL REQUEST START — Remoção do catch genérico \Throwable que mascarava exceções downstream como falso erro 500
        // catch (\Throwable $e) {
        //     $errCtx = [
        //         'exception' => get_class($e),
        //         'message' => $e->getMessage(),
        //         'method' => $request->getMethod(),
        //     ];
        //     Log::error(__METHOD__ . ' UnexpectedException', $errCtx);
        //     Log::channel(SettingsConstants::ERR_TRACE)->debug(__METHOD__ . ' UnexpectedException', array_merge(
        //         $errCtx,
        //         ['trace' => $e->getTraceAsString()]
        //     ));
        //     $this->logExecutionTime($start, __METHOD__ . '::UnexpectedException');
        //     $msg = "[Auth] {$base} Unexpected error: {$e->getMessage()}";
        //     app()->runningInConsole() ? $output->writeln("<error> {$msg} </error>") : $output->writeln("## AUTH ERROR: {$msg}");
        //     abort(Response::HTTP_INTERNAL_SERVER_ERROR, 'Internal server error.');
        // }
        # PULL REQUEST END
        return $next($request);
    }

    /**
     * Determine where to redirect unauthenticated users.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (!$request->expectsJson()) return route(self::REDIRECT_ROUTE);
        return null;
    }
}
