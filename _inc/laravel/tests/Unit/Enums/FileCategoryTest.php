<?php

namespace Tests\Unit\Enums;

use App\Enums\FileCategory;
use App\Enums\MimeType;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(FileCategory::class)]
#[Group('enums')]
#[Group('file-category')]
class FileCategoryTest extends TestCase
{
	// ───────── Cases ─────────

	#[Test]
	public function it_has_exactly_ten_cases(): void
	{
		$this->assertCount(10, FileCategory::cases());
	}

	#[Test]
	public function case_values_are_expected(): void
	{
		$expected = [
			'Document',
			'Spreadsheet',
			'Presentation',
			'Image',
			'Audio',
			'Video',
			'Archive',
			'Code',
			'Font',
			'Other',
		];
		$names = array_map(fn($c) => $c->name, FileCategory::cases());
		$this->assertEqualsCanonicalizing($expected, $names);
	}

	// ───────── isMedia() ─────────

	#[Test]
	public function is_media_true_for_image_audio_video(): void
	{
		$this->assertTrue(FileCategory::Image->isMedia());
		$this->assertTrue(FileCategory::Audio->isMedia());
		$this->assertTrue(FileCategory::Video->isMedia());
	}

	#[Test]
	public function is_media_false_for_non_media(): void
	{
		$nonMedia = [
			FileCategory::Document,
			FileCategory::Spreadsheet,
			FileCategory::Presentation,
			FileCategory::Archive,
			FileCategory::Code,
			FileCategory::Font,
			FileCategory::Other,
		];
		foreach ($nonMedia as $cat) {
			$this->assertFalse($cat->isMedia(), "{$cat->name} should not be media");
		}
	}

	// ───────── fromMimeType() — cross-enum conversion ─────────

	public static function fromMimeTypeProvider(): array
	{
		return [
			// Images
			[MimeType::IMAGE_JPEG, FileCategory::Image],
			[MimeType::IMAGE_PNG, FileCategory::Image],
			[MimeType::IMAGE_GIF, FileCategory::Image],
			[MimeType::IMAGE_SVG, FileCategory::Image],
			[MimeType::IMAGE_WEBP, FileCategory::Image],
			[MimeType::IMAGE_BMP, FileCategory::Image],
			// Audio
			[MimeType::AUDIO_MPEG, FileCategory::Audio],
			[MimeType::AUDIO_WAV, FileCategory::Audio],
			[MimeType::AUDIO_OGG, FileCategory::Audio],
			[MimeType::AUDIO_FLAC, FileCategory::Audio],
			// Video
			[MimeType::VIDEO_MP4, FileCategory::Video],
			[MimeType::VIDEO_WEBM, FileCategory::Video],
			[MimeType::VIDEO_AVI, FileCategory::Video],
			[MimeType::VIDEO_QUICKTIME, FileCategory::Video],
			// Documents
			[MimeType::APPLICATION_PDF, FileCategory::Document],
			[MimeType::APPLICATION_MSWORD, FileCategory::Document],
			[MimeType::APPLICATION_DOCX, FileCategory::Document],
			[MimeType::APPLICATION_RTF, FileCategory::Document],
			[MimeType::APPLICATION_ODT, FileCategory::Document],
			// Spreadsheets
			[MimeType::APPLICATION_MSEXCEL, FileCategory::Spreadsheet],
			[MimeType::APPLICATION_XLSX, FileCategory::Spreadsheet],
			[MimeType::APPLICATION_ODS, FileCategory::Spreadsheet],
			[MimeType::TEXT_CSV, FileCategory::Spreadsheet],
			// Presentations
			[MimeType::APPLICATION_MSPPT, FileCategory::Presentation],
			[MimeType::APPLICATION_PPTX, FileCategory::Presentation],
			[MimeType::APPLICATION_ODP, FileCategory::Presentation],
			// Archives
			[MimeType::APPLICATION_ZIP, FileCategory::Archive],
			[MimeType::APPLICATION_GZIP, FileCategory::Archive],
			[MimeType::APPLICATION_TAR, FileCategory::Archive],
			[MimeType::APPLICATION_RAR, FileCategory::Archive],
			[MimeType::APPLICATION_7Z, FileCategory::Archive],
			// Code
			[MimeType::TEXT_JS, FileCategory::Code],
			[MimeType::TEXT_PHP, FileCategory::Code],
			[MimeType::APPLICATION_JSON, FileCategory::Code],
			[MimeType::APPLICATION_XML, FileCategory::Code],
			[MimeType::TEXT_HTML, FileCategory::Code],
			[MimeType::TEXT_CSS, FileCategory::Code],
			[MimeType::APPLICATION_JAVASCRIPT, FileCategory::Code],
			// Fonts
			[MimeType::FONT_WOFF, FileCategory::Font],
			[MimeType::FONT_WOFF2, FileCategory::Font],
			[MimeType::FONT_TTF, FileCategory::Font],
			[MimeType::FONT_OTF, FileCategory::Font],
		];
	}

	#[Test]
	#[DataProvider('fromMimeTypeProvider')]
	public function from_mime_type_resolves_correctly(MimeType $mime, FileCategory $expected): void
	{
		$this->assertSame($expected, FileCategory::fromMimeType($mime));
	}

	#[Test]
	public function from_mime_type_always_returns_a_category(): void
	{
		foreach (MimeType::cases() as $mime) {
			$result = FileCategory::fromMimeType($mime);
			$this->assertInstanceOf(FileCategory::class, $result, "No category for {$mime->name}");
		}
	}

	// ───────── normalize() ─────────

	#[Test]
	public function normalize_returns_self_for_instance(): void
	{
		foreach (FileCategory::cases() as $case) {
			$this->assertSame($case, FileCategory::normalize($case));
		}
	}

	#[Test]
	public function normalize_resolves_string_values(): void
	{
		foreach (FileCategory::cases() as $case) {
			$this->assertSame($case, FileCategory::normalize($case->value));
		}
	}

	#[Test]
	public function normalize_returns_null_for_unknown(): void
	{
		$this->assertNull(FileCategory::normalize('nonsense_garbage'));
	}

	#[Test]
	public function normalize_returns_null_for_null(): void
	{
		$this->assertNull(FileCategory::normalize(null));
	}

	// ───────── Cross-enum consistency with MimeType classification ─────────

	#[Test]
	public function image_mime_types_map_to_image_category(): void
	{
		foreach (MimeType::cases() as $mime) {
			if ($mime->isImage()) {
				$this->assertSame(
					FileCategory::Image,
					FileCategory::fromMimeType($mime),
					"Image MimeType {$mime->name} should map to Image category"
				);
			}
		}
	}

	#[Test]
	public function audio_mime_types_map_to_audio_category(): void
	{
		foreach (MimeType::cases() as $mime) {
			if ($mime->isAudio()) {
				$this->assertSame(
					FileCategory::Audio,
					FileCategory::fromMimeType($mime),
					"Audio MimeType {$mime->name} should map to Audio category"
				);
			}
		}
	}

	#[Test]
	public function video_mime_types_map_to_video_category(): void
	{
		foreach (MimeType::cases() as $mime) {
			if ($mime->isVideo()) {
				$this->assertSame(
					FileCategory::Video,
					FileCategory::fromMimeType($mime),
					"Video MimeType {$mime->name} should map to Video category"
				);
			}
		}
	}

	#[Test]
	public function font_mime_types_map_to_font_category(): void
	{
		foreach (MimeType::cases() as $mime) {
			if ($mime->isFont()) {
				$this->assertSame(
					FileCategory::Font,
					FileCategory::fromMimeType($mime),
					"Font MimeType {$mime->name} should map to Font category"
				);
			}
		}
	}

	// ───────── Performance ─────────

	#[Test]
	public function from_mime_type_performance(): void
	{
		$samples = [MimeType::TEXT_PLAIN, MimeType::IMAGE_JPEG, MimeType::AUDIO_MPEG, MimeType::VIDEO_MP4, MimeType::APPLICATION_ZIP, MimeType::FONT_WOFF];
		$start = hrtime(true);
		for ($i = 0; $i < 1000; $i++) {
			foreach ($samples as $mime) {
				FileCategory::fromMimeType($mime);
			}
		}
		$perCall = (hrtime(true) - $start) / 1e6 / (1000 * count($samples));
		$this->assertLessThan(1.0, $perCall);
	}
}
