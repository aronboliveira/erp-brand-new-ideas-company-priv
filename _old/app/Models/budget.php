<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'period',
        'created_by'
    ];

    // * kept for legacy. The Frequency enum should be used instead.
    public static $frequency = [
        'monthly' => 'Monthly',
        'weekly' => 'Weekly',
        'biweekly' => 'Biweekly',
        'semimonthly' => 'Semimonthly',
        'semestral' => 'Semestral',
        'quaternaly' => 'Quaternaly',
        'half-yearly' => 'Half Yearly',
        'yearly' => 'Yearly',
        'annual' => 'Annual',
        'once' => 'Once',
        'variable' => 'Variable',
        'hourly' => 'Hourly',
    ];

    public function getAvailabilityDate()
    {

        $start_date = '';
        $end_date = '';
        $date = '';
        $date_formate = ('M-Y');
        if (!empty($this->start_date)) {
            $start_date = date($date_formate, strtotime($this->start_date));
            $date = $start_date;
        }
        if (!empty($this->end_date)) {
            $end_date = date($date_formate, strtotime($this->end_date));
            $date .= ' - ' . $end_date . ' ';
        }


        return $date;
    }

    public static function percentage($actual, $budget)
    {
        $percentage = $budget * 100 / $actual;
        return  number_format($percentage, 2);
    }
}
