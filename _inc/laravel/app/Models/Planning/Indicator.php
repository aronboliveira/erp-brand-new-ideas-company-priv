<?php

namespace App\Models;

use App\Config\Constants\{
    DatabaseConstants as DC,
    ProjectsConstants as PJC
};
use App\Enums\IndicatorTechnicalLevel;
use App\Models\{
    Branch,
    Department,
    Designation,
    Employee,
    Project,
    User
};
use App\Traits\{
    HasAuditFields,
    NormalizesArrays,
    UsesUuids
};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class Indicator extends Model
{
    use UsesUuids;
    use HasAuditFields;
    use NormalizesArrays;

    protected $table = DC::TABLE_IND;

    private const FILLABLE_FIELDS = [
        // HasRatingColumns
        'company',
        'branch',
        'employee',
        'rating',
        'attendance',
        'administration',
        PJC::COL_CST_EXP, // customer_experience
        'integrity',
        'marketing',
        'professionalism',

        // CreateIndicatorsTable
        'department',
        'designation',
        'project',
        DC::COL_CRT_USR, // created_user
        'level',
        'sources',
    ];

    protected $fillable = self::FILLABLE_FIELDS;

    protected $guarded = [
        'id',
        DC::COL_TABLE_CREATOR,
    ];

    protected $casts = [
        'id'                 => 'string',
        'company'            => 'string',
        'branch'             => 'string',
        'employee'           => 'string',
        'department'         => 'string',
        'designation'        => 'string',
        'project'            => 'string',

        'rating'             => 'string',
        'attendance'         => 'int',
        'administration'     => 'int',
        PJC::COL_CST_EXP     => 'int', // customer_experience
        'integrity'          => 'int',
        'marketing'          => 'int',
        'professionalism'    => 'int',

        DC::COL_CRT_USR      => 'string',
        'level'              => IndicatorTechnicalLevel::class,

        'sources'            => 'array',

        DC::COL_C_AT         => 'datetime',
        DC::COL_U_AT         => 'datetime',
    ];

    // * legacy, should match the default labels of the IndicatorTechnicalLevel enum
    private const ORGANIZATIONAL_LEVELS = [
        'None',
        'Beginner',
        'Intermediate',
        'Advanced',
    ];

    public static array $organizational = self::ORGANIZATIONAL_LEVELS;

    // * legacy, should match the default labels of the IndicatorTechnicalLevel enum
    private const TECHNICAL_LEVELS = [
        'None',
        'Beginner',
        'Intermediate',
        'Advanced',
        'Expert / Leader',
    ];

    public static array $technical = self::TECHNICAL_LEVELS;

    protected static function booted(): void
    {
        static::saving(function (self $model): void {
            // Clampa os ratings entre 0 e 10
            foreach (
                [
                    'attendance',
                    'administration',
                    PJC::COL_CST_EXP,
                    'integrity',
                    'marketing',
                    'professionalism',
                ] as $field
            ) {
                $value = $model->getAttribute($field);

                $int = (int) ($value ?? 0);

                if ($int < 0) {
                    $int = 0;
                } elseif ($int > 10) {
                    $int = 10;
                }

                $model->setAttribute($field, $int);
            }

            // Normaliza rating textual
            $rating = $model->getAttribute('rating');
            if ($rating !== null) {
                $model->setAttribute('rating', trim((string) $rating));
            }

            // Normaliza sources: array<string> de UUIDs
            $sources = $model->getAttribute('sources');

            if (is_array($sources)) {
                $normalized = [];

                foreach ($sources as $value) {
                    if (!is_string($value)) {
                        continue;
                    }

                    $trimmed = trim($value);
                    if ($trimmed === '') {
                        continue;
                    }

                    if (!Utility::looksLikeUuid($trimmed)) {
                        continue;
                    }

                    $normalized[] = $trimmed;
                }

                $normalized = array_values(array_unique($normalized));

                $model->setAttribute('sources', $normalized);
            } else {
                $model->setAttribute('sources', []);
            }

            try {
                $model->ensureJsonAttributesAreEncoded(['sources']);
            } catch (\Throwable $e) {
                Log::warning(static::class . ' failed to encode JSON attributes', [
                    'id'    => $model->getAttribute('id'),
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }

    public function branches(): BelongsTo
    {
        // coluna literal "branch" (BranchConnected com prefixed: false)
        return $this->belongsTo(Branch::class, 'branch', 'id');
    }

    public function departments(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department', 'id');
    }

    public function designations(): BelongsTo
    {
        return $this->belongsTo(Designation::class, 'designation', 'id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project', 'id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee', 'id');
    }

    public function company(): BelongsTo
    {
        // usuário do tipo company, FK "company"
        return $this->belongsTo(User::class, 'company', 'id');
    }

    /**
     * Usuário que criou o indicador (DC::COL_CRT_USR = 'created_user').
     *
     * Mantém nome legado "user" para compatibilidade.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, DC::COL_CRT_USR, 'id');
    }

    /**
     * Alias mais explícito para o criador do indicador.
     */
    public function creator(): BelongsTo
    {
        return $this->user();
    }
}
