<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use UsesUuids;

    private const COL_CREATED_BY = 'created_by';
    private const COL_END_DATE  = 'end_date';
    private const COL_INCOME    = 'income_data';
    private const COL_NAME      = 'name';
    private const COL_PERIOD    = 'period';
    private const COL_START_DATE = 'start_date';

    protected $fillable = [
        self::COL_NAME,
        self::COL_START_DATE,
        self::COL_END_DATE,
        self::COL_PERIOD,
        self::COL_CREATED_BY,
    ];

    public static $period = [
        'monthly'      => 'Monthly',
        'quarterly'    => 'Quarterly',
        'half-yearly'  => 'Half Yearly',
        'yearly'       => 'Yearly',
    ];

    public function getAvailabilityDate(): string
    {
        $startDate = '';
        $endDate   = '';
        $dateOutput = '';
        $dateFormat = 'M-Y';
        if (!empty($this->start_date)) {
            $startDate = date($dateFormat, strtotime($this->start_date));
            $dateOutput = $startDate;
        }
        if (!empty($this->end_date)) {
            $endDate   = date($dateFormat, strtotime($this->end_date));
            $dateOutput .= ' - ' . $endDate . ' ';
        }
        return $dateOutput;
    }

    public static function percentage(float $actual, float $budget): string
    {
        $percentage = $budget * 100 / $actual;
        return number_format($percentage, 2);
    }
}
