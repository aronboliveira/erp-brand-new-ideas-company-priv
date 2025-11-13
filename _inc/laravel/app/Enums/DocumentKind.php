<?php

namespace App\Enums;

enum DocumentKind: string
{
	case TEXT_PLAIN         = 'texto simples';
	case TEXT_MARKDOWN      = 'markdown';
	case TEXT_CSV           = 'planilha CSV';
	case TEXT_HTML          = 'documento HTML';
	case TEXT_CSS           = 'folha de estilo CSS';
	case TEXT_JS            = 'script JavaScript';
	case APPLICATION_JSON   = 'documento JSON';
	case APPLICATION_XML    = 'documento XML';
	case APPLICATION_SQL    = 'script SQL';

	case APPLICATION_PDF    = 'documento PDF';

	case IMAGE_PNG          = 'imagem PNG';
	case IMAGE_JPEG         = 'imagem JPEG';
	case IMAGE_GIF          = 'imagem GIF';
	case IMAGE_SVG          = 'imagem SVG';

	case AUDIO_MPEG         = 'áudio MPEG';
	case AUDIO_OGG          = 'áudio OGG';
	case AUDIO_WAV          = 'áudio WAV';

	case VIDEO_MP4          = 'vídeo MP4';
	case VIDEO_WEBM         = 'vídeo WebM';
	case VIDEO_QUICKTIME    = 'vídeo QuickTime';
	case VIDEO_AVI          = 'vídeo AVI';

	case APPLICATION_ZIP    = 'arquivo ZIP';
	case APPLICATION_7Z     = 'arquivo 7z';
	case APPLICATION_RAR    = 'arquivo RAR';

	case APPLICATION_MSWORD = 'documento Word';
	case APPLICATION_DOCX   = 'documento Word (OOXML)';
	case APPLICATION_MSEXCEL = 'planilha Excel';
	case APPLICATION_XLSX   = 'planilha Excel (OOXML)';
	case APPLICATION_MSPPT  = 'apresentação PowerPoint';
	case APPLICATION_PPTX   = 'apresentação PowerPoint (OOXML)';

	case APPLICATION_SQLITE = 'banco SQLite';
	case TEXT_PHP           = 'código PHP';

	public static function fromExtension(string $ext): ?self
	{
		$e = strtolower(ltrim($ext, '.'));
		return match ($e) {
			'txt', 'log'                 => self::TEXT_PLAIN,
			'md'                        => self::TEXT_MARKDOWN,
			'csv'                       => self::TEXT_CSV,
			'htm', 'html'                => self::TEXT_HTML,
			'css'                       => self::TEXT_CSS,
			'js', 'mjs'                  => self::TEXT_JS,
			'json'                      => self::APPLICATION_JSON,
			'xml'                       => self::APPLICATION_XML,
			'sql'                       => self::APPLICATION_SQL,

			'pdf'                       => self::APPLICATION_PDF,

			'png'                       => self::IMAGE_PNG,
			'jpg', 'jpeg'                => self::IMAGE_JPEG,
			'gif'                       => self::IMAGE_GIF,
			'svg'                       => self::IMAGE_SVG,

			'mp3'                       => self::AUDIO_MPEG,
			'ogg'                       => self::AUDIO_OGG,
			'wav'                       => self::AUDIO_WAV,

			'mp4'                       => self::VIDEO_MP4,
			'webm'                      => self::VIDEO_WEBM,
			'mov'                       => self::VIDEO_QUICKTIME,
			'avi'                       => self::VIDEO_AVI,

			'zip'                       => self::APPLICATION_ZIP,
			'7z'                        => self::APPLICATION_7Z,
			'rar'                       => self::APPLICATION_RAR,

			'doc'                       => self::APPLICATION_MSWORD,
			'docx'                      => self::APPLICATION_DOCX,
			'xls'                       => self::APPLICATION_MSEXCEL,
			'xlsx'                      => self::APPLICATION_XLSX,
			'ppt'                       => self::APPLICATION_MSPPT,
			'pptx'                      => self::APPLICATION_PPTX,

			'sqlite', 'db'               => self::APPLICATION_SQLITE,
			'php'                       => self::TEXT_PHP,
			default                     => null,
		};
	}

	public static function fromMime(MimeType $mime): self
	{
		return match ($mime) {
			MimeType::TEXT_PLAIN        => self::TEXT_PLAIN,
			MimeType::TEXT_MARKDOWN     => self::TEXT_MARKDOWN,
			MimeType::TEXT_CSV          => self::TEXT_CSV,
			MimeType::TEXT_HTML         => self::TEXT_HTML,
			MimeType::TEXT_CSS          => self::TEXT_CSS,
			MimeType::TEXT_JS           => self::TEXT_JS,
			MimeType::APPLICATION_JSON  => self::APPLICATION_JSON,
			MimeType::APPLICATION_XML   => self::APPLICATION_XML,
			MimeType::APPLICATION_SQL   => self::APPLICATION_SQL,

			MimeType::APPLICATION_PDF   => self::APPLICATION_PDF,

			MimeType::IMAGE_PNG         => self::IMAGE_PNG,
			MimeType::IMAGE_JPEG        => self::IMAGE_JPEG,
			MimeType::IMAGE_GIF         => self::IMAGE_GIF,
			MimeType::IMAGE_SVG         => self::IMAGE_SVG,

			MimeType::AUDIO_MPEG        => self::AUDIO_MPEG,
			MimeType::AUDIO_OGG         => self::AUDIO_OGG,
			MimeType::AUDIO_WAV         => self::AUDIO_WAV,

			MimeType::VIDEO_MP4         => self::VIDEO_MP4,
			MimeType::VIDEO_WEBM        => self::VIDEO_WEBM,
			MimeType::VIDEO_QUICKTIME   => self::VIDEO_QUICKTIME,
			MimeType::VIDEO_AVI         => self::VIDEO_AVI,

			MimeType::APPLICATION_ZIP   => self::APPLICATION_ZIP,
			MimeType::APPLICATION_7Z    => self::APPLICATION_7Z,
			MimeType::APPLICATION_RAR   => self::APPLICATION_RAR,

			MimeType::APPLICATION_MSWORD => self::APPLICATION_MSWORD,
			MimeType::APPLICATION_DOCX  => self::APPLICATION_DOCX,
			MimeType::APPLICATION_MSEXCEL => self::APPLICATION_MSEXCEL,
			MimeType::APPLICATION_XLSX  => self::APPLICATION_XLSX,
			MimeType::APPLICATION_MSPPT => self::APPLICATION_MSPPT,
			MimeType::APPLICATION_PPTX  => self::APPLICATION_PPTX,

			MimeType::APPLICATION_SQLITE => self::APPLICATION_SQLITE,
			MimeType::TEXT_PHP          => self::TEXT_PHP,
		};
	}
}
