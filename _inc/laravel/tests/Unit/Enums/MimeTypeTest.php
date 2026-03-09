<?php

namespace Tests\Unit\Enums;

use App\Enums\MimeType;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(MimeType::class)]
#[Group('enums')]
#[Group('mime-type')]
class MimeTypeTest extends TestCase
{
	// ───────── Cases ─────────

	#[Test]
	public function it_has_at_least_60_cases(): void
	{
		$this->assertGreaterThanOrEqual(60, count(MimeType::cases()));
	}

	// ───────── fromExtension() ─────────

	public static function fromExtensionProvider(): array
	{
		return [
			// Text / markup
			['txt', MimeType::TEXT_PLAIN],
			['html', MimeType::TEXT_HTML],
			['htm', MimeType::TEXT_HTML],
			['css', MimeType::TEXT_CSS],
			['csv', MimeType::TEXT_CSV],
			['xml', MimeType::APPLICATION_XML],
			['md', MimeType::TEXT_MARKDOWN],
			['json', MimeType::APPLICATION_JSON],
			['sql', MimeType::APPLICATION_SQL],
			// Images
			['jpg', MimeType::IMAGE_JPEG],
			['jpeg', MimeType::IMAGE_JPEG],
			['png', MimeType::IMAGE_PNG],
			['gif', MimeType::IMAGE_GIF],
			['svg', MimeType::IMAGE_SVG],
			['webp', MimeType::IMAGE_WEBP],
			['bmp', MimeType::IMAGE_BMP],
			['tiff', MimeType::IMAGE_TIFF],
			// Audio
			['mp3', MimeType::AUDIO_MPEG],
			['wav', MimeType::AUDIO_WAV],
			['ogg', MimeType::AUDIO_OGG],
			['flac', MimeType::AUDIO_FLAC],
			['aac', MimeType::AUDIO_AAC],
			// Video
			['mp4', MimeType::VIDEO_MP4],
			['webm', MimeType::VIDEO_WEBM],
			['avi', MimeType::VIDEO_AVI],
			['mov', MimeType::VIDEO_QUICKTIME],
			['mpeg', MimeType::VIDEO_MPEG],
			['ogv', MimeType::VIDEO_OGG],
			// Documents / Office
			['pdf', MimeType::APPLICATION_PDF],
			['zip', MimeType::APPLICATION_ZIP],
			['gz', MimeType::APPLICATION_GZIP],
			['tar', MimeType::APPLICATION_TAR],
			['rar', MimeType::APPLICATION_RAR],
			['7z', MimeType::APPLICATION_7Z],
			// Microsoft Office
			['doc', MimeType::APPLICATION_MSWORD],
			['docx', MimeType::APPLICATION_DOCX],
			['xls', MimeType::APPLICATION_MSEXCEL],
			['xlsx', MimeType::APPLICATION_XLSX],
			['ppt', MimeType::APPLICATION_MSPPT],
			['pptx', MimeType::APPLICATION_PPTX],
			['rtf', MimeType::APPLICATION_RTF],
			// Code
			['js', MimeType::TEXT_JS],
			['php', MimeType::TEXT_PHP],
			['py', MimeType::TEXT_PYTHON],
			['java', MimeType::TEXT_JAVA],
			['rb', MimeType::TEXT_RUBY],
			['sh', MimeType::TEXT_SHELL],
			['yaml', MimeType::TEXT_YAML],
			['toml', MimeType::TEXT_TOML],
			// Font
			['woff', MimeType::FONT_WOFF],
			['woff2', MimeType::FONT_WOFF2],
			['ttf', MimeType::FONT_TTF],
			['otf', MimeType::FONT_OTF],
		];
	}

	#[Test]
	#[DataProvider('fromExtensionProvider')]
	public function from_extension_resolves_correctly(string $ext, MimeType $expected): void
	{
		$this->assertSame($expected, MimeType::fromExtension($ext));
	}

	#[Test]
	public function from_extension_is_case_insensitive(): void
	{
		$this->assertSame(MimeType::IMAGE_JPEG, MimeType::fromExtension('JPG'));
		$this->assertSame(MimeType::APPLICATION_PDF, MimeType::fromExtension('PDF'));
	}

	#[Test]
	public function from_extension_returns_null_for_unknown(): void
	{
		$this->assertNull(MimeType::fromExtension('xyz_garbage'));
	}

	#[Test]
	public function from_extension_strips_leading_dot(): void
	{
		$this->assertSame(MimeType::IMAGE_PNG, MimeType::fromExtension('.png'));
	}

	// ───────── getExtension() round-trip ─────────

	#[Test]
	public function get_extension_returns_non_empty_string_for_all_cases(): void
	{
		foreach (MimeType::cases() as $case) {
			$ext = $case->getExtension();
			$this->assertIsString($ext);
			$this->assertNotEmpty($ext, "Extension empty for {$case->name}");
		}
	}

	#[Test]
	public function get_extension_round_trips_for_common_types(): void
	{
		$commonTypes = [
			MimeType::TEXT_PLAIN,
			MimeType::TEXT_HTML,
			MimeType::IMAGE_JPEG,
			MimeType::IMAGE_PNG,
			MimeType::APPLICATION_PDF,
			MimeType::APPLICATION_JSON,
			MimeType::APPLICATION_ZIP,
			MimeType::AUDIO_MPEG,
			MimeType::VIDEO_MP4,
		];

		foreach ($commonTypes as $type) {
			$ext = $type->getExtension();
			$resolved = MimeType::fromExtension($ext);
			$this->assertSame($type, $resolved, "Round-trip failed for {$type->name}: ext={$ext}");
		}
	}

	// ───────── normalize() — accepts string only ─────────

	#[Test]
	public function normalize_resolves_known_mime_strings(): void
	{
		$this->assertSame(MimeType::TEXT_PLAIN, MimeType::normalize('text/plain'));
		$this->assertSame(MimeType::IMAGE_JPEG, MimeType::normalize('image/jpeg'));
		$this->assertSame(MimeType::APPLICATION_JSON, MimeType::normalize('application/json'));
	}

	#[Test]
	public function normalize_strips_parameters(): void
	{
		$result = MimeType::normalize('text/plain; charset=utf-8');
		$this->assertSame(MimeType::TEXT_PLAIN, $result);
	}

	#[Test]
	public function normalize_returns_null_for_unknown_string(): void
	{
		$this->assertNull(MimeType::normalize('totally/unknown'));
	}

	#[Test]
	public function normalize_resolves_aliases(): void
	{
		$this->assertSame(MimeType::IMAGE_JPEG, MimeType::normalize('image/jpg'));
		$this->assertSame(MimeType::IMAGE_PNG, MimeType::normalize('image/x-png'));
		$this->assertSame(MimeType::TEXT_JS, MimeType::normalize('application/x-javascript'));
		$this->assertSame(MimeType::AUDIO_MPEG, MimeType::normalize('audio/mp3'));
	}

	// ───────── Classification: isText() ─────────

	#[Test]
	public function is_text_true_for_text_types(): void
	{
		$this->assertTrue(MimeType::TEXT_PLAIN->isText());
		$this->assertTrue(MimeType::TEXT_HTML->isText());
		$this->assertTrue(MimeType::TEXT_CSS->isText());
		$this->assertTrue(MimeType::TEXT_CSV->isText());
		$this->assertTrue(MimeType::APPLICATION_JSON->isText());
		$this->assertTrue(MimeType::APPLICATION_XML->isText());
	}

	#[Test]
	public function is_text_false_for_non_text_types(): void
	{
		$this->assertFalse(MimeType::IMAGE_JPEG->isText());
		$this->assertFalse(MimeType::AUDIO_MPEG->isText());
		$this->assertFalse(MimeType::VIDEO_MP4->isText());
	}

	// ───────── Classification: isImage() ─────────

	#[Test]
	public function is_image_true_for_image_types(): void
	{
		$this->assertTrue(MimeType::IMAGE_JPEG->isImage());
		$this->assertTrue(MimeType::IMAGE_PNG->isImage());
		$this->assertTrue(MimeType::IMAGE_GIF->isImage());
		$this->assertTrue(MimeType::IMAGE_SVG->isImage());
		$this->assertTrue(MimeType::IMAGE_WEBP->isImage());
		$this->assertTrue(MimeType::IMAGE_BMP->isImage());
	}

	#[Test]
	public function is_image_false_for_non_image_types(): void
	{
		$this->assertFalse(MimeType::TEXT_PLAIN->isImage());
		$this->assertFalse(MimeType::AUDIO_MPEG->isImage());
		$this->assertFalse(MimeType::APPLICATION_PDF->isImage());
	}

	// ───────── Classification: isAudio() ─────────

	#[Test]
	public function is_audio_true_for_audio_types(): void
	{
		$this->assertTrue(MimeType::AUDIO_MPEG->isAudio());
		$this->assertTrue(MimeType::AUDIO_WAV->isAudio());
		$this->assertTrue(MimeType::AUDIO_OGG->isAudio());
		$this->assertTrue(MimeType::AUDIO_FLAC->isAudio());
	}

	#[Test]
	public function is_audio_false_for_non_audio_types(): void
	{
		$this->assertFalse(MimeType::VIDEO_MP4->isAudio());
		$this->assertFalse(MimeType::TEXT_PLAIN->isAudio());
	}

	// ───────── Classification: isVideo() ─────────

	#[Test]
	public function is_video_true_for_video_types(): void
	{
		$this->assertTrue(MimeType::VIDEO_MP4->isVideo());
		$this->assertTrue(MimeType::VIDEO_WEBM->isVideo());
		$this->assertTrue(MimeType::VIDEO_AVI->isVideo());
		$this->assertTrue(MimeType::VIDEO_MPEG->isVideo());
		$this->assertTrue(MimeType::VIDEO_OGG->isVideo());
	}

	#[Test]
	public function is_video_false_for_non_video_types(): void
	{
		$this->assertFalse(MimeType::AUDIO_MPEG->isVideo());
		$this->assertFalse(MimeType::IMAGE_PNG->isVideo());
	}

	// ───────── Classification: isArchive() ─────────

	#[Test]
	public function is_archive_true_for_archive_types(): void
	{
		$this->assertTrue(MimeType::APPLICATION_ZIP->isArchive());
		$this->assertTrue(MimeType::APPLICATION_GZIP->isArchive());
		$this->assertTrue(MimeType::APPLICATION_TAR->isArchive());
		$this->assertTrue(MimeType::APPLICATION_RAR->isArchive());
		$this->assertTrue(MimeType::APPLICATION_7Z->isArchive());
	}

	#[Test]
	public function is_archive_false_for_non_archive_types(): void
	{
		$this->assertFalse(MimeType::APPLICATION_PDF->isArchive());
		$this->assertFalse(MimeType::TEXT_PLAIN->isArchive());
	}

	// ───────── Classification: isDocument() ─────────

	#[Test]
	public function is_document_true_for_document_types(): void
	{
		$this->assertTrue(MimeType::APPLICATION_PDF->isDocument());
		$this->assertTrue(MimeType::APPLICATION_MSWORD->isDocument());
		$this->assertTrue(MimeType::APPLICATION_DOCX->isDocument());
		$this->assertTrue(MimeType::APPLICATION_MSEXCEL->isDocument());
		$this->assertTrue(MimeType::APPLICATION_XLSX->isDocument());
		$this->assertTrue(MimeType::TEXT_PLAIN->isDocument());
	}

	#[Test]
	public function is_document_false_for_non_document_types(): void
	{
		$this->assertFalse(MimeType::IMAGE_PNG->isDocument());
		$this->assertFalse(MimeType::VIDEO_MP4->isDocument());
	}

	// ───────── Classification: isCode() ─────────

	#[Test]
	public function is_code_true_for_code_types(): void
	{
		$this->assertTrue(MimeType::TEXT_JS->isCode());
		$this->assertTrue(MimeType::TEXT_PHP->isCode());
		$this->assertTrue(MimeType::APPLICATION_JSON->isCode());
		$this->assertTrue(MimeType::APPLICATION_XML->isCode());
		$this->assertTrue(MimeType::TEXT_HTML->isCode());
		$this->assertTrue(MimeType::TEXT_CSS->isCode());
	}

	// ───────── Classification: isFont() ─────────

	#[Test]
	public function is_font_true_for_font_types(): void
	{
		$this->assertTrue(MimeType::FONT_WOFF->isFont());
		$this->assertTrue(MimeType::FONT_WOFF2->isFont());
		$this->assertTrue(MimeType::FONT_TTF->isFont());
		$this->assertTrue(MimeType::FONT_OTF->isFont());
	}

	#[Test]
	public function is_font_false_for_non_font_types(): void
	{
		$this->assertFalse(MimeType::TEXT_PLAIN->isFont());
		$this->assertFalse(MimeType::IMAGE_PNG->isFont());
	}

	// ───────── Classification: mutual exclusiveness ─────────

	#[Test]
	public function image_audio_video_are_mutually_exclusive(): void
	{
		foreach (MimeType::cases() as $case) {
			$count = ($case->isImage() ? 1 : 0) + ($case->isAudio() ? 1 : 0) + ($case->isVideo() ? 1 : 0);
			$this->assertLessThanOrEqual(
				1,
				$count,
				"{$case->name} overlaps image/audio/video"
			);
		}
	}

	// ───────── getAllExtensions() — instance method ─────────

	#[Test]
	public function get_all_extensions_returns_non_empty_for_common_types(): void
	{
		$typesWithMultiple = [
			MimeType::TEXT_PLAIN,
			MimeType::IMAGE_JPEG,
			MimeType::TEXT_PHP,
			MimeType::TEXT_SHELL,
		];
		foreach ($typesWithMultiple as $type) {
			$exts = $type->getAllExtensions();
			$this->assertIsArray($exts);
			$this->assertGreaterThan(1, count($exts), "{$type->name} should have multiple extensions");
		}
	}

	#[Test]
	public function get_all_extensions_each_resolves_back(): void
	{
		$typesToCheck = [
			MimeType::TEXT_PLAIN,
			MimeType::IMAGE_JPEG,
			MimeType::AUDIO_MPEG,
			MimeType::TEXT_PHP,
			MimeType::TEXT_SHELL,
		];
		foreach ($typesToCheck as $type) {
			foreach ($type->getAllExtensions() as $ext) {
				$resolved = MimeType::fromExtension($ext);
				$this->assertSame($type, $resolved, "Extension '{$ext}' did not resolve back to {$type->name}");
			}
		}
	}

	// ───────── Performance ─────────

	#[Test]
	public function from_extension_performance(): void
	{
		$exts = ['txt', 'jpg', 'png', 'mp3', 'mp4', 'pdf', 'zip', 'docx', 'woff2', 'php', 'unknown_ext'];
		$start = hrtime(true);
		for ($i = 0; $i < 1000; $i++) {
			foreach ($exts as $ext) {
				MimeType::fromExtension($ext);
			}
		}
		$perCall = (hrtime(true) - $start) / 1e6 / (1000 * count($exts));
		$this->assertLessThan(1.0, $perCall, "fromExtension() averaged {$perCall}ms per call");
	}

	#[Test]
	public function classification_performance(): void
	{
		$samples = [MimeType::TEXT_PLAIN, MimeType::IMAGE_JPEG, MimeType::AUDIO_MPEG, MimeType::VIDEO_MP4, MimeType::APPLICATION_ZIP, MimeType::FONT_WOFF];
		$start = hrtime(true);
		for ($i = 0; $i < 1000; $i++) {
			foreach ($samples as $mime) {
				$mime->isText();
				$mime->isImage();
				$mime->isAudio();
				$mime->isVideo();
				$mime->isArchive();
				$mime->isDocument();
				$mime->isCode();
				$mime->isFont();
			}
		}
		$perCall = (hrtime(true) - $start) / 1e6 / (1000 * count($samples) * 8);
		$this->assertLessThan(1.0, $perCall);
	}
}
