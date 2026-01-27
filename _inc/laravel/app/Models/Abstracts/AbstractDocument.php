<?php

namespace App\Models;

use App\Enums\DocumentKind;
use App\Helpers\DocumentKindCast;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class AbstractDocument extends AbstractFile
{
	protected const ABSTRACT_DOCUMENT_APPENDS = [
		...self::ABSTRACT_FILE_APPENDS,
		'document_kind',
		'document_kind_label',
	];
	protected $appends = [
		...self::ABSTRACT_DOCUMENT_APPENDS,
	];
	protected const ABSTRACT_DOCUMENT_CASTS = [
		...self::ABSTRACT_FILE_CASTS,
		'type' => DocumentKindCast::class,
	];
	protected $casts = [
		...self::ABSTRACT_DOCUMENT_CASTS,
	];

	protected static function booted(): void
	{
		parent::booted();
		static::saving(function (self $m): void {
			try {
				$m->ensureDocumentKind();
			} catch (Throwable $e) {
				Log::warning(static::class . ' ensureDocumentKind failed', [
					'id'    => $m->getAttribute('id'),
					'error' => $e->getMessage(),
				]);
			}
		});
	}

	protected function ensureDocumentKind(): void
	{
		if (!$this->getIsDocumentAttribute()) {
			$this->setAttribute('type', null);
			return;
		}
		$type = $this->getAttribute('type');
		if (is_string($type) && DocumentKind::normalize($type))
			return;
		$ext = (string) ($this->getAttribute('extension') ?? '');
		$kind = $ext !== '' ? DocumentKind::fromExtension($ext) : null;
		$this->setAttribute('type', ($kind ?? DocumentKind::OTHER)->value);
	}

	public function getDocumentKindAttribute(): ?DocumentKind
	{
		if (!$this->getIsDocumentAttribute()) return null;

		$type = $this->getAttribute('type');
		if (!is_string($type)) return null;

		return DocumentKind::normalize($type);
	}

	public function getDocumentKindLabelAttribute(): ?string
	{
		$k = $this->getDocumentKindAttribute();
		return $k ? $k->label() : null;
	}
}
