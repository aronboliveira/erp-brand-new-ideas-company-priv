<?php

namespace App\Models;

use App\Config\Constants\DatabaseConstants;
use App\Traits\{ChecksLogin, UsesUuids};
use Illuminate\Database\Eloquent\{Collection, Factories\HasFactory, Model};
use Illuminate\Database\Eloquent\Relations\{BelongsToMany, HasMany, HasOne};

class Deal extends Model
{
    use HasFactory, UsesUuids, ChecksLogin;
    protected $fillable = [
        'name', 'phone', 'price', 'pipeline_id', 'stage_id', 'group_id',
        'sources', DatabaseConstants::TABLE_PRODUCTS,
        DatabaseConstants::TABLE_NOTES, 'labels', DatabaseConstants::TABLE_PERMISSIONS,
        'status', 'order', DatabaseConstants::TABLE_CREATOR, 'is_active'
    ];
    private const PERM_BASE         = 'Client';
    private const PERM_VIEW_TARGETS = [
        'Tasks', 'Products', 'Sources', 'Contacts', 'Files',
        'Invoices', 'Custom fields', 'Members'
    ];
    private const PERM_EXTRAS       = ['Add File', 'Deal Activity'];
    public static $statuses = [
        'Active' => 'Active', 'Loss' => 'Loss', 'Won' => 'Won'
    ];
    public $customField;
    /** @var string[] */
    public static array $permissions = [];
    protected static function booted(): void
    {
        parent::booted();
        self::$permissions = array_merge(
            array_map(fn ($t) => self::PERM_BASE . " View " . $t, self::PERM_VIEW_TARGETS),
            array_map(fn ($e) => self::PERM_BASE . " " . $e, self::PERM_EXTRAS)
        );
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
        return $this->belongsToMany(
            User::class,
            'client_deals',
            'deal_id',
            'client_id'
        );
    }
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_deals',
            'deal_id',
            'user_id'
        );
    }
    public function products(): Collection
    {
        return $this->products
            ? ProductService::whereIn(
                'id',
                explode(',', $this->products)
            )->get()
            : collect();
    }
    public function sources(): Collection
    {
        return $this->sources
            ? Source::whereIn(
                'id',
                explode(',', $this->sources)
            )->get()
            : collect();
    }
    public function files(): HasMany
    {
        return $this->hasMany(DealFile::class, 'deal_id', 'id');
    }
    public function tasks(): HasMany
    {
        return $this->hasMany(DealTask::class, 'deal_id', 'id');
    }
    public function completeTasks(): Collection
    {
        return $this->tasks()->where('status', 1);
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
        return $this->hasMany(DealEmail::class, 'deal_id', 'id')
            ->orderByDesc('id');
    }
    public function activities(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'deal_id', 'id')
            ->orderByDesc('id');
    }
    public function discussions(): HasMany
    {
        return $this->hasMany(DealDiscussion::class, 'deal_id', 'id')
            ->orderByDesc('id');
    }
    public static function getDealSummary($deals): float
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        return $user?->priceFormat(collect($deals)->sum(fn ($d) => $d->price));
    }
}
