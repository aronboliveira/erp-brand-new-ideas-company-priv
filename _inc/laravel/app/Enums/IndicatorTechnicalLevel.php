<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum IndicatorTechnicalLevel: string
{
	case None = '0';
	case Beginner = '1';
	case Intermediate = '2';
	case Advanced = '3';
	case Expert = '4';

	public static function normalize(string|int|null|self $value = null): self
	{
		if ($value instanceof self)
			return $value;
		if ($value === null)
			return self::None;

		$normalizedValue = preg_replace('/[^a-z0-9]/', '', strtolower(trim((string) $value)));
		return match ($normalizedValue) {
			'0', 'none', 'notapplicable', 'na', 'n/a' => self::None,
			'1', 'beginner', 'novice', 'entry', 'basic', 'foundational' => self::Beginner,
			'2', 'intermediate', 'midlevel', 'competent', 'proficient' => self::Intermediate,
			'3', 'advanced', 'senior', 'experienced', 'skilled' => self::Advanced,
			'4', 'expert', 'leader', 'master', 'specialist', 'authority', 'expert/leader' => self::Expert,
			default => self::None,
		};
	}

	public static function values(): array
	{
		return array_map(fn($case) => $case->value, self::cases());
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

	public static function labelsEn(): array
	{
		return [
			self::None->value => 'None',
			self::Beginner->value => 'Beginner',
			self::Intermediate->value => 'Intermediate',
			self::Advanced->value => 'Advanced',
			self::Expert->value => 'Expert / Leader',
		];
	}

	public static function labelsPtBr(): array
	{
		return [
			self::None->value => 'Nenhum',
			self::Beginner->value => 'Iniciante',
			self::Intermediate->value => 'Intermediário',
			self::Advanced->value => 'Avançado',
			self::Expert->value => 'Especialista / Líder',
		];
	}

	public static function labelsEs(): array
	{
		return [
			self::None->value => 'Ninguno',
			self::Beginner->value => 'Principiante',
			self::Intermediate->value => 'Intermedio',
			self::Advanced->value => 'Avanzado',
			self::Expert->value => 'Experto / Líder',
		];
	}

	public static function labelsAr(): array
	{
		return [
			self::None->value => 'لا شيء',
			self::Beginner->value => 'مبتدئ',
			self::Intermediate->value => 'متوسط',
			self::Advanced->value => 'متقدم',
			self::Expert->value => 'خبير / قائد',
		];
	}

	public static function labelsDa(): array
	{
		return [
			self::None->value => 'Ingen',
			self::Beginner->value => 'Begynder',
			self::Intermediate->value => 'Mellemniveau',
			self::Advanced->value => 'Avanceret',
			self::Expert->value => 'Ekspert / Leder',
		];
	}

	public static function labelsDe(): array
	{
		return [
			self::None->value => 'Keine',
			self::Beginner->value => 'Anfänger',
			self::Intermediate->value => 'Mittelmäßig',
			self::Advanced->value => 'Fortgeschritten',
			self::Expert->value => 'Experte / Führungskraft',
		];
	}

	public static function labelsFr(): array
	{
		return [
			self::None->value => 'Aucun',
			self::Beginner->value => 'Débutant',
			self::Intermediate->value => 'Intermédiaire',
			self::Advanced->value => 'Avancé',
			self::Expert->value => 'Expert / Leader',
		];
	}

	public static function labelsHe(): array
	{
		return [
			self::None->value => 'ללא',
			self::Beginner->value => 'מתחיל',
			self::Intermediate->value => 'ביניים',
			self::Advanced->value => 'מתקדם',
			self::Expert->value => 'מומחה / מנהיג',
		];
	}

	public static function labelsIt(): array
	{
		return [
			self::None->value => 'Nessuno',
			self::Beginner->value => 'Principiante',
			self::Intermediate->value => 'Intermedio',
			self::Advanced->value => 'Avanzato',
			self::Expert->value => 'Esperto / Leader',
		];
	}

	public static function labelsJa(): array
	{
		return [
			self::None->value => 'なし',
			self::Beginner->value => '初心者',
			self::Intermediate->value => '中級者',
			self::Advanced->value => '上級者',
			self::Expert->value => 'エキスパート / リーダー',
		];
	}

	public static function labelsNl(): array
	{
		return [
			self::None->value => 'Geen',
			self::Beginner->value => 'Beginner',
			self::Intermediate->value => 'Gemiddeld',
			self::Advanced->value => 'Gevorderd',
			self::Expert->value => 'Expert / Leider',
		];
	}

	public static function labelsPl(): array
	{
		return [
			self::None->value => 'Brak',
			self::Beginner->value => 'Początkujący',
			self::Intermediate->value => 'Średniozaawansowany',
			self::Advanced->value => 'Zaawansowany',
			self::Expert->value => 'Ekspert / Lider',
		];
	}

	public static function labelsRu(): array
	{
		return [
			self::None->value => 'Отсутствует',
			self::Beginner->value => 'Начинающий',
			self::Intermediate->value => 'Средний уровень',
			self::Advanced->value => 'Продвинутый',
			self::Expert->value => 'Эксперт / Лидер',
		];
	}

	public static function labelsTr(): array
	{
		return [
			self::None->value => 'Yok',
			self::Beginner->value => 'Yeni Başlayan',
			self::Intermediate->value => 'Orta Seviye',
			self::Advanced->value => 'İleri Seviye',
			self::Expert->value => 'Uzman / Lider',
		];
	}

	public static function labelsZh(): array
	{
		return [
			self::None->value => '无',
			self::Beginner->value => '初级',
			self::Intermediate->value => '中级',
			self::Advanced->value => '高级',
			self::Expert->value => '专家 / 领导',
		];
	}

	/**
	 * Get the label for a specific technical level in the specified language
	 */
	public function label($lang = DatabaseConstants::DEFAULT_LANG): string
	{
		$labels = self::labels($lang);
		return $labels[$this->value] ?? '';
	}

	/**
	 * Get the short label without the "/ Leader" part
	 */
	public function shortLabel($lang = DatabaseConstants::DEFAULT_LANG): string
	{
		$label = $this->label($lang);
		// Remove " / Leader" part for Expert level
		if ($this === self::Expert) {
			return preg_replace('/\s*\/\s*Leader/i', '', $label);
		}
		return $label;
	}
	/**
	 * Get the description of what this level typically means
	 */
	public function getDescription($lang = DatabaseConstants::DEFAULT_LANG): string
	{
		$descriptions = self::descriptions($lang);
		return $descriptions[$this->value] ?? '';
	}

	public static function descriptions($lang = DatabaseConstants::DEFAULT_LANG): array
	{
		$lang = preg_replace('/_/', '-', strtolower(trim($lang ?? '')));
		return match ($lang) {
			'pt-br', 'pt' => self::descriptionsPtBr(),
			'es', 'es-es' => self::descriptionsEs(),
			'ar', 'ar-sa' => self::descriptionsAr(),
			'da', 'da-dk' => self::descriptionsDa(),
			'de', 'de-de' => self::descriptionsDe(),
			'fr', 'fr-fr' => self::descriptionsFr(),
			'he', 'he-il' => self::descriptionsHe(),
			'it', 'it-it' => self::descriptionsIt(),
			'ja', 'ja-jp' => self::descriptionsJa(),
			'nl', 'nl-nl' => self::descriptionsNl(),
			'pl', 'pl-pl' => self::descriptionsPl(),
			'ru', 'ru-ru' => self::descriptionsRu(),
			'tr', 'tr-tr' => self::descriptionsTr(),
			'zh', 'zh-cn' => self::descriptionsZh(),
			default => self::descriptionsEn(),
		};
	}

	public static function descriptionsPtBr(): array
	{
		return [
			self::None->value => 'Nenhum conhecimento técnico ou experiência necessária',
			self::Beginner->value => 'Compreensão básica, pode executar tarefas com orientação',
			self::Intermediate->value => 'Competente, pode trabalhar independentemente na maioria das tarefas',
			self::Advanced->value => 'Conhecimento profundo, pode resolver problemas complexos e orientar outros',
			self::Expert->value => 'Líder do setor, cria novos conhecimentos e estabelece padrões',
		];
	}

	public static function descriptionsEs(): array
	{
		return [
			self::None->value => 'No se requiere conocimiento técnico ni experiencia',
			self::Beginner->value => 'Comprensión básica, puede realizar tareas con orientación',
			self::Intermediate->value => 'Competente, puede trabajar independientemente en la mayoría de las tareas',
			self::Advanced->value => 'Experiencia profunda, puede resolver problemas complejos y orientar a otros',
			self::Expert->value => 'Líder de la industria, crea nuevos conocimientos y establece estándares',
		];
	}

	public static function descriptionsEn(): array
	{
		return [
			self::None->value => 'No technical knowledge or experience required',
			self::Beginner->value => 'Basic understanding, can perform tasks with guidance',
			self::Intermediate->value => 'Competent, can work independently on most tasks',
			self::Advanced->value => 'Deep expertise, can solve complex problems and mentor others',
			self::Expert->value => 'Industry leader, creates new knowledge and sets standards',
		];
	}

	public static function descriptionsAr(): array
	{
		return [
			self::None->value => 'لا يلزم معرفة أو خبرة تقنية',
			self::Beginner->value => 'فهم أساسي، يمكنه أداء المهام تحت التوجيه',
			self::Intermediate->value => 'كفء، يمكنه العمل بشكل مستقل في معظم المهام',
			self::Advanced->value => 'خبرة عميقة، يمكنه حل المشكلات المعقدة وتوجيه الآخرين',
			self::Expert->value => 'قائد في الصناعة، يخلق معارف جديدة ويضع المعايير',
		];
	}

	public static function descriptionsDa(): array
	{
		return [
			self::None->value => 'Ingen teknisk viden eller erfaring kræves',
			self::Beginner->value => 'Grundlæggende forståelse, kan udføre opgaver med vejledning',
			self::Intermediate->value => 'Kompetent, kan arbejde selvstændigt med de fleste opgaver',
			self::Advanced->value => 'Dyb ekspertise, kan løse komplekse problemer og vejlede andre',
			self::Expert->value => 'Brancheleder, skaber ny viden og sætter standarder',
		];
	}

	public static function descriptionsDe(): array
	{
		return [
			self::None->value => 'Keine technischen Kenntnisse oder Erfahrungen erforderlich',
			self::Beginner->value => 'Grundlegendes Verständnis, kann Aufgaben mit Anleitung ausführen',
			self::Intermediate->value => 'Kompetent, kann die meisten Aufgaben selbstständig bearbeiten',
			self::Advanced->value => 'Tiefgreifende Expertise, kann komplexe Probleme lösen und andere anleiten',
			self::Expert->value => 'Branchenführer, schafft neues Wissen und setzt Standards',
		];
	}

	public static function descriptionsFr(): array
	{
		return [
			self::None->value => 'Aucune connaissance ou expérience technique requise',
			self::Beginner->value => 'Compréhension de base, peut exécuter des tâches avec guidage',
			self::Intermediate->value => 'Compétent, peut travailler indépendamment sur la plupart des tâches',
			self::Advanced->value => 'Expertise approfondie, peut résoudre des problèmes complexes et encadrer d\'autres',
			self::Expert->value => 'Leader de l\'industrie, crée de nouvelles connaissances et établit des normes',
		];
	}

	public static function descriptionsHe(): array
	{
		return [
			self::None->value => 'לא נדרש ידע או ניסיון טכני',
			self::Beginner->value => 'הבנה בסיסית, יכול לבצע משימות בהנחייה',
			self::Intermediate->value => 'מוכשר, יכול לעבוד באופן עצמאי ברוב המשימות',
			self::Advanced->value => 'מומחיות עמוקה, יכול לפתור בעיות מורכבות ולהדריך אחרים',
			self::Expert->value => 'מנהיג בתעשייה, יוצר ידע חדש וקובע סטנדרטים',
		];
	}

	public static function descriptionsIt(): array
	{
		return [
			self::None->value => 'Nessuna conoscenza tecnica o esperienza richiesta',
			self::Beginner->value => 'Comprensione di base, può eseguire compiti con guida',
			self::Intermediate->value => 'Competente, può lavorare in modo indipendente sulla maggior parte dei compiti',
			self::Advanced->value => 'Competenza approfondita, può risolvere problemi complessi e guidare altri',
			self::Expert->value => 'Leader del settore, crea nuove conoscenze e stabilisce standard',
		];
	}

	public static function descriptionsJa(): array
	{
		return [
			self::None->value => '技術的知識や経験は不要',
			self::Beginner->value => '基本的な理解があり、指導を受けながら作業を実行できる',
			self::Intermediate->value => '有能であり、ほとんどの作業を独立して行うことができる',
			self::Advanced->value => '深い専門知識があり、複雑な問題を解決し、他の人を指導できる',
			self::Expert->value => '業界リーダーであり、新しい知識を創造し、基準を設定する',
		];
	}

	public static function descriptionsNl(): array
	{
		return [
			self::None->value => 'Geen technische kennis of ervaring vereist',
			self::Beginner->value => 'Basisbegrip, kan taken uitvoeren met begeleiding',
			self::Intermediate->value => 'Bekwaam, kan zelfstandig werken aan de meeste taken',
			self::Advanced->value => 'Diepgaande expertise, kan complexe problemen oplossen en anderen begeleiden',
			self::Expert->value => 'Brancheleider, creëert nieuwe kennis en stelt normen',
		];
	}

	public static function descriptionsPl(): array
	{
		return [
			self::None->value => 'Nie wymaga wiedzy technicznej ani doświadczenia',
			self::Beginner->value => 'Podstawowe zrozumienie, może wykonywać zadania z przewodnictwem',
			self::Intermediate->value => 'Kompetentny, może samodzielnie pracować nad większością zadań',
			self::Advanced->value => 'Dogłębna wiedza, może rozwiązywać złożone problemy i szkolić innych',
			self::Expert->value => 'Lider w branży, tworzy nową wiedzę i ustala standardy',
		];
	}

	public static function descriptionsRu(): array
	{
		return [
			self::None->value => 'Технические знания или опыт не требуются',
			self::Beginner->value => 'Базовое понимание, может выполнять задачи под руководством',
			self::Intermediate->value => 'Компетентен, может работать самостоятельно над большинством задач',
			self::Advanced->value => 'Глубокие знания, может решать сложные проблемы и наставлять других',
			self::Expert->value => 'Лидер в отрасли, создает новые знания и устанавливает стандарты',
		];
	}

	public static function descriptionsTr(): array
	{
		return [
			self::None->value => 'Teknik bilgi veya deneyim gerekmez',
			self::Beginner->value => 'Temel anlayış, rehberlikle görevleri yerine getirebilir',
			self::Intermediate->value => 'Yeterli, çoğu görevde bağımsız çalışabilir',
			self::Advanced->value => 'Derin uzmanlık, karmaşık sorunları çözebilir ve başkalarına rehberlik edebilir',
			self::Expert->value => 'Sektör lideri, yeni bilgi yaratır ve standartlar belirler',
		];
	}

	public static function descriptionsZh(): array
	{
		return [
			self::None->value => '不需要技术知识或经验',
			self::Beginner->value => '基本理解，可以在指导下执行任务',
			self::Intermediate->value => '胜任，可以独立完成大部分任务',
			self::Advanced->value => '深入的专业知识，可以解决复杂问题并指导他人',
			self::Expert->value => '行业领导者，创造新知识并设定标准',
		];
	}

	/**
	 * Get the icon for UI representation
	 */
	public function getIcon(): string
	{
		return match ($this) {
			self::None => 'circle-slash',
			self::Beginner => 'graduation-cap',
			self::Intermediate => 'book-open',
			self::Advanced => 'award',
			self::Expert => 'crown',
		};
	}

	/**
	 * Get the color for UI display
	 */
	public function getColor(): string
	{
		return match ($this) {
			self::None => '#6b7280', // gray
			self::Beginner => '#3b82f6', // blue
			self::Intermediate => '#10b981', // green
			self::Advanced => '#8b5cf6', // purple
			self::Expert => '#f59e0b', // amber
		};
	}

	/**
	 * Get the proficiency percentage (0-100)
	 */
	public function getProficiency(): int
	{
		return match ($this) {
			self::None => 0,
			self::Beginner => 25,
			self::Intermediate => 50,
			self::Advanced => 75,
			self::Expert => 100,
		};
	}

	/**
	 * Get the next higher level (or null if already highest)
	 */
	public function getNextHigher(): ?self
	{
		return match ($this) {
			self::None => self::Beginner,
			self::Beginner => self::Intermediate,
			self::Intermediate => self::Advanced,
			self::Advanced => self::Expert,
			self::Expert => null,
		};
	}

	/**
	 * Get the previous lower level (or null if already lowest)
	 */
	public function getPreviousLower(): ?self
	{
		return match ($this) {
			self::Expert => self::Advanced,
			self::Advanced => self::Intermediate,
			self::Intermediate => self::Beginner,
			self::Beginner => self::None,
			self::None => null,
		};
	}

	/**
	 * Check if this level is considered skilled (Intermediate or above)
	 */
	public function isSkilled(): bool
	{
		return $this->value >= self::Intermediate->value;
	}

	/**
	 * Check if this level is considered expert level
	 */
	public function isExpertLevel(): bool
	{
		return $this->value >= self::Advanced->value;
	}

	/**
	 * Get the typical years of experience range for this level
	 */
	public function getExperienceRange(): string
	{
		return match ($this) {
			self::None => '0 years',
			self::Beginner => '0-2 years',
			self::Intermediate => '2-5 years',
			self::Advanced => '5-10 years',
			self::Expert => '10+ years',
		};
	}

	/**
	 * Get the minimum score (0-100) required for this level
	 */
	public function getMinScore(): int
	{
		return match ($this) {
			self::None => 0,
			self::Beginner => 20,
			self::Intermediate => 40,
			self::Advanced => 60,
			self::Expert => 80,
		};
	}

	/**
	 * Convert a numeric score (0-100) to a technical level
	 */
	public static function fromScore(int $score): self
	{
		return match (true) {
			$score >= 80 => self::Expert,
			$score >= 60 => self::Advanced,
			$score >= 40 => self::Intermediate,
			$score >= 20 => self::Beginner,
			default => self::None,
		};
	}

	/**
	 * Get recommended learning resources for this level
	 */
	public function getLearningResources(): array
	{
		return match ($this) {
			self::None => [
				'introductory_courses',
				'foundational_books',
				'online_tutorials',
			],
			self::Beginner => [
				'guided_projects',
				'video_courses',
				'practice_exercises',
			],
			self::Intermediate => [
				'advanced_courses',
				'real_world_projects',
				'technical_books',
			],
			self::Advanced => [
				'specialized_certifications',
				'mentorship_programs',
				'conference_attendance',
			],
			self::Expert => [
				'teaching_opportunities',
				'research_publications',
				'industry_leadership',
			],
		};
	}

	/**
	 * Get the typical role titles for this technical level
	 */
	public function getRoleTitles(): array
	{
		return match ($this) {
			self::None => [
				'Non-technical',
				'General staff',
				'Business user',
			],
			self::Beginner => [
				'Junior Developer',
				'Entry-level Engineer',
				'Technical Assistant',
				'Trainee',
			],
			self::Intermediate => [
				'Developer',
				'Engineer',
				'Analyst',
				'Specialist',
			],
			self::Advanced => [
				'Senior Developer',
				'Lead Engineer',
				'Architect',
				'Technical Lead',
			],
			self::Expert => [
				'Principal Engineer',
				'Distinguished Engineer',
				'Technical Director',
				'CTO',
				'Fellow',
			],
		};
	}

	/**
	 * Check if this level qualifies for leadership responsibilities
	 */
	public function qualifiesForLeadership(): bool
	{
		return $this->value >= self::Advanced->value;
	}

	/**
	 * Get the certification level associated with this technical level
	 */
	public function getCertificationLevel(): string
	{
		return match ($this) {
			self::None => 'None',
			self::Beginner => 'Fundamental',
			self::Intermediate => 'Associate',
			self::Advanced => 'Professional',
			self::Expert => 'Master',
		};
	}

	/**
	 * Calculate the salary multiplier for this technical level
	 */
	public function getSalaryMultiplier(): float
	{
		return match ($this) {
			self::None => 1.0,
			self::Beginner => 1.2,
			self::Intermediate => 1.5,
			self::Advanced => 2.0,
			self::Expert => 3.0,
		};
	}
}
