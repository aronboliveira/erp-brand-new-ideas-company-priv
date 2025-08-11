<?php

namespace App\Models;

use App\Traits\{ChecksLogin, UsesUuids};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, HasOne};
use Illuminate\Support\Facades\Auth;

class Contract extends Model
{
    use ChecksLogin, UsesUuids;

    private const COL_CLIENT_NAME          = 'client_name';
    // CNPJ 
    private const COL_COMPANY_SIGNATURE    = 'company_signature';
    private const COL_CONTRACT_DESCRIPTION = 'contract_description';
    private const COL_CREATED_BY           = 'created_by';
    private const COL_DESCRIPTION          = 'description';
    private const COL_END_DATE             = 'end_date';
    private const COL_PROJECT_ID           = 'project_id';
    private const COL_START_DATE           = 'start_date';
    private const COL_STATUS               = 'status';
    private const COL_SUBJECT              = 'subject';
    private const COL_TYPE                 = 'type';
    private const COL_VALUE                = 'value';
    private const COL_CLIENT_SIGNATURE     = 'client_signature';
    private const STATUS_OPTIONS = [
        'accept'  => 'Accept',
        'decline' => 'Decline',
    ]; // ! CHANGED

    private const FILLABLE = [
        self::COL_CLIENT_NAME,
        self::COL_SUBJECT,
        self::COL_VALUE,
        self::COL_TYPE,
        self::COL_START_DATE,
        self::COL_END_DATE,
        self::COL_DESCRIPTION,
        self::COL_STATUS,
        self::COL_CONTRACT_DESCRIPTION,
        self::COL_COMPANY_SIGNATURE,
        self::COL_CLIENT_SIGNATURE, // ! ALERT constant for client_signature missing
        self::COL_CREATED_BY,
    ];

    protected $fillable = self::FILLABLE;

    public static function status(): array
    {
        return self::STATUS_OPTIONS;
    }

    public function clients(): HasOne
    {
        return $this->hasOne(User::class, 'id', self::COL_CLIENT_NAME);
        // * consider belongsTo(User::class,self::COL_CLIENT_NAME,'id')
    }

    public function types(): HasOne
    {
        return $this->hasOne(ContractType::class, 'id', self::COL_TYPE);
        // * consider belongsTo(ContractType::class,self::COL_TYPE,'id')
    }

    public static function getContractSummary($contracts): string // ! CHANGED
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $total = $contracts->sum(fn ($c) => $c->value);
        return $user?->priceFormat($total);
    }

    public function projects(): HasOne
    {
        return $this->hasOne(Project::class, 'id', self::COL_PROJECT_ID);
        // * consider belongsTo(Project::class,self::COL_PROJECT_ID,'id')
    }

    public function files(): HasMany
    {
        return $this->hasMany(ContractAttachment::class, 'contract_id', 'id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ContractNotes::class, 'contract_id', 'id');
    }

    public function comment(): HasMany
    {
        return $this->hasMany(ContractComment::class, 'contract_id', 'id');
    }

    public function note(): HasMany
    {
        return $this->hasMany(ContractNotes::class, 'contract_id', 'id');
    }

    public function contractAttachment(): BelongsTo // ! CHANGED
    {
        return $this->belongsTo(ContractAttachment::class, 'id', 'contract_id');
    }

    public function ContractAttechment(): BelongsTo // * KEPT FOR COMPATIBILITY, DON'T USE IN ENDPOINTS
    {
        return $this->contractAttachment();
    }


    public function contractComment(): BelongsTo // ! CHANGED
    {
        return $this->belongsTo(ContractComment::class, 'id', 'contract_id');
    }

    public function contractNote(): BelongsTo // ! CHANGED
    {
        return $this->belongsTo(ContractNotes::class, 'id', 'contract_id');
    }
}
