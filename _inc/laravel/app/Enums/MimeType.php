<?php

namespace App\Enums;

enum MimeType: string
{
	case TEXT_PLAIN   = 'text/plain';
	case TEXT_MARKDOWN = 'text/markdown';
	case TEXT_CSV     = 'text/csv';
	case TEXT_HTML    = 'text/html';
	case TEXT_CSS     = 'text/css';
	case TEXT_JS      = 'text/javascript';
	case APPLICATION_JSON = 'application/json';
	case APPLICATION_XML  = 'application/xml';
	case APPLICATION_SQL  = 'application/sql';

	case APPLICATION_PDF  = 'application/pdf';

	case IMAGE_PNG   = 'image/png';
	case IMAGE_JPEG  = 'image/jpeg';
	case IMAGE_GIF   = 'image/gif';
	case IMAGE_SVG   = 'image/svg+xml';

	case AUDIO_MPEG  = 'audio/mpeg';
	case AUDIO_OGG   = 'audio/ogg';
	case AUDIO_WAV   = 'audio/wav';

	case VIDEO_MP4       = 'video/mp4';
	case VIDEO_WEBM      = 'video/webm';
	case VIDEO_QUICKTIME = 'video/quicktime';
	case VIDEO_AVI       = 'video/x-msvideo';

	case APPLICATION_ZIP = 'application/zip';
	case APPLICATION_7Z  = 'application/x-7z-compressed';
	case APPLICATION_RAR = 'application/x-rar-compressed';

	case APPLICATION_MSWORD   = 'application/msword';
	case APPLICATION_DOCX     = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
	case APPLICATION_MSEXCEL  = 'application/vnd.ms-excel';
	case APPLICATION_XLSX     = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
	case APPLICATION_MSPPT    = 'application/vnd.ms-powerpoint';
	case APPLICATION_PPTX     = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';

	case APPLICATION_SQLITE = 'application/x-sqlite3';
	case TEXT_PHP           = 'text/x-php';

	public static function fromExtension(string $ext): ?self
	{
		$e = strtolower(ltrim($ext, '.'));
		return match ($e) {
			'txt', 'log'              => self::TEXT_PLAIN,
			'md'                      => self::TEXT_MARKDOWN,
			'csv'                     => self::TEXT_CSV,
			'htm', 'html'              => self::TEXT_HTML,
			'css'                     => self::TEXT_CSS,
			'js', 'mjs'                => self::TEXT_JS,
			'json'                    => self::APPLICATION_JSON,
			'xml'                     => self::APPLICATION_XML,
			'sql'                     => self::APPLICATION_SQL,

			'pdf'                     => self::APPLICATION_PDF,

			'png'                     => self::IMAGE_PNG,
			'jpg', 'jpeg'              => self::IMAGE_JPEG,
			'gif'                     => self::IMAGE_GIF,
			'svg'                     => self::IMAGE_SVG,

			'mp3'                     => self::AUDIO_MPEG,
			'ogg'                     => self::AUDIO_OGG,
			'wav'                     => self::AUDIO_WAV,

			'mp4'                     => self::VIDEO_MP4,
			'webm'                    => self::VIDEO_WEBM,
			'mov'                     => self::VIDEO_QUICKTIME,
			'avi'                     => self::VIDEO_AVI,

			'zip'                     => self::APPLICATION_ZIP,
			'7z'                      => self::APPLICATION_7Z,
			'rar'                     => self::APPLICATION_RAR,

			'doc'                     => self::APPLICATION_MSWORD,
			'docx'                    => self::APPLICATION_DOCX,
			'xls'                     => self::APPLICATION_MSEXCEL,
			'xlsx'                    => self::APPLICATION_XLSX,
			'ppt'                     => self::APPLICATION_MSPPT,
			'pptx'                    => self::APPLICATION_PPTX,

			'sqlite', 'db'             => self::APPLICATION_SQLITE,
			'php'                     => self::TEXT_PHP,
			default                   => null,
		};
	}
}
