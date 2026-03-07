<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Config\Constants\DatabaseConstants;
use App\Config\Constants\SettingsConstants;
use App\Models\Utility;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Helper class for PDF/print templates.
 * Provides reusable methods for document language, error HTML, and variable initialization.
 */
class TemplateHelper
{
    /**
     * Get safe HTML-escaped string.
     * Uses Laravel's built-in e() if available, otherwise htmlspecialchars.
     *
     * @param mixed $value
     * @return string
     */
    public static function escape(mixed $value): string
    {
        if (function_exists('e')) {
            return e($value);
        }
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Get the document language code (e.g., 'en', 'pt-BR').
     *
     * @param mixed $userLang Optional user language preference
     * @return string
     */
    public static function getDocLang(mixed $userLang = null): string
    {
        try {
            if (is_string($userLang) && !empty($userLang)) {
                return str_replace('_', '-', $userLang);
            }
            $locale = app()->getLocale();
            if (is_string($locale) && !empty($locale)) {
                return str_replace('_', '-', $locale);
            }
        } catch (\Throwable $e) {
            Log::error('DocLang Throwable: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
        }
        return DatabaseConstants::DEFAULT_LANG;
    }

    /**
     * Get the text direction ('rtl' or '') based on settings.
     *
     * @param array|object $settingsData
     * @return string
     */
    public static function getDirection(array|object $settingsData): string
    {
        try {
            return (data_get($settingsData, SettingsConstants::RTL) === 'on') ? 'rtl' : '';
        } catch (\Throwable $e) {
            Log::error('RTL Throwable: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
            return '';
        }
    }

    /**
     * Get settings data for a creator/user.
     *
     * @param int|string|null $creatorId
     * @return array
     */
    public static function getSettingsData(int|string|null $creatorId): array
    {
        try {
            return Utility::settingsById($creatorId) ?? [];
        } catch (\Throwable $e) {
            Log::error('settingsById Throwable: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
            return [];
        }
    }

    /**
     * Get creator ID from an entity (bill, invoice, purchase, etc.).
     *
     * @param array|object|null $entity
     * @return int|string|null
     */
    public static function getCreatorId(array|object|null $entity): int|string|null
    {
        if (empty($entity)) {
            return null;
        }
        
        if (is_array($entity) && Utility::isFilled($entity)) {
            return $entity[DatabaseConstants::COL_TABLE_CREATOR] ?? null;
        }
        
        return data_get($entity, DatabaseConstants::COL_TABLE_CREATOR) 
            ?? data_get($entity, 'created_by');
    }

    /**
     * Generate "no data available" error HTML for templates.
     * Returns a complete HTML document with the error message.
     *
     * @param string $entityType Type of entity (e.g., 'bill', 'purchase', 'invoice', 'proposal', 'pos')
     * @param string|null $docLang Document language code
     * @return string Complete HTML document with error message
     */
    public static function getNoDataHtml(string $entityType, ?string $docLang = null): string
    {
        $lang = self::escape($docLang ?? self::getDocLang());
        $message = self::escape(__("No {$entityType} data available."));
        
        return <<<HTML
<!DOCTYPE html>
<html lang="{$lang}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="alert alert-warning">{$message}</div>
</body>
</html>
HTML;
    }

    /**
     * Generate simple "no data available" div HTML (without full document wrapper).
     *
     * @param string $entityType Type of entity
     * @return string Div HTML with error message  
     */
    public static function getNoDataDiv(string $entityType): string
    {
        $message = self::escape(__("No {$entityType} data available."));
        return '<div class="alert alert-warning">' . $message . '</div>';
    }

    /**
     * Initialize common template variables with defaults.
     *
     * @param array $overrides Optional array of values to override defaults
     * @return array Array of initialized variables
     */
    public static function initializeVars(array $overrides = []): array
    {
        $user = Auth::user();
        $lang = Utility::fetchUserLang(user: $user);
        
        $defaults = [
            'user' => $user,
            'lang' => $lang,
            'docLang' => self::getDocLang($lang),
            'vendor' => null,
            'customer' => null,
            'settings' => [],
            'settings_data' => [],
            'customFields' => [],
            'meta_title' => '',
            'meta_desc' => '',
            'themeCSS' => '',
            'color' => '#ffffff',
            'font_color' => '#000000',
            'img' => '',
            'preview' => null,
            'dir' => '',
        ];
        
        return array_merge($defaults, $overrides);
    }

    /**
     * Get user language from the current authenticated user.
     *
     * @return string|null
     */
    public static function getUserLang(): ?string
    {
        try {
            $user = Auth::user();
            return Utility::fetchUserLang(user: $user);
        } catch (\Throwable $e) {
            Log::error('getUserLang Throwable: ' . get_class($e) . ' | "' . $e->getMessage() . '" | file=' . __FILE__ . ' | line=' . __LINE__);
            return null;
        }
    }
}
