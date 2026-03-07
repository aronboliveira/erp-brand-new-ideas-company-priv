<?php
/**
 * Template Variable Initialization Partial
 * 
 * Include this partial at the beginning of templates to initialize common variables.
 * 
 * Usage in templates:
 *   <?php 
 *   extract(include resource_path('views/partials/templates/init-vars.blade.php'));
 *   // or
 *   include resource_path('views/partials/templates/init-vars.blade.php');
 *   ?>
 * 
 * Sets the following variables with defaults (can be overridden by passing $overrides):
 *   $user        - Current authenticated user
 *   $lang        - User's preferred language
 *   $docLang     - Document language code (e.g., 'en', 'pt-BR')
 *   $vendor      - null
 *   $customer    - null
 *   $settings    - []
 *   $settings_data - []
 *   $customFields - []
 *   $meta_title  - ''
 *   $meta_desc   - ''
 *   $themeCSS    - ''
 *   $color       - '#ffffff'
 *   $font_color  - '#000000'
 *   $img         - ''
 *   $preview     - null
 *   $dir         - ''
 */

declare(strict_types=1);

use App\Helpers\TemplateHelper;
use App\Models\Utility;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

// Allow overrides to be passed via $overrides variable
$overrides = $overrides ?? [];

// Get initialized variables from TemplateHelper
try {
    $vars = TemplateHelper::initializeVars($overrides);
    
    // Extract variables into current scope
    $user = $vars['user'] ?? Auth::user();
    $lang = $vars['lang'] ?? Utility::fetchUserLang(user: $user);
    $docLang = $vars['docLang'] ?? TemplateHelper::getDocLang($lang);
    $vendor = $vars['vendor'] ?? $vendor ?? null;
    $customer = $vars['customer'] ?? $customer ?? null;
    $settings = $vars['settings'] ?? $settings ?? [];
    $settings_data = $vars['settings_data'] ?? $settings_data ?? [];
    $customFields = $vars['customFields'] ?? $customFields ?? [];
    $meta_title = $vars['meta_title'] ?? $meta_title ?? '';
    $meta_desc = $vars['meta_desc'] ?? $meta_desc ?? '';
    $themeCSS = $vars['themeCSS'] ?? $themeCSS ?? '';
    $color = $vars['color'] ?? $color ?? '#ffffff';
    $font_color = $vars['font_color'] ?? $font_color ?? '#000000';
    $img = $vars['img'] ?? $img ?? '';
    $preview = $vars['preview'] ?? $preview ?? null;
    $dir = $vars['dir'] ?? $dir ?? '';
    
} catch (\Throwable $e) {
    Log::error('InitVars Partial Throwable: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__);
    
    // Fallback defaults
    $user = null;
    $lang = null;
    $docLang = 'en';
    $vendor = $vendor ?? null;
    $customer = $customer ?? null;
    $settings = $settings ?? [];
    $settings_data = $settings_data ?? [];
    $customFields = $customFields ?? [];
    $meta_title = $meta_title ?? '';
    $meta_desc = $meta_desc ?? '';
    $themeCSS = $themeCSS ?? '';
    $color = $color ?? '#ffffff';
    $font_color = $font_color ?? '#000000';
    $img = $img ?? '';
    $preview = $preview ?? null;
    $dir = $dir ?? '';
}

// Return vars array for use with extract()
return compact(
    'user', 'lang', 'docLang', 'vendor', 'customer', 'settings', 
    'settings_data', 'customFields', 'meta_title', 'meta_desc', 
    'themeCSS', 'color', 'font_color', 'img', 'preview', 'dir'
);
