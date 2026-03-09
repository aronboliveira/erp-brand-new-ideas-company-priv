<?php

namespace App\Enums;

enum FileCategory: string
{
	case Document = 'document';
	case Spreadsheet = 'spreadsheet';
	case Presentation = 'presentation';
	case Image = 'image';
	case Audio = 'audio';
	case Video = 'video';
	case Archive = 'archive';
	case Code = 'code';
	case Font = 'font';
	case Other = 'other';

	public function label(): string
	{
		return match ($this) {
			self::Document     => 'Document',
			self::Spreadsheet  => 'Spreadsheet',
			self::Presentation => 'Presentation',
			self::Image        => 'Image',
			self::Audio        => 'Audio',
			self::Video        => 'Video',
			self::Archive      => 'Archive',
			self::Code         => 'Code',
			self::Font         => 'Font',
			self::Other        => 'Other',
		};
	}

	public function isMedia(): bool
	{
		return in_array($this, [self::Image, self::Audio, self::Video], true);
	}

	public static function fromMimeType(MimeType $mime): self
	{
		$value = $mime->value;
		if (str_starts_with($value, 'image/')) return self::Image;
		if (str_starts_with($value, 'audio/')) return self::Audio;
		if (str_starts_with($value, 'video/')) return self::Video;
		if (str_starts_with($value, 'font/')) return self::Font;

		return match ($mime) {
			MimeType::APPLICATION_PDF,
			MimeType::APPLICATION_MSWORD,
			MimeType::APPLICATION_DOCX,
			MimeType::APPLICATION_RTF,
			MimeType::APPLICATION_ODT     => self::Document,

			MimeType::APPLICATION_MSEXCEL,
			MimeType::APPLICATION_XLSX,
			MimeType::APPLICATION_ODS,
			MimeType::TEXT_CSV            => self::Spreadsheet,

			MimeType::APPLICATION_MSPPT,
			MimeType::APPLICATION_PPTX,
			MimeType::APPLICATION_ODP     => self::Presentation,

			MimeType::APPLICATION_ZIP,
			MimeType::APPLICATION_7Z,
			MimeType::APPLICATION_RAR,
			MimeType::APPLICATION_GZIP,
			MimeType::APPLICATION_TAR     => self::Archive,

			MimeType::TEXT_PHP,
			MimeType::TEXT_PYTHON,
			MimeType::TEXT_JAVA,
			MimeType::TEXT_CPP,
			MimeType::TEXT_CSHARP,
			MimeType::TEXT_RUBY,
			MimeType::TEXT_SHELL,
			MimeType::TEXT_CSS,
			MimeType::TEXT_JS,
			MimeType::TEXT_HTML,
			MimeType::TEXT_YAML,
			MimeType::TEXT_TOML,
			MimeType::APPLICATION_JSON,
			MimeType::APPLICATION_XML,
			MimeType::APPLICATION_SQL,
			MimeType::APPLICATION_JAVASCRIPT,
			MimeType::APPLICATION_XHTML   => self::Code,

			default => self::Other,
		};
	}

	public static function normalize(mixed $value): ?self
	{
		if ($value instanceof self) return $value;
		if (!is_string($value)) return null;
		$lower = strtolower(trim($value));
		foreach (self::cases() as $case) {
			if ($case->value === $lower) return $case;
		}
		return null;
	}
}
