<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

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
	case Text = 'text';
	case Configuration = 'configuration';
	case Binary = 'binary';
	case Font = 'font';
	case Database = 'database';
	case Ebook = 'ebook';
	case Executable = 'executable';
	case Certificate = 'certificate';
	case Log = 'log';
	case Data = 'data';
	case Script = 'script';
	case Style = 'style';
	case Markup = 'markup';
	case Vector = 'vector';
	case ThreeD = '3d';
	case DiskImage = 'disk_image';
	case Backup = 'backup';
	case Temporary = 'temporary';
	case Other = 'other';

	public static function normalize(?string $value): ?self
	{
		if ($value === null)
			return self::Other;
		$v = strtolower(trim($value));
		foreach (self::cases() as $case)
			if ($case->value === $v)
				return $case;
		$map = [
			// Document
			'document'         => self::Document,
			'doc'              => self::Document,
			'docs'             => self::Document,
			'pdf'              => self::Document,
			'word'             => self::Document,
			'rtf'              => self::Document,
			'odt'              => self::Document,
			'word_document'    => self::Document,
			'text_document'    => self::Document,
			'office_document'  => self::Document,
			'write'            => self::Document,
			'writing'          => self::Document,
			'paper'            => self::Document,
			'page'             => self::Document,
			'pages'            => self::Document,
			'writer'           => self::Document,
			'written'          => self::Document,
			'read'             => self::Document,
			'reading'          => self::Document,

			// Spreadsheet
			'spreadsheet'      => self::Spreadsheet,
			'spreadsheets'     => self::Spreadsheet,
			'sheet'            => self::Spreadsheet,
			'sheets'           => self::Spreadsheet,
			'excel'            => self::Spreadsheet,
			'xls'              => self::Spreadsheet,
			'xlsx'             => self::Spreadsheet,
			'ods'              => self::Spreadsheet,
			'calc'             => self::Spreadsheet,
			'calculation'      => self::Spreadsheet,
			'data_sheet'       => self::Spreadsheet,
			'worksheet'        => self::Spreadsheet,
			'workbook'         => self::Spreadsheet,
			'table'            => self::Spreadsheet,
			'tables'           => self::Spreadsheet,
			'grid'             => self::Spreadsheet,
			'cells'            => self::Spreadsheet,

			// Presentation
			'presentation'     => self::Presentation,
			'presentations'    => self::Presentation,
			'ppt'              => self::Presentation,
			'pptx'             => self::Presentation,
			'slides'           => self::Presentation,
			'slideshow'        => self::Presentation,
			'slide'            => self::Presentation,
			'powerpoint'       => self::Presentation,
			'keynote'          => self::Presentation,
			'impress'          => self::Presentation,
			'odp'              => self::Presentation,
			'show'             => self::Presentation,
			'demo'             => self::Presentation,
			'deck'             => self::Presentation,

			// Image
			'image'            => self::Image,
			'images'           => self::Image,
			'picture'          => self::Image,
			'pictures'         => self::Image,
			'photo'            => self::Image,
			'photos'           => self::Image,
			'photograph'       => self::Image,
			'photography'      => self::Image,
			'img'              => self::Image,
			'jpg'              => self::Image,
			'jpeg'             => self::Image,
			'png'              => self::Image,
			'gif'              => self::Image,
			'webp'             => self::Image,
			'bmp'              => self::Image,
			'tiff'             => self::Image,
			'bitmap'           => self::Image,
			'raster'           => self::Image,
			'graphic'          => self::Image,
			'graphics'         => self::Image,
			'visual'           => self::Image,
			'illustration'     => self::Image,

			// Audio
			'audio'            => self::Audio,
			'sound'            => self::Audio,
			'sounds'           => self::Audio,
			'music'            => self::Audio,
			'mp3'              => self::Audio,
			'wav'              => self::Audio,
			'ogg'              => self::Audio,
			'flac'             => self::Audio,
			'aac'              => self::Audio,
			'voice'            => self::Audio,
			'voiceover'        => self::Audio,
			'vocal'            => self::Audio,
			'song'             => self::Audio,
			'track'            => self::Audio,
			'recording'        => self::Audio,
			'podcast'          => self::Audio,
			'ringtone'         => self::Audio,
			'beep'             => self::Audio,
			'tone'             => self::Audio,

			// Video
			'video'            => self::Video,
			'videos'           => self::Video,
			'movie'            => self::Video,
			'movies'           => self::Video,
			'film'             => self::Video,
			'films'            => self::Video,
			'mp4'              => self::Video,
			'mov'              => self::Video,
			'avi'              => self::Video,
			'webm'             => self::Video,
			'mpeg'             => self::Video,
			'clip'             => self::Video,
			'clips'            => self::Video,
			'footage'          => self::Video,
			'recording'        => self::Video,
			'screen_record'    => self::Video,
			'animation'        => self::Video,
			'animations'       => self::Video,
			'cartoon'          => self::Video,
			'motion'           => self::Video,
			'motion_picture'   => self::Video,

			// Archive
			'archive'          => self::Archive,
			'archives'         => self::Archive,
			'zip'              => self::Archive,
			'rar'              => self::Archive,
			'7z'               => self::Archive,
			'tar'              => self::Archive,
			'gz'               => self::Archive,
			'compressed'       => self::Archive,
			'compression'      => self::Archive,
			'packed'           => self::Archive,
			'packed_files'     => self::Archive,
			'bundle'           => self::Archive,
			'bundled'          => self::Archive,
			'package'          => self::Archive,
			'packaged'         => self::Archive,
			'container'        => self::Archive,
			'box'              => self::Archive,

			// Code
			'code'             => self::Code,
			'source'           => self::Code,
			'source_code'      => self::Code,
			'program'          => self::Code,
			'programming'      => self::Code,
			'script'           => self::Code,
			'scripts'          => self::Code,
			'developer'        => self::Code,
			'development'      => self::Code,
			'dev'              => self::Code,
			'application_code' => self::Code,
			'app_code'         => self::Code,
			'software_code'    => self::Code,
			'program_code'     => self::Code,

			// Text
			'text'             => self::Text,
			'plain_text'       => self::Text,
			'text_file'        => self::Text,
			'txt'              => self::Text,
			'ascii'            => self::Text,
			'plain'            => self::Text,
			'readme'           => self::Text,
			'notes'            => self::Text,
			'note'             => self::Text,
			'memo'             => self::Text,
			'letter'           => self::Text,
			'correspondence'   => self::Text,
			'writeup'          => self::Text,
			'article'          => self::Text,

			// Configuration
			'configuration'    => self::Configuration,
			'config'           => self::Configuration,
			'cfg'              => self::Configuration,
			'conf'             => self::Configuration,
			'settings'         => self::Configuration,
			'setting'          => self::Configuration,
			'preferences'      => self::Configuration,
			'preference'       => self::Configuration,
			'options'          => self::Configuration,
			'option'           => self::Configuration,
			'ini'              => self::Configuration,
			'yaml'             => self::Configuration,
			'toml'             => self::Configuration,
			'json_config'      => self::Configuration,
			'xml_config'       => self::Configuration,
			'property'         => self::Configuration,
			'properties'       => self::Configuration,
			'env'              => self::Configuration,
			'environment'      => self::Configuration,

			// Binary
			'binary'           => self::Binary,
			'bin'              => self::Binary,
			'exe'              => self::Binary,
			'dll'              => self::Binary,
			'so'               => self::Binary,
			'dylib'            => self::Binary,
			'executable'       => self::Binary,
			'executables'      => self::Binary,
			'machine_code'     => self::Binary,
			'compiled'         => self::Binary,
			'compiled_code'    => self::Binary,
			'object'           => self::Binary,
			'object_code'      => self::Binary,
			'library'          => self::Binary,
			'libraries'        => self::Binary,

			// Font
			'font'             => self::Font,
			'fonts'            => self::Font,
			'typeface'         => self::Font,
			'typefaces'        => self::Font,
			'typography'       => self::Font,
			'ttf'              => self::Font,
			'otf'              => self::Font,
			'woff'             => self::Font,
			'woff2'            => self::Font,
			'lettering'        => self::Font,
			'glyph'            => self::Font,
			'glyphs'           => self::Font,
			'character_set'    => self::Font,

			// Database
			'database'         => self::Database,
			'db'               => self::Database,
			'sql'              => self::Database,
			'sqlite'           => self::Database,
			'data_file'        => self::Database,
			'data_store'       => self::Database,
			'storage'          => self::Database,
			'store'            => self::Database,
			'repository'       => self::Database,
			'repositories'     => self::Database,
			'collection'       => self::Database,
			'collections'      => self::Database,
			'table_data'       => self::Database,

			// Ebook
			'ebook'            => self::Ebook,
			'ebooks'           => self::Ebook,
			'epub'             => self::Ebook,
			'digital_book'     => self::Ebook,
			'digital_books'    => self::Ebook,
			'e_book'           => self::Ebook,
			'e_books'          => self::Ebook,
			'electronic_book'  => self::Ebook,
			'electronic_books' => self::Ebook,
			'reader'           => self::Ebook,
			'readers'          => self::Ebook,
			'book'             => self::Ebook,
			'books'            => self::Ebook,

			// Executable
			'executable'       => self::Executable,
			'app'              => self::Executable,
			'application'      => self::Executable,
			'program'          => self::Executable,
			'software'         => self::Executable,
			'installer'        => self::Executable,
			'installers'       => self::Executable,
			'msi'              => self::Executable,
			'deb'              => self::Executable,
			'rpm'              => self::Executable,
			'pkg'              => self::Executable,
			'apk'              => self::Executable,
			'run'              => self::Executable,
			'runner'           => self::Executable,

			// Certificate
			'certificate'      => self::Certificate,
			'cert'             => self::Certificate,
			'crt'              => self::Certificate,
			'pem'              => self::Certificate,
			'key'              => self::Certificate,
			'public_key'       => self::Certificate,
			'private_key'      => self::Certificate,
			'security'         => self::Certificate,
			'secure'           => self::Certificate,
			'encryption'       => self::Certificate,
			'encrypted'        => self::Certificate,
			'ssl'              => self::Certificate,
			'tls'              => self::Certificate,
			'signature'        => self::Certificate,
			'signed'           => self::Certificate,

			// Log
			'log'              => self::Log,
			'logs'             => self::Log,
			'logging'          => self::Log,
			'logger'           => self::Log,
			'logfile'          => self::Log,
			'logfiles'         => self::Log,
			'history'          => self::Log,
			'historical'       => self::Log,
			'audit'            => self::Log,
			'audit_log'        => self::Log,
			'trace'            => self::Log,
			'tracing'          => self::Log,
			'debug'            => self::Log,
			'debugging'        => self::Log,

			// Data
			'data'             => self::Data,
			'dataset'          => self::Data,
			'datasets'         => self::Data,
			'information'      => self::Data,
			'content'          => self::Data,
			'contents'         => self::Data,
			'raw_data'         => self::Data,
			'processed_data'   => self::Data,
			'structured_data'  => self::Data,
			'unstructured_data' => self::Data,
			'export'           => self::Data,
			'exported'         => self::Data,
			'import'           => self::Data,
			'imported'         => self::Data,

			// Script
			'script'           => self::Script,
			'scripts'          => self::Script,
			'shell_script'     => self::Script,
			'batch'            => self::Script,
			'batch_file'       => self::Script,
			'automation'       => self::Script,
			'automated'        => self::Script,
			'runner'           => self::Script,
			'run_script'       => self::Script,
			'command'          => self::Script,
			'commands'         => self::Script,

			// Style
			'style'            => self::Style,
			'stylesheet'       => self::Style,
			'styles'           => self::Style,
			'css'              => self::Style,
			'styling'          => self::Style,
			'design'           => self::Style,
			'design_file'      => self::Style,
			'theme'            => self::Style,
			'themes'           => self::Style,
			'layout'           => self::Style,
			'layouts'          => self::Style,
			'formatting'       => self::Style,
			'formatted'        => self::Style,

			// Markup
			'markup'           => self::Markup,
			'html'             => self::Markup,
			'xml'              => self::Markup,
			'markdown'         => self::Markup,
			'md'               => self::Markup,
			'structured_text'  => self::Markup,
			'structured_document' => self::Markup,
			'tagged'           => self::Markup,
			'tags'             => self::Markup,
			'annotation'       => self::Markup,
			'annotated'        => self::Markup,

			// Vector
			'vector'           => self::Vector,
			'vector_graphic'   => self::Vector,
			'vector_graphics'  => self::Vector,
			'svg'              => self::Vector,
			'ai'               => self::Vector,
			'eps'              => self::Vector,
			'illustrator'      => self::Vector,
			'drawing'          => self::Vector,
			'drawings'         => self::Vector,
			'scalable'         => self::Vector,
			'scalable_graphic' => self::Vector,

			// 3D
			'3d'               => self::ThreeD,
			'three_d'          => self::ThreeD,
			'three_dimensional' => self::ThreeD,
			'3d_model'         => self::ThreeD,
			'3d_models'        => self::ThreeD,
			'stl'              => self::ThreeD,
			'obj'              => self::ThreeD,
			'fbx'              => self::ThreeD,
			'model'            => self::ThreeD,
			'models'           => self::ThreeD,
			'modeling'         => self::ThreeD,
			'cad'              => self::ThreeD,

			// Disk Image
			'disk_image'       => self::DiskImage,
			'disk'             => self::DiskImage,
			'iso'              => self::DiskImage,
			'dmg'              => self::DiskImage,
			'img'              => self::DiskImage,
			'virtual_disk'     => self::DiskImage,
			'virtual_drive'    => self::DiskImage,
			'emulation'        => self::DiskImage,
			'emulated'         => self::DiskImage,
			'mount'            => self::DiskImage,
			'mountable'        => self::DiskImage,

			// Backup
			'backup'           => self::Backup,
			'backups'          => self::Backup,
			'back_up'          => self::Backup,
			'backed_up'        => self::Backup,
			'restore'          => self::Backup,
			'restoration'      => self::Backup,
			'recovery'         => self::Backup,
			'recover'          => self::Backup,
			'snapshot'         => self::Backup,
			'snapshots'        => self::Backup,
			'copy'             => self::Backup,
			'copies'           => self::Backup,

			// Temporary
			'temporary'        => self::Temporary,
			'temp'             => self::Temporary,
			'tmp'              => self::Temporary,
			'cache'            => self::Temporary,
			'cached'           => self::Temporary,
			'transient'        => self::Temporary,
			'transitory'       => self::Temporary,
			'volatile'         => self::Temporary,
			'scratch'          => self::Temporary,
			'scratch_file'     => self::Temporary,
			'work'             => self::Temporary,
			'working'          => self::Temporary,
			'swap'             => self::Temporary,
			'swap_file'        => self::Temporary,
		];

		return $map[$v] ?? self::Other;
	}

	public static function values(): array
	{
		return array_map(fn($case) => $case->value, self::cases());
	}

	public function label(): string
	{
		return match ($this) {
			self::Document       => 'Document',
			self::Spreadsheet    => 'Spreadsheet',
			self::Presentation   => 'Presentation',
			self::Image          => 'Image',
			self::Audio          => 'Audio',
			self::Video          => 'Video',
			self::Archive        => 'Archive',
			self::Code           => 'Code',
			self::Text           => 'Text',
			self::Configuration  => 'Configuration',
			self::Binary         => 'Binary',
			self::Font           => 'Font',
			self::Database       => 'Database',
			self::Ebook          => 'Ebook',
			self::Executable     => 'Executable',
			self::Certificate    => 'Certificate',
			self::Log            => 'Log',
			self::Data           => 'Data',
			self::Script         => 'Script',
			self::Style          => 'Style',
			self::Markup         => 'Markup',
			self::Vector         => 'Vector',
			self::ThreeD         => '3D',
			self::DiskImage      => 'Disk Image',
			self::Backup         => 'Backup',
			self::Temporary      => 'Temporary',
			self::Other          => 'Other',
		};
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
			self::Document->value      => 'Documento',
			self::Spreadsheet->value   => 'Planilha',
			self::Presentation->value  => 'Apresentação',
			self::Image->value         => 'Imagem',
			self::Audio->value         => 'Áudio',
			self::Video->value         => 'Vídeo',
			self::Archive->value       => 'Arquivo',
			self::Code->value          => 'Código',
			self::Text->value          => 'Texto',
			self::Configuration->value => 'Configuração',
			self::Binary->value        => 'Binário',
			self::Font->value          => 'Fonte',
			self::Database->value      => 'Banco de Dados',
			self::Ebook->value         => 'Ebook',
			self::Executable->value    => 'Executável',
			self::Certificate->value   => 'Certificado',
			self::Log->value           => 'Log',
			self::Data->value          => 'Dados',
			self::Script->value        => 'Script',
			self::Style->value         => 'Estilo',
			self::Markup->value        => 'Marcação',
			self::Vector->value        => 'Vetor',
			self::ThreeD->value        => '3D',
			self::DiskImage->value     => 'Imagem de Disco',
			self::Backup->value        => 'Backup',
			self::Temporary->value     => 'Temporário',
			self::Other->value         => 'Outro',
		];
	}

	public static function labelsEn(): array
	{
		return [
			self::Document->value      => 'Document',
			self::Spreadsheet->value   => 'Spreadsheet',
			self::Presentation->value  => 'Presentation',
			self::Image->value         => 'Image',
			self::Audio->value         => 'Audio',
			self::Video->value         => 'Video',
			self::Archive->value       => 'Archive',
			self::Code->value          => 'Code',
			self::Text->value          => 'Text',
			self::Configuration->value => 'Configuration',
			self::Binary->value        => 'Binary',
			self::Font->value          => 'Font',
			self::Database->value      => 'Database',
			self::Ebook->value         => 'Ebook',
			self::Executable->value    => 'Executable',
			self::Certificate->value   => 'Certificate',
			self::Log->value           => 'Log',
			self::Data->value          => 'Data',
			self::Script->value        => 'Script',
			self::Style->value         => 'Style',
			self::Markup->value        => 'Markup',
			self::Vector->value        => 'Vector',
			self::ThreeD->value        => '3D',
			self::DiskImage->value     => 'Disk Image',
			self::Backup->value        => 'Backup',
			self::Temporary->value     => 'Temporary',
			self::Other->value         => 'Other',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::Document->value      => 'Documento',
			self::Spreadsheet->value   => 'Hoja de Cálculo',
			self::Presentation->value  => 'Presentación',
			self::Image->value         => 'Imagen',
			self::Audio->value         => 'Audio',
			self::Video->value         => 'Video',
			self::Archive->value       => 'Archivo',
			self::Code->value          => 'Código',
			self::Text->value          => 'Texto',
			self::Configuration->value => 'Configuración',
			self::Binary->value        => 'Binario',
			self::Font->value          => 'Fuente',
			self::Database->value      => 'Base de Datos',
			self::Ebook->value         => 'Ebook',
			self::Executable->value    => 'Ejecutable',
			self::Certificate->value   => 'Certificado',
			self::Log->value           => 'Registro',
			self::Data->value          => 'Datos',
			self::Script->value        => 'Script',
			self::Style->value         => 'Estilo',
			self::Markup->value        => 'Marcado',
			self::Vector->value        => 'Vector',
			self::ThreeD->value        => '3D',
			self::DiskImage->value     => 'Imagen de Disco',
			self::Backup->value        => 'Copia de Seguridad',
			self::Temporary->value     => 'Temporal',
			self::Other->value         => 'Otro',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::Document->value      => 'مستند',
			self::Spreadsheet->value   => 'جدول بيانات',
			self::Presentation->value  => 'عرض تقديمي',
			self::Image->value         => 'صورة',
			self::Audio->value         => 'صوت',
			self::Video->value         => 'فيديو',
			self::Archive->value       => 'أرشيف',
			self::Code->value          => 'كود',
			self::Text->value          => 'نص',
			self::Configuration->value => 'إعدادات',
			self::Binary->value        => 'ثنائي',
			self::Font->value          => 'خط',
			self::Database->value      => 'قاعدة بيانات',
			self::Ebook->value         => 'كتاب إلكتروني',
			self::Executable->value    => 'تنفيذي',
			self::Certificate->value   => 'شهادة',
			self::Log->value           => 'سجل',
			self::Data->value          => 'بيانات',
			self::Script->value        => 'سكريبت',
			self::Style->value         => 'نمط',
			self::Markup->value        => 'توصيف',
			self::Vector->value        => 'متجه',
			self::ThreeD->value        => 'ثلاثي الأبعاد',
			self::DiskImage->value     => 'صورة القرص',
			self::Backup->value        => 'نسخة احتياطية',
			self::Temporary->value     => 'مؤقت',
			self::Other->value         => 'آخر',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::Document->value      => 'Document',
			self::Spreadsheet->value   => 'Tableur',
			self::Presentation->value  => 'Présentation',
			self::Image->value         => 'Image',
			self::Audio->value         => 'Audio',
			self::Video->value         => 'Vidéo',
			self::Archive->value       => 'Archive',
			self::Code->value          => 'Code',
			self::Text->value          => 'Texte',
			self::Configuration->value => 'Configuration',
			self::Binary->value        => 'Binaire',
			self::Font->value          => 'Police',
			self::Database->value      => 'Base de données',
			self::Ebook->value         => 'Livre numérique',
			self::Executable->value    => 'Exécutable',
			self::Certificate->value   => 'Certificat',
			self::Log->value           => 'Journal',
			self::Data->value          => 'Données',
			self::Script->value        => 'Script',
			self::Style->value         => 'Style',
			self::Markup->value        => 'Balisage',
			self::Vector->value        => 'Vecteur',
			self::ThreeD->value        => '3D',
			self::DiskImage->value     => 'Image disque',
			self::Backup->value        => 'Sauvegarde',
			self::Temporary->value     => 'Temporaire',
			self::Other->value         => 'Autre',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::Document->value      => 'Dokument',
			self::Spreadsheet->value   => 'Tabellenkalkulation',
			self::Presentation->value  => 'Präsentation',
			self::Image->value         => 'Bild',
			self::Audio->value         => 'Audio',
			self::Video->value         => 'Video',
			self::Archive->value       => 'Archiv',
			self::Code->value          => 'Code',
			self::Text->value          => 'Text',
			self::Configuration->value => 'Konfiguration',
			self::Binary->value        => 'Binär',
			self::Font->value          => 'Schriftart',
			self::Database->value      => 'Datenbank',
			self::Ebook->value         => 'E-Book',
			self::Executable->value    => 'Ausführbar',
			self::Certificate->value   => 'Zertifikat',
			self::Log->value           => 'Protokoll',
			self::Data->value          => 'Daten',
			self::Script->value        => 'Skript',
			self::Style->value         => 'Stil',
			self::Markup->value        => 'Auszeichnung',
			self::Vector->value        => 'Vektor',
			self::ThreeD->value        => '3D',
			self::DiskImage->value     => 'Datenträgerabbild',
			self::Backup->value        => 'Sicherung',
			self::Temporary->value     => 'Temporär',
			self::Other->value         => 'Andere',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::Document->value      => 'Dokument',
			self::Spreadsheet->value   => 'Regneark',
			self::Presentation->value  => 'Præsentation',
			self::Image->value         => 'Billede',
			self::Audio->value         => 'Lyd',
			self::Video->value         => 'Video',
			self::Archive->value       => 'Arkiv',
			self::Code->value          => 'Kode',
			self::Text->value          => 'Tekst',
			self::Configuration->value => 'Konfiguration',
			self::Binary->value        => 'Binær',
			self::Font->value          => 'Skrifttype',
			self::Database->value      => 'Database',
			self::Ebook->value         => 'E-bog',
			self::Executable->value    => 'Eksekverbar',
			self::Certificate->value   => 'Certifikat',
			self::Log->value           => 'Log',
			self::Data->value          => 'Data',
			self::Script->value        => 'Script',
			self::Style->value         => 'Stil',
			self::Markup->value        => 'Markup',
			self::Vector->value        => 'Vektor',
			self::ThreeD->value        => '3D',
			self::DiskImage->value     => 'Diskbillede',
			self::Backup->value        => 'Sikkerhedskopi',
			self::Temporary->value     => 'Midlertidig',
			self::Other->value         => 'Andet',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::Document->value      => 'מסמך',
			self::Spreadsheet->value   => 'גיליון אלקטרוני',
			self::Presentation->value  => 'מצגת',
			self::Image->value         => 'תמונה',
			self::Audio->value         => 'שמע',
			self::Video->value         => 'וידאו',
			self::Archive->value       => 'ארכיון',
			self::Code->value          => 'קוד',
			self::Text->value          => 'טקסט',
			self::Configuration->value => 'הגדרה',
			self::Binary->value        => 'בינארי',
			self::Font->value          => 'גופן',
			self::Database->value      => 'מסד נתונים',
			self::Ebook->value         => 'ספר אלקטרוני',
			self::Executable->value    => 'קובץ הרצה',
			self::Certificate->value   => 'תעודה',
			self::Log->value           => 'יומן',
			self::Data->value          => 'נתונים',
			self::Script->value        => 'סקריפט',
			self::Style->value         => 'סגנון',
			self::Markup->value        => 'סימון',
			self::Vector->value        => 'וקטור',
			self::ThreeD->value        => 'תלת מימד',
			self::DiskImage->value     => 'תמונת דיסק',
			self::Backup->value        => 'גיבוי',
			self::Temporary->value     => 'זמני',
			self::Other->value         => 'אחר',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::Document->value      => 'Documento',
			self::Spreadsheet->value   => 'Foglio di calcolo',
			self::Presentation->value  => 'Presentazione',
			self::Image->value         => 'Immagine',
			self::Audio->value         => 'Audio',
			self::Video->value         => 'Video',
			self::Archive->value       => 'Archivio',
			self::Code->value          => 'Codice',
			self::Text->value          => 'Testo',
			self::Configuration->value => 'Configurazione',
			self::Binary->value        => 'Binario',
			self::Font->value          => 'Carattere',
			self::Database->value      => 'Database',
			self::Ebook->value         => 'Ebook',
			self::Executable->value    => 'Eseguibile',
			self::Certificate->value   => 'Certificato',
			self::Log->value           => 'Log',
			self::Data->value          => 'Dati',
			self::Script->value        => 'Script',
			self::Style->value         => 'Stile',
			self::Markup->value        => 'Markup',
			self::Vector->value        => 'Vettoriale',
			self::ThreeD->value        => '3D',
			self::DiskImage->value     => 'Immagine disco',
			self::Backup->value        => 'Backup',
			self::Temporary->value     => 'Temporaneo',
			self::Other->value         => 'Altro',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::Document->value      => 'ドキュメント',
			self::Spreadsheet->value   => 'スプレッドシート',
			self::Presentation->value  => 'プレゼンテーション',
			self::Image->value         => '画像',
			self::Audio->value         => '音声',
			self::Video->value         => 'ビデオ',
			self::Archive->value       => 'アーカイブ',
			self::Code->value          => 'コード',
			self::Text->value          => 'テキスト',
			self::Configuration->value => '設定',
			self::Binary->value        => 'バイナリ',
			self::Font->value          => 'フォント',
			self::Database->value      => 'データベース',
			self::Ebook->value         => '電子書籍',
			self::Executable->value    => '実行ファイル',
			self::Certificate->value   => '証明書',
			self::Log->value           => 'ログ',
			self::Data->value          => 'データ',
			self::Script->value        => 'スクリプト',
			self::Style->value         => 'スタイル',
			self::Markup->value        => 'マークアップ',
			self::Vector->value        => 'ベクター',
			self::ThreeD->value        => '3D',
			self::DiskImage->value     => 'ディスクイメージ',
			self::Backup->value        => 'バックアップ',
			self::Temporary->value     => '一時的',
			self::Other->value         => 'その他',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::Document->value      => 'Document',
			self::Spreadsheet->value   => 'Spreadsheet',
			self::Presentation->value  => 'Presentatie',
			self::Image->value         => 'Afbeelding',
			self::Audio->value         => 'Audio',
			self::Video->value         => 'Video',
			self::Archive->value       => 'Archief',
			self::Code->value          => 'Code',
			self::Text->value          => 'Tekst',
			self::Configuration->value => 'Configuratie',
			self::Binary->value        => 'Binair',
			self::Font->value          => 'Lettertype',
			self::Database->value      => 'Database',
			self::Ebook->value         => 'E-book',
			self::Executable->value    => 'Uitvoerbaar',
			self::Certificate->value   => 'Certificaat',
			self::Log->value           => 'Log',
			self::Data->value          => 'Gegevens',
			self::Script->value        => 'Script',
			self::Style->value         => 'Stijl',
			self::Markup->value        => 'Markering',
			self::Vector->value        => 'Vector',
			self::ThreeD->value        => '3D',
			self::DiskImage->value     => 'Schijfimage',
			self::Backup->value        => 'Back-up',
			self::Temporary->value     => 'Tijdelijk',
			self::Other->value         => 'Anders',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::Document->value      => 'Dokument',
			self::Spreadsheet->value   => 'Arkusz kalkulacyjny',
			self::Presentation->value  => 'Prezentacja',
			self::Image->value         => 'Obraz',
			self::Audio->value         => 'Audio',
			self::Video->value         => 'Wideo',
			self::Archive->value       => 'Archiwum',
			self::Code->value          => 'Kod',
			self::Text->value          => 'Tekst',
			self::Configuration->value => 'Konfiguracja',
			self::Binary->value        => 'Binarny',
			self::Font->value          => 'Czcionka',
			self::Database->value      => 'Baza danych',
			self::Ebook->value         => 'E-book',
			self::Executable->value    => 'Wykonywalny',
			self::Certificate->value   => 'Certyfikat',
			self::Log->value           => 'Log',
			self::Data->value          => 'Dane',
			self::Script->value        => 'Skrypt',
			self::Style->value         => 'Styl',
			self::Markup->value        => 'Znacznik',
			self::Vector->value        => 'Wektor',
			self::ThreeD->value        => '3D',
			self::DiskImage->value     => 'Obraz dysku',
			self::Backup->value        => 'Kopia zapasowa',
			self::Temporary->value     => 'Tymczasowy',
			self::Other->value         => 'Inny',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::Document->value      => 'Документ',
			self::Spreadsheet->value   => 'Таблица',
			self::Presentation->value  => 'Презентация',
			self::Image->value         => 'Изображение',
			self::Audio->value         => 'Аудио',
			self::Video->value         => 'Видео',
			self::Archive->value       => 'Архив',
			self::Code->value          => 'Код',
			self::Text->value          => 'Текст',
			self::Configuration->value => 'Конфигурация',
			self::Binary->value        => 'Двоичный',
			self::Font->value          => 'Шрифт',
			self::Database->value      => 'База данных',
			self::Ebook->value         => 'Электронная книга',
			self::Executable->value    => 'Исполняемый',
			self::Certificate->value   => 'Сертификат',
			self::Log->value           => 'Лог',
			self::Data->value          => 'Данные',
			self::Script->value        => 'Скрипт',
			self::Style->value         => 'Стиль',
			self::Markup->value        => 'Разметка',
			self::Vector->value        => 'Вектор',
			self::ThreeD->value        => '3D',
			self::DiskImage->value     => 'Образ диска',
			self::Backup->value        => 'Резервная копия',
			self::Temporary->value     => 'Временный',
			self::Other->value         => 'Другое',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::Document->value      => 'Belge',
			self::Spreadsheet->value   => 'Elektronik Tablo',
			self::Presentation->value  => 'Sunum',
			self::Image->value         => 'Görüntü',
			self::Audio->value         => 'Ses',
			self::Video->value         => 'Video',
			self::Archive->value       => 'Arşiv',
			self::Code->value          => 'Kod',
			self::Text->value          => 'Metin',
			self::Configuration->value => 'Yapılandırma',
			self::Binary->value        => 'İkili',
			self::Font->value          => 'Yazı Tipi',
			self::Database->value      => 'Veritabanı',
			self::Ebook->value         => 'E-kitap',
			self::Executable->value    => 'Çalıştırılabilir',
			self::Certificate->value   => 'Sertifika',
			self::Log->value           => 'Log',
			self::Data->value          => 'Veri',
			self::Script->value        => 'Betik',
			self::Style->value         => 'Stil',
			self::Markup->value        => 'İşaretleme',
			self::Vector->value        => 'Vektör',
			self::ThreeD->value        => '3D',
			self::DiskImage->value     => 'Disk Görüntüsü',
			self::Backup->value        => 'Yedek',
			self::Temporary->value     => 'Geçici',
			self::Other->value         => 'Diğer',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::Document->value      => '文档',
			self::Spreadsheet->value   => '电子表格',
			self::Presentation->value  => '演示文稿',
			self::Image->value         => '图像',
			self::Audio->value         => '音频',
			self::Video->value         => '视频',
			self::Archive->value       => '压缩文件',
			self::Code->value          => '代码',
			self::Text->value          => '文本',
			self::Configuration->value => '配置',
			self::Binary->value        => '二进制',
			self::Font->value          => '字体',
			self::Database->value      => '数据库',
			self::Ebook->value         => '电子书',
			self::Executable->value    => '可执行文件',
			self::Certificate->value   => '证书',
			self::Log->value           => '日志',
			self::Data->value          => '数据',
			self::Script->value        => '脚本',
			self::Style->value         => '样式',
			self::Markup->value        => '标记',
			self::Vector->value        => '矢量',
			self::ThreeD->value        => '三维',
			self::DiskImage->value     => '磁盘映像',
			self::Backup->value        => '备份',
			self::Temporary->value     => '临时',
			self::Other->value         => '其他',
		];
	}

	// Helper methods for business logic
	public function isReadable(): bool
	{
		return match ($this) {
			self::Document,
			self::Text,
			self::Code,
			self::Configuration,
			self::Log,
			self::Data,
			self::Script,
			self::Style,
			self::Markup,
			self::Ebook => true,
			default => false,
		};
	}

	public function isEditable(): bool
	{
		return match ($this) {
			self::Document,
			self::Spreadsheet,
			self::Presentation,
			self::Text,
			self::Code,
			self::Configuration,
			self::Data,
			self::Script,
			self::Style,
			self::Markup,
			self::Vector => true,
			default => false,
		};
	}

	public function isMedia(): bool
	{
		return match ($this) {
			self::Image,
			self::Audio,
			self::Video => true,
			default => false,
		};
	}

	public function isCompressed(): bool
	{
		return match ($this) {
			self::Archive,
			self::DiskImage,
			self::Backup => true,
			default => false,
		};
	}

	public function isSystemFile(): bool
	{
		return match ($this) {
			self::Binary,
			self::Executable,
			self::Certificate,
			self::Font,
			self::Database,
			self::DiskImage,
			self::Temporary => true,
			default => false,
		};
	}

	public function isApplication(): bool
	{
		return match ($this) {
			self::Code,
			self::Script,
			self::Executable,
			self::Binary => true,
			default => false,
		};
	}

	public function isStructured(): bool
	{
		return match ($this) {
			self::Database,
			self::Spreadsheet,
			self::Code,
			self::Configuration,
			self::Data,
			self::Markup => true,
			default => false,
		};
	}

	public function getIcon(): string
	{
		return match ($this) {
			self::Document       => '📄',
			self::Spreadsheet    => '📊',
			self::Presentation   => '📽️',
			self::Image          => '🖼️',
			self::Audio          => '🎵',
			self::Video          => '🎬',
			self::Archive        => '🗜️',
			self::Code           => '💻',
			self::Text           => '📝',
			self::Configuration  => '⚙️',
			self::Binary         => '🔢',
			self::Font           => '🔤',
			self::Database       => '🗄️',
			self::Ebook          => '📚',
			self::Executable     => '🚀',
			self::Certificate    => '🔐',
			self::Log            => '📋',
			self::Data           => '📈',
			self::Script         => '📜',
			self::Style          => '🎨',
			self::Markup         => '🏷️',
			self::Vector         => '📐',
			self::ThreeD         => '🎲',
			self::DiskImage      => '💿',
			self::Backup         => '💾',
			self::Temporary      => '⏳',
			self::Other          => '📎',
		};
	}

	public function getColor(): string
	{
		return match ($this) {
			self::Document       => '#3498db',
			self::Spreadsheet    => '#2ecc71',
			self::Presentation   => '#9b59b6',
			self::Image          => '#e74c3c',
			self::Audio          => '#1abc9c',
			self::Video          => '#d35400',
			self::Archive        => '#7f8c8d',
			self::Code           => '#f39c12',
			self::Text           => '#34495e',
			self::Configuration  => '#16a085',
			self::Binary         => '#2c3e50',
			self::Font           => '#8e44ad',
			self::Database       => '#27ae60',
			self::Ebook          => '#c0392b',
			self::Executable     => '#2980b9',
			self::Certificate    => '#f1c40f',
			self::Log            => '#95a5a6',
			self::Data           => '#e67e22',
			self::Script         => '#d35400',
			self::Style          => '#9b59b6',
			self::Markup         => '#3498db',
			self::Vector         => '#e74c3c',
			self::ThreeD         => '#1abc9c',
			self::DiskImage      => '#7f8c8d',
			self::Backup         => '#34495e',
			self::Temporary      => '#bdc3c7',
			self::Other          => '#95a5a6',
		};
	}

	public function getPriority(): int
	{
		return match ($this) {
			self::Document       => 10,
			self::Spreadsheet    => 9,
			self::Presentation   => 8,
			self::Image          => 7,
			self::Video          => 6,
			self::Audio          => 5,
			self::Code           => 4,
			self::Database       => 3,
			self::Configuration  => 2,
			self::Executable     => 1,
			self::Archive        => 0,
			self::Text           => -1,
			self::Font           => -2,
			self::Ebook          => -3,
			self::Certificate    => -4,
			self::Log            => -5,
			self::Data           => -6,
			self::Script         => -7,
			self::Style          => -8,
			self::Markup         => -9,
			self::Vector         => -10,
			self::ThreeD         => -11,
			self::DiskImage      => -12,
			self::Backup         => -13,
			self::Temporary      => -14,
			self::Binary         => -15,
			self::Other          => -100,
		};
	}

	/**
	 * Convert from MimeType to FileCategory
	 */
	public static function fromMimeType(MimeType $mimeType): self
	{
		return match ($mimeType) {
			// Document types
			MimeType::APPLICATION_PDF,
			MimeType::APPLICATION_MSWORD,
			MimeType::APPLICATION_DOCX,
			MimeType::APPLICATION_RTF,
			MimeType::APPLICATION_ODT,
			MimeType::APPLICATION_EPUB => self::Document,

			// Spreadsheet types
			MimeType::APPLICATION_MSEXCEL,
			MimeType::APPLICATION_XLSX,
			MimeType::APPLICATION_ODS,
			MimeType::TEXT_CSV => self::Spreadsheet,

			// Presentation types
			MimeType::APPLICATION_MSPPT,
			MimeType::APPLICATION_PPTX,
			MimeType::APPLICATION_ODP => self::Presentation,

			// Image types
			MimeType::IMAGE_PNG,
			MimeType::IMAGE_JPEG,
			MimeType::IMAGE_GIF,
			MimeType::IMAGE_SVG,
			MimeType::IMAGE_WEBP,
			MimeType::IMAGE_BMP,
			MimeType::IMAGE_TIFF => self::Image,

			// Audio types
			MimeType::AUDIO_MPEG,
			MimeType::AUDIO_OGG,
			MimeType::AUDIO_WAV,
			MimeType::AUDIO_FLAC,
			MimeType::AUDIO_AAC => self::Audio,

			// Video types
			MimeType::VIDEO_MP4,
			MimeType::VIDEO_WEBM,
			MimeType::VIDEO_QUICKTIME,
			MimeType::VIDEO_AVI,
			MimeType::VIDEO_MPEG,
			MimeType::VIDEO_OGG => self::Video,

			// Archive types
			MimeType::APPLICATION_ZIP,
			MimeType::APPLICATION_7Z,
			MimeType::APPLICATION_RAR,
			MimeType::APPLICATION_GZIP,
			MimeType::APPLICATION_TAR,
			MimeType::APPLICATION_CBZ,
			MimeType::APPLICATION_CBR => self::Archive,

			// Code types
			MimeType::TEXT_PHP,
			MimeType::TEXT_PYTHON,
			MimeType::TEXT_JAVA,
			MimeType::TEXT_CPP,
			MimeType::TEXT_CSHARP,
			MimeType::TEXT_RUBY,
			MimeType::TEXT_SHELL,
			MimeType::APPLICATION_JSON,
			MimeType::APPLICATION_XML,
			MimeType::APPLICATION_SQL => self::Code,

			// Text types
			MimeType::TEXT_PLAIN,
			MimeType::TEXT_MARKDOWN,
			MimeType::TEXT_YAML,
			MimeType::TEXT_TOML => self::Text,

			// Configuration types
			MimeType::TEXT_PLAIN => self::Configuration, // .ini, .cfg, .conf files

			// Binary types
			MimeType::APPLICATION_OCTET_STREAM => self::Binary,

			// Font types
			MimeType::FONT_WOFF,
			MimeType::FONT_WOFF2,
			MimeType::FONT_TTF,
			MimeType::FONT_OTF => self::Font,

			// Database types
			MimeType::APPLICATION_SQLITE => self::Database,

			// Markup types
			MimeType::TEXT_HTML,
			MimeType::APPLICATION_XHTML,
			MimeType::APPLICATION_XML => self::Markup,

			// Style types
			MimeType::TEXT_CSS => self::Style,

			// Script types
			MimeType::TEXT_JS,
			MimeType::APPLICATION_JAVASCRIPT => self::Script,

			// Executable types
			MimeType::APPLICATION_OCTET_STREAM => self::Executable, // .exe, .app, etc.

			// Certificate types
			MimeType::APPLICATION_PKCS12,
			MimeType::APPLICATION_PKCS8,
			MimeType::APPLICATION_CERTIFICATE => self::Certificate,

			// Default fallback
			default => self::Other,
		};
	}

	/**
	 * Get file extensions for this category
	 */
	public function getExtensions(): array
	{
		return match ($this) {
			self::Document => ['pdf', 'doc', 'docx', 'odt', 'rtf', 'txt', 'md'],
			self::Spreadsheet => ['xls', 'xlsx', 'csv', 'ods', 'tsv'],
			self::Presentation => ['ppt', 'pptx', 'odp', 'key'],
			self::Image => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff', 'svg'],
			self::Audio => ['mp3', 'wav', 'ogg', 'flac', 'aac', 'm4a'],
			self::Video => ['mp4', 'mov', 'avi', 'mkv', 'webm', 'mpeg', 'wmv'],
			self::Archive => ['zip', 'rar', '7z', 'tar', 'gz', 'bz2'],
			self::Code => ['php', 'js', 'py', 'java', 'cpp', 'cs', 'rb', 'json', 'xml'],
			self::Text => ['txt', 'md', 'log', 'text', 'readme'],
			self::Configuration => ['ini', 'cfg', 'conf', 'yaml', 'yml', 'toml', 'json'],
			self::Binary => ['bin', 'exe', 'dll', 'so', 'dylib'],
			self::Font => ['ttf', 'otf', 'woff', 'woff2'],
			self::Database => ['db', 'sqlite', 'sql', 'mdb'],
			self::Ebook => ['epub', 'mobi', 'azw', 'pdf'],
			self::Executable => ['exe', 'app', 'msi', 'deb', 'rpm', 'apk'],
			self::Certificate => ['crt', 'pem', 'key', 'pfx', 'p12'],
			self::Log => ['log', 'txt', 'err', 'out'],
			self::Data => ['csv', 'json', 'xml', 'yaml', 'sql'],
			self::Script => ['sh', 'bash', 'ps1', 'bat', 'cmd'],
			self::Style => ['css', 'scss', 'sass', 'less'],
			self::Markup => ['html', 'htm', 'xml', 'md'],
			self::Vector => ['svg', 'ai', 'eps', 'pdf'],
			self::ThreeD => ['stl', 'obj', 'fbx', '3ds'],
			self::DiskImage => ['iso', 'dmg', 'img', 'vhd'],
			self::Backup => ['bak', 'backup', 'old', 'prev'],
			self::Temporary => ['tmp', 'temp', 'cache', 'swp'],
			self::Other => [],
		};
	}
}
