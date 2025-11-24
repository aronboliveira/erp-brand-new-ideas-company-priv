<?php

namespace App\Enums;

use BackedEnum;

enum EvaluationStatus: string
{
	case Draft     = 'draft';
	case Pending   = 'pending';
	case Active    = 'active';
	case Suspended = 'suspended';
	case Completed = 'completed';
	case Cancelled = 'cancelled';
	case Expired   = 'expired';
	case Archived  = 'archived';
	case Undefined = 'undefined';
	case Accept    = 'accept';
	case Decline   = 'decline';

	public static function normalize(null|string|BackedEnum $v): self
	{
		if ($v instanceof self) return $v;
		if ($v === null) return self::Undefined;

		$k = strtolower(trim((string) $v));

		$aliases = [
			'canceled'    => 'cancelled',
			'complete'    => 'completed',
			'finished'    => 'completed',
			'in_progress' => 'active',
			'rascunho'    => 'draft',
			'pendente'    => 'pending',
			'ativo'       => 'active',
			'cancelado'   => 'cancelled',
			'concluido'   => 'completed',
			'expirado'    => 'expired',
			'arquivado'   => 'archived',
			'aceito'     => 'accept',
			'recusado'     => 'decline',
		];

		$k = $aliases[$k] ?? $k;

		return self::tryFrom($k);
	}

	public static function labels(): array
	{
		return [
			self::Draft->value     => 'Draft',
			self::Pending->value   => 'Pending',
			self::Active->value    => 'Active',
			self::Suspended->value => 'Suspended',
			self::Completed->value => 'Completed',
			self::Cancelled->value => 'Cancelled',
			self::Expired->value   => 'Expired',
			self::Archived->value  => 'Archived',
			self::Accept->value    => 'Accept', // TODO ajuste de gramática posterior
			self::Decline->value   => 'Decline',
		];
	}
}
