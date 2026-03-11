<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
/**
 * @property string|null $url
 */
class WebhookSettings extends Model
{
    use HasFactory;
    use UsesUuids;

    private const FILLABLE_FIELDS = [
        'module', 'url', 'method', 'created_by'
    ];
    protected $fillable = self::FILLABLE_FIELDS;

    public static array $modules = [
        'new lead'                => 'New Lead',
        'lead to deal conversion' => 'Lead to Deal Conversion',
        'new project'             => 'New Project',
        'task stage updated'      => 'Task Stage Updated',
        'new deal'                => 'New Deal',
        'new contract'            => 'New Contract',
        'new task'                => 'New Task',
        'new task comment'        => 'New Task Comment',
        'new monthly payslip'     => 'New Monthly Payslip',
        'new announcement'        => 'New Announcement',
        'new support ticket'      => 'New Support Ticket',
        'new meeting'             => 'New Meeting',
        'new award'               => 'New Award',
        'new holiday'             => 'New Holiday',
        'new event'               => 'New Event',
        'new company policy'      => 'New Company Policy',
        'new invoice'             => 'New Invoice',
        'new bill'                => 'New Bill',
        'new budget'              => 'New Budget',
        'new revenue'             => 'New Revenue',
        'new invoice payment'     => 'New Invoice Payment'
    ];

    public static array $method = [
        'get'  => 'GET',
        'post' => 'POST'
    ];

    // * consider adding belongsTo(User::class,'created_by','id')
}
