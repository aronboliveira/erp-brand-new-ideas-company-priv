<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants,
    DatabaseConstants,
    EmailsConstants
};
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\{Builder, Model, Relations\MorphMany};
use Illuminate\Support\{Facades\Auth, Str};

class Email extends Model
{
    use UsesUuids;

    protected $table    = DatabaseConstants::TABLE_EMAILS;
    public $incrementing = false; // ! CHANGED
    protected $keyType  = 'string'; // ! CHANGED
    private const CREATED_BY_FIELD = EmailsConstants::COL_EM . '_'
        . DatabaseConstants::COL_TABLE_CREATOR;
    private const GLOBAL_SCOPE   = DatabaseConstants::ORDER_C_AT;
    private const FILLABLE_FIELDS = [ // ! CHANGED
        'id',
        ActivitiesConstants::COL_TT,
        ActivitiesConstants::COL_DESC,
        self::CREATED_BY_FIELD,
        EmailsConstants::COL_D_URL,
        EmailsConstants::COL_ATC,
        EmailsConstants::COL_EM,
        ActivitiesConstants::COL_MT,
        ActivitiesConstants::COL_MI
    ];

    protected $fillable = self::FILLABLE_FIELDS;

    protected $casts = [ // * from Describable
        DatabaseConstants::COL_C_AT => 'datetime',
        DatabaseConstants::COL_U_AT => 'datetime'
    ];

    protected $attributes = [ // * from Describable
        ActivitiesConstants::COL_TT       => DatabaseConstants::DEFAULT_TT,
        ActivitiesConstants::COL_DESC => DatabaseConstants::DEFAULT_DESC,
        DatabaseConstants::TABLE_NOTES       => DatabaseConstants::DEFAULT_NOTES
    ];

    protected static function booted(): void
    {
        parent::booted();
        static::creating(function (self $model): void {
            $model->{self::CREATED_BY_FIELD} = $model->{self::CREATED_BY_FIELD} ?? Auth::id();
        });
        static::addGlobalScope(self::GLOBAL_SCOPE, function (Builder $builder): void {
            $builder->reorder()->orderBy(DatabaseConstants::COL_C_AT, 'desc');
        });
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, ActivitiesConstants::COL_MD);
    }

    public function addNote(string $text, ?string $createdBy = null): Note
    {
        return $this->notes()->create(
            [
                ActivitiesConstants::COL_NT => $text,
                DatabaseConstants::COL_TABLE_CREATOR => $createdBy ?? Auth::id()
            ]
        );
    }

    public function getLatestNote(): ?string
    {
        return $this->notes()->latest()->first()?->note;
    }

    public function getAllNotes(): string
    {
        $notes = $this->notes()->orderBy(DatabaseConstants::COL_TABLE_CREATOR)->get();
        if ($notes->isEmpty()) return DatabaseConstants::DEFAULT_NOTES;
        return $notes->pluck(ActivitiesConstants::COL_NT)->implode(' | ');
    }
}
