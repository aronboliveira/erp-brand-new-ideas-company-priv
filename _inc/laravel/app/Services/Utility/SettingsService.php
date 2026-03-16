<?php

declare(strict_types=1);

namespace App\Services\Utility;

use App\Config\Constants\{
    DatabaseConstants as DC,
    PermissionsConstants as PMC,
    SettingsConstants as SC,
    UsersConstants as UC,
};
use App\Models\User;
use Illuminate\Support\Facades\{Auth, DB, Log};

/**
 * SettingsService — extracted from Utility.php
 *
 * Handles all settings-related DB queries: global settings,
 * user-scoped settings, payment settings, theme, SEO, GDPR,
 * cookie settings, and logo retrieval.
 *
 * @see \App\Models\Utility — delegates to this service
 */
class SettingsService
{
    /**
     * Fetch global settings (created_by = DEFAULT_UUID) from DB.
     * Raw DB query only — caching is handled by Utility.
     */
    public static function fetchGlobalSettings(): array
    {
        return DB::table(DC::TABLE_SETTINGS)
            ->where(DC::COL_TABLE_CREATOR, DC::DEFAULT_UUID)
            ->pluck('value', 'name')
            ->toArray();
    }

    /**
     * Fetch settings for a specific user/creator from DB.
     * Raw DB query only — caching is handled by Utility.
     */
    public static function fetchSettingsForUser(string|int $id): array
    {
        return DB::table(DC::TABLE_SETTINGS)
            ->where(DC::COL_TABLE_CREATOR, $id)
            ->pluck('value', 'name')
            ->toArray();
    }

    /**
     * Retrieve a single company setting value by key.
     */
    public static function getCompanyData(string|int $companyId, string $key): string
    {
        $row = DB::table(DC::TABLE_SETTINGS)
            ->where(UC::COL_USER_ID, $companyId)
            ->where('name', $key)
            ->first();
        return $row->value ?? '';
    }

    /**
     * Fetch admin payment settings (role-aware).
     */
    public static function getAdminPaymentSettings(?User $user): array
    {
        try {
            $query = DB::table('admin_payment_settings');
            if (Auth::check())
                $query->where(
                    DC::COL_TABLE_CREATOR,
                    $user?->{UC::COL_TP} === PMC::SA ? $user->id : DC::DEFAULT_UUID
                );
            $rows = $query->get();
            $settings = [];
            foreach ($rows as $row)
                $settings[$row->name] = $row->value;
            return $settings;
        } catch (\Throwable $e) {
            Log::error(self::class . '::' . __FUNCTION__ . " failed: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Fetch company payment settings for a specific user.
     */
    public static function getCompanyPaymentSettings(string|int $userId): array
    {
        $rows = DB::table('company_payment_settings')
            ->where(DC::COL_TABLE_CREATOR, $userId)
            ->get();
        $settings = [];
        foreach ($rows as $row)
            $settings[$row->name] = $row->value;
        return $settings;
    }

    /**
     * Fetch company payment settings for the current user's creator.
     */
    public static function getCompanyPaymentForUser(?User $user): array
    {
        $query = DB::table('company_payment_settings');
        if (Auth::check())
            $query->where(DC::COL_TABLE_CREATOR, $user?->creatorId());
        $rows = $query->get();
        $settings = [];
        foreach ($rows as $row)
            $settings[$row->name] = $row->value;
        return $settings;
    }

    /**
     * Fetch theme settings (dark mode, background, color) for a user.
     * Falls back to default settings if the user has none.
     */
    public static function getThemeSettings(?User $user): array
    {
        if (Auth::check()) {
            $rows = DB::table(DC::TABLE_SETTINGS)
                ->where(UC::COL_USER_ID, $user?->creatorId())
                ->get();
            if ($rows->isEmpty())
                $rows = DB::table(DC::TABLE_SETTINGS)
                    ->where(UC::COL_USER_ID, DC::DEFAULT_UUID)
                    ->get();
        } else {
            $rows = DB::table(DC::TABLE_SETTINGS)
                ->where(UC::COL_USER_ID, DC::DEFAULT_UUID)
                ->get();
        }
        $defaults = [
            SC::CST_DRK => 'off',
            SC::CST_BG  => 'on',
            SC::CLR     => '',
        ];
        foreach ($rows as $row)
            $defaults[$row->name] = $row->value;
        return $defaults;
    }

    /**
     * Fetch color settings for a user (role-aware).
     */
    public static function getColorSettings(User $user): array
    {
        $default = [SC::CST_DRK => 'off'];
        $role    = Auth::user()[UC::COL_TP];
        $userId  = $user->id;
        $creator = $user->creatorId();
        $qb = DB::table(DC::TABLE_SETTINGS)->select('name', 'value');
        if (in_array($role, [PMC::SA, PMC::ADM, PMC::CPN], true))
            $rows = $qb
                ->where('user_id', $userId)
                ->orWhere(DC::COL_TABLE_CREATOR, $creator)
                ->get();
        else
            $rows = $qb
                ->where('user_id', $userId)
                ->get();
        $fetched = $rows->pluck('value', 'name')->toArray();
        $colorSettings = $default + $fetched;
        if (empty($colorSettings[SC::CST_DRK]))
            $colorSettings[SC::CST_DRK] = 'off';
        return $colorSettings;
    }

    /**
     * Fetch SEO-related settings (meta title, description, image).
     */
    public static function getSeoSettings(): array
    {
        $rows = DB::table(DC::TABLE_SETTINGS)
            ->whereIn('name', [SC::MT_TTL_K, SC::MT_DSC_K, SC::MT_IMG_K])
            ->get();
        $settings = [];
        foreach ($rows as $row)
            $settings[$row->name] = $row->value;
        return $settings;
    }

    /**
     * Fetch the superadmin logo based on dark-mode preference.
     */
    public static function getSuperadminLogo(): string
    {
        $settings = DB::table(DC::TABLE_SETTINGS)
            ->where(UC::COL_USER_ID, Auth::user()->id)
            ->pluck('value', 'name')
            ->toArray();
        $mode = $settings[SC::CST_DRK] ?? 'off';
        if ($mode === 'on')
            return SC::CPN_LG_LT_DEF;
        return SC::CPN_LG_DK_DEF;
    }

    /**
     * Fetch GDPR/cookie consent settings.
     */
    public static function getGdprSettings(): array
    {
        $rows = DB::table(DC::TABLE_SETTINGS)
            ->where(UC::COL_USER_ID, DC::DEFAULT_UUID)
            ->get();
        $defaults = [
            'gdpr_cookie' => '',
            'cookie_text' => '',
        ];
        foreach ($rows as $row)
            $defaults[$row->name] = $row->value;
        return $defaults;
    }

    /**
     * Fetch detailed cookie configuration settings.
     */
    public static function getCookieSettings(): array
    {
        $rows = DB::table(DC::TABLE_SETTINGS)
            ->whereIn('name', [
                'enable_cookie',
                'cookie_logging',
                'cookie_title',
                'cookie_description',
                'necessary_cookies',
                'strictly_cookie_title',
                'strictly_cookie_description',
                'more_information_description',
                'contactus_url'
            ])->get();
        $defaults = [
            'enable_cookie'                => 'off',
            'necessary_cookies'            => 'on',
            'cookie_logging'               => 'on',
            'cookie_title'                 => '',
            'cookie_description'           => '',
            'strictly_cookie_title'        => '',
            'strictly_cookie_description'  => '',
            'more_information_description' => '',
            'contactus_url'                => '#',
        ];
        foreach ($rows as $row)
            $defaults[$row->name] = $row->value;
        return $defaults;
    }

    /**
     * Fetch language-related settings.
     */
    public static function getLangSettings(): array
    {
        $rows = DB::table(DC::TABLE_SETTINGS)
            ->where(UC::COL_USER_ID, DC::DEFAULT_UUID)
            ->get();
        $settings = [];
        foreach ($rows as $row)
            $settings[$row->name] = $row->value;
        return $settings;
    }
}
