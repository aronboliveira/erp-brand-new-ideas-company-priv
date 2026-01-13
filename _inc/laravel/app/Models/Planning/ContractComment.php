<?php

namespace App\Models;

use App\Config\Constants\{
    ActivitiesConstants as AC,
    DatabaseConstants as DC,
    ProjectsConstants as PC,
    UsersConstants as UC
};
use App\Enums\UserType;
use App\Traits\{FiltersSecureAttachments, HasAuditFields, NormalizesArrays, UsesUuids};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\{DB, Log};

class ContractComment extends Comment
{
    use FiltersSecureAttachments, HasAuditFields, HasFactory, NormalizesArrays, UsesUuids;

    protected $table = DC::TABLE_CTC_CMT;

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
        DC::COL_TABLE_UPDATER,
    ];

    protected $with = ['author', 'contract'];

    protected $casts = [
        UC::COL_U_TP     => UserType::class,
        'time'          => 'datetime',
        AC::COL_DEL_AT   => 'datetime',

        AC::COL_IS_EDT   => 'bool',
        AC::COL_IS_DEL   => 'bool',
        AC::COL_IS_RPL   => 'bool',
        'flagged'       => 'bool',

        AC::COL_EDT_CNT  => 'int',
        AC::COL_RPL_CNT  => 'int',
        'order'         => 'int',
        'depth'         => 'int',

        'attachments'   => 'array',
        'tags'          => 'array',
        'reactions'     => 'array',
        'replies'       => 'array',
        'edits'         => 'array',
        'metadata'      => 'array',
    ];

    protected static function booted(): void
    {
        if (is_callable('parent::booted')) parent::booted();

        static::saving(function (self $m): void {
            try {
                if (empty($m->getAttribute(UC::COL_U_TP)))
                    $m->setAttribute(UC::COL_U_TP, static::defaultUserType()->value);

                $m->setAttribute('attachments', $m->getAttribute('attachments'));
            } catch (\Throwable $e) {
                Log::warning(static::class . ' saving normalization failed', [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'error' => $e->getMessage(),
                    'table' => $m->getTable(),
                    'model_id' => $m->getKey(),
                ]);
            }
        });
    }

    protected static function defaultUserType(): UserType
    {
        return UserType::Client;
    }

    protected static function fillableFields(): array
    {
        $base = [];
        try {
            $base = is_callable('parent::fillableFields') ? parent::fillableFields() : [];
        } catch (\Throwable $e) {
            Log::notice(static::class . ' failed reading parent fillableFields', [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'error' => $e->getMessage(),
            ]);
        }

        $out = array_merge($base, [PC::COL_CTC_ID]);
        return array_values(array_unique($out));
    }

    protected static function withRelations(): array
    {
        $base = [];
        try {
            $base = is_callable('parent::withRelations') ? parent::withRelations() : [];
        } catch (\Throwable $e) {
            Log::notice(static::class . ' failed reading parent withRelations', [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'error' => $e->getMessage(),
            ]);
        }

        $out = array_merge($base, ['contract']);
        return array_values(array_unique($out));
    }

    public function contract(): ?BelongsTo
    {
        return $this->belongsTo(Contract::class, PC::COL_CTC_ID, 'id');
    }

    public function scopeForContract($query, string $contractId)
    {
        return $query->where(PC::COL_CTC_ID, $contractId);
    }

    public function setAttachmentsAttribute(mixed $value): void
    {
        $arr = self::normalizeAttachmentListSmart($value);

        if ($arr === null) {
            $this->attributes['attachments'] = null;
            return;
        }

        $out = [];
        foreach ($arr as $v) {
            $sv = self::sanitizeCommentAttachmentValue($v, $this);
            if ($sv !== null) $out[] = $sv;
        }

        $out = $out ? array_values(array_unique($out)) : null;

        if ($out === null) {
            $this->attributes['attachments'] = null;
            return;
        }

        try {
            $this->attributes['attachments'] = json_encode($out, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            Log::error(static::class . ' failed encoding attachments json', [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'error' => $e->getMessage(),
                'table' => $this->getTable(),
                'model_id' => $this->getKey(),
            ]);
            $this->attributes['attachments'] = json_encode($out);
        }
    }

    protected static function normalizeAttachmentListSmart(mixed $value): ?array
    {
        if ($value === null)
            return null;

        if (is_string($value)) {
            $trim = trim($value);
            if ($trim === '')
                return null;

            try {
                $decoded = json_decode($trim, true, 512, JSON_THROW_ON_ERROR);
                $value = $decoded;
            } catch (\Throwable) {
                $parts = preg_split('/[\r\n,]+/', $trim) ?: [];
                $value = $parts;
            }
        }

        if (!is_array($value))
            $value = (array) $value;

        $out = [];
        foreach ($value as $v) {
            if (!is_scalar($v))
                continue;
            $s = trim((string) $v);
            if ($s === '')
                continue;
            $out[] = $s;
        }

        $out = array_values(array_unique($out));
        return $out ?: null;
    }

    protected static function sanitizeCommentAttachmentValue(mixed $value, self $m): ?string
    {
        $v = trim((string) $value);
        if ($v === '')
            return null;

        if (Utility::looksLikeUuid($v)) {
            try {
                if (DB::table(DC::TABLE_DOCS)->where('id', $v)->exists())
                    return $v;
            } catch (\Throwable $e) {
                Log::error(static::class . ' failed checking Document uuid attachment', [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'error' => $e->getMessage(),
                    'doc_id' => $v,
                    'table' => $m->getTable(),
                    'model_id' => $m->getKey(),
                ]);
            }

            try {
                if (DB::table(DC::TABLE_CTC_ATC)->where('id', $v)->exists())
                    return $v;
            } catch (\Throwable $e) {
                Log::error(static::class . ' failed checking ContractAttachment uuid attachment', [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'error' => $e->getMessage(),
                    'attachment_id' => $v,
                    'table' => $m->getTable(),
                    'model_id' => $m->getKey(),
                ]);
            }

            return null;
        }

        if (str_starts_with($v, 'https://'))
            return self::validateSafeUrl($v);

        return self::validatePath($v);
    }
}
