@php
if (!function_exists('resolveRouteWithGuard')) {
    function resolveRouteWithGuard(
        string $baseName,
        ?string $lang = null,
        string $viewContext = '',
        string $msgKey = 'route_unavailable',
        array $params = [],
        ?string $fallbackMsg = null
    ): array {
        $resolved = null;
        $url = '#';
        $guardMsg = $fallbackMsg ?? Utility::fetchLinkMessage($lang ?? Utility::fetchUserLang(), $viewContext, $msgKey) ?? 'Route is unavailable. Please contact technical support.';

        try {
            $resolved = Route::has($baseName) ? $baseName : (Route::has(Str::kebab($baseName)) ? Str::kebab($baseName) : null);
        } catch (\Throwable $e) {
            Log::error("Route resolution failed for {$baseName}: " . $e->getMessage());
        }

        if ($resolved && !empty(array_filter($params, fn($p) => $p !== '' && $p !== null))) {
            try {
                $url = route($resolved, $params);
            } catch (\Throwable $e) {
                Log::error("URL generation failed for {$resolved}: " . $e->getMessage());
                $url = '#';
            }
        } elseif ($resolved && empty($params)) {
            try {
                $url = route($resolved);
            } catch (\Throwable $e) {
                Log::error("URL generation failed for {$resolved}: " . $e->getMessage());
                $url = '#';
            }
        }

        return compact('resolved', 'url', 'guardMsg');
    }
}

if (!function_exists('safeDataGet')) {
    function safeDataGet($target, $key, $default = '') {
        $val = data_get($target, $key, $default);
        return $val !== null && $val !== '' ? $val : $default;
    }
}

if (!function_exists('buildElementId')) {
    function buildElementId(string $prefix, ...$parts): string {
        $safe = array_map(fn($p) => ($p === '' || $p === null) ? 'x' : (string)$p, $parts);
        return $prefix . '-' . implode('-', $safe);
    }
}

if (!function_exists('ensureIterable')) {
    function ensureIterable($data): array {
        if ($data instanceof \Illuminate\Support\Collection) return $data->all();
        if (is_array($data)) return $data;
        return [];
    }
}

if (!function_exists('safeProgress')) {
    function safeProgress($task): array {
        try {
            if (is_object($task) && method_exists($task, 'taskProgress'))
                return $task->taskProgress($task) ?? ['percentage' => '0%', 'color' => 'secondary'];
        } catch (\Throwable $e) {
            Log::error('Task progress calculation failed: ' . $e->getMessage());
        }
        return ['percentage' => '0%', 'color' => 'secondary'];
    }
}
@endphp
