<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC
};
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, HasOne};

class Bug extends Model
{
    use UsesUuids;

    private const COL_ASSIGN_TO  = PJC::COL_ASGN;
    private const COL_BUG_ID     = 'bug_id';
    private const COL_DESCRIPTION = AC::COL_DESC;
    private const COL_DUE_DATE   = 'due_date';
    private const COL_ORDER      = 'order';
    private const COL_PRIORITY   = PJC::COL_PRT;
    private const COL_PROJECT_ID = PJC::COL_PJ_ID;
    private const COL_START_DATE = PJC::COL_S_DT;
    private const COL_STATUS     = AC::COL_TSK_STT;
    private const COL_TITLE      = AC::COL_TT;
    private const COL_CREATED_BY = DC::TABLE_CREATOR;

    protected $fillable = [
        self::COL_BUG_ID,
        self::COL_PROJECT_ID,
        self::COL_TITLE,
        self::COL_PRIORITY,
        self::COL_START_DATE,
        self::COL_DUE_DATE,
        self::COL_DESCRIPTION,
        self::COL_STATUS,
        self::COL_ASSIGN_TO,
        self::COL_ORDER,
    ];
    protected $guarded = [
        'id',
        DC::TABLE_CREATOR,
    ];

    public static $priority = [
        'low'    => 'Low',
        'medium' => 'Medium',
        'high'   => 'High',
    ];

    public function bugStatus(): HasOne
    {
        return $this
            ->hasOne(BugStatus::class, 'id', self::COL_STATUS);
        // * consider using belongsTo(BugStatus::class, self::COL_STATUS)
    }

    public function assignTo(): HasOne
    {
        return $this
            ->hasOne(User::class, 'id', self::COL_ASSIGN_TO);
        // * consider using belongsTo(User::class, self::COL_ASSIGN_TO)
    }

    public function createdBy(): HasOne
    {
        return $this
            ->hasOne(User::class, 'id', self::COL_CREATED_BY);
        // * consider using belongsTo(User::class, self::COL_CREATED_BY)
    }

    public function comments(): HasMany
    {
        return $this
            ->hasMany(BugComment::class, 'bug_id', 'id')
            ->orderBy('id', 'DESC');
    }

    public function bugFiles(): HasMany
    {
        return $this
            ->hasMany(BugFile::class, 'bug_id', 'id')
            ->orderBy('id', 'DESC');
    }

    public function project(): HasOne
    {
        return $this
            ->hasOne(Project::class, 'id', self::COL_PROJECT_ID);
        // * consider using belongsTo(Project::class, self::COL_PROJECT_ID)
    }

    public function users(): mixed
    {
        return User::whereIn(
            'id',
            explode(',', $this->{self::COL_ASSIGN_TO})
        )->get();
        // * consider defining a proper relation instead of raw query
    }

    public function projectBug(): BelongsTo
    {
        return $this
            ->belongsTo(User::class, self::COL_PROJECT_ID);
        // ! ALERT BUG: likely should reference Project::class here
    }
}
