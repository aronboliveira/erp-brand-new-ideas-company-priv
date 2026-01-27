<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    TemplatesConstants as TC
};
use App\Services\TemplateRequestService;
use App\Traits\{HasAuditFields, UsesUuids, _StringTemplating};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class JoiningLetter extends Model
{
    use HasAuditFields, UsesUuids, _StringTemplating;
    protected $table = DC::TABLE_JL;
    protected $fillable = [
        TC::COL_LG,
        TC::COL_CT,
    ];

    public static function replaceVariable(string $content, array $obj): string
    {
        $arrVariable = [
            '{date}',
            '{app_name}',
            '{employee_name}',
            '{address}',
            '{start_date}',
            '{designation}',
            '{branch}',
            '{start_time}',
            '{end_time}',
            '{total_hours}',
        ];
        $settings   = Utility::settings();
        $arrValue   = [
            AC::COL_TSK_DATE          => date($settings['site_date_format'] . ' '
                . $settings['site_time_format']),
            'app_name'      => $settings['company_name'],
            'employee_name' => '-',
            'address'       => $settings['company_address'],
            'start_date'    => '-',
            'designation'   => '-',
            'branch'        => '-',
            AC::COL_ST_TIME    => '-',
            AC::COL_E_TIME      => '-',
            'total_hours'   => '-',
        ];
        foreach ($obj as $key => $val)
            $arrValue[$key] = $val;
        return str_replace($arrVariable, array_values($arrValue), $content);
    }

    public static function defaultJoiningLetter(?string $userId = null): void
    {
        app(TemplateRequestService::class)->ensureDefaultJoiningLetter(new static(), $userId);
    }

    public const DEF_JG_LT_REG = 'defaultJoiningLetterRegister';
    public static function defaultJoiningLetterRegister(int|string $userId)
    {
        $defaultTemplate = self::DEFAULT_JOINING_LETTER_REGISTER;
        foreach ($defaultTemplate as $lang => $content)
            JoiningLetter::create(
                [
                    TC::COL_LG => $lang,
                    TC::COL_CT => $content,
                    DC::COL_TABLE_CREATOR => $userId,

                ]
            );
    }
}
