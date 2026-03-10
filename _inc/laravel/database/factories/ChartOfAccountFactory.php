<?php

namespace Database\Factories;

use App\Config\Constants\{
    ChartsConstants as CHTC,
    DatabaseConstants as DC,
    UsersConstants as UC
};
use App\Models\{ChartOfAccount, ChartOfAccountType, ChartOfAccountSubType};
use Illuminate\Database\Eloquent\Factories\Factory;

class ChartOfAccountFactory extends Factory
{
    protected $model = ChartOfAccount::class;

    public function definition(): array
    {
        // Ensure a valid ChartOfAccountType exists (reuse or create).
        $type = ChartOfAccountType::first()
            ?? ChartOfAccountType::create([
                CHTC::COL_NM  => 'Default Type',
                CHTC::COL_CD  => 'DEFAULT_TYPE',
                DC::COL_TABLE_CREATOR => DC::DEFAULT_UUID,
            ]);

        // Ensure a valid ChartOfAccountSubType linked to the type.
        $subType = ChartOfAccountSubType::where(CHTC::COL_TP, $type->id)->first()
            ?? ChartOfAccountSubType::create([
                CHTC::COL_NM  => 'Default SubType',
                CHTC::COL_CD  => 'DEFAULT_SUBTYPE',
                CHTC::COL_TP  => $type->id,
                DC::COL_TABLE_CREATOR => DC::DEFAULT_UUID,
            ]);

        return [
            CHTC::COL_NM     => $this->faker->words(3, true),
            CHTC::COL_CD     => $this->faker->unique()->numberBetween(1000, 99999),
            CHTC::COL_TP     => $type->id,
            CHTC::COL_SUBTP  => $subType->id,
            CHTC::COL_ENB    => 1,
            CHTC::CUR_BL     => $this->faker->randomFloat(2, 0, 100000),
            CHTC::INIT_BL    => $this->faker->randomFloat(2, 0, 100000),
            CHTC::EXP_NXT_MN_BL => $this->faker->randomFloat(2, 0, 100000),
            'currency_id'    => 'USD',
            'depth'          => 0,
            'rules'          => [],
            'restrictions'   => [],
            UC::COL_USER_ID  => DC::DEFAULT_UUID,
            DC::COL_TABLE_CREATOR => DC::DEFAULT_UUID,
        ];
    }
}