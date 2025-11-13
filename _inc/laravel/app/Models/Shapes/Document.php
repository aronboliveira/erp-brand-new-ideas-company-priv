<?php

namespace App\Models;

use App\Config\Constants\DatabaseConstants as DC;
use App\Config\Constants\UsersConstants as UC;
use App\Enums\DocumentKind;
use App\Enums\MimeType;
use App\Enums\UserType;
use App\Traits\HasAuditFields;
use App\Traits\UsesUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Document extends Model
{
    use UsesUuids, HasAuditFields;

    protected $table = DC::TABLE_DOCS;

    protected $fillable = [
        'name',
        'is_required',
        'file_path',
        'extension',
        'mime_type',
        'type',
        'size',
        'description',
        'notes',
        'expiration_date',
        'last_accessed',
        'viewers',
        'editors',
        'executors',
        'permission_rules',
    ];

    protected $guarded = ['id', DC::TABLE_CREATOR];

    protected $with = ['user'];

    protected $casts = [
        // * Migration define is_required como string; considerar boolean em evolução futura
        'is_private'     => 'boolean',
        'size'           => 'integer',
        'expiration_date' => 'date',
        'last_accessed'  => 'date',
        'mime_type'      => MimeType::class,
        'type'           => DocumentKind::class,
    ];

    // Ordem de papéis para o vetor de permissões (octal RWX por papel)
    // Ex.: permission_rules = '7776444' → [superAdmin, admin, company, accountant, vendor, customer, client]
    private const ROLES_ORDER = [
        UserType::SuperAdmin->value,
        UserType::Admin->value,
        UserType::Company->value,
        UserType::Accountant->value,
        UserType::Vendor->value,
        UserType::Customer->value,
        UserType::Client->value,
    ];
    private const DEFAULT_RULES = '7776444'; // rwx/rwx/rwx, etc.

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', DC::TABLE_CREATOR);
    }

    public function userRoleHasPermission(?string $id, string|int $type): bool
    {
        if (!is_numeric($type)) return false;
        $mask = (int) $type;                // 4=read, 2=write, 1=execute (padrão octal)
        if ($mask < 0 || $mask > 7) return false;

        $uid = $id ?? auth()->id();
        if (!$uid) return false;

        $u = User::query()->select(['id', UC::COL_TP])->find($uid);
        if (!$u) return false;

        $role = (string) $u->{UC::COL_TP};
        $index = array_search($role, self::ROLES_ORDER, true);
        if ($index === false) return false;

        $rules = str_split((string) ($this->permission_rules ?? self::DEFAULT_RULES));
        // ! Garante comprimento mínimo
        if (count($rules) < count(self::ROLES_ORDER)) {
            $rules = str_split(self::DEFAULT_RULES);
        }

        $digit = (int) ($rules[$index] ?? '0');
        return (($digit & $mask) === $mask);
    }

    public function userCanExecute(?string $id): bool
    {
        $uid = $id ?? auth()->id();
        if (!$uid) return false;
        $raw = (string) ($this->getAttribute('executors') ?? '');
        if ($raw === '') return false;
        return in_array($uid, array_filter(explode(',', $raw)), true);
    }

    public function userCanEdit(?string $id): bool
    {
        $uid = $id ?? auth()->id();
        if (!$uid) return false;
        $raw = (string) ($this->getAttribute('editors') ?? '');
        if ($raw === '') return false;
        return in_array($uid, array_filter(explode(',', $raw)), true);
    }

    public function userCanView(?string $id): bool
    {
        $uid = $id ?? auth()->id();
        if (!$uid) return false;
        $raw = (string) ($this->getAttribute('viewers') ?? '');
        if ($raw === '') return false;
        return in_array($uid, array_filter(explode(',', $raw)), true);
    }

    public function setExecutorsAttribute(array $ids): void
    {
        $this->attributes['executors'] = implode(',', $ids);
    }

    public function setEditorsAttribute(array $ids): void
    {
        $this->attributes['editors'] = implode(',', $ids);
    }

    public function setViewersAttribute(array $ids): void
    {
        $this->attributes['viewers'] = implode(',', $ids);
    }

    public function setRolePermission(string $role, int $permission): void
    {
        $idx = array_search($role, self::ROLES_ORDER, true);
        if ($idx === false || $permission < 0 || $permission > 7) return;

        $rules = str_split((string) ($this->permission_rules ?? self::DEFAULT_RULES));
        if (count($rules) < count(self::ROLES_ORDER)) {
            $rules = str_split(self::DEFAULT_RULES);
        }
        $rules[$idx] = (string) $permission;
        $this->permission_rules = implode('', $rules);
    }

    public function ensureMimeAndTypeFromExtension(): void
    {
        $ext = strtolower((string) ($this->extension ?? ''));
        if ($ext === '') return;

        $mime = MimeType::fromExtension($ext);
        if ($mime && !$this->mime_type instanceof MimeType) {
            $this->mime_type = $mime;
        }
        if (!$this->type instanceof DocumentKind) {
            $kind = DocumentKind::fromExtension($ext);
            if ($kind) $this->type = $kind;
        }
    }

    protected static function booted(): void
    {
        static::creating(function (self $m) {
            $m->ensureMimeAndTypeFromExtension();
        });
        static::updating(function (self $m) {
            if ($m->isDirty('extension') || $m->isDirty('mime_type') || $m->isDirty('type')) {
                $m->ensureMimeAndTypeFromExtension();
            }
        });
    }
}
