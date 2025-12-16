<?php

namespace App\Models;

use App\Config\Constants\{
    DatabaseConstants as DC,
    ActivitiesConstants as AC,
    BillsConstants as BC,
    ProjectsConstants as PJC
};
use App\Enums\EvaluationStatus;
use App\Traits\{ChecksLogin, HasAuditFields, UsesUuids, NormalizesAddresses};
use Illuminate\Database\Eloquent\{Collection, Factories\HasFactory, Model};
use Illuminate\Database\Eloquent\Relations\{BelongsToMany, HasMany, HasOne};
use Illuminate\Http\RedirectResponse;

class Deal extends Model
{
    use HasFactory, UsesUuids, ChecksLogin, HasAuditFields, NormalizesAddresses;

    protected $table = DC::TABLE_DEALS;

    protected $fillable = [
        'name',
        'phone',
        'price',
        'pipeline_id',
        'stage_id',
        'group_id',
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
            if (!empty($model->getAttribute('phone'))) $model->setAttribute('phone', self::normalizePhone($model->getAttribute('phone'), 'deal.phone', $model->id));
            if (!empty($model->getAttribute('email'))) $model->setAttribute('email', self::normalizeEmail($model->getAttribute('email'), 'deal.email', $model->id));
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
        return $this->hasOne(Pipeline::class, 'id', 'pipeline_id');
    }

    public function stage(): HasOne
    {
        return $this->hasOne(Stage::class, 'id', 'stage_id');
    }

    public function group(): HasOne
    {
        return $this->users()->hasOne(User::class, 'id', 'group_id'); // * KEPT FOR COMPATIBILITY
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'client_deals', 'deal_id', 'client_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_deals', 'deal_id', 'user_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(DealFile::class, 'deal_id', 'id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(DealTask::class, 'deal_id', 'id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'deal_id', 'id');
    }

    public function calls(): HasMany
    {
        return $this->hasMany(DealCall::class, 'deal_id', 'id');
    }

    public function emails(): HasMany
    {
        return $this->hasMany(DealEmail::class, 'deal_id', 'id')->orderByDesc('id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'deal_id', 'id')->orderByDesc('id');
    }

    public function discussions(): HasMany
    {
        return $this->hasMany(DealDiscussion::class, 'deal_id', 'id')->orderByDesc('id');
    }

    public function getContactInfo(): array
    {
        return [
            'phone' => $this->phone,
            'email' => $this->email,
        ];
    }

    public static function getDealSummary(array|Collection $deals, bool $numeric = false): string|array|RedirectResponse
    {
        if (($userOrRedirect = self::_checkLogin()) instanceof \Illuminate\Http\RedirectResponse)
            return $userOrRedirect;

        $user  = $userOrRedirect;
        $deals = is_array($deals) ? $deals : ($deals instanceof Collection ? $deals->toArray() : []);
        return $user?->priceFormat(collect($deals)->sum(fn($d) => $d->price), $numeric);
    }
}
