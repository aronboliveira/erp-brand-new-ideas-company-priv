<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants as DC, TemplatesConstants as TC};
use App\Services\TemplateRequestService;
use App\Traits\{HasAuditFields, UsesUuids, _StringTemplating};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Noc extends Model
{
    use UsesUUids, HasAuditFields, _StringTemplating;

    protected $table = DC::TABLE_NOC;

    protected $fillable = [
        TC::COL_LG,
        TC::COL_CT,
    ];

    protected $attributes = [
        TC::COL_LG => DC::DEFAULT_LANG,
    ];

    public static function replaceVariable(string $content, array $obj): string
    {
        $settings = Utility::settings();
        $defaults = [
            'date'          => Carbon::now()->format('Y-m-d'),
            'employee_name' => '',
            'designation'   => '',
            'app_name'      => $settings['company_name'] ?? env('APP_NAME'),
        ];
        $values = array_merge($defaults, $obj);
        $placeholders = [
            '{date}',
            '{employee_name}',
            '{designation}',
            '{app_name}',
        ];
        $replacements = [
            $values['date'],
            $values['employee_name'],
            $values['designation'],
            $values['app_name'],
        ];
        return str_replace($placeholders, $replacements, $content);
    }

    public static function defaultNocCertificate(?string $userId = null): void
    {
        app(TemplateRequestService::class)->ensureDefaultNocCertificate(new static(), $userId); // @phpstan-ignore new.static, argument.type
    }


    public const DEF_NOC_CRT_REG = 'defaultNocCertificateRegister';
    public static function defaultNocCertificateRegister($user_id): void
    {
        $defaultTemplate = self::DEFAULT_NOC_CERTIFICATE_REGISTER;
        foreach ($defaultTemplate as $lang => $content)
            Noc::create(
                [
                    TC::COL_LG => $lang,
                    TC::COL_CT => $content,
                    DC::COL_TABLE_CREATOR => $user_id,
                ]
            );
    }
}
