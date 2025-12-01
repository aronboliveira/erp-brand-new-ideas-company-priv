<?php

namespace App\Models;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Enums\UserType;
use App\Traits\{HasAuditFields, UsesUuids};
use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Support\{Collection, Str, Facades\Log};

class PayslipType extends Model
{
    use UsesUuids, HasAuditFields;

    protected $fillable = ['name', 'description', BC::COL_MIN_AMT, BC::COL_MAX_AMT, BC::COL_RL_APL];
    protected $guarded = ['id', DC::COL_TABLE_CREATOR];
    protected $casts    = [
        BC::COL_MIN_AMT => 'decimal:2',
        BC::COL_MAX_AMT => 'decimal:2',
    ];

    protected static function booted(): void
    {
        parent::booted();
        $normalizeAmount = static function (string|int|float|null $v): ?string {
            if ($v === null || $v === '') return null;
            $raw = (string) $v;
            $raw = str_replace([' ', '.'], ['', ''], $raw);
            $raw = str_replace(',', '.', $raw);
            if (!is_numeric($raw)) {
                throw new \InvalidArgumentException('Valor monetário inválido.');
            }
            $num = (float) $raw;
            if ($num < 0) {
                throw new \DomainException('Valor não pode ser negativo.');
            }
            return number_format($num, 2, '.', '');
        };

        $validateMinMax = static function (self $m): void {
            $min = $m->{BC::COL_MIN_AMT};
            $max = $m->{BC::COL_MAX_AMT};
            if ($min !== null && $max !== null && (float) $min > (float) $max) {
                throw new \DomainException('Faixa inválida: min_amount não pode ser maior que max_amount.');
            }
        };

        static::creating(function (self $m) use ($normalizeAmount, $validateMinMax) {
            $m->{BC::COL_MIN_AMT} = $normalizeAmount($m->{BC::COL_MIN_AMT} ?? null);
            $m->{BC::COL_MAX_AMT} = $normalizeAmount($m->{BC::COL_MAX_AMT} ?? null);
            if (is_string($m->name)) {
                $m->name = Str::of($m->name)->squish()->limit(150)->toString();
            }
            $validateMinMax($m);
        });

        static::updating(function (self $m) use ($normalizeAmount, $validateMinMax) {
            if ($m->isDirty(BC::COL_MIN_AMT)) {
                $m->{BC::COL_MIN_AMT} = $normalizeAmount($m->{BC::COL_MIN_AMT} ?? null);
            }
            if ($m->isDirty(BC::COL_MAX_AMT)) {
                $m->{BC::COL_MAX_AMT} = $normalizeAmount($m->{BC::COL_MAX_AMT} ?? null);
            }
            if ($m->isDirty('name') && is_string($m->name)) {
                $m->name = Str::of($m->name)->squish()->limit(150)->toString();
            }
            $validateMinMax($m);
        });
    }

    public function setRolesApplicableAttribute(array|Collection|string|null $value): void
    {
        if ($value === null) {
            $this->attributes[BC::COL_RL_APL] = null;
            return;
        }
        $col = is_array($value)
            ? collect($value)
            : (is_string($value) ? collect(explode(',', $value)) : ($value instanceof Collection ? $value : collect()));

        if ($col->isEmpty()) {
            $this->attributes[BC::COL_RL_APL] = null;
            return;
        }

        $normalized = $col
            ->map(fn($r) => UserType::normalize($r)?->value)
            ->filter()
            ->unique()
            ->values();

        if ($normalized->isEmpty()) {
            // ! Nenhum papel válido: mantém nulo (aplicável a todos) para evitar bloquear indevidamente
            $this->attributes[BC::COL_RL_APL] = null;
            return;
        }

        $this->attributes[BC::COL_RL_APL] = $normalized->implode(',');
    }

    public function setRoleAsApplicable(string $role): void
    {
        $norm = UserType::normalize($role)?->value;
        if (!$norm) {
            Log::warning("Tentativa de aplicar role inválido '{$role}' ao PayslipType {$this->id}");
            return;
        }

        $current = $this->attributes[BC::COL_RL_APL] ?? null;
        if ($current === null || $current === '') {
            $this->attributes[BC::COL_RL_APL] = $norm;
            return;
        }

        $roles = explode(',', $current);
        if (!in_array($norm, $roles, true)) {
            $roles[] = $norm;
            $this->attributes[BC::COL_RL_APL] = implode(',', $roles);
        }
    }

    public function getRolesApplicableListAttribute(): array
    {
        $raw = $this->attributes[BC::COL_RL_APL] ?? null;
        if ($raw === null || $raw === '') return [];
        return array_values(array_filter(
            array_map(fn($r) => UserType::normalize($r)?->value, explode(',', $raw))
        ));
    }

    public function appliesToRole(UserType|string|null $role): bool
    {
        $raw = $this->attributes[BC::COL_RL_APL] ?? null;
        if ($raw === null || $raw === '')
            return true;
        $norm = $role instanceof UserType ? $role->value : (UserType::normalize($role)?->value);
        if (!$norm) return false;
        return in_array($norm, explode(',', $raw), true);
    }

    public function amountWithinRange(int|float|string $amount): bool
    {
        $a = is_string($amount)
            ? (float) str_replace(',', '.', str_replace([' ', '.'], ['', ''], $amount))
            : (float) $amount;

        $min = $this->{BC::COL_MIN_AMT} !== null ? (float) $this->{BC::COL_MIN_AMT} : null;
        $max = $this->{BC::COL_MAX_AMT} !== null ? (float) $this->{BC::COL_MAX_AMT} : null;

        if ($min !== null && $a < $min) return false;
        if ($max !== null && $a > $max) return false;
        return true;
    }

    public function scopeApplicableToRole(Builder $q, UserType|string|null $role): Builder
    {
        $norm = $role instanceof UserType ? $role->value : (UserType::normalize($role)?->value);
        if (!$norm) return $q->whereRaw('1 = 0');
        // ? Se a folha é inválida, garante retorno vazio para não “abrir” o filtro
        return $q->where(function (Builder $w) use ($norm) {
            $w->whereNull(BC::COL_RL_APL)
                ->orWhere(BC::COL_RL_APL, '=', '')
                ->orWhereRaw("FIND_IN_SET(?, " . BC::COL_RL_APL . ")", [$norm]);
        });
    }

    public function scopeForAmount(Builder $q, int|float|string $amount): Builder
    {
        $a = is_string($amount)
            ? (float) str_replace(',', '.', str_replace([' ', '.'], ['', ''], $amount))
            : (float) $amount;
        return $q->where(function (Builder $w) use ($a) {
            $w->whereNull(BC::COL_MIN_AMT)->orWhere(BC::COL_MIN_AMT, '<=', $a);
        })->where(function (Builder $w) use ($a) {
            $w->whereNull(BC::COL_MAX_AMT)->orWhere(BC::COL_MAX_AMT, '>=', $a);
        });
    }
}
