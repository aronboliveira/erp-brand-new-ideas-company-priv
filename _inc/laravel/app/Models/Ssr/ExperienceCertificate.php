<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, TemplatesConstants as TC};
use App\Services\TemplateRequestService;
use App\Traits\{HasAuditFields, UsesUuids, _StringTemplating};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ExperienceCertificate extends Model
{
    use UsesUuids, HasAuditFields, _StringTemplating;
    protected $table = DC::TABLE_EC;
    protected $fillable = [
        TC::COL_LG,
        TC::COL_CT,
    ];

    public static function replaceVariable(string $content, array $obj): string
    {
        $arrVariable = [
            '{app_name}',
            '{date}',
            '{employee_name}',
            '{duration}',
            '{designation}',
            '{payroll}',
        ];
        $arrValue = [
            'app_name'      => '-',
            'date'          => '-',
            'employee_name' => '-',
            'duration'      => '-',
            'designation'   => '-',
            'payroll'       => '-',
        ];
        foreach ($obj as $key => $val)
            if (array_key_exists($key, $arrValue))
                $arrValue[$key] = $val;
        $settings = Utility::settings();
        if (!empty($settings['app_name']))
            $arrValue['app_name'] = $settings['app_name'];
        elseif (env('APP_NAME') !== null)
            $arrValue['app_name'] = env('APP_NAME');
        $dateFormat = 'Y-m-d';
        if (isset($settings['site_date_format']) && $settings['site_date_format'] !== '')
            $dateFormat = $settings['site_date_format'];
        try {
            $arrValue['date'] = now()->format($dateFormat);
        } catch (\Throwable $e) {
            Log::error(
                __CLASS__ . '::' . __FUNCTION__ . " failed formatting date: {$e->getMessage()}"
            );
            $arrValue['date'] = now()->toDateString();
        }
        return str_replace($arrVariable, array_values($arrValue), $content);
    }

    public static function defaultExpCertificate(?string $userId = null): void
    {
        app(TemplateRequestService::class)->ensureDefaultExpCertificate(new static(), $userId);
    }

    public const DEF_EXP_CRT_REG = 'defaultExpCertificateRegister';
    public static function defaultExpCertificateRegister(string $userId): void
    {
        $defaultTemplate = self::DEFAULT_XP_CERTIFICATE_REGISTER;
        foreach ($defaultTemplate as $lang => $content)
            ExperienceCertificate::create(
                [
                    TC::COL_LG => $lang,
                    TC::COL_CT => $content,
                    DC::COL_TABLE_CREATOR => $userId,

                ]
            );
    }
}
