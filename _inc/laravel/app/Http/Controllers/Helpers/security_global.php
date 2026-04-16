<?php

/**
 * Global aliases for namespaced helper functions in security.php
 * so that Blade templates (compiled in the global scope) can call
 * them without a fully-qualified namespace prefix.
 */

if (!function_exists('purify_html')) {
    function purify_html(?string $dirty): string
    {
        return \App\Http\Controllers\Helpers\purify_html($dirty);
    }
}
