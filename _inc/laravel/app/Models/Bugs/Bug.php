<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    ProjectsConstants as PJC
};
use App\Traits\{DefinesDates, HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

use Illuminate\Database\Eloquent\Factories\HasFactory;
/**
 * @property string|null $status
 * @property string|null $assign_to
 * @property string|null $title
 * @property string|null $description
 * @property string|null $priority
 * @property string|null $start_date
 */
class Bug extends Model
{
    use HasFactory;
    use UsesUuids, HasAuditFields, DefinesDates;

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
    private const COL_CREATED_BY = DC::COL_TABLE_CREATOR;

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
        DC::COL_TABLE_CREATOR,
    ];

    public static $priority = [
        'low'    => 'Low',
        'medium' => 'Medium',
        'high'   => 'High',
    ];

    public function bugStatus(): BelongsTo
    {
        return $this->belongsTo(BugStatus::class, self::COL_STATUS);
    }

    public function assignTo(): BelongsTo
    {
        return $this->belongsTo(User::class, self::COL_ASSIGN_TO);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, self::COL_CREATED_BY);
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

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, self::COL_PROJECT_ID);
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

    // NOTE: created_by() and project_bug() aliases were removed.
    // They collided with the 'created_by' / 'project_bug' column names,
    // causing Eloquent to resolve $model->created_by as a relation
    // instead of an attribute → infinite recursion → OOM.
    // Use $model->createdBy (relation) or $model->getAttributes()['created_by'] (column).
}
