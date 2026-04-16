<?php

declare(strict_types=1);

namespace App\Services\Utility;

use App\Config\Constants\{
    DatabaseConstants as DC,
    LangsConstants as LC,
    SettingsConstants as SC,
    UsersConstants as UC,
};
use App\Enums\BrazilState;
use App\Helpers\SafeConsoleOutput;
use App\Models\{Language, Utility};
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\{Collection, Str};
use Illuminate\Support\Facades\{Cache, DB, Log, Schema};
use App\Models\User;

/**
 * LocalizationService — extracted from Utility.php
 *
 * Language lists, locale resolution, Brazilian document/phone
 * generation and validation, and client-facing error messages.
 *
 * @see \App\Models\Utility — delegates to this service
 */
class LocalizationService
{
    // ─────────────────────────────────────────────────────────
    //  Language helpers
    // ─────────────────────────────────────────────────────────

    public static function languages(): Collection
    {
        $output = SafeConsoleOutput::make();
        $tag   = class_basename(self::class) . '::' . __FUNCTION__;
        Log::debug("{$tag} called");
        $output->writeln("## [{$tag}] Loading languages…");
        try {
            if (Utility::$languageSetting === null) {
                Log::debug("{$tag} no cache, building language list");
                $output->writeln("## [{$tag}] Generating language list");
                if (Schema::hasTable(DC::TABLE_LANGS)) {
                    Log::debug("{$tag} languages table exists");
                    $output->writeln("## [{$tag}] Querying DB for languages");
                    $settings = Utility::settings();
                    $disabled = $settings[SC::DSB_LNG] ?? '';
                    if (!empty($disabled)) {
                        $codes = array_filter(explode(',', $disabled));
                        Log::debug("{$tag} excluding codes", ['disabled' => $codes]);
                        $languages = Language::whereNotIn('code', $codes)
                            ->pluck('full_name', 'code');
                    } else {
                        Log::debug("{$tag} no disabled languages, loading all");
                        $languages = Language::pluck('full_name', 'code');
                    }
                    Log::debug("{$tag} DB languages loaded", ['count' => $languages->count()]);
                    $collection = $languages;
                } else {
                    Log::warning("{$tag} languages table missing, using default list");
                    $output->writeln("## [{$tag}] Using default map");
                    $default = self::langList();
                    $collection = collect($default);
                }
                Utility::$languageSetting = $collection;
            } else {
                $count = Utility::$languageSetting instanceof Collection
                    ? Utility::$languageSetting->count()
                    : count(Utility::$languageSetting);
                Log::debug("{$tag} cache hit", ['count' => $count]);
                $output->writeln("## [{$tag}] Cache hit ({$count} entries)");
                if (!Utility::$languageSetting instanceof Collection)
                    Utility::$languageSetting = collect(Utility::$languageSetting);
            }
            if (Utility::$languageSetting->isEmpty()) {
                Log::warning("{$tag} languages list empty, falling back to DEFAULT_LANG");
                $output->writeln("## [{$tag}] Empty list, using DEFAULT_LANG");
                Utility::$languageSetting = collect([
                    DC::DEFAULT_LANG => DC::DEFAULT_LANG_LONG
                ]);
            }
            return Utility::$languageSetting;
        } catch (QueryException $qe) {
            Log::error("{$tag} QueryException", ['message' => $qe->getMessage()]);
            $output->writeln("## [{$tag}] DB error: {$qe->getMessage()}");
            return collect([DC::DEFAULT_LANG => DC::DEFAULT_LANG_LONG]);
        } catch (\Throwable $e) {
            Log::error("{$tag} unexpected error", [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);
            $output->writeln("## [{$tag}] Exception: {$e->getMessage()}");
            return collect([DC::DEFAULT_LANG => DC::DEFAULT_LANG_LONG]);
        }
    }

    public static function flagOfCountry(): array
    {
        return [
            'ar'    => '🇦🇪 ar',
            'zh'    => '🇨🇳 zh',
            'da'    => '🇩🇰 da',
            'de'    => '🇩🇪 de',
            'es'    => '🇪🇸 es',
            'fr'    => '🇫🇷 fr',
            'he'    => '🇮🇱 he',
            'it'    => '🇮🇹 it',
            'ja'    => '🇯🇵 ja',
            'nl'    => '🇳🇱 nl',
            'pl'    => '🇵🇱 pl',
            'ru'    => '🇷🇺 ru',
            'pt'    => '🇵🇹 pt',
            'en'    => '🇮🇳 en',
            'tr'    => '🇹🇷 tr',
            'pt-br' => '🇵🇹 pt-br',
        ];
    }

    public static function langList(): array
    {
        return [
            'ar'    => 'Arabic',
            'zh'    => 'Chinese',
            'da'    => 'Danish',
            'de'    => 'German',
            'en'    => 'English',
            'es'    => 'Spanish',
            'fr'    => 'French',
            'he'    => 'Hebrew',
            'it'    => 'Italian',
            'ja'    => 'Japanese',
            'nl'    => 'Dutch',
            'pl'    => 'Polish',
            'pt'    => 'Portuguese',
            'ru'    => 'Russian',
            'tr'    => 'Turkish',
            'pt-br' => 'Portuguese (Brazil)',
        ];
    }

    public static function fetchUserLang(?User $user = null, ?Request $req = null): ?string
    {
        $lang = DC::DEFAULT_LANG;
        try {
            $locale = $req?->cookie('LANGUAGE');
            $id = $user?->id ?? $req?->user()?->id ?? null;
            if ($id && (!$locale || !array_key_exists($locale, Utility::langList())))
                $locale = Cache::get("user_{$id}_lang");
            if (!$locale || !array_key_exists($locale, Utility::langList())) {
                $user ??= $req?->user() ?? null;
                $locale = $user?->{UC::COL_LG} ?? $locale;
            }
            if (!$locale || !array_key_exists($locale, Utility::langList()))
                config('app.locale');
            if (!$locale || !array_key_exists($locale, Utility::langList()))
                $locale = DC::DEFAULT_LANG;
            $lang = $locale;
        } catch (\Throwable $e) {
            Log::warning(static::class . ' failed to retrieve user language', [
                'exception'    => get_class($e),
                'message'      => $e->getMessage(),
                'default_lang' => $lang,
            ]);
            $lang = DC::DEFAULT_LANG;
        }
        if (!array_key_exists($lang, Utility::langList()))
            $lang = DC::DEFAULT_LANG;
        return $lang;
    }

    public static function fetchLinkMessage(string $lang = DC::DEFAULT_LANG, ?string $set = 'generics', string $key = '', bool $isFailure = true, bool $shouldFallback = true): ?string
    {
        $startMsg = 'Undefined server message. This could mean either a failure or a success. Check with your support team about your request.';
        $resultMsg = $startMsg;
        try {
            $msgs = LC::LINK_MESSAGES;
            $canDefault = $isFailure && is_array(LC::DEFAULT_CLIENT_MESSAGES) && !empty(LC::DEFAULT_CLIENT_MESSAGES['link_not_found']);
            if (!is_array($msgs)) {
                if ($isFailure && $canDefault)
                    return LC::DEFAULT_CLIENT_MESSAGES['link_not_found'];
                throw new \RuntimeException('Link messages are not defined properly.');
            }
            if (!array_key_exists($lang, Utility::langList()))
                $lang = self::fetchUserLang();
            if (!array_key_exists($set, $msgs)) {
                if ($isFailure && $canDefault)
                    return LC::DEFAULT_CLIENT_MESSAGES['link_not_found'];
                throw new \RuntimeException('Set not found in link messages.');
            }
            if (!array_key_exists($lang, $msgs)) {
                if ($isFailure && $canDefault)
                    return LC::DEFAULT_CLIENT_MESSAGES['link_not_found'];
                throw new \RuntimeException('Language not found in link messages.');
            }
            $langMsg = $msgs[$lang] ?? [];
            if (!array_key_exists($key, $langMsg)) {
                if ($isFailure && is_array(LC::DEFAULT_CLIENT_MESSAGES) && !empty(LC::DEFAULT_CLIENT_MESSAGES['link_not_found']))
                    return LC::DEFAULT_CLIENT_MESSAGES['link_not_found'];
                throw new \RuntimeException('Key not found in link messages for the specified language.');
            }
            $resultMsg = $langMsg[$key] ?? ($shouldFallback ? null : $startMsg);
            return $resultMsg;
        } catch (\Throwable) {
            if ($shouldFallback) return null;
            return !$isFailure && !isset($resultMsg) ? $startMsg : 'Something went wrong! Try again later.'; // @phpstan-ignore isset.variable
        }
    }

    public static function displayErrorMessage($msg = null, $lang = DC::DEFAULT_LANG): string
    {
        if (!$msg)
            $msg = Utility::fetchLinkMessage($lang, 'generics', 'route_unavailable') ?? 'Request unavailable';
        $escapedMsg = addslashes(__($msg));
        $uuid = Str::uuid();
        $snippet = <<<HTML
        <script id="{$uuid}-route-alert">
            (function() {
                const message = '{$escapedMsg}';
                if (typeof window.bootstrap !== 'undefined' && window.bootstrap.Toast) {
                    const toast = document.getElementById('loginToast') || document.querySelector('.toast');
                    if (toast) {
                        const body = toast.querySelector('.toast-body');
                        if (body) {
                            const delay = 5000;
                            const bs = new window.bootstrap.Toast(toast, { delay });
                            body.textContent = message;
                            toast.style.display = 'block';
                            bs.show();
                            const handleHidden = function() {
                                body.textContent = '';
                                toast.style.display = 'none';
                                toast.removeEventListener('hidden.bs.toast', handleHidden);
                            };
                            toast.addEventListener('hidden.bs.toast', handleHidden);
                            setTimeout(function() {
                                document.getElementById('{$uuid}')?.remove();
                            }, delay * 1.25);
                            return;
                        }
                    }
                }
                if (typeof window.LaravelToast !== 'undefined' || typeof window.toastr !== 'undefined') {
                    if (window.toastr) {
                        window.toastr.error(message);
                    } else if (window.LaravelToast) {
                        window.LaravelToast.error(message);
                    }
                    setTimeout(function() {
                        document.getElementById('{$uuid}')?.remove();
                    }, 5000);
                    return;
                }
                alert(message);
                document.getElementById('{$uuid}')?.remove();
            })();
        </script>
        HTML;
        return $snippet;
    }

    public static function languageCreate(?string $createdBy = DC::DEFAULT_UUID): void
    {
        foreach (self::langList() as $code => $fullName) {
            $output = SafeConsoleOutput::make();
            $output->writeln("Creating or finding language: {$code} - {$fullName}");
            try {
                Language::firstOrCreate(
                    ['code'      => $code],
                    [
                        'full_name'         => $fullName,
                        DC::COL_TABLE_CREATOR => $createdBy,
                    ]
                );
                Log::debug(
                    __CLASS__ . '::' . __FUNCTION__
                        . ": language [{$code}] newly created or already existed."
                );
            } catch (QueryException $e) {
                Log::error(
                    __CLASS__ . '::' . __FUNCTION__
                        . " DB error creating language [{$code}]: {$e->getMessage()}",
                    ['sql' => $e->getSql(), 'bindings' => $e->getBindings()]
                );
            } catch (\Throwable $e) {
                Log::critical(
                    __CLASS__ . '::' . __FUNCTION__
                        . " unexpected error for language [{$code}]: {$e->getMessage()}"
                );
            }
        }
    }

    // ─────────────────────────────────────────────────────────
    //  Brazilian document/phone generators & validators
    // ─────────────────────────────────────────────────────────

    public static function generateBrazilianPhone($mobile = true, $formatted = true): string
    {
        $areaCodes = BrazilState::DDD;
        $areaCode = $areaCodes[array_rand($areaCodes)];
        if ($mobile) {
            $firstDigit = 9;
            $secondDigit = rand(6, 9);
            $remaining = str_pad((string) rand(0, 9999999), 7, '0', STR_PAD_LEFT);
            $number = $firstDigit . $secondDigit . $remaining;

            if ($formatted) {
                return sprintf(
                    '+55 (%02d) %d%d%d%d%d-%d%d%d%d',
                    $areaCode,
                    $firstDigit,
                    $secondDigit,
                    (int)$remaining[0],
                    (int)$remaining[1],
                    (int)$remaining[2],
                    (int)$remaining[3],
                    (int)$remaining[4],
                    (int)$remaining[5],
                    (int)$remaining[6]
                );
            } else {
                return '55' . $areaCode . $number;
            }
        } else {
            $firstDigit = rand(2, 5);
            $remaining = str_pad((string) rand(0, 9999999), 7, '0', STR_PAD_LEFT);
            $number = $firstDigit . $remaining;

            if ($formatted) {
                return sprintf(
                    '+55 (%02d) %d%d%d%d-%d%d%d%d',
                    $areaCode,
                    $firstDigit,
                    (int)$remaining[0],
                    (int)$remaining[1],
                    (int)$remaining[2],
                    (int)$remaining[3],
                    (int)$remaining[4],
                    (int)$remaining[5],
                    (int)$remaining[6]
                );
            } else {
                return '55' . $areaCode . $number;
            }
        }
        return '+55 00000-0000';
    }

    public static function generateRandomCpf(bool $formatted = true): string
    {
        $cpf = '';
        for ($i = 0; $i < 9; $i++)
            $cpf .= random_int(0, 9);
        $sum = 0;
        $weight = 10;
        for ($i = 0; $i < 9; $i++) {
            $sum += (int) $cpf[$i] * $weight;
            $weight--;
        }
        $remainder = $sum % 11;
        $cpf .= ($remainder < 2) ? '0' : (string) (11 - $remainder);
        $sum = 0;
        $weight = 11;
        for ($i = 0; $i < 10; $i++) {
            $sum += (int) $cpf[$i] * $weight;
            $weight--;
        }
        $remainder = $sum % 11;
        $cpf .= ($remainder < 2) ? '0' : (string) (11 - $remainder);
        if ($formatted)
            return sprintf(
                '%s.%s.%s-%s',
                substr($cpf, 0, 3),
                substr($cpf, 3, 3),
                substr($cpf, 6, 3),
                substr($cpf, 9, 2)
            );
        return $cpf;
    }

    public static function generateRandomCnpj(bool $formatted = true): string
    {
        $cnpj = '';
        for ($i = 0; $i < 8; $i++)
            $cnpj .= random_int(0, 9);
        $cnpj .= '0001';
        $weights1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 12; $i++)
            $sum += (int) $cnpj[$i] * $weights1[$i];
        $remainder = $sum % 11;
        $cnpj .= ($remainder < 2) ? '0' : (string) (11 - $remainder);
        $weights2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 13; $i++)
            $sum += (int) $cnpj[$i] * $weights2[$i];
        $remainder = $sum % 11;
        $cnpj .= ($remainder < 2) ? '0' : (string) (11 - $remainder);
        if ($formatted)
            return sprintf(
                '%s.%s.%s/%s-%s',
                substr($cnpj, 0, 2),
                substr($cnpj, 2, 3),
                substr($cnpj, 5, 3),
                substr($cnpj, 8, 4),
                substr($cnpj, 12, 2)
            );
        return $cnpj;
    }

    public static function isValidCpf(?string $cpf): bool
    {
        if (!$cpf) return false;
        $cpf = preg_replace('/\D/', '', $cpf);
        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf))
            return false;
        $sum = 0;
        for ($i = 0; $i < 9; $i++)
            $sum += (int) $cpf[$i] * (10 - $i);
        $remainder = $sum % 11;
        $digit1 = ($remainder < 2) ? 0 : 11 - $remainder;
        if ($digit1 !== (int) $cpf[9])
            return false;
        $sum = 0;
        for ($i = 0; $i < 10; $i++)
            $sum += (int) $cpf[$i] * (11 - $i);
        $remainder = $sum % 11;
        $digit2 = ($remainder < 2) ? 0 : 11 - $remainder;
        if ($digit2 !== (int) $cpf[10])
            return false;
        return true;
    }

    public static function isValidCnpj(?string $cnpj): bool
    {
        if (!$cnpj) return false;
        $cnpj = preg_replace('/\D/', '', $cnpj);
        if (strlen($cnpj) !== 14 || preg_match('/^(\d)\1{13}$/', $cnpj))
            return false;
        $weights1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $weights2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 12; $i++)
            $sum += (int) $cnpj[$i] * $weights1[$i];
        $remainder = $sum % 11;
        $digit1 = ($remainder < 2) ? 0 : 11 - $remainder;
        if ($digit1 !== (int) $cnpj[12])
            return false;
        $sum = 0;
        for ($i = 0; $i < 13; $i++)
            $sum += (int) $cnpj[$i] * $weights2[$i];
        $remainder = $sum % 11;
        $digit2 = ($remainder < 2) ? 0 : 11 - $remainder;
        if ($digit2 !== (int) $cnpj[13])
            return false;
        return true;
    }
}
