<?php

namespace App\Models;

use App\Enums\DocumentKind;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class AbstractDocument extends AbstractFile
{
	protected $appends = [
		'document_kind',
		'document_kind_label',
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
