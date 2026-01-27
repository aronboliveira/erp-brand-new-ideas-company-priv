<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants, TemplatesConstants};
use App\Traits\{HasAuditFields, UsesUuids, _StringTemplating};
use Illuminate\Database\Eloquent\{Model, Relations\BelongsTo};
use App\Models\{Utility, User};

class GeneratedOfferLetter extends Model
{
        use UsesUuids, HasAuditFields, _StringTemplating;

        protected $table = DatabaseConstants::TABLE_GOL;

        protected $fillable = [
                TemplatesConstants::COL_LG,
                TemplatesConstants::COL_CT,
                DatabaseConstants::COL_TABLE_CREATOR,
        ];

        public function createdBy(): BelongsTo
        {
                return $this->belongsTo(User::class, DatabaseConstants::COL_TABLE_CREATOR);
        }

        public static function replaceVariable(string $content, array $obj): string
        {
                $arrVariable = [
                        '{applicant_name}',
                        '{app_name}',
                        '{job_title}',
                        '{job_type}',
                        '{start_date}',
                        '{workplace_location}',
                        '{days_of_week}',
                        '{salary}',
                        '{salary_type}',
                        '{salary_duration}',
                        '{next_pay_period}',
                        '{offer_expiration_date}',
                ];
                $arrValue = [
                        'applicant_name'       => '-',
                        'app_name'             => '-',
                        'job_title'            => '-',
                        'job_type'             => '-',
                        'start_date'           => '-',
                        'workplace_location'   => '-',
                        'days_of_week'         => '-',
                        'salary'               => '-',
                        'salary_type'          => '-',
                        'salary_duration'      => '-',
                        'next_pay_period'      => '-',
                        'offer_expiration_date' => '-',
                ];
                foreach ($obj as $key => $val)
                        $arrValue[$key] = $val;
                $settings = Utility::settings();
                if (!empty($settings['app_name']))
                        $arrValue['app_name'] = $settings['app_name'];
                elseif (env('APP_NAME') !== null)
                        $arrValue['app_name'] = env('APP_NAME');
                if (isset($settings['default_salary_type']) && $settings['default_salary_type'] !== '')
                        $arrValue['salary_type'] = $settings['default_salary_type'];
                if (isset($settings['default_salary_duration']) && $settings['default_salary_duration'] !== '')
                        $arrValue['salary_duration'] = $settings['default_salary_duration'];
                return str_replace($arrVariable, array_values($arrValue), $content);
        }

        public static function defaultOfferLetter(string $createdBy): void
        {
                foreach (self::OFFER_LETTER_TEMPLATE as $lang => $content) {
                        try {
                                self::create([
                                        TemplatesConstants::COL_LG                     => $lang,
                                        TemplatesConstants::COL_CT                  => $content,
                                        DatabaseConstants::COL_TABLE_CREATOR => $createdBy,
                                ]);
                        } catch (\Throwable $e) {
                                \Illuminate\Support\Facades\Log::error(
                                        __CLASS__ . '::' . __FUNCTION__
                                                . " failed for lang [{$lang}]: {$e->getMessage()}"
                                );
                        }
                }
        }

        public const DEF_OFL_REG = 'defaultOfferLetterRegister';
        public static function defaultOfferLetterRegister($userId): void
        {
                $defaultTemplate = self::OFFER_LETTER_REGISTER_TEMPLATE;
                foreach ($defaultTemplate as $lang => $content)
                        GeneratedOfferLetter::create(
                                [
                                        TemplatesConstants::COL_LG => $lang,
                                        TemplatesConstants::COL_CT => $content,
                                        DatabaseConstants::COL_TABLE_CREATOR => $userId,

                                ]
                        );
        }
}
