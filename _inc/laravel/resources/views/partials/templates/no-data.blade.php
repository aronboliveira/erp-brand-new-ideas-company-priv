<?php
/**
 * No Data Available Error Partial
 * 
 * Include this partial to output the "no data available" error HTML.
 * 
 * Usage in templates:
 *   if (empty($bill)) {
 *       $entityType = 'bill';
 *       include resource_path('views/partials/templates/no-data.blade.php');
 *       return;
 *   }
 * 
 * Or directly via TemplateHelper:
 *   echo App\Helpers\TemplateHelper::getNoDataHtml('bill', $docLang);
 * 
 * Required: $entityType (string) - Type of entity (e.g., 'bill', 'invoice', 'purchase', 'proposal', 'pos')
 * Optional: $docLang (string) - Document language code
 */

declare(strict_types=1);

use App\Helpers\TemplateHelper;

$entityType = $entityType ?? 'document';
$docLang = $docLang ?? null;

echo TemplateHelper::getNoDataHtml($entityType, $docLang);
