<?php

namespace App\Models;

use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Collection, Model, Relations\HasOne};

class Job extends Model
{
    use UsesUuids;

    private const COL_APPLICANT      = 'applicant';
    private const COL_BRANCH         = 'branch';
    private const COL_CATEGORY       = 'category';
    private const COL_CODE           = 'code';
    private const COL_CREATED_BY     = 'created_by';
    private const COL_CUSTOM_QUESTION = 'custom_question';
    private const COL_DESCRIPTION    = 'description';
    private const COL_END_DATE       = 'end_date';
    private const COL_POSITION       = 'position';
    private const COL_REQUIREMENT    = 'requirement';
    private const COL_SKILL          = 'skill';
    private const COL_START_DATE     = 'start_date';
    private const COL_STATUS         = 'status';
    private const COL_TITLE          = 'title';
    private const COL_VISIBILITY     = 'visibility';

    protected $fillable = [
        self::COL_TITLE,
        self::COL_DESCRIPTION,
        self::COL_REQUIREMENT,
        self::COL_BRANCH,
        self::COL_CATEGORY,
        self::COL_SKILL,
        self::COL_POSITION,
        self::COL_START_DATE,
        self::COL_END_DATE,
        self::COL_STATUS,
        self::COL_APPLICANT,
        self::COL_VISIBILITY,
        self::COL_CODE,
        self::COL_CUSTOM_QUESTION,
        self::COL_CREATED_BY,
    ];

    public static $status = [
        'active'    => 'Active',
        'in_active' => 'In Active',
    ];

    public function branches(): HasOne
    {
        return $this
            ->hasOne(Branch::class, 'id', self::COL_BRANCH);
        // * consider using belongsTo(Branch::class, self::COL_BRANCH)
    }

    public function categories(): HasOne
    {
        return $this
            ->hasOne(JobCategory::class, 'id', self::COL_CATEGORY);
        // * consider using belongsTo(JobCategory::class, self::COL_CATEGORY)
    }

    public function createdBy(): HasOne
    {
        return $this
            ->hasOne(User::class, 'id', self::COL_CREATED_BY);
        // * consider using belongsTo(User::class, self::COL_CREATED_BY)
    }

    public function questions(): Collection
    {
        $ids = explode(',', $this->{self::COL_CUSTOM_QUESTION});
        return CustomQuestion::whereIn('id', $ids)->get();
        // * consider defining a proper relation
    }
}
