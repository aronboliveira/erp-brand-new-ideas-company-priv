<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

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

	public static function normalize(?string $value): ?self
	{
		if ($value === null)
			return null;

		$v = strtolower(trim($value));

		foreach (self::cases() as $case)
			if ($case->value === $v)
				return $case;

		$map = [
			// TEXT_PLAIN
			'plain text' => self::TEXT_PLAIN,
			'text' => self::TEXT_PLAIN,
			'txt' => self::TEXT_PLAIN,
			'text/plain' => self::TEXT_PLAIN,
			'plain' => self::TEXT_PLAIN,
			'texto' => self::TEXT_PLAIN,
			'texto plano' => self::TEXT_PLAIN,
			'texto puro' => self::TEXT_PLAIN,
			'texto simples' => self::TEXT_PLAIN,
			'simple text' => self::TEXT_PLAIN,
			'clear text' => self::TEXT_PLAIN,
			'texte simple' => self::TEXT_PLAIN,
			'texto sin formato' => self::TEXT_PLAIN,
			'纯文本' => self::TEXT_PLAIN,
			'テキスト' => self::TEXT_PLAIN,

			// TEXT_MARKDOWN
			'markdown' => self::TEXT_MARKDOWN,
			'md' => self::TEXT_MARKDOWN,
			'text/markdown' => self::TEXT_MARKDOWN,
			'markdown text' => self::TEXT_MARKDOWN,
			'markdown file' => self::TEXT_MARKDOWN,
			'readme' => self::TEXT_MARKDOWN,
			'readme file' => self::TEXT_MARKDOWN,
			'markdown document' => self::TEXT_MARKDOWN,
			'document markdown' => self::TEXT_MARKDOWN,

			// TEXT_CSV
			'csv' => self::TEXT_CSV,
			'comma separated values' => self::TEXT_CSV,
			'text/csv' => self::TEXT_CSV,
			'spreadsheet csv' => self::TEXT_CSV,
			'csv spreadsheet' => self::TEXT_CSV,
			'csv file' => self::TEXT_CSV,
			'csv data' => self::TEXT_CSV,
			'valores separados por vírgula' => self::TEXT_CSV,
			'planilha' => self::TEXT_CSV,
			'hoja de cálculo' => self::TEXT_CSV,
			'tableur' => self::TEXT_CSV,

			// TEXT_HTML
			'html' => self::TEXT_HTML,
			'htm' => self::TEXT_HTML,
			'text/html' => self::TEXT_HTML,
			'html document' => self::TEXT_HTML,
			'html file' => self::TEXT_HTML,
			'web page' => self::TEXT_HTML,
			'webpage' => self::TEXT_HTML,
			'website' => self::TEXT_HTML,
			'html page' => self::TEXT_HTML,
			'html5' => self::TEXT_HTML,
			'xhtml' => self::TEXT_HTML,
			'página web' => self::TEXT_HTML,
			'网页' => self::TEXT_HTML,
			'ウェブページ' => self::TEXT_HTML,

			// TEXT_CSS
			'css' => self::TEXT_CSS,
			'text/css' => self::TEXT_CSS,
			'stylesheet' => self::TEXT_CSS,
			'css stylesheet' => self::TEXT_CSS,
			'css file' => self::TEXT_CSS,
			'style sheet' => self::TEXT_CSS,
			'cascading style sheets' => self::TEXT_CSS,
			'css3' => self::TEXT_CSS,
			'style' => self::TEXT_CSS,
			'styling' => self::TEXT_CSS,
			'feuille de style' => self::TEXT_CSS,
			'hoja de estilo' => self::TEXT_CSS,
			'样式表' => self::TEXT_CSS,

			// TEXT_JS
			'javascript' => self::TEXT_JS,
			'js' => self::TEXT_JS,
			'text/javascript' => self::TEXT_JS,
			'application/javascript' => self::TEXT_JS,
			'javascript file' => self::TEXT_JS,
			'js file' => self::TEXT_JS,
			'javascript script' => self::TEXT_JS,
			'script js' => self::TEXT_JS,
			'ecmascript' => self::TEXT_JS,
			'es6' => self::TEXT_JS,
			'nodejs' => self::TEXT_JS,
			'script' => self::TEXT_JS,
			'脚本' => self::TEXT_JS,
			'スクリプト' => self::TEXT_JS,

			// APPLICATION_JSON
			'json' => self::APPLICATION_JSON,
			'application/json' => self::APPLICATION_JSON,
			'json file' => self::APPLICATION_JSON,
			'json data' => self::APPLICATION_JSON,
			'json document' => self::APPLICATION_JSON,
			'javascript object notation' => self::APPLICATION_JSON,
			'json format' => self::APPLICATION_JSON,
			'json config' => self::APPLICATION_JSON,
			'json settings' => self::APPLICATION_JSON,
			'json file format' => self::APPLICATION_JSON,

			// APPLICATION_XML
			'xml' => self::APPLICATION_XML,
			'application/xml' => self::APPLICATION_XML,
			'text/xml' => self::APPLICATION_XML,
			'xml file' => self::APPLICATION_XML,
			'xml document' => self::APPLICATION_XML,
			'extensible markup language' => self::APPLICATION_XML,
			'xml data' => self::APPLICATION_XML,
			'xml config' => self::APPLICATION_XML,
			'xml format' => self::APPLICATION_XML,

			// APPLICATION_SQL
			'sql' => self::APPLICATION_SQL,
			'application/sql' => self::APPLICATION_SQL,
			'sql file' => self::APPLICATION_SQL,
			'sql script' => self::APPLICATION_SQL,
			'sql query' => self::APPLICATION_SQL,
			'sql commands' => self::APPLICATION_SQL,
			'structured query language' => self::APPLICATION_SQL,
			'database script' => self::APPLICATION_SQL,
			'database query' => self::APPLICATION_SQL,
			'sql dump' => self::APPLICATION_SQL,
			'sql backup' => self::APPLICATION_SQL,

			// APPLICATION_PDF
			'pdf' => self::APPLICATION_PDF,
			'application/pdf' => self::APPLICATION_PDF,
			'pdf document' => self::APPLICATION_PDF,
			'pdf file' => self::APPLICATION_PDF,
			'portable document format' => self::APPLICATION_PDF,
			'adobe pdf' => self::APPLICATION_PDF,
			'pdf format' => self::APPLICATION_PDF,
			'documento pdf' => self::APPLICATION_PDF,
			'pdf文件' => self::APPLICATION_PDF,
			'PDFファイル' => self::APPLICATION_PDF,

			// IMAGE_PNG
			'png' => self::IMAGE_PNG,
			'image/png' => self::IMAGE_PNG,
			'png image' => self::IMAGE_PNG,
			'png file' => self::IMAGE_PNG,
			'portable network graphics' => self::IMAGE_PNG,
			'png format' => self::IMAGE_PNG,
			'png picture' => self::IMAGE_PNG,
			'png photo' => self::IMAGE_PNG,
			'imagem png' => self::IMAGE_PNG,
			'PNG图像' => self::IMAGE_PNG,

			// IMAGE_JPEG
			'jpeg' => self::IMAGE_JPEG,
			'jpg' => self::IMAGE_JPEG,
			'image/jpeg' => self::IMAGE_JPEG,
			'image/jpg' => self::IMAGE_JPEG,
			'jpeg image' => self::IMAGE_JPEG,
			'jpg image' => self::IMAGE_JPEG,
			'jpeg file' => self::IMAGE_JPEG,
			'jpg file' => self::IMAGE_JPEG,
			'joint photographic experts group' => self::IMAGE_JPEG,
			'jpeg format' => self::IMAGE_JPEG,
			'photo' => self::IMAGE_JPEG,
			'photograph' => self::IMAGE_JPEG,
			'picture' => self::IMAGE_JPEG,
			'imagem jpeg' => self::IMAGE_JPEG,
			'JPEG图像' => self::IMAGE_JPEG,

			// IMAGE_GIF
			'gif' => self::IMAGE_GIF,
			'image/gif' => self::IMAGE_GIF,
			'gif image' => self::IMAGE_GIF,
			'gif file' => self::IMAGE_GIF,
			'graphics interchange format' => self::IMAGE_GIF,
			'gif format' => self::IMAGE_GIF,
			'animated gif' => self::IMAGE_GIF,
			'gif animation' => self::IMAGE_GIF,
			'imagem gif' => self::IMAGE_GIF,
			'GIF图像' => self::IMAGE_GIF,

			// IMAGE_SVG
			'svg' => self::IMAGE_SVG,
			'image/svg+xml' => self::IMAGE_SVG,
			'svg image' => self::IMAGE_SVG,
			'svg file' => self::IMAGE_SVG,
			'scalable vector graphics' => self::IMAGE_SVG,
			'vector image' => self::IMAGE_SVG,
			'vector graphic' => self::IMAGE_SVG,
			'svg graphic' => self::IMAGE_SVG,
			'svg vector' => self::IMAGE_SVG,
			'imagem svg' => self::IMAGE_SVG,
			'SVG图像' => self::IMAGE_SVG,

			// AUDIO_MPEG
			'mp3' => self::AUDIO_MPEG,
			'audio/mpeg' => self::AUDIO_MPEG,
			'mp3 audio' => self::AUDIO_MPEG,
			'mp3 file' => self::AUDIO_MPEG,
			'mpeg audio' => self::AUDIO_MPEG,
			'mp3 sound' => self::AUDIO_MPEG,
			'mp3 music' => self::AUDIO_MPEG,
			'audio file' => self::AUDIO_MPEG,
			'sound file' => self::AUDIO_MPEG,
			'music file' => self::AUDIO_MPEG,
			'áudio mp3' => self::AUDIO_MPEG,
			'MP3音频' => self::AUDIO_MPEG,

			// AUDIO_OGG
			'ogg' => self::AUDIO_OGG,
			'audio/ogg' => self::AUDIO_OGG,
			'ogg audio' => self::AUDIO_OGG,
			'ogg file' => self::AUDIO_OGG,
			'ogg sound' => self::AUDIO_OGG,
			'ogg music' => self::AUDIO_OGG,
			'vorbis' => self::AUDIO_OGG,
			'ogg vorbis' => self::AUDIO_OGG,
			'áudio ogg' => self::AUDIO_OGG,
			'OGG音频' => self::AUDIO_OGG,

			// AUDIO_WAV
			'wav' => self::AUDIO_WAV,
			'audio/wav' => self::AUDIO_WAV,
			'wav audio' => self::AUDIO_WAV,
			'wav file' => self::AUDIO_WAV,
			'wave' => self::AUDIO_WAV,
			'wave audio' => self::AUDIO_WAV,
			'wave file' => self::AUDIO_WAV,
			'waveform audio' => self::AUDIO_WAV,
			'audio waveform' => self::AUDIO_WAV,
			'áudio wav' => self::AUDIO_WAV,
			'WAV音频' => self::AUDIO_WAV,

			// VIDEO_MP4
			'mp4' => self::VIDEO_MP4,
			'video/mp4' => self::VIDEO_MP4,
			'mp4 video' => self::VIDEO_MP4,
			'mp4 file' => self::VIDEO_MP4,
			'mpeg-4' => self::VIDEO_MP4,
			'h.264' => self::VIDEO_MP4,
			'mp4 movie' => self::VIDEO_MP4,
			'mp4 clip' => self::VIDEO_MP4,
			'video file' => self::VIDEO_MP4,
			'vídeo mp4' => self::VIDEO_MP4,
			'MP4视频' => self::VIDEO_MP4,

			// VIDEO_WEBM
			'webm' => self::VIDEO_WEBM,
			'video/webm' => self::VIDEO_WEBM,
			'webm video' => self::VIDEO_WEBM,
			'webm file' => self::VIDEO_WEBM,
			'webm format' => self::VIDEO_WEBM,
			'web media' => self::VIDEO_WEBM,
			'vídeo webm' => self::VIDEO_WEBM,
			'WebM视频' => self::VIDEO_WEBM,

			// VIDEO_QUICKTIME
			'quicktime' => self::VIDEO_QUICKTIME,
			'mov' => self::VIDEO_QUICKTIME,
			'video/quicktime' => self::VIDEO_QUICKTIME,
			'quicktime video' => self::VIDEO_QUICKTIME,
			'mov video' => self::VIDEO_QUICKTIME,
			'quicktime movie' => self::VIDEO_QUICKTIME,
			'mov file' => self::VIDEO_QUICKTIME,
			'apple quicktime' => self::VIDEO_QUICKTIME,
			'vídeo quicktime' => self::VIDEO_QUICKTIME,
			'QuickTime视频' => self::VIDEO_QUICKTIME,

			// VIDEO_AVI
			'avi' => self::VIDEO_AVI,
			'video/x-msvideo' => self::VIDEO_AVI,
			'avi video' => self::VIDEO_AVI,
			'avi file' => self::VIDEO_AVI,
			'audio video interleave' => self::VIDEO_AVI,
			'avi format' => self::VIDEO_AVI,
			'avi movie' => self::VIDEO_AVI,
			'vídeo avi' => self::VIDEO_AVI,
			'AVI视频' => self::VIDEO_AVI,

			// APPLICATION_ZIP
			'zip' => self::APPLICATION_ZIP,
			'application/zip' => self::APPLICATION_ZIP,
			'zip file' => self::APPLICATION_ZIP,
			'zip archive' => self::APPLICATION_ZIP,
			'zip compressed' => self::APPLICATION_ZIP,
			'zip package' => self::APPLICATION_ZIP,
			'zip bundle' => self::APPLICATION_ZIP,
			'compressed file' => self::APPLICATION_ZIP,
			'archive file' => self::APPLICATION_ZIP,
			'arquivo zip' => self::APPLICATION_ZIP,
			'ZIP文件' => self::APPLICATION_ZIP,

			// APPLICATION_7Z
			'7z' => self::APPLICATION_7Z,
			'7zip' => self::APPLICATION_7Z,
			'application/x-7z-compressed' => self::APPLICATION_7Z,
			'7z file' => self::APPLICATION_7Z,
			'7z archive' => self::APPLICATION_7Z,
			'7z compressed' => self::APPLICATION_7Z,
			'7-zip' => self::APPLICATION_7Z,
			'seven zip' => self::APPLICATION_7Z,
			'arquivo 7z' => self::APPLICATION_7Z,
			'7Z文件' => self::APPLICATION_7Z,

			// APPLICATION_RAR
			'rar' => self::APPLICATION_RAR,
			'application/x-rar-compressed' => self::APPLICATION_RAR,
			'rar file' => self::APPLICATION_RAR,
			'rar archive' => self::APPLICATION_RAR,
			'rar compressed' => self::APPLICATION_RAR,
			'winrar' => self::APPLICATION_RAR,
			'rar package' => self::APPLICATION_RAR,
			'arquivo rar' => self::APPLICATION_RAR,
			'RAR文件' => self::APPLICATION_RAR,

			// APPLICATION_MSWORD
			'word' => self::APPLICATION_MSWORD,
			'doc' => self::APPLICATION_MSWORD,
			'application/msword' => self::APPLICATION_MSWORD,
			'microsoft word' => self::APPLICATION_MSWORD,
			'word document' => self::APPLICATION_MSWORD,
			'word file' => self::APPLICATION_MSWORD,
			'doc file' => self::APPLICATION_MSWORD,
			'documento word' => self::APPLICATION_MSWORD,
			'Word文档' => self::APPLICATION_MSWORD,

			// APPLICATION_DOCX
			'docx' => self::APPLICATION_DOCX,
			'word ooxml' => self::APPLICATION_DOCX,
			'word xml' => self::APPLICATION_DOCX,
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => self::APPLICATION_DOCX,
			'word 2007+' => self::APPLICATION_DOCX,
			'word modern' => self::APPLICATION_DOCX,
			'word open xml' => self::APPLICATION_DOCX,
			'docx file' => self::APPLICATION_DOCX,
			'documento word moderno' => self::APPLICATION_DOCX,
			'Word文档(新格式)' => self::APPLICATION_DOCX,

			// APPLICATION_MSEXCEL
			'excel' => self::APPLICATION_MSEXCEL,
			'xls' => self::APPLICATION_MSEXCEL,
			'application/vnd.ms-excel' => self::APPLICATION_MSEXCEL,
			'microsoft excel' => self::APPLICATION_MSEXCEL,
			'excel spreadsheet' => self::APPLICATION_MSEXCEL,
			'excel file' => self::APPLICATION_MSEXCEL,
			'xls file' => self::APPLICATION_MSEXCEL,
			'spreadsheet excel' => self::APPLICATION_MSEXCEL,
			'planilha excel' => self::APPLICATION_MSEXCEL,
			'Excel表格' => self::APPLICATION_MSEXCEL,

			// APPLICATION_XLSX
			'xlsx' => self::APPLICATION_XLSX,
			'excel ooxml' => self::APPLICATION_XLSX,
			'excel xml' => self::APPLICATION_XLSX,
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => self::APPLICATION_XLSX,
			'excel 2007+' => self::APPLICATION_XLSX,
			'excel modern' => self::APPLICATION_XLSX,
			'excel open xml' => self::APPLICATION_XLSX,
			'xlsx file' => self::APPLICATION_XLSX,
			'planilha excel moderna' => self::APPLICATION_XLSX,
			'Excel表格(新格式)' => self::APPLICATION_XLSX,

			// APPLICATION_MSPPT
			'powerpoint' => self::APPLICATION_MSPPT,
			'ppt' => self::APPLICATION_MSPPT,
			'application/vnd.ms-powerpoint' => self::APPLICATION_MSPPT,
			'microsoft powerpoint' => self::APPLICATION_MSPPT,
			'powerpoint presentation' => self::APPLICATION_MSPPT,
			'powerpoint file' => self::APPLICATION_MSPPT,
			'ppt file' => self::APPLICATION_MSPPT,
			'slideshow' => self::APPLICATION_MSPPT,
			'presentation' => self::APPLICATION_MSPPT,
			'apresentação powerpoint' => self::APPLICATION_MSPPT,
			'PowerPoint演示文稿' => self::APPLICATION_MSPPT,

			// APPLICATION_PPTX
			'pptx' => self::APPLICATION_PPTX,
			'powerpoint ooxml' => self::APPLICATION_PPTX,
			'powerpoint xml' => self::APPLICATION_PPTX,
			'application/vnd.openxmlformats-officedocument.presentationml.presentation' => self::APPLICATION_PPTX,
			'powerpoint 2007+' => self::APPLICATION_PPTX,
			'powerpoint modern' => self::APPLICATION_PPTX,
			'powerpoint open xml' => self::APPLICATION_PPTX,
			'pptx file' => self::APPLICATION_PPTX,
			'apresentação powerpoint moderna' => self::APPLICATION_PPTX,
			'PowerPoint演示文稿(新格式)' => self::APPLICATION_PPTX,

			// APPLICATION_SQLITE
			'sqlite' => self::APPLICATION_SQLITE,
			'db' => self::APPLICATION_SQLITE,
			'application/x-sqlite3' => self::APPLICATION_SQLITE,
			'sqlite database' => self::APPLICATION_SQLITE,
			'sqlite file' => self::APPLICATION_SQLITE,
			'database file' => self::APPLICATION_SQLITE,
			'sqlite db' => self::APPLICATION_SQLITE,
			'embedded database' => self::APPLICATION_SQLITE,
			'banco de dados sqlite' => self::APPLICATION_SQLITE,
			'SQLite数据库' => self::APPLICATION_SQLITE,

			// TEXT_PHP
			'php' => self::TEXT_PHP,
			'text/x-php' => self::TEXT_PHP,
			'application/x-php' => self::TEXT_PHP,
			'php file' => self::TEXT_PHP,
			'php script' => self::TEXT_PHP,
			'php code' => self::TEXT_PHP,
			'php programming' => self::TEXT_PHP,
			'php source' => self::TEXT_PHP,
			'hypertext preprocessor' => self::TEXT_PHP,
			'php web' => self::TEXT_PHP,
			'código php' => self::TEXT_PHP,
			'PHP代码' => self::TEXT_PHP,
		];

		return $map[$v] ?? null;
	}

	public static function values(): array
	{
		return array_map(fn($case) => $case->value, self::cases());
	}

	public function label(): string
	{
		return $this->value;
	}

	public static function labels($lang = DatabaseConstants::DEFAULT_LANG): array
	{
		$lang = preg_replace('/_/', '-', strtolower(trim($lang ?? '')));
		return match ($lang) {
			'pt-br', 'pt' => self::labelsPtBr(),
			'es', 'es-es' => self::labelsEs(),
			'ar', 'ar-sa' => self::labelsAr(),
			'da', 'da-dk' => self::labelsDa(),
			'de', 'de-de' => self::labelsDe(),
			'fr', 'fr-fr' => self::labelsFr(),
			'he', 'he-il' => self::labelsHe(),
			'it', 'it-it' => self::labelsIt(),
			'ja', 'ja-jp' => self::labelsJa(),
			'nl', 'nl-nl' => self::labelsNl(),
			'pl', 'pl-pl' => self::labelsPl(),
			'ru', 'ru-ru' => self::labelsRu(),
			'tr', 'tr-tr' => self::labelsTr(),
			'zh', 'zh-cn' => self::labelsZh(),
			default => self::labelsEn(),
		};
	}

	public static function labelsPtBr(): array
	{
		return [
			self::TEXT_PLAIN->value         => 'Texto Simples',
			self::TEXT_MARKDOWN->value      => 'Markdown',
			self::TEXT_CSV->value           => 'Planilha CSV',
			self::TEXT_HTML->value          => 'Documento HTML',
			self::TEXT_CSS->value           => 'Folha de Estilo CSS',
			self::TEXT_JS->value            => 'Script JavaScript',
			self::APPLICATION_JSON->value   => 'Documento JSON',
			self::APPLICATION_XML->value    => 'Documento XML',
			self::APPLICATION_SQL->value    => 'Script SQL',
			self::APPLICATION_PDF->value    => 'Documento PDF',
			self::IMAGE_PNG->value          => 'Imagem PNG',
			self::IMAGE_JPEG->value         => 'Imagem JPEG',
			self::IMAGE_GIF->value          => 'Imagem GIF',
			self::IMAGE_SVG->value          => 'Imagem SVG',
			self::AUDIO_MPEG->value         => 'Áudio MPEG',
			self::AUDIO_OGG->value          => 'Áudio OGG',
			self::AUDIO_WAV->value          => 'Áudio WAV',
			self::VIDEO_MP4->value          => 'Vídeo MP4',
			self::VIDEO_WEBM->value         => 'Vídeo WebM',
			self::VIDEO_QUICKTIME->value    => 'Vídeo QuickTime',
			self::VIDEO_AVI->value          => 'Vídeo AVI',
			self::APPLICATION_ZIP->value    => 'Arquivo ZIP',
			self::APPLICATION_7Z->value     => 'Arquivo 7z',
			self::APPLICATION_RAR->value    => 'Arquivo RAR',
			self::APPLICATION_MSWORD->value => 'Documento Word',
			self::APPLICATION_DOCX->value   => 'Documento Word (OOXML)',
			self::APPLICATION_MSEXCEL->value => 'Planilha Excel',
			self::APPLICATION_XLSX->value   => 'Planilha Excel (OOXML)',
			self::APPLICATION_MSPPT->value  => 'Apresentação PowerPoint',
			self::APPLICATION_PPTX->value   => 'Apresentação PowerPoint (OOXML)',
			self::APPLICATION_SQLITE->value => 'Banco SQLite',
			self::TEXT_PHP->value           => 'Código PHP',
		];
	}

	public static function labelsEn(): array
	{
		return [
			self::TEXT_PLAIN->value         => 'Plain Text',
			self::TEXT_MARKDOWN->value      => 'Markdown',
			self::TEXT_CSV->value           => 'CSV Spreadsheet',
			self::TEXT_HTML->value          => 'HTML Document',
			self::TEXT_CSS->value           => 'CSS Stylesheet',
			self::TEXT_JS->value            => 'JavaScript Script',
			self::APPLICATION_JSON->value   => 'JSON Document',
			self::APPLICATION_XML->value    => 'XML Document',
			self::APPLICATION_SQL->value    => 'SQL Script',
			self::APPLICATION_PDF->value    => 'PDF Document',
			self::IMAGE_PNG->value          => 'PNG Image',
			self::IMAGE_JPEG->value         => 'JPEG Image',
			self::IMAGE_GIF->value          => 'GIF Image',
			self::IMAGE_SVG->value          => 'SVG Image',
			self::AUDIO_MPEG->value         => 'MPEG Audio',
			self::AUDIO_OGG->value          => 'OGG Audio',
			self::AUDIO_WAV->value          => 'WAV Audio',
			self::VIDEO_MP4->value          => 'MP4 Video',
			self::VIDEO_WEBM->value         => 'WebM Video',
			self::VIDEO_QUICKTIME->value    => 'QuickTime Video',
			self::VIDEO_AVI->value          => 'AVI Video',
			self::APPLICATION_ZIP->value    => 'ZIP File',
			self::APPLICATION_7Z->value     => '7z File',
			self::APPLICATION_RAR->value    => 'RAR File',
			self::APPLICATION_MSWORD->value => 'Word Document',
			self::APPLICATION_DOCX->value   => 'Word Document (OOXML)',
			self::APPLICATION_MSEXCEL->value => 'Excel Spreadsheet',
			self::APPLICATION_XLSX->value   => 'Excel Spreadsheet (OOXML)',
			self::APPLICATION_MSPPT->value  => 'PowerPoint Presentation',
			self::APPLICATION_PPTX->value   => 'PowerPoint Presentation (OOXML)',
			self::APPLICATION_SQLITE->value => 'SQLite Database',
			self::TEXT_PHP->value           => 'PHP Code',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::TEXT_PLAIN->value         => 'Texto Plano',
			self::TEXT_MARKDOWN->value      => 'Markdown',
			self::TEXT_CSV->value           => 'Hoja de Cálculo CSV',
			self::TEXT_HTML->value          => 'Documento HTML',
			self::TEXT_CSS->value           => 'Hoja de Estilo CSS',
			self::TEXT_JS->value            => 'Script JavaScript',
			self::APPLICATION_JSON->value   => 'Documento JSON',
			self::APPLICATION_XML->value    => 'Documento XML',
			self::APPLICATION_SQL->value    => 'Script SQL',
			self::APPLICATION_PDF->value    => 'Documento PDF',
			self::IMAGE_PNG->value          => 'Imagen PNG',
			self::IMAGE_JPEG->value         => 'Imagen JPEG',
			self::IMAGE_GIF->value          => 'Imagen GIF',
			self::IMAGE_SVG->value          => 'Imagen SVG',
			self::AUDIO_MPEG->value         => 'Audio MPEG',
			self::AUDIO_OGG->value          => 'Audio OGG',
			self::AUDIO_WAV->value          => 'Audio WAV',
			self::VIDEO_MP4->value          => 'Video MP4',
			self::VIDEO_WEBM->value         => 'Video WebM',
			self::VIDEO_QUICKTIME->value    => 'Video QuickTime',
			self::VIDEO_AVI->value          => 'Video AVI',
			self::APPLICATION_ZIP->value    => 'Archivo ZIP',
			self::APPLICATION_7Z->value     => 'Archivo 7z',
			self::APPLICATION_RAR->value    => 'Archivo RAR',
			self::APPLICATION_MSWORD->value => 'Documento Word',
			self::APPLICATION_DOCX->value   => 'Documento Word (OOXML)',
			self::APPLICATION_MSEXCEL->value => 'Hoja de Cálculo Excel',
			self::APPLICATION_XLSX->value   => 'Hoja de Cálculo Excel (OOXML)',
			self::APPLICATION_MSPPT->value  => 'Presentación PowerPoint',
			self::APPLICATION_PPTX->value   => 'Presentación PowerPoint (OOXML)',
			self::APPLICATION_SQLITE->value => 'Base de Datos SQLite',
			self::TEXT_PHP->value           => 'Código PHP',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::TEXT_PLAIN->value         => 'نص عادي',
			self::TEXT_MARKDOWN->value      => 'ماركداون',
			self::TEXT_CSV->value           => 'جدول بيانات CSV',
			self::TEXT_HTML->value          => 'مستند HTML',
			self::TEXT_CSS->value           => 'ورقة أنماط CSS',
			self::TEXT_JS->value            => 'سكريبت JavaScript',
			self::APPLICATION_JSON->value   => 'مستند JSON',
			self::APPLICATION_XML->value    => 'مستند XML',
			self::APPLICATION_SQL->value    => 'سكريبت SQL',
			self::APPLICATION_PDF->value    => 'مستند PDF',
			self::IMAGE_PNG->value          => 'صورة PNG',
			self::IMAGE_JPEG->value         => 'صورة JPEG',
			self::IMAGE_GIF->value          => 'صورة GIF',
			self::IMAGE_SVG->value          => 'صورة SVG',
			self::AUDIO_MPEG->value         => 'صوت MPEG',
			self::AUDIO_OGG->value          => 'صوت OGG',
			self::AUDIO_WAV->value          => 'صوت WAV',
			self::VIDEO_MP4->value          => 'فيديو MP4',
			self::VIDEO_WEBM->value         => 'فيديو WebM',
			self::VIDEO_QUICKTIME->value    => 'فيديو QuickTime',
			self::VIDEO_AVI->value          => 'فيديو AVI',
			self::APPLICATION_ZIP->value    => 'ملف ZIP',
			self::APPLICATION_7Z->value     => 'ملف 7z',
			self::APPLICATION_RAR->value    => 'ملف RAR',
			self::APPLICATION_MSWORD->value => 'مستند Word',
			self::APPLICATION_DOCX->value   => 'مستند Word (OOXML)',
			self::APPLICATION_MSEXCEL->value => 'جدول بيانات Excel',
			self::APPLICATION_XLSX->value   => 'جدول بيانات Excel (OOXML)',
			self::APPLICATION_MSPPT->value  => 'عرض تقديمي PowerPoint',
			self::APPLICATION_PPTX->value   => 'عرض تقديمي PowerPoint (OOXML)',
			self::APPLICATION_SQLITE->value => 'قاعدة بيانات SQLite',
			self::TEXT_PHP->value           => 'كود PHP',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::TEXT_PLAIN->value         => 'Ren Tekst',
			self::TEXT_MARKDOWN->value      => 'Markdown',
			self::TEXT_CSV->value           => 'CSV Regneark',
			self::TEXT_HTML->value          => 'HTML-dokument',
			self::TEXT_CSS->value           => 'CSS-stilark',
			self::TEXT_JS->value            => 'JavaScript-script',
			self::APPLICATION_JSON->value   => 'JSON-dokument',
			self::APPLICATION_XML->value    => 'XML-dokument',
			self::APPLICATION_SQL->value    => 'SQL-script',
			self::APPLICATION_PDF->value    => 'PDF-dokument',
			self::IMAGE_PNG->value          => 'PNG-billede',
			self::IMAGE_JPEG->value         => 'JPEG-billede',
			self::IMAGE_GIF->value          => 'GIF-billede',
			self::IMAGE_SVG->value          => 'SVG-billede',
			self::AUDIO_MPEG->value         => 'MPEG-lyd',
			self::AUDIO_OGG->value          => 'OGG-lyd',
			self::AUDIO_WAV->value          => 'WAV-lyd',
			self::VIDEO_MP4->value          => 'MP4-video',
			self::VIDEO_WEBM->value         => 'WebM-video',
			self::VIDEO_QUICKTIME->value    => 'QuickTime-video',
			self::VIDEO_AVI->value          => 'AVI-video',
			self::APPLICATION_ZIP->value    => 'ZIP-fil',
			self::APPLICATION_7Z->value     => '7z-fil',
			self::APPLICATION_RAR->value    => 'RAR-fil',
			self::APPLICATION_MSWORD->value => 'Word-dokument',
			self::APPLICATION_DOCX->value   => 'Word-dokument (OOXML)',
			self::APPLICATION_MSEXCEL->value => 'Excel-regneark',
			self::APPLICATION_XLSX->value   => 'Excel-regneark (OOXML)',
			self::APPLICATION_MSPPT->value  => 'PowerPoint-præsentation',
			self::APPLICATION_PPTX->value   => 'PowerPoint-præsentation (OOXML)',
			self::APPLICATION_SQLITE->value => 'SQLite-database',
			self::TEXT_PHP->value           => 'PHP-kode',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::TEXT_PLAIN->value         => 'Klartext',
			self::TEXT_MARKDOWN->value      => 'Markdown',
			self::TEXT_CSV->value           => 'CSV-Tabelle',
			self::TEXT_HTML->value          => 'HTML-Dokument',
			self::TEXT_CSS->value           => 'CSS-Stylesheet',
			self::TEXT_JS->value            => 'JavaScript-Skript',
			self::APPLICATION_JSON->value   => 'JSON-Dokument',
			self::APPLICATION_XML->value    => 'XML-Dokument',
			self::APPLICATION_SQL->value    => 'SQL-Skript',
			self::APPLICATION_PDF->value    => 'PDF-Dokument',
			self::IMAGE_PNG->value          => 'PNG-Bild',
			self::IMAGE_JPEG->value         => 'JPEG-Bild',
			self::IMAGE_GIF->value          => 'GIF-Bild',
			self::IMAGE_SVG->value          => 'SVG-Bild',
			self::AUDIO_MPEG->value         => 'MPEG-Audio',
			self::AUDIO_OGG->value          => 'OGG-Audio',
			self::AUDIO_WAV->value          => 'WAV-Audio',
			self::VIDEO_MP4->value          => 'MP4-Video',
			self::VIDEO_WEBM->value         => 'WebM-Video',
			self::VIDEO_QUICKTIME->value    => 'QuickTime-Video',
			self::VIDEO_AVI->value          => 'AVI-Video',
			self::APPLICATION_ZIP->value    => 'ZIP-Datei',
			self::APPLICATION_7Z->value     => '7z-Datei',
			self::APPLICATION_RAR->value    => 'RAR-Datei',
			self::APPLICATION_MSWORD->value => 'Word-Dokument',
			self::APPLICATION_DOCX->value   => 'Word-Dokument (OOXML)',
			self::APPLICATION_MSEXCEL->value => 'Excel-Tabelle',
			self::APPLICATION_XLSX->value   => 'Excel-Tabelle (OOXML)',
			self::APPLICATION_MSPPT->value  => 'PowerPoint-Präsentation',
			self::APPLICATION_PPTX->value   => 'PowerPoint-Präsentation (OOXML)',
			self::APPLICATION_SQLITE->value => 'SQLite-Datenbank',
			self::TEXT_PHP->value           => 'PHP-Code',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::TEXT_PLAIN->value         => 'Texte Brut',
			self::TEXT_MARKDOWN->value      => 'Markdown',
			self::TEXT_CSV->value           => 'Tableur CSV',
			self::TEXT_HTML->value          => 'Document HTML',
			self::TEXT_CSS->value           => 'Feuille de Style CSS',
			self::TEXT_JS->value            => 'Script JavaScript',
			self::APPLICATION_JSON->value   => 'Document JSON',
			self::APPLICATION_XML->value    => 'Document XML',
			self::APPLICATION_SQL->value    => 'Script SQL',
			self::APPLICATION_PDF->value    => 'Document PDF',
			self::IMAGE_PNG->value          => 'Image PNG',
			self::IMAGE_JPEG->value         => 'Image JPEG',
			self::IMAGE_GIF->value          => 'Image GIF',
			self::IMAGE_SVG->value          => 'Image SVG',
			self::AUDIO_MPEG->value         => 'Audio MPEG',
			self::AUDIO_OGG->value          => 'Audio OGG',
			self::AUDIO_WAV->value          => 'Audio WAV',
			self::VIDEO_MP4->value          => 'Vidéo MP4',
			self::VIDEO_WEBM->value         => 'Vidéo WebM',
			self::VIDEO_QUICKTIME->value    => 'Vidéo QuickTime',
			self::VIDEO_AVI->value          => 'Vidéo AVI',
			self::APPLICATION_ZIP->value    => 'Fichier ZIP',
			self::APPLICATION_7Z->value     => 'Fichier 7z',
			self::APPLICATION_RAR->value    => 'Fichier RAR',
			self::APPLICATION_MSWORD->value => 'Document Word',
			self::APPLICATION_DOCX->value   => 'Document Word (OOXML)',
			self::APPLICATION_MSEXCEL->value => 'Tableur Excel',
			self::APPLICATION_XLSX->value   => 'Tableur Excel (OOXML)',
			self::APPLICATION_MSPPT->value  => 'Présentation PowerPoint',
			self::APPLICATION_PPTX->value   => 'Présentation PowerPoint (OOXML)',
			self::APPLICATION_SQLITE->value => 'Base de Données SQLite',
			self::TEXT_PHP->value           => 'Code PHP',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::TEXT_PLAIN->value         => 'טקסט רגיל',
			self::TEXT_MARKDOWN->value      => 'Markdown',
			self::TEXT_CSV->value           => 'גיליון נתונים CSV',
			self::TEXT_HTML->value          => 'מסמך HTML',
			self::TEXT_CSS->value           => 'גיליון סגנון CSS',
			self::TEXT_JS->value            => 'סקריפט JavaScript',
			self::APPLICATION_JSON->value   => 'מסמך JSON',
			self::APPLICATION_XML->value    => 'מסמך XML',
			self::APPLICATION_SQL->value    => 'סקריפט SQL',
			self::APPLICATION_PDF->value    => 'מסמך PDF',
			self::IMAGE_PNG->value          => 'תמונה PNG',
			self::IMAGE_JPEG->value         => 'תמונה JPEG',
			self::IMAGE_GIF->value          => 'תמונה GIF',
			self::IMAGE_SVG->value          => 'תמונה SVG',
			self::AUDIO_MPEG->value         => 'שמע MPEG',
			self::AUDIO_OGG->value          => 'שמע OGG',
			self::AUDIO_WAV->value          => 'שמע WAV',
			self::VIDEO_MP4->value          => 'וידאו MP4',
			self::VIDEO_WEBM->value         => 'וידאו WebM',
			self::VIDEO_QUICKTIME->value    => 'וידאו QuickTime',
			self::VIDEO_AVI->value          => 'וידאו AVI',
			self::APPLICATION_ZIP->value    => 'קובץ ZIP',
			self::APPLICATION_7Z->value     => 'קובץ 7z',
			self::APPLICATION_RAR->value    => 'קובץ RAR',
			self::APPLICATION_MSWORD->value => 'מסמך Word',
			self::APPLICATION_DOCX->value   => 'מסמך Word (OOXML)',
			self::APPLICATION_MSEXCEL->value => 'גיליון נתונים Excel',
			self::APPLICATION_XLSX->value   => 'גיליון נתונים Excel (OOXML)',
			self::APPLICATION_MSPPT->value  => 'מצגת PowerPoint',
			self::APPLICATION_PPTX->value   => 'מצגת PowerPoint (OOXML)',
			self::APPLICATION_SQLITE->value => 'מסד נתונים SQLite',
			self::TEXT_PHP->value           => 'קוד PHP',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::TEXT_PLAIN->value         => 'Testo Semplice',
			self::TEXT_MARKDOWN->value      => 'Markdown',
			self::TEXT_CSV->value           => 'Foglio di Calcolo CSV',
			self::TEXT_HTML->value          => 'Documento HTML',
			self::TEXT_CSS->value           => 'Foglio di Stile CSS',
			self::TEXT_JS->value            => 'Script JavaScript',
			self::APPLICATION_JSON->value   => 'Documento JSON',
			self::APPLICATION_XML->value    => 'Documento XML',
			self::APPLICATION_SQL->value    => 'Script SQL',
			self::APPLICATION_PDF->value    => 'Documento PDF',
			self::IMAGE_PNG->value          => 'Immagine PNG',
			self::IMAGE_JPEG->value         => 'Immagine JPEG',
			self::IMAGE_GIF->value          => 'Immagine GIF',
			self::IMAGE_SVG->value          => 'Immagine SVG',
			self::AUDIO_MPEG->value         => 'Audio MPEG',
			self::AUDIO_OGG->value          => 'Audio OGG',
			self::AUDIO_WAV->value          => 'Audio WAV',
			self::VIDEO_MP4->value          => 'Video MP4',
			self::VIDEO_WEBM->value         => 'Video WebM',
			self::VIDEO_QUICKTIME->value    => 'Video QuickTime',
			self::VIDEO_AVI->value          => 'Video AVI',
			self::APPLICATION_ZIP->value    => 'File ZIP',
			self::APPLICATION_7Z->value     => 'File 7z',
			self::APPLICATION_RAR->value    => 'File RAR',
			self::APPLICATION_MSWORD->value => 'Documento Word',
			self::APPLICATION_DOCX->value   => 'Documento Word (OOXML)',
			self::APPLICATION_MSEXCEL->value => 'Foglio di Calcolo Excel',
			self::APPLICATION_XLSX->value   => 'Foglio di Calcolo Excel (OOXML)',
			self::APPLICATION_MSPPT->value  => 'Presentazione PowerPoint',
			self::APPLICATION_PPTX->value   => 'Presentazione PowerPoint (OOXML)',
			self::APPLICATION_SQLITE->value => 'Database SQLite',
			self::TEXT_PHP->value           => 'Codice PHP',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::TEXT_PLAIN->value         => 'プレーンテキスト',
			self::TEXT_MARKDOWN->value      => 'マークダウン',
			self::TEXT_CSV->value           => 'CSVスプレッドシート',
			self::TEXT_HTML->value          => 'HTMLドキュメント',
			self::TEXT_CSS->value           => 'CSSスタイルシート',
			self::TEXT_JS->value            => 'JavaScriptスクリプト',
			self::APPLICATION_JSON->value   => 'JSONドキュメント',
			self::APPLICATION_XML->value    => 'XMLドキュメント',
			self::APPLICATION_SQL->value    => 'SQLスクリプト',
			self::APPLICATION_PDF->value    => 'PDFドキュメント',
			self::IMAGE_PNG->value          => 'PNG画像',
			self::IMAGE_JPEG->value         => 'JPEG画像',
			self::IMAGE_GIF->value          => 'GIF画像',
			self::IMAGE_SVG->value          => 'SVG画像',
			self::AUDIO_MPEG->value         => 'MPEGオーディオ',
			self::AUDIO_OGG->value          => 'OGGオーディオ',
			self::AUDIO_WAV->value          => 'WAVオーディオ',
			self::VIDEO_MP4->value          => 'MP4ビデオ',
			self::VIDEO_WEBM->value         => 'WebMビデオ',
			self::VIDEO_QUICKTIME->value    => 'QuickTimeビデオ',
			self::VIDEO_AVI->value          => 'AVIビデオ',
			self::APPLICATION_ZIP->value    => 'ZIPファイル',
			self::APPLICATION_7Z->value     => '7zファイル',
			self::APPLICATION_RAR->value    => 'RARファイル',
			self::APPLICATION_MSWORD->value => 'Wordドキュメント',
			self::APPLICATION_DOCX->value   => 'Wordドキュメント (OOXML)',
			self::APPLICATION_MSEXCEL->value => 'Excelスプレッドシート',
			self::APPLICATION_XLSX->value   => 'Excelスプレッドシート (OOXML)',
			self::APPLICATION_MSPPT->value  => 'PowerPointプレゼンテーション',
			self::APPLICATION_PPTX->value   => 'PowerPointプレゼンテーション (OOXML)',
			self::APPLICATION_SQLITE->value => 'SQLiteデータベース',
			self::TEXT_PHP->value           => 'PHPコード',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::TEXT_PLAIN->value         => 'Platte Tekst',
			self::TEXT_MARKDOWN->value      => 'Markdown',
			self::TEXT_CSV->value           => 'CSV-spreadsheet',
			self::TEXT_HTML->value          => 'HTML-document',
			self::TEXT_CSS->value           => 'CSS-stylesheet',
			self::TEXT_JS->value            => 'JavaScript-script',
			self::APPLICATION_JSON->value   => 'JSON-document',
			self::APPLICATION_XML->value    => 'XML-document',
			self::APPLICATION_SQL->value    => 'SQL-script',
			self::APPLICATION_PDF->value    => 'PDF-document',
			self::IMAGE_PNG->value          => 'PNG-afbeelding',
			self::IMAGE_JPEG->value         => 'JPEG-afbeelding',
			self::IMAGE_GIF->value          => 'GIF-afbeelding',
			self::IMAGE_SVG->value          => 'SVG-afbeelding',
			self::AUDIO_MPEG->value         => 'MPEG-audio',
			self::AUDIO_OGG->value          => 'OGG-audio',
			self::AUDIO_WAV->value          => 'WAV-audio',
			self::VIDEO_MP4->value          => 'MP4-video',
			self::VIDEO_WEBM->value         => 'WebM-video',
			self::VIDEO_QUICKTIME->value    => 'QuickTime-video',
			self::VIDEO_AVI->value          => 'AVI-video',
			self::APPLICATION_ZIP->value    => 'ZIP-bestand',
			self::APPLICATION_7Z->value     => '7z-bestand',
			self::APPLICATION_RAR->value    => 'RAR-bestand',
			self::APPLICATION_MSWORD->value => 'Word-document',
			self::APPLICATION_DOCX->value   => 'Word-document (OOXML)',
			self::APPLICATION_MSEXCEL->value => 'Excel-spreadsheet',
			self::APPLICATION_XLSX->value   => 'Excel-spreadsheet (OOXML)',
			self::APPLICATION_MSPPT->value  => 'PowerPoint-presentatie',
			self::APPLICATION_PPTX->value   => 'PowerPoint-presentatie (OOXML)',
			self::APPLICATION_SQLITE->value => 'SQLite-database',
			self::TEXT_PHP->value           => 'PHP-code',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::TEXT_PLAIN->value         => 'Zwykły Tekst',
			self::TEXT_MARKDOWN->value      => 'Markdown',
			self::TEXT_CSV->value           => 'Arkusz CSV',
			self::TEXT_HTML->value          => 'Dokument HTML',
			self::TEXT_CSS->value           => 'Arkusz Stylów CSS',
			self::TEXT_JS->value            => 'Skrypt JavaScript',
			self::APPLICATION_JSON->value   => 'Dokument JSON',
			self::APPLICATION_XML->value    => 'Dokument XML',
			self::APPLICATION_SQL->value    => 'Skrypt SQL',
			self::APPLICATION_PDF->value    => 'Dokument PDF',
			self::IMAGE_PNG->value          => 'Obraz PNG',
			self::IMAGE_JPEG->value         => 'Obraz JPEG',
			self::IMAGE_GIF->value          => 'Obraz GIF',
			self::IMAGE_SVG->value          => 'Obraz SVG',
			self::AUDIO_MPEG->value         => 'Audio MPEG',
			self::AUDIO_OGG->value          => 'Audio OGG',
			self::AUDIO_WAV->value          => 'Audio WAV',
			self::VIDEO_MP4->value          => 'Wideo MP4',
			self::VIDEO_WEBM->value         => 'Wideo WebM',
			self::VIDEO_QUICKTIME->value    => 'Wideo QuickTime',
			self::VIDEO_AVI->value          => 'Wideo AVI',
			self::APPLICATION_ZIP->value    => 'Plik ZIP',
			self::APPLICATION_7Z->value     => 'Plik 7z',
			self::APPLICATION_RAR->value    => 'Plik RAR',
			self::APPLICATION_MSWORD->value => 'Dokument Word',
			self::APPLICATION_DOCX->value   => 'Dokument Word (OOXML)',
			self::APPLICATION_MSEXCEL->value => 'Arkusz Excel',
			self::APPLICATION_XLSX->value   => 'Arkusz Excel (OOXML)',
			self::APPLICATION_MSPPT->value  => 'Prezentacja PowerPoint',
			self::APPLICATION_PPTX->value   => 'Prezentacja PowerPoint (OOXML)',
			self::APPLICATION_SQLITE->value => 'Baza Danych SQLite',
			self::TEXT_PHP->value           => 'Kod PHP',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::TEXT_PLAIN->value         => 'Простой Текст',
			self::TEXT_MARKDOWN->value      => 'Markdown',
			self::TEXT_CSV->value           => 'Таблица CSV',
			self::TEXT_HTML->value          => 'Документ HTML',
			self::TEXT_CSS->value           => 'Таблица Стилей CSS',
			self::TEXT_JS->value            => 'Скрипт JavaScript',
			self::APPLICATION_JSON->value   => 'Документ JSON',
			self::APPLICATION_XML->value    => 'Документ XML',
			self::APPLICATION_SQL->value    => 'Скрипт SQL',
			self::APPLICATION_PDF->value    => 'Документ PDF',
			self::IMAGE_PNG->value          => 'Изображение PNG',
			self::IMAGE_JPEG->value         => 'Изображение JPEG',
			self::IMAGE_GIF->value          => 'Изображение GIF',
			self::IMAGE_SVG->value          => 'Изображение SVG',
			self::AUDIO_MPEG->value         => 'Аудио MPEG',
			self::AUDIO_OGG->value          => 'Аудио OGG',
			self::AUDIO_WAV->value          => 'Аудио WAV',
			self::VIDEO_MP4->value          => 'Видео MP4',
			self::VIDEO_WEBM->value         => 'Видео WebM',
			self::VIDEO_QUICKTIME->value    => 'Видео QuickTime',
			self::VIDEO_AVI->value          => 'Видео AVI',
			self::APPLICATION_ZIP->value    => 'Файл ZIP',
			self::APPLICATION_7Z->value     => 'Файл 7z',
			self::APPLICATION_RAR->value    => 'Файл RAR',
			self::APPLICATION_MSWORD->value => 'Документ Word',
			self::APPLICATION_DOCX->value   => 'Документ Word (OOXML)',
			self::APPLICATION_MSEXCEL->value => 'Таблица Excel',
			self::APPLICATION_XLSX->value   => 'Таблица Excel (OOXML)',
			self::APPLICATION_MSPPT->value  => 'Презентация PowerPoint',
			self::APPLICATION_PPTX->value   => 'Презентация PowerPoint (OOXML)',
			self::APPLICATION_SQLITE->value => 'База Данных SQLite',
			self::TEXT_PHP->value           => 'Код PHP',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::TEXT_PLAIN->value         => 'Düz Metin',
			self::TEXT_MARKDOWN->value      => 'Markdown',
			self::TEXT_CSV->value           => 'CSV Tablosu',
			self::TEXT_HTML->value          => 'HTML Belgesi',
			self::TEXT_CSS->value           => 'CSS Stil Sayfası',
			self::TEXT_JS->value            => 'JavaScript Betiği',
			self::APPLICATION_JSON->value   => 'JSON Belgesi',
			self::APPLICATION_XML->value    => 'XML Belgesi',
			self::APPLICATION_SQL->value    => 'SQL Betiği',
			self::APPLICATION_PDF->value    => 'PDF Belgesi',
			self::IMAGE_PNG->value          => 'PNG Görüntüsü',
			self::IMAGE_JPEG->value         => 'JPEG Görüntüsü',
			self::IMAGE_GIF->value          => 'GIF Görüntüsü',
			self::IMAGE_SVG->value          => 'SVG Görüntüsü',
			self::AUDIO_MPEG->value         => 'MPEG Ses',
			self::AUDIO_OGG->value          => 'OGG Ses',
			self::AUDIO_WAV->value          => 'WAV Ses',
			self::VIDEO_MP4->value          => 'MP4 Video',
			self::VIDEO_WEBM->value         => 'WebM Video',
			self::VIDEO_QUICKTIME->value    => 'QuickTime Video',
			self::VIDEO_AVI->value          => 'AVI Video',
			self::APPLICATION_ZIP->value    => 'ZIP Dosyası',
			self::APPLICATION_7Z->value     => '7z Dosyası',
			self::APPLICATION_RAR->value    => 'RAR Dosyası',
			self::APPLICATION_MSWORD->value => 'Word Belgesi',
			self::APPLICATION_DOCX->value   => 'Word Belgesi (OOXML)',
			self::APPLICATION_MSEXCEL->value => 'Excel Tablosu',
			self::APPLICATION_XLSX->value   => 'Excel Tablosu (OOXML)',
			self::APPLICATION_MSPPT->value  => 'PowerPoint Sunumu',
			self::APPLICATION_PPTX->value   => 'PowerPoint Sunumu (OOXML)',
			self::APPLICATION_SQLITE->value => 'SQLite Veritabanı',
			self::TEXT_PHP->value           => 'PHP Kodu',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::TEXT_PLAIN->value         => '纯文本',
			self::TEXT_MARKDOWN->value      => 'Markdown',
			self::TEXT_CSV->value           => 'CSV 电子表格',
			self::TEXT_HTML->value          => 'HTML 文档',
			self::TEXT_CSS->value           => 'CSS 样式表',
			self::TEXT_JS->value            => 'JavaScript 脚本',
			self::APPLICATION_JSON->value   => 'JSON 文档',
			self::APPLICATION_XML->value    => 'XML 文档',
			self::APPLICATION_SQL->value    => 'SQL 脚本',
			self::APPLICATION_PDF->value    => 'PDF 文档',
			self::IMAGE_PNG->value          => 'PNG 图像',
			self::IMAGE_JPEG->value         => 'JPEG 图像',
			self::IMAGE_GIF->value          => 'GIF 图像',
			self::IMAGE_SVG->value          => 'SVG 图像',
			self::AUDIO_MPEG->value         => 'MPEG 音频',
			self::AUDIO_OGG->value          => 'OGG 音频',
			self::AUDIO_WAV->value          => 'WAV 音频',
			self::VIDEO_MP4->value          => 'MP4 视频',
			self::VIDEO_WEBM->value         => 'WebM 视频',
			self::VIDEO_QUICKTIME->value    => 'QuickTime 视频',
			self::VIDEO_AVI->value          => 'AVI 视频',
			self::APPLICATION_ZIP->value    => 'ZIP 文件',
			self::APPLICATION_7Z->value     => '7z 文件',
			self::APPLICATION_RAR->value    => 'RAR 文件',
			self::APPLICATION_MSWORD->value => 'Word 文档',
			self::APPLICATION_DOCX->value   => 'Word 文档 (OOXML)',
			self::APPLICATION_MSEXCEL->value => 'Excel 电子表格',
			self::APPLICATION_XLSX->value   => 'Excel 电子表格 (OOXML)',
			self::APPLICATION_MSPPT->value  => 'PowerPoint 演示文稿',
			self::APPLICATION_PPTX->value   => 'PowerPoint 演示文稿 (OOXML)',
			self::APPLICATION_SQLITE->value => 'SQLite 数据库',
			self::TEXT_PHP->value           => 'PHP 代码',
		];
	}
}
