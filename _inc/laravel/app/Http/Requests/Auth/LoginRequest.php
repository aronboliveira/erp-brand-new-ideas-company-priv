<?php

namespace App\Http\Requests\Auth;

use App\Config\Constants\UsersConstants;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\{
    Auth,
    Hash,
    Log,
    RateLimiter,
    Session
};
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

final class LoginRequest extends FormRequest
{
    private const EMAIL_MAX_LENGTH   = 255;
    private const PASSWORD_MIN_LENGTH = 8;
    private const PASSWORD_MAX_LENGTH = 128;
    private const RATE_LIMIT_ATTEMPTS = 5;

    /**
     * Ensure request is over HTTPS.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        try {
            $host     = $this->getHost();
            $isLocal  = in_array($host, ['localhost', '127.0.0.1'], true);
            $isSecure = $this->secure();
            $context = [
                'ip'          => $this->ip(),
                'uri'         => $this->getUri(),
                'host'        => $host,
                'secure'      => $isSecure,
                'local'       => $isLocal,
                'environment' => app()->environment(),
            ];
            if ($isSecure || $isLocal) {
                Log::info(sprintf('%s::%s authorized request', __CLASS__, __FUNCTION__), $context);
                return true;
            }
            Log::warning(sprintf('%s::%s insecure request blocked', __CLASS__, __FUNCTION__), $context);
            return false;
        } catch (Throwable $e) {
            Log::error(sprintf('%s::%s failed to authorize request', __CLASS__, __FUNCTION__), [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'code'    => $e->getCode()
            ]);
            return false;
        }
    }

    /**
     * Define validation rules.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        try {
            return [
                'email'    => ['required', 'string', 'email', 'max:' . self::EMAIL_MAX_LENGTH],
                'password' => ['required', 'string', 'min:' . self::PASSWORD_MIN_LENGTH, 'max:' . self::PASSWORD_MAX_LENGTH]
            ];
        } catch (Throwable $e) {
            Log::notice('LoginRequest::rules threw an exception', [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function validateResolved()
    {
        try {
            parent::validateResolved();
        } catch (Throwable $e) {
            Log::notice('LoginRequest::validateResolved caught', [
                'exception' => get_class($e),
                'message'   => $e->getMessage(),
            ]);
            throw $e;
        }
    }


    /**
     * Attempt authentication and secure session.
     *
     * @return void
     * @throws ValidationException
     */
    public function authenticate(?array $meta = [], ?User $user = null): void
    {
        try {
            $email = $this->input('email');
            $pw = $this->input('password');
            Log::debug(__CLASS__ . '::' . __FUNCTION__ . ' invoked', [
                'email' => $email,
                ...$meta
            ]);
            $this->ensureIsNotRateLimited();
            $email = $this->input('email');
            $password = $this->input('password');
            $remember = $this->boolean('remember');
            if (!($user instanceof User)) {
                try {
                    $user = User::where(UsersConstants::COL_EM, $email)
                        ->orWhere(UsersConstants::COL_NM, $email)
                        ->first();
                } catch (QueryException $e) {
                    Log::critical(__CLASS__ . '::' . __FUNCTION__ . ' failed to retrieve user due to a database error', [
                        'email'   => $email,
                        'message' => $e->getMessage(),
                        ...$meta
                    ]);
                } catch (Throwable $e) {
                    Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed to retrieve user due to an unexpected error', [
                        'email'   => $email,
                        'message' => $e->getMessage(),
                        ...$meta
                    ]);
                }
            }
            if (!$user) {
                Log::notice('' . __CLASS__ . '::' . __FUNCTION__ . ' user not found', [
                    'email' => $email,
                    ...$meta
                ]);
                throw ValidationException::withMessages([
                    'email' => trans('auth.failed'),
                ]);
            }
            if (strlen($email) > self::EMAIL_MAX_LENGTH) {
                Log::notice('' . __CLASS__ . '::' . __FUNCTION__ . ' email too long', [
                    'email' => $email,
                    ...$meta
                ]);
                throw ValidationException::withMessages([
                    'email' => trans('auth.failed'),
                ]);
            }
            if ($user[UsersConstants::COL_IB] === 1) {
                Log::notice('' . __CLASS__ . '::' . __FUNCTION__ . ' user is banned', [
                    'email' => $email,
                    'user_id' => $user['id'] ?? null,
                    ...$meta
                ]);
                throw ValidationException::withMessages([
                    'email' => trans('auth.banned'),
                ]);
            }
            if (empty($user[UsersConstants::COL_IA]) || $user[UsersConstants::COL_IA] === 0) {
                Log::notice('' . __CLASS__ . '::' . __FUNCTION__ . ' user account inactive', [
                    'email' => $email,
                    'user_id' => $user['id'] ?? null,
                    ...$meta
                ]);
                throw ValidationException::withMessages([
                    'email' => trans('auth.inactive'),
                ]);
            }
            if (strlen($pw) > self::PASSWORD_MAX_LENGTH) {
                Log::notice('' . __CLASS__ . '::' . __FUNCTION__ . ' password too long', [
                    'email' => $email,
                    'password_length' => strlen($pw),
                    ...$meta
                ]);
                throw ValidationException::withMessages([
                    'email' => trans('auth.password'),
                ]);
            }
            if (strlen($pw) < self::PASSWORD_MIN_LENGTH) {
                Log::notice('' . __CLASS__ . '::' . __FUNCTION__ . ' password too short', [
                    'email' => $email,
                    'password_length' => strlen($pw),
                    ...$meta
                ]);
                throw ValidationException::withMessages([
                    'email' => trans('auth.password')
                ]);
            }
            if (!Hash::check($password, $user[UsersConstants::COL_PW])) {
                Log::notice('' . __CLASS__ . '::' . __FUNCTION__ . ' password verification failed', [
                    'email' => $email,
                    'user_id' => $user['id'] ?? null,
                    'password_verified' => false,
                    ...$meta
                ]);
                throw ValidationException::withMessages([
                    'email' => trans('auth.password'),
                ]);
            }
            Auth::login($user, $remember);
            Session::regenerate();
            RateLimiter::clear($this->throttleKey());
            Log::info(__CLASS__ . '::' . __FUNCTION__ . ' successful', ['email' => $this->input('email'), ...$meta]);
        } catch (Throwable $e) {
            RateLimiter::hit($this->throttleKey());
            Log::error(__CLASS__ . '::' . __FUNCTION__ . ' failed', ['message' => $e->getMessage(), ...$meta]);
            throw $e instanceof ValidationException
                ? $e
                : ValidationException::withMessages(['email' => trans('auth.unexpected')]);
        }
    }

    /**
     * Ensure the request is not rate limited.
     *
     * @return void
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), self::RATE_LIMIT_ATTEMPTS))
            return;
        event(new Lockout($this));
        $seconds = RateLimiter::availableIn($this->throttleKey());
        Log::warning(__CLASS__ . '::ensureIsNotRateLimited throttled', [
            'email'   => $this->input('email'),
            'seconds' => $seconds
        ]);
        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ])
        ]);
    }

    /**
     * Generate rate limiter key.
     *
     * @return string
     */
    public function throttleKey(): string
    {
        return Str::lower($this->input('email')) . '|' . $this->ip();
    }

    /**
     * Trim and normalize inputs before validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email'    => Str::lower(trim($this->input('email'))),
            'password' => trim($this->input('password'))
        ]);
    }
}
