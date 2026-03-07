<?php
/**
 * Document Language Setup Partial
 * 
 * Include this partial at the beginning of templates to set up document language.
 * 
 * Usage in templates:
 *   <?php include resource_path('views/partials/templates/doc-lang.blade.php'); ?>
 *   
 * Or with view:
 *   <?php extract(view('partials.templates.doc-lang', ['userLang' => $lang ?? null])->getData()); ?>
 * 
 * Sets: $docLang (string) - document language code (e.g., 'en', 'pt-BR')
 */

declare(strict_types=1);

use App\Config\Constants\DatabaseConstants;
use App\Helpers\TemplateHelper;
use Illuminate\Support\Facades\Log;

// Get user language if passed, otherwise use TemplateHelper
$userLang = $userLang ?? $lang ?? null;

try {
    $docLang = TemplateHelper::getDocLang($userLang);
} catch (\Throwable $e) {
    Log::error('DocLang Partial Throwable: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__);
    $docLang = DatabaseConstants::DEFAULT_LANG ?? 'en';
}
