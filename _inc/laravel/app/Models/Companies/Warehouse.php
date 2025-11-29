<?php

namespace App\Models;

use App\Config\Constants\{
    CompaniesConstants as CC,
    DatabaseConstants as DC,
    UsersConstants as UC
};
use App\Enums\Weekday;
use App\Traits\{
    ChecksLogin,
    HasAuditFields,
    NormalizesAddresses,
    UsesUuids
};
use Illuminate\Database\Eloquent\{
    Factories\HasFactory,
    Model,
    Relations\BelongsTo
};
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\{
    DB,
    Log
};

class Warehouse extends Model
{
    use ChecksLogin;
    use HasAuditFields;
    use HasFactory;
    use NormalizesAddresses;
    use UsesUuids;

    protected $table = DC::TABLE_WRH;
    protected $fillable = [
        'code',
        'name',
        CC::COL_CP_ID,
        'zip',
        'country',
        'state',
        'city',
        'address',
        CC::COL_ADR_DTL,
        'notes',
        'phone',
        'email',
        CC::COL_OWN_ID,
        CC::COL_OWN_NM,
        CC::COL_IA,
        CC::COL_IS_SHP,
        CC::COL_FD_DT,
        'dimensions',
        'capacity',
        'employees',
        'supervisors',
        'managers',
        'partners',
        'sections',
        CC::COL_REACH,
        CC::COL_OP_TM,
        CC::COL_CL_TM,
        CC::COL_WK_DYS,
        UC::COL_AVG_RT,
        DC::TABLE_CREATOR,
        DC::TABLE_UPDATER,
    ];
    protected $with = [
        'company',
        'owner',
        'createdBy',
    ];
    protected $casts = [
        CC::COL_IA       => 'boolean',
        CC::COL_IS_SHP   => 'boolean',
        CC::COL_FD_DT    => 'date',
        CC::COL_OP_TM    => 'datetime:H:i:s',
        CC::COL_CL_TM    => 'datetime:H:i:s',
        'dimensions'     => 'array',
        'capacity'       => 'array',
        'employees'      => 'array',
        'supervisors'    => 'array',
        'managers'       => 'array',
        'partners'       => 'array',
        'sections'       => 'array',
        CC::COL_REACH    => 'array',
        CC::COL_WK_DYS   => 'array',
        UC::COL_AVG_RT   => 'decimal:2',
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (self $m): void {
            foreach (
                [
                    'dimensions',
                    'capacity',
                    'employees',
                    'supervisors',
                    'managers',
                    'partners',
                    'sections',
                    CC::COL_REACH,
                ] as $field
            ) {
                $m->{$field} = static::normalizeArrayField($m->{$field} ?? null);
            }

            $ownerId = $m->{DC::TABLE_CREATOR} ?? $m->{CC::COL_CP_ID} ?? $m->{CC::COL_OWN_ID} ?? null;

            $m->email = static::normalizeEmail(
                $m->email ?? null,
                'warehouse',
                $ownerId
            );

            $m->phone = static::normalizePhone(
                $m->phone ?? null,
                'warehouse',
                $ownerId
            );

            $m->zip = static::normalizeZip(
                $m->zip ?? null,
                $m->country ?? null,
                'warehouse',
                $ownerId
            );

            // Clamp básico de rating (0.00–5.00) se informado
            $ratingField = UC::COL_AVG_RT;
            if ($m->{$ratingField} !== null) {
                $val = (float) $m->{$ratingField};
                if ($val < 0.0) {
                    $val = 0.0;
                } elseif ($val > 5.0) {
                    $val = 5.0;
                }
                $m->{$ratingField} = $val;
            }
        });
    }

    /**
     * Normaliza working_days na escrita usando Weekday::normalize
     * e garante ordenação ISO (segunda–domingo).
     */
    public function setWorkingDaysAttribute(mixed $value): void
    {
        $raw = static::normalizeArrayField($value);

        $valid = [];
        foreach ($raw as $item)
            $weekday = Weekday::normalize(is_string($item) ? $item : (string) $item);
        if ($weekday !== null)
            $valid[$weekday->value] = true; // evitar duplicatas
        $ordered = [];
        foreach (Weekday::ordered(true) as $enumDay)
            if (isset($valid[$enumDay->value]))
                $ordered[] = $enumDay->value;
        $this->attributes[CC::COL_WK_DYS] = json_encode($ordered);
    }

    /**
     * Garante que working_days lido venha sempre normalizado e ordenado.
     */
    public function getWorkingDaysAttribute(mixed $value): array
    {
        $raw = static::normalizeArrayField($value);
        $valid = [];
        foreach ($raw as $item)
            $weekday = Weekday::normalize(is_string($item) ? $item : (string) $item);
        if ($weekday !== null)
            $valid[$weekday->value] = true;
        $ordered = [];
        foreach (Weekday::ordered(true) as $enumDay)
            if (isset($valid[$enumDay->value]))
                $ordered[] = $enumDay->value;
        return $ordered;
    }

    /**
     * Relação com a "empresa" (company_id).
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            CC::COL_CP_ID,
            'id'
        );
    }

    /**
     * Relação com o owner (owner_id).
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            CC::COL_OWN_ID,
            'id'
        );
    }

    /**
     * Mantida para compatibilidade: se não logado, retorna RedirectResponse,
     * senão retorna o ID numérico (ou 0).
     */
    public static function warehouseId(string $warehouseId): string|int|RedirectResponse
    {
        try {
            $userOrRedirect = self::_checkLogin();

            if ($userOrRedirect instanceof RedirectResponse) {
                return $userOrRedirect;
            }

            $user = $userOrRedirect;

            $id = DB::table((new self())->getTable())
                ->where('id', $warehouseId)
                ->where(DC::TABLE_CREATOR, $user?->creatorId())
                ->value('id');

            return (int) ($id ?? 0);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . '::' . __FUNCTION__ . " failed: {$e->getMessage()}");
            return 0;
        }
    }
}
