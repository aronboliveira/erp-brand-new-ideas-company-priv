<?php

namespace App\Models;

use App\Config\Constants\{
    DatabaseConstants as DC,
    MessagesConstants as MC,
    SupportsConstants as SC
};
use App\Traits\{DefinesDates, FiltersSecureAttachments, HasAuditFields, NormalizesArrays, UsesUuids};
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model, Relations\BelongsTo};
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;

class SupportReply extends Model
{
    use UsesUuids, HasAuditFields, FiltersSecureAttachments, HasFactory, NormalizesArrays, DefinesDates;

    protected $table = DC::TABLE_SUP_REP;

    protected $guarded = ['id', DC::COL_TABLE_CREATOR];

    protected $fillable = [
        'code',
        SC::COL_SPT_ID,
        'user',
        'description',
        MC::COL_SNT_AT,
        MC::COL_IS_RD,
        MC::COL_RD_AT,
        'email',
        'notification',
        'task',
        'form',
        SC::COL_FORM_RSP,
        'log',
        'attachment',
        SC::COL_OTHER_ATTACHMENTS,
    ];

    protected $casts = [
        'description' => 'string',

        MC::COL_SNT_AT => 'datetime',
        MC::COL_RD_AT => 'datetime',
        MC::COL_IS_RD => 'boolean',

        SC::COL_OTHER_ATTACHMENTS => 'array',
    ];

    protected $with = ['support', 'user'];

    protected $appends = ['is_draft'];

    protected static function booted(): void
    {
        static::saving(function (self $m): void {
            $m->ensureReplyCode();
            $m->normalizeReadPolicy();
            $m->inferTaskFromSupport();
            $m->ensureJsonAttributesAreEncoded([SC::COL_OTHER_ATTACHMENTS]);
        });
    }

    public function getIsDraftAttribute(): bool
    {
        return $this->getAttribute(MC::COL_SNT_AT) === null;
    }

    protected function ensureReplyCode(): void
    {
        $code = trim((string) $this->getAttribute('code'));
        if ($code !== '')
            return;

        $table = $this->getTable();
        $id = (string) ($this->getKey() ?: Str::uuid());
        $attempts = 0;

        do {
            $attempts++;
            $candidate = 'SUP-REP-' . strtoupper($id) . '-' . now()->format('YmdHis');
            $exists = false;

            try {
                $exists = DB::table($table)
                    ->where('code', $candidate)
                    ->where('id', '!=', (string) ($this->getAttribute('id') ?? ''))
                    ->exists();
            } catch (\Throwable $e) {
                Log::error(static::class . ' failed ensuring unique reply code', [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'error' => $e->getMessage(),
                    'table' => $table,
                    'model_id' => $this->getKey(),
                ]);
                $exists = false;
            }

            if (!$exists) {
                $this->setAttribute('code', $candidate);
                return;
            }

            $id = (string) Str::uuid();
        } while ($attempts < 12);

        $this->setAttribute('code', 'SUP-REP-' . strtoupper((string) Str::uuid()) . '-' . now()->format('YmdHis'));
    }

    protected function normalizeReadPolicy(): void
    {
        $isRead = $this->getAttribute(MC::COL_IS_RD);

        if ($isRead === null)
            $this->setAttribute(MC::COL_IS_RD, false);

        $isRead = (bool) $this->getAttribute(MC::COL_IS_RD);

        if (!$isRead) {
            if ($this->getAttribute(MC::COL_RD_AT) !== null)
                $this->setAttribute(MC::COL_RD_AT, null);
            return;
        }

        if ($this->getAttribute(MC::COL_RD_AT) === null)
            $this->setAttribute(MC::COL_RD_AT, now());
    }

    protected function inferTaskFromSupport(): void
    {
        if ($this->getAttribute('task'))
            return;

        $supportId = $this->getAttribute(SC::COL_SPT_ID);
        if (!$supportId)
            return;

        try {
            $taskId = DB::table(DC::TABLE_SUPPORTS)
                ->where('id', $supportId)
                ->value('task');

            if ($taskId)
                $this->setAttribute('task', $taskId);
        } catch (\Throwable $e) {
            Log::warning(static::class . ' failed inferring task from support', [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'error' => $e->getMessage(),
                'support_id' => $supportId,
                'model_id' => $this->getKey(),
            ]);
        }
    }

    public function support(): BelongsTo
    {
        return $this->belongsTo(Support::class, SC::COL_SPT_ID, 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user', 'id');
    }

    public function email(): BelongsTo
    {
        return $this->belongsTo(Email::class, 'email', 'id');
    }

    public function notification(): BelongsTo
    {
        return $this->belongsTo(Notification::class, 'notification', 'id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task', 'id');
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(FormBuilder::class, 'form', 'id');
    }

    public function formResponse(): BelongsTo
    {
        return $this->belongsTo(FormResponse::class, SC::COL_FORM_RSP, 'id');
    }

    public function log(): BelongsTo
    {
        return $this->belongsTo(ActivityLog::class, 'log', 'id');
    }

    public function markSent(?\DateTimeInterface $when = null): self
    {
        $this->setAttribute(MC::COL_SNT_AT, $when ?: now());
        return $this;
    }

    public function markRead(?\DateTimeInterface $when = null): self
    {
        $this->setAttribute(MC::COL_IS_RD, true);
        $this->setAttribute(MC::COL_RD_AT, $when ?: now());
        return $this;
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, $this> */
    public function users(): BelongsTo
    {
        return $this->user();
    }
}
