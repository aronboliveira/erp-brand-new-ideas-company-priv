<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    BillsConstants as BC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC
};
use App\Enums\EvaluationStatus;
use App\Services\DealRequestService;
use App\Traits\{HasAuditFields, UsesUuids, NormalizesAddresses};
use Illuminate\Database\Eloquent\{Collection, Factories\HasFactory, Model};
use Illuminate\Database\Eloquent\Relations\{BelongsToMany, HasMany, HasOne};
use Illuminate\Http\RedirectResponse;

class Deal extends Model
{
    use HasFactory, UsesUuids, HasAuditFields, NormalizesAddresses;

    protected $table = DC::TABLE_DEALS;

    protected $fillable = [
        'name',
        'phone',
        'price',
        PJC::COL_PPL_ID,
        PJC::COL_STG_ID,
        PJC::COL_GRP_ID,
        'sources',
        'products',
        'description',
        'customer',
        'notes',
        'labels',
        'permissions',
        'status',
        'order',
        'responsible',
        'supervisor',
        'involded',
        AC::COL_IA,
        BC::COL_STT_LB,
    ];

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $casts = [
        'price'            => 'decimal:2',
        'involded'         => 'array',
        AC::COL_IA         => 'integer',
        BC::COL_STT_LB     => EvaluationStatus::class,
    ];

    private const PERM_BASE         = 'Client';
    private const PERM_VIEW_TARGETS = [
        'Tasks',
        'Products',
        'Sources',
        'Contacts',
        'Files',
        'Invoices',
        'Custom fields',
        'Members'
    ];
    private const PERM_EXTRAS       = ['Add File', 'Deal Activity'];

    public static $statuses = [
        'Active' => 'Active',
        'Loss'   => 'Loss',
        'Won'    => 'Won',
    ];

    public $customField;

    /** @var string[] */
    public static array $permissions = [];

    protected static function booted(): void
    {
        parent::booted();

        self::$permissions = array_merge(
            array_map(fn($t) => self::PERM_BASE . " View " . $t, self::PERM_VIEW_TARGETS),
            array_map(fn($e) => self::PERM_BASE . " " . $e, self::PERM_EXTRAS)
        );

        static::saving(function (Deal $model): void {
            $isNormalizePhoneCallable = is_callable([self::class, 'normalizePhone']);
            $isNormalizeEmailCallable = is_callable([self::class, 'normalizeEmail']);
            if (!empty($model->getAttribute('phone')) && $isNormalizePhoneCallable) $model->setAttribute('phone', self::normalizePhone($model->getAttribute('phone'), 'deal.phone', $model->id));
            if (!empty($model->getAttribute('email')) && $isNormalizeEmailCallable) $model->setAttribute('email', self::normalizeEmail($model->getAttribute('email'), 'deal.email', $model->id));
            foreach (['sources', 'products', 'labels'] as $csvField) {
                $raw = $model->getAttribute($csvField);
                if (is_array($raw))
                    $model->setAttribute($csvField, implode(',', array_values($raw)));
                elseif (is_string($raw)) {
                    $val = preg_replace('/\s*,\s*/', ',', trim($raw));
                    $val = preg_replace('/\s+/', ' ', $val);
                    $model->setAttribute($csvField, $val);
                } elseif ($raw === null)
                    $model->setAttribute($csvField, null);
            }
            if (!is_numeric($model->getAttribute('price')) || $model->getAttribute('price') < 0)
                $model->setAttribute('price', 0.00);
            $st = $model->getAttribute(BC::COL_STT_LB);
            $model->setAttribute(BC::COL_STT_LB, EvaluationStatus::normalize($st));
        });
    }

    public function labels(): Collection
    {
        return $this->labels
            ? Label::whereIn('id', explode(',', $this->labels))->get()
            : collect();
    }

    public function pipeline(): HasOne
    {
        return $this->hasOne(Pipeline::class, 'id', PJC::COL_PPL_ID);
    }

    public function stage(): HasOne
    {
        return $this->hasOne(Stage::class, 'id', PJC::COL_STG_ID);
    }

    public function group(): HasOne
    {
        return $this->users()->hasOne(User::class, 'id', PJC::COL_GRP_ID); // * KEPT FOR COMPATIBILITY
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'client_deals', PJC::COL_DL_ID, 'client_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_deals', PJC::COL_DL_ID, 'user_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(DealFile::class, PJC::COL_DL_ID, 'id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(DealTask::class, PJC::COL_DL_ID, 'id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, PJC::COL_DL_ID, 'id');
    }

    public function calls(): HasMany
    {
        return $this->hasMany(DealCall::class, PJC::COL_DL_ID, 'id');
    }

    public function emails(): HasMany
    {
        return $this->hasMany(DealEmail::class, PJC::COL_DL_ID, 'id')->orderByDesc('id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ActivityLog::class, PJC::COL_DL_ID, 'id')->orderByDesc('id');
    }

    public function discussions(): HasMany
    {
        return $this->hasMany(DealDiscussion::class, PJC::COL_DL_ID, 'id')->orderByDesc('id');
    }

    public function getContactInfo(): array
    {
        return [
            'phone' => $this->phone,
            'email' => $this->email,
        ];
    }

    /**
     * Get deal summary
     * Pure alias to DealRequestService - auth check happens in service
     */
    public static function getDealSummary(
        array|Collection $deals,
        bool $numeric = false
    ): string|array|RedirectResponse {
        return app(DealRequestService::class)->getDealSummary($deals, $numeric);
    }
}
