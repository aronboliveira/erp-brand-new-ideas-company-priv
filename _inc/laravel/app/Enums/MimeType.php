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
	case IMAGE_WEBP  = 'image/webp';
	case IMAGE_BMP   = 'image/bmp';
	case IMAGE_TIFF  = 'image/tiff';

	case AUDIO_MPEG  = 'audio/mpeg';
	case AUDIO_OGG   = 'audio/ogg';
	case AUDIO_WAV   = 'audio/wav';
	case AUDIO_FLAC  = 'audio/flac';
	case AUDIO_AAC   = 'audio/aac';

	case VIDEO_MP4       = 'video/mp4';
	case VIDEO_WEBM      = 'video/webm';
	case VIDEO_QUICKTIME = 'video/quicktime';
	case VIDEO_AVI       = 'video/x-msvideo';
	case VIDEO_MPEG      = 'video/mpeg';
	case VIDEO_OGG       = 'video/ogg';

	case APPLICATION_ZIP = 'application/zip';
	case APPLICATION_7Z  = 'application/x-7z-compressed';
	case APPLICATION_RAR = 'application/x-rar-compressed';
	case APPLICATION_GZIP = 'application/gzip';
	case APPLICATION_TAR = 'application/x-tar';

	case APPLICATION_MSWORD   = 'application/msword';
	case APPLICATION_DOCX     = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
	case APPLICATION_MSEXCEL  = 'application/vnd.ms-excel';
	case APPLICATION_XLSX     = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
	case APPLICATION_MSPPT    = 'application/vnd.ms-powerpoint';
	case APPLICATION_PPTX     = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
	case APPLICATION_RTF      = 'application/rtf';
	case APPLICATION_ODT      = 'application/vnd.oasis.opendocument.text';
	case APPLICATION_ODS      = 'application/vnd.oasis.opendocument.spreadsheet';
	case APPLICATION_ODP      = 'application/vnd.oasis.opendocument.presentation';

	case APPLICATION_SQLITE = 'application/x-sqlite3';
	case TEXT_PHP           = 'text/x-php';
	case TEXT_PYTHON        = 'text/x-python';
	case TEXT_JAVA          = 'text/x-java';
	case TEXT_CPP           = 'text/x-c++';
	case TEXT_CSHARP        = 'text/x-csharp';
	case TEXT_RUBY          = 'text/x-ruby';
	case TEXT_SHELL         = 'text/x-shellscript';
	case TEXT_YAML          = 'text/yaml';
	case TEXT_TOML          = 'text/toml';

	case APPLICATION_OCTET_STREAM = 'application/octet-stream';
	case APPLICATION_XHTML        = 'application/xhtml+xml';
	case APPLICATION_JAVASCRIPT   = 'application/javascript';
	case APPLICATION_FORM_URLENCODED = 'application/x-www-form-urlencoded';
	case APPLICATION_MULTIPART_FORM   = 'multipart/form-data';
	case APPLICATION_PKCS12       = 'application/pkcs12';
	case APPLICATION_PKCS8        = 'application/pkcs8';
	case APPLICATION_CERTIFICATE  = 'application/x-x509-ca-cert';

	case FONT_WOFF  = 'font/woff';
	case FONT_WOFF2 = 'font/woff2';
	case FONT_TTF   = 'font/ttf';
	case FONT_OTF   = 'font/otf';

	case APPLICATION_EPUB     = 'application/epub+zip';
	case APPLICATION_CBZ      = 'application/x-cbz';
	case APPLICATION_CBR      = 'application/x-cbr';

	case OTHER = 'other';

	public static function fromExtension(string $ext): ?self
	{
		$e = strtolower(ltrim($ext, '.'));
		return match ($e) {
			'txt', 'log', 'ini', 'cfg', 'conf', 'config' => self::TEXT_PLAIN,
			'md', 'markdown' => self::TEXT_MARKDOWN,
			'csv', 'tsv' => self::TEXT_CSV,
			'htm', 'html', 'xhtml' => self::TEXT_HTML,
			'css', 'scss', 'sass', 'less' => self::TEXT_CSS,
			'js', 'mjs', 'cjs', 'es', 'es6' => self::TEXT_JS,
			'json', 'jsonl', 'json5' => self::APPLICATION_JSON,
			'xml', 'xsd', 'xsl', 'xslt' => self::APPLICATION_XML,
			'sql', 'ddl', 'dml' => self::APPLICATION_SQL,

			'pdf' => self::APPLICATION_PDF,

			'png' => self::IMAGE_PNG,
			'jpg', 'jpeg', 'jpe', 'jif', 'jfif' => self::IMAGE_JPEG,
			'gif' => self::IMAGE_GIF,
			'svg', 'svgz' => self::IMAGE_SVG,
			'webp' => self::IMAGE_WEBP,
			'bmp', 'dib' => self::IMAGE_BMP,
			'tiff', 'tif' => self::IMAGE_TIFF,

			'mp3', 'mpga', 'mpega' => self::AUDIO_MPEG,
			'ogg', 'oga', 'spx' => self::AUDIO_OGG,
			'wav', 'wave' => self::AUDIO_WAV,
			'flac' => self::AUDIO_FLAC,
			'aac' => self::AUDIO_AAC,

			'mp4', 'm4v', 'f4v' => self::VIDEO_MP4,
			'webm' => self::VIDEO_WEBM,
			'mov', 'qt' => self::VIDEO_QUICKTIME,
			'avi' => self::VIDEO_AVI,
			'mpeg', 'mpg', 'mpe', 'm2v', 'm2ts' => self::VIDEO_MPEG,
			'ogv' => self::VIDEO_OGG,

			'zip' => self::APPLICATION_ZIP,
			'7z', '7zip' => self::APPLICATION_7Z,
			'rar' => self::APPLICATION_RAR,
			'gz', 'gzip' => self::APPLICATION_GZIP,
			'tar', 'gtar' => self::APPLICATION_TAR,

			'doc' => self::APPLICATION_MSWORD,
			'docx' => self::APPLICATION_DOCX,
			'xls' => self::APPLICATION_MSEXCEL,
			'xlsx' => self::APPLICATION_XLSX,
			'ppt' => self::APPLICATION_MSPPT,
			'pptx' => self::APPLICATION_PPTX,
			'rtf' => self::APPLICATION_RTF,
			'odt' => self::APPLICATION_ODT,
			'ods' => self::APPLICATION_ODS,
			'odp' => self::APPLICATION_ODP,

			'sqlite', 'db', 'db3', 'sdb', 'sqlite3' => self::APPLICATION_SQLITE,
			'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml' => self::TEXT_PHP,
			'py', 'pyw', 'pyc', 'pyo' => self::TEXT_PYTHON,
			'java', 'jav', 'class' => self::TEXT_JAVA,
			'cpp', 'cc', 'cxx', 'c++', 'hpp', 'hh', 'hxx', 'h++' => self::TEXT_CPP,
			'cs', 'csharp' => self::TEXT_CSHARP,
			'rb', 'ruby', 'rbw' => self::TEXT_RUBY,
			'sh', 'bash', 'zsh', 'ksh', 'csh', 'tcsh', 'fish' => self::TEXT_SHELL,
			'yaml', 'yml' => self::TEXT_YAML,
			'toml' => self::TEXT_TOML,

			'woff' => self::FONT_WOFF,
			'woff2' => self::FONT_WOFF2,
			'ttf', 'ttc' => self::FONT_TTF,
			'otf' => self::FONT_OTF,

			'epub' => self::APPLICATION_EPUB,
			'cbz' => self::APPLICATION_CBZ,
			'cbr' => self::APPLICATION_CBR,

			'bin', 'exe', 'dll', 'so', 'dylib', 'app', 'msi', 'deb', 'rpm', 'pkg' => self::APPLICATION_OCTET_STREAM,

			default => null,
		};
	}

	/**
	 * Normalize a MIME type string to a standard MimeType enum case
	 * Handles variations, aliases, and parameters (like charset)
	 */
	public static function normalize(string $mimeType): ?self
	{
		// Remove any parameters (like ; charset=utf-8)
		$mimeType = strtolower(trim(explode(';', $mimeType)[0]));

		// Handle common aliases and variations
		return match ($mimeType) {
			// Text types
			'text/plain', 'text/x-text', 'text/x-log' => self::TEXT_PLAIN,
			'text/markdown' => self::TEXT_MARKDOWN,
			'text/csv', 'text/tab-separated-values' => self::TEXT_CSV,
			'text/html', 'text/x-html' => self::TEXT_HTML,
			'text/css', 'text/x-css' => self::TEXT_CSS,
			'text/javascript', 'application/javascript', 'application/x-javascript',
			'application/ecmascript', 'text/ecmascript' => self::TEXT_JS,
			'application/json', 'text/json', 'application/x-json' => self::APPLICATION_JSON,
			'application/xml', 'text/xml' => self::APPLICATION_XML,
			'application/sql', 'text/x-sql' => self::APPLICATION_SQL,

			// Application types
			'application/pdf', 'application/x-pdf' => self::APPLICATION_PDF,
			'application/zip', 'application/x-zip', 'application/x-zip-compressed' => self::APPLICATION_ZIP,
			'application/x-7z-compressed', 'application/x-7zip' => self::APPLICATION_7Z,
			'application/x-rar-compressed', 'application/x-rar' => self::APPLICATION_RAR,
			'application/gzip', 'application/x-gzip', 'application/x-gtar' => self::APPLICATION_GZIP,
			'application/x-tar', 'application/x-gtar' => self::APPLICATION_TAR,
			'application/octet-stream', 'application/binary' => self::APPLICATION_OCTET_STREAM,
			'application/xhtml+xml' => self::APPLICATION_XHTML,

			// Image types
			'image/png', 'image/x-png' => self::IMAGE_PNG,
			'image/jpeg', 'image/pjpeg', 'image/jpg' => self::IMAGE_JPEG,
			'image/gif' => self::IMAGE_GIF,
			'image/svg+xml', 'image/svg' => self::IMAGE_SVG,
			'image/webp' => self::IMAGE_WEBP,
			'image/bmp', 'image/x-bmp', 'image/x-windows-bmp' => self::IMAGE_BMP,
			'image/tiff', 'image/tif', 'image/x-tiff' => self::IMAGE_TIFF,

			// Audio types
			'audio/mpeg', 'audio/mp3', 'audio/x-mpeg', 'audio/mpeg3' => self::AUDIO_MPEG,
			'audio/ogg', 'audio/x-ogg', 'audio/oga' => self::AUDIO_OGG,
			'audio/wav', 'audio/x-wav', 'audio/wave' => self::AUDIO_WAV,
			'audio/flac', 'audio/x-flac' => self::AUDIO_FLAC,
			'audio/aac' => self::AUDIO_AAC,

			// Video types
			'video/mp4', 'video/mp4v-es', 'video/x-m4v' => self::VIDEO_MP4,
			'video/webm' => self::VIDEO_WEBM,
			'video/quicktime', 'video/x-quicktime' => self::VIDEO_QUICKTIME,
			'video/x-msvideo', 'video/avi' => self::VIDEO_AVI,
			'video/mpeg', 'video/x-mpeg', 'video/x-mpeg2' => self::VIDEO_MPEG,
			'video/ogg', 'video/ogv' => self::VIDEO_OGG,

			// Office documents
			'application/msword', 'application/word', 'application/vnd.ms-word' => self::APPLICATION_MSWORD,
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => self::APPLICATION_DOCX,
			'application/vnd.ms-excel', 'application/excel', 'application/x-excel', 'application/x-msexcel' => self::APPLICATION_MSEXCEL,
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => self::APPLICATION_XLSX,
			'application/vnd.ms-powerpoint', 'application/powerpoint', 'application/x-powerpoint' => self::APPLICATION_MSPPT,
			'application/vnd.openxmlformats-officedocument.presentationml.presentation' => self::APPLICATION_PPTX,
			'application/rtf', 'text/rtf', 'application/x-rtf' => self::APPLICATION_RTF,
			'application/vnd.oasis.opendocument.text' => self::APPLICATION_ODT,
			'application/vnd.oasis.opendocument.spreadsheet' => self::APPLICATION_ODS,
			'application/vnd.oasis.opendocument.presentation' => self::APPLICATION_ODP,

			// Programming languages
			'text/x-php', 'application/x-php', 'application/x-httpd-php' => self::TEXT_PHP,
			'text/x-python', 'application/x-python' => self::TEXT_PYTHON,
			'text/x-java', 'text/java', 'application/java' => self::TEXT_JAVA,
			'text/x-c++', 'text/x-cpp', 'text/c++' => self::TEXT_CPP,
			'text/x-csharp', 'text/csharp', 'application/x-csharp' => self::TEXT_CSHARP,
			'text/x-ruby', 'application/x-ruby' => self::TEXT_RUBY,
			'text/x-shellscript', 'application/x-shellscript' => self::TEXT_SHELL,
			'text/yaml', 'application/x-yaml', 'text/x-yaml' => self::TEXT_YAML,
			'text/toml', 'application/toml' => self::TEXT_TOML,

			// Fonts
			'font/woff', 'application/font-woff' => self::FONT_WOFF,
			'font/woff2', 'application/font-woff2' => self::FONT_WOFF2,
			'font/ttf', 'application/x-font-ttf', 'application/x-font-truetype' => self::FONT_TTF,
			'font/otf', 'application/x-font-otf', 'application/x-font-opentype' => self::FONT_OTF,

			// Ebooks and comics
			'application/epub+zip' => self::APPLICATION_EPUB,
			'application/x-cbz' => self::APPLICATION_CBZ,
			'application/x-cbr' => self::APPLICATION_CBR,

			// Database
			'application/x-sqlite3', 'application/x-sqlite', 'application/vnd.sqlite3' => self::APPLICATION_SQLITE,

			// Other
			'application/x-www-form-urlencoded' => self::APPLICATION_FORM_URLENCODED,
			'multipart/form-data' => self::APPLICATION_MULTIPART_FORM,
			'application/pkcs12' => self::APPLICATION_PKCS12,
			'application/pkcs8' => self::APPLICATION_PKCS8,
			'application/x-x509-ca-cert', 'application/x-x509-user-cert', 'application/x-x509-server-cert' => self::APPLICATION_CERTIFICATE,

			// Try to match against exact enum values
			default => self::tryFrom($mimeType) ?? null,
		};
	}

	/**
	 * Get the primary file extension for this MIME type
	 */
	public function getExtension(): string
	{
		return match ($this) {
			self::TEXT_PLAIN => 'txt',
			self::TEXT_MARKDOWN => 'md',
			self::TEXT_CSV => 'csv',
			self::TEXT_HTML => 'html',
			self::TEXT_CSS => 'css',
			self::TEXT_JS => 'js',
			self::APPLICATION_JSON => 'json',
			self::APPLICATION_XML => 'xml',
			self::APPLICATION_SQL => 'sql',
			self::APPLICATION_PDF => 'pdf',
			self::IMAGE_PNG => 'png',
			self::IMAGE_JPEG => 'jpg',
			self::IMAGE_GIF => 'gif',
			self::IMAGE_SVG => 'svg',
			self::IMAGE_WEBP => 'webp',
			self::IMAGE_BMP => 'bmp',
			self::IMAGE_TIFF => 'tiff',
			self::AUDIO_MPEG => 'mp3',
			self::AUDIO_OGG => 'ogg',
			self::AUDIO_WAV => 'wav',
			self::AUDIO_FLAC => 'flac',
			self::AUDIO_AAC => 'aac',
			self::VIDEO_MP4 => 'mp4',
			self::VIDEO_WEBM => 'webm',
			self::VIDEO_QUICKTIME => 'mov',
			self::VIDEO_AVI => 'avi',
			self::VIDEO_MPEG => 'mpeg',
			self::VIDEO_OGG => 'ogv',
			self::APPLICATION_ZIP => 'zip',
			self::APPLICATION_7Z => '7z',
			self::APPLICATION_RAR => 'rar',
			self::APPLICATION_GZIP => 'gz',
			self::APPLICATION_TAR => 'tar',
			self::APPLICATION_MSWORD => 'doc',
			self::APPLICATION_DOCX => 'docx',
			self::APPLICATION_MSEXCEL => 'xls',
			self::APPLICATION_XLSX => 'xlsx',
			self::APPLICATION_MSPPT => 'ppt',
			self::APPLICATION_PPTX => 'pptx',
			self::APPLICATION_RTF => 'rtf',
			self::APPLICATION_ODT => 'odt',
			self::APPLICATION_ODS => 'ods',
			self::APPLICATION_ODP => 'odp',
			self::APPLICATION_SQLITE => 'sqlite',
			self::TEXT_PHP => 'php',
			self::TEXT_PYTHON => 'py',
			self::TEXT_JAVA => 'java',
			self::TEXT_CPP => 'cpp',
			self::TEXT_CSHARP => 'cs',
			self::TEXT_RUBY => 'rb',
			self::TEXT_SHELL => 'sh',
			self::TEXT_YAML => 'yaml',
			self::TEXT_TOML => 'toml',
			self::APPLICATION_OCTET_STREAM => 'bin',
			self::APPLICATION_XHTML => 'xhtml',
			self::APPLICATION_JAVASCRIPT => 'js',
			self::APPLICATION_FORM_URLENCODED => 'txt',
			self::APPLICATION_MULTIPART_FORM => 'txt',
			self::APPLICATION_PKCS12 => 'p12',
			self::APPLICATION_PKCS8 => 'p8',
			self::APPLICATION_CERTIFICATE => 'crt',
			self::FONT_WOFF => 'woff',
			self::FONT_WOFF2 => 'woff2',
			self::FONT_TTF => 'ttf',
			self::FONT_OTF => 'otf',
			self::APPLICATION_EPUB => 'epub',
			self::APPLICATION_CBZ => 'cbz',
			self::APPLICATION_CBR => 'cbr',
			self::OTHER => 'bin',
		};
	}

	/**
	 * Check if this MIME type is a text type (can be read as text)
	 */
	public function isText(): bool
	{
		return str_starts_with($this->value, 'text/')
			|| in_array($this, [
				self::APPLICATION_JSON,
				self::APPLICATION_XML,
				self::APPLICATION_SQL,
				self::TEXT_PHP,
				self::TEXT_PYTHON,
				self::TEXT_JAVA,
				self::TEXT_CPP,
				self::TEXT_CSHARP,
				self::TEXT_RUBY,
				self::TEXT_SHELL,
				self::TEXT_YAML,
				self::TEXT_TOML,
				self::APPLICATION_XHTML,
				self::APPLICATION_JAVASCRIPT,
				self::APPLICATION_RTF,
				self::APPLICATION_ODT,
				self::APPLICATION_ODS,
				self::APPLICATION_ODP,
			]);
	}

	/**
	 * Check if this MIME type is an image
	 */
	public function isImage(): bool
	{
		return str_starts_with($this->value, 'image/');
	}

	/**
	 * Check if this MIME type is audio
	 */
	public function isAudio(): bool
	{
		return str_starts_with($this->value, 'audio/');
	}

	/**
	 * Check if this MIME type is video
	 */
	public function isVideo(): bool
	{
		return str_starts_with($this->value, 'video/');
	}

	/**
	 * Check if this MIME type is an archive/compressed file
	 */
	public function isArchive(): bool
	{
		return in_array($this, [
			self::APPLICATION_ZIP,
			self::APPLICATION_7Z,
			self::APPLICATION_RAR,
			self::APPLICATION_GZIP,
			self::APPLICATION_TAR,
			self::APPLICATION_EPUB,
			self::APPLICATION_CBZ,
			self::APPLICATION_CBR,
		]) || str_contains($this->value, 'zip')
			|| str_contains($this->value, 'compressed');
	}

	/**
	 * Check if this MIME type is a document (text, PDF, office docs)
	 */
	public function isDocument(): bool
	{
		return $this->isText()
			|| in_array($this, [
				self::APPLICATION_PDF,
				self::APPLICATION_MSWORD,
				self::APPLICATION_DOCX,
				self::APPLICATION_MSEXCEL,
				self::APPLICATION_XLSX,
				self::APPLICATION_MSPPT,
				self::APPLICATION_PPTX,
				self::APPLICATION_RTF,
				self::APPLICATION_ODT,
				self::APPLICATION_ODS,
				self::APPLICATION_ODP,
				self::APPLICATION_EPUB,
			]);
	}

	/**
	 * Check if this MIME type is code/programming language
	 */
	public function isCode(): bool
	{
		return in_array($this, [
			self::TEXT_PHP,
			self::TEXT_PYTHON,
			self::TEXT_JAVA,
			self::TEXT_CPP,
			self::TEXT_CSHARP,
			self::TEXT_RUBY,
			self::TEXT_SHELL,
			self::TEXT_JS,
			self::TEXT_CSS,
			self::TEXT_HTML,
			self::APPLICATION_JSON,
			self::APPLICATION_XML,
			self::APPLICATION_SQL,
			self::TEXT_YAML,
			self::TEXT_TOML,
		]);
	}

	/**
	 * Check if this MIME type is a font
	 */
	public function isFont(): bool
	{
		return str_starts_with($this->value, 'font/');
	}

	/**
	 * Get all possible extensions for this MIME type
	 */
	public function getAllExtensions(): array
	{
		$extensions = [];

		foreach (get_class_methods(self::class) as $method) {
			if ($method === 'fromExtension') {
				return match ($this) {
					self::TEXT_PLAIN => ['txt', 'log', 'ini', 'cfg', 'conf', 'config'],
					self::TEXT_MARKDOWN => ['md', 'markdown'],
					self::TEXT_CSV => ['csv', 'tsv'],
					self::TEXT_HTML => ['html', 'htm', 'xhtml'],
					self::TEXT_CSS => ['css', 'scss', 'sass', 'less'],
					self::TEXT_JS => ['js', 'mjs', 'cjs', 'es', 'es6'],
					self::APPLICATION_JSON => ['json', 'jsonl', 'json5'],
					self::APPLICATION_XML => ['xml', 'xsd', 'xsl', 'xslt'],
					self::APPLICATION_SQL => ['sql', 'ddl', 'dml'],
					self::IMAGE_JPEG => ['jpg', 'jpeg', 'jpe', 'jif', 'jfif'],
					self::IMAGE_SVG => ['svg', 'svgz'],
					self::IMAGE_TIFF => ['tiff', 'tif'],
					self::AUDIO_MPEG => ['mp3', 'mpga', 'mpega'],
					self::AUDIO_OGG => ['ogg', 'oga', 'spx'],
					self::AUDIO_WAV => ['wav', 'wave'],
					self::VIDEO_MP4 => ['mp4', 'm4v', 'f4v'],
					self::VIDEO_MPEG => ['mpeg', 'mpg', 'mpe', 'm2v', 'm2ts'],
					self::VIDEO_OGG => ['ogv'],
					self::TEXT_PHP => ['php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml'],
					self::TEXT_PYTHON => ['py', 'pyw', 'pyc', 'pyo'],
					self::TEXT_JAVA => ['java', 'jav', 'class'],
					self::TEXT_CPP => ['cpp', 'cc', 'cxx', 'c++', 'hpp', 'hh', 'hxx', 'h++'],
					self::TEXT_CSHARP => ['cs', 'csharp'],
					self::TEXT_RUBY => ['rb', 'ruby', 'rbw'],
					self::TEXT_SHELL => ['sh', 'bash', 'zsh', 'ksh', 'csh', 'tcsh', 'fish'],
					self::TEXT_YAML => ['yaml', 'yml'],
					self::APPLICATION_SQLITE => ['sqlite', 'db', 'db3', 'sdb', 'sqlite3'],
					self::FONT_TTF => ['ttf', 'ttc'],
					default => [$this->getExtension()],
				};
			}
		}

		return $extensions;
	}
}
