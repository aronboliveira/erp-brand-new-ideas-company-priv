<?php

namespace App\Enums;

use App\Config\Constants\DatabaseConstants;

enum DEICategory: string
{
	// Gender & Sexual Identity
	case Women = 'women';
	case Men = 'men';
	case NonBinary = 'non_binary';
	case Transgender = 'transgender';
	case Genderqueer = 'genderqueer';
	case Genderfluid = 'genderfluid';
	case Agender = 'agender';
	case Intersex = 'intersex';
	case LGBTQPlus = 'lgbtq_plus';
	case Lesbian = 'lesbian';
	case Gay = 'gay';
	case Bisexual = 'bisexual';
	case Queer = 'queer';
	case Asexual = 'asexual';
	case Pansexual = 'pansexual';
	case TwoSpirit = 'two_spirit';

		// Disability & Accessibility
	case Disabled = 'disabled';
	case PhysicallyDisabled = 'physically_disabled';
	case VisuallyImpaired = 'visually_impaired';
	case HearingImpaired = 'hearing_impaired';
	case Neurodiverse = 'neurodiverse';
	case Autistic = 'autistic';
	case ADHD = 'adhd';
	case Dyslexic = 'dyslexic';
	case MentalHealth = 'mental_health';
	case MobilityImpairment = 'mobility_impairment';
	case ChronicIllness = 'chronic_illness';
	case InvisibleDisability = 'invisible_disability';

		// Race & Ethnicity
	case RacialMinority = 'racial_minority';
	case EthnicMinority = 'ethnic_minority';
	case Black = 'black';
	case African = 'african';
	case AfricanAmerican = 'african_american';
	case Asian = 'asian';
	case Hispanic = 'hispanic';
	case Latino = 'latino';
	case Indigenous = 'indigenous';
	case NativeAmerican = 'native_american';
	case PacificIslander = 'pacific_islander';
	case MiddleEastern = 'middle_eastern';
	case Multiracial = 'multiracial';
	case MixedRace = 'mixed_race';

		// Brazil-specific / additional (to fix your undefined constants)
	case Quilombola = 'quilombola';

		// India-specific / additional (to fix your undefined constants)
	case ScheduledCaste = 'scheduled_caste';
	case ScheduledTribe = 'scheduled_tribe';
	case OBC = 'obc';

		// Age
	case Youth = 'youth';
	case YoungAdult = 'young_adult';
	case Senior = 'senior';
	case AgeDiverse = 'age_diverse';
	case GenerationZ = 'generation_z';
	case Millennial = 'millennial';
	case GenerationX = 'generation_x';
	case BabyBoomer = 'baby_boomer';

		// Nationality & Migration
	case Immigrant = 'immigrant';
	case Refugee = 'refugee';
	case AsylumSeeker = 'asylum_seeker';
	case ForeignNational = 'foreign_national';
	case International = 'international';
	case MigrantWorker = 'migrant_worker';
	case Diaspora = 'diaspora';
	case Stateless = 'stateless';

		// Religion & Belief
	case ReligiousMinority = 'religious_minority';
	case Muslim = 'muslim';
	case Jewish = 'jewish';
	case Hindu = 'hindu';
	case Buddhist = 'buddhist';
	case Sikh = 'sikh';
	case ChristianMinority = 'christian_minority';
	case Atheist = 'atheist';
	case Agnostic = 'agnostic';
	case Spiritual = 'spiritual';

		// Socioeconomic
	case LowIncome = 'low_income';
	case EconomicallyDisadvantaged = 'economically_disadvantaged';
	case FirstGeneration = 'first_generation';
	case FirstGenerationProfessional = 'first_generation_professional';
	case FirstGenerationCollege = 'first_generation_college';
	case WorkingClass = 'working_class';
	case UnderrepresentedBackground = 'underrepresented_background';
	case SocioeconomicallyDisadvantaged = 'socioeconomically_disadvantaged';

		// Military & Veterans
	case Veteran = 'veteran';
	case MilitaryFamily = 'military_family';
	case ActiveDuty = 'active_duty';
	case Reservist = 'reservist';
	case MilitarySpouse = 'military_spouse';
	case GoldStarFamily = 'gold_star_family';

		// Education & Language
	case EnglishLanguageLearner = 'english_language_learner';
	case ESL = 'esl';
	case NonNativeSpeaker = 'non_native_speaker';
	case DifferentAbledLearner = 'different_abled_learner';
	case AlternativeEducation = 'alternative_education';

		// Family & Caregiving
	case SingleParent = 'single_parent';
	case Caregiver = 'caregiver';
	case Parent = 'parent';
	case FosterYouth = 'foster_youth';
	case Adoptee = 'adoptee';
	case Orphan = 'orphan';

		// Regional & Geographic
	case Rural = 'rural';
	case Urban = 'urban';
	case Suburban = 'suburban';
	case RemoteArea = 'remote_area';
	case UnderservedRegion = 'underserved_region';

		// Other Marginalized Groups
	case FormerlyIncarcerated = 'formerly_incarcerated';
	case JusticeInvolved = 'justice_involved';
	case Homeless = 'homeless';
	case DomesticViolenceSurvivor = 'domestic_violence_survivor';
	case HumanTraffickingSurvivor = 'human_trafficking_survivor';
	case AddictionRecovery = 'addiction_recovery';

		// Intersectional Categories
	case WomenOfColor = 'women_of_color';
	case DisabledWomen = 'disabled_women';
	case LGBTQPlusYouth = 'lgbtq_plus_youth';
	case IndigenousWomen = 'indigenous_women';
	case DisabledVeteran = 'disabled_veteran';

		// General
	case DiverseBackground = 'diverse_background';
	case Underrepresented = 'underrepresented';
	case Marginalized = 'marginalized';
	case Minority = 'minority';
	case ProtectedClass = 'protected_class';

	/**
	 * Normalize input to DEICategory
	 */
	public static function normalize(string|int|null|self $value = null): ?self
	{
		if ($value instanceof self) {
			return $value;
		}
		if ($value === null) {
			return null;
		}

		$normalizedValue = preg_replace('/[^a-z0-9]/', '', strtolower(trim((string) $value)));

		return match ($normalizedValue) {
			// Gender & Sexual Identity
			'women', 'woman', 'female', 'mulher', 'mujer' => self::Women,
			'men', 'man', 'male', 'homem', 'hombre' => self::Men,
			'nonbinary', 'nb', 'naobinario' => self::NonBinary,
			'transgender', 'trans', 'transgenero', 'transgnero', 'transgénero' => self::Transgender,
			'genderqueer' => self::Genderqueer,
			'genderfluid' => self::Genderfluid,
			'agender', 'agenero', 'agênero' => self::Agender,
			'intersex', 'intersexo' => self::Intersex,
			'lgbtq', 'lgbt', 'lgbtqia', 'lgbtplus' => self::LGBTQPlus,
			'lesbian', 'lesbica', 'lesbiana' => self::Lesbian,
			'gay' => self::Gay,
			'bisexual', 'bissexual' => self::Bisexual,
			'queer' => self::Queer,
			'asexual', 'assexual' => self::Asexual,
			'pansexual' => self::Pansexual,
			'twospirit', 'twospirit', 'espiritoduplo' => self::TwoSpirit,

			// Disability & Accessibility
			'disabled', 'disability', 'deficiencia', 'deficiente' => self::Disabled,
			'physicallydisabled', 'physicaldisability' => self::PhysicallyDisabled,
			'visuallyimpaired', 'blind', 'lowvision' => self::VisuallyImpaired,
			'hearingimpaired', 'deaf', 'hardofhearing' => self::HearingImpaired,
			'neurodiverse', 'neurodivergent', 'neurodiversidade' => self::Neurodiverse,
			'autistic', 'autism', 'autista' => self::Autistic,
			'adhd', 'add', 'tdah' => self::ADHD,
			'dyslexic', 'dyslexia', 'dislexia' => self::Dyslexic,
			'mentalhealth', 'mentalillness', 'saudemental' => self::MentalHealth,
			'mobilityimpairment', 'mobilitydisability' => self::MobilityImpairment,
			'chronicillness', 'chronicdisease', 'doencacronica' => self::ChronicIllness,
			'invisibledisability', 'hiddendisability' => self::InvisibleDisability,

			// Race & Ethnicity
			'racialminority', 'racialdiversity' => self::RacialMinority,
			'ethnicminority', 'ethnicdiversity' => self::EthnicMinority,
			'black', 'afrodescendente', 'negro' => self::Black,
			'african', 'africano' => self::African,
			'africanamerican', 'afroamerican', 'afroamericano' => self::AfricanAmerican,
			'asian', 'asiatico', 'asiático' => self::Asian,
			'hispanic', 'hispano', 'hispanico', 'hispânico' => self::Hispanic,
			'latino', 'latina', 'latinoamerican' => self::Latino,
			'indigenous', 'indigena', 'indígena' => self::Indigenous,
			'nativeamerican', 'native', 'indigenaamericano' => self::NativeAmerican,
			'pacificislander' => self::PacificIslander,
			'middleeastern', 'arab', 'orientemedio' => self::MiddleEastern,
			'multiracial', 'multietnico', 'multiétnico' => self::Multiracial,
			'mixedrace', 'mestizo', 'metis', 'métis' => self::MixedRace,
			'quilombola', 'quilombo' => self::Quilombola,

			// India-specific
			'scheduledcaste', 'sc' => self::ScheduledCaste,
			'scheduledtribe', 'st' => self::ScheduledTribe,
			'obc', 'otherbackwardclass', 'otherbackwardclasses' => self::OBC,

			// Age
			'youth', 'young', 'joven', 'jeune' => self::Youth,
			'youngadult', 'youngadults' => self::YoungAdult,
			'senior', 'elderly', 'idoso', 'anciano' => self::Senior,
			'agediverse', 'agediversity' => self::AgeDiverse,
			'generationz', 'genz' => self::GenerationZ,
			'millennial', 'millennials', 'millenial' => self::Millennial,
			'generationx', 'genx' => self::GenerationX,
			'babyboomer', 'boomer' => self::BabyBoomer,

			// Nationality & Migration
			'immigrant', 'immigrante' => self::Immigrant,
			'refugee', 'refugiado' => self::Refugee,
			'asylumseeker', 'asiloseeker' => self::AsylumSeeker,
			'foreignnational', 'foreigner', 'estrangeiro' => self::ForeignNational,
			'international', 'internacional' => self::International,
			'migrantworker', 'migrant' => self::MigrantWorker,
			'diaspora', 'diaspora', 'diáspora' => self::Diaspora,
			'stateless', 'apatrida' => self::Stateless,

			// Religion & Belief
			'religiousminority', 'religiousdiversity' => self::ReligiousMinority,
			'muslim', 'islam', 'muculmano', 'muçulmano' => self::Muslim,
			'jewish', 'jew', 'judio', 'judeu' => self::Jewish,
			'hindu', 'hinduism', 'hindusta', 'hinduísta' => self::Hindu,
			'buddhist', 'buddhism', 'budista' => self::Buddhist,
			'sikh', 'sikhism' => self::Sikh,
			'christianminority', 'minoritychristian' => self::ChristianMinority,
			'atheist', 'atheism', 'ateu' => self::Atheist,
			'agnostic', 'agnosticism', 'agnostico', 'agnóstico' => self::Agnostic,
			'spiritual', 'espiritual' => self::Spiritual,

			// Socioeconomic
			'lowincome', 'baixarenda' => self::LowIncome,
			'economicallydisadvantaged', 'economicdisadvantage' => self::EconomicallyDisadvantaged,
			'firstgeneration', 'firstgen' => self::FirstGeneration,
			'firstgenerationprofessional', 'firstgenpro' => self::FirstGenerationProfessional,
			'firstgenerationcollege', 'firstgencollege' => self::FirstGenerationCollege,
			'workingclass', 'clasetrabajadora' => self::WorkingClass,
			'underrepresentedbackground', 'underrepbackground' => self::UnderrepresentedBackground,
			'socioeconomicallydisadvantaged', 'socioeconomicdisadvantage' => self::SocioeconomicallyDisadvantaged,

			// Military & Veterans
			'veteran', 'veterano' => self::Veteran,
			'militaryfamily', 'militaryfamilies' => self::MilitaryFamily,
			'activeduty', 'activeservice' => self::ActiveDuty,
			'reservist', 'reservista' => self::Reservist,
			'militaryspouse', 'militarypartner' => self::MilitarySpouse,
			'goldstarfamily', 'goldstar' => self::GoldStarFamily,

			// Education & Language
			'englishlanguagelearner', 'ell' => self::EnglishLanguageLearner,
			'esl', 'englishsecondlanguage' => self::ESL,
			'nonnativespeaker', 'nonnative' => self::NonNativeSpeaker,
			'differentabledlearner', 'specialneeds' => self::DifferentAbledLearner,
			'alternativeeducation', 'alternativeducation' => self::AlternativeEducation,

			// Family & Caregiving
			'singleparent', 'singlemom', 'singledad' => self::SingleParent,
			'caregiver', 'caretaker', 'cuidador' => self::Caregiver,
			'parent', 'parents', 'pais' => self::Parent,
			'fosteryouth', 'fostercare' => self::FosterYouth,
			'adoptee', 'adopted' => self::Adoptee,
			'orphan', 'orphaned' => self::Orphan,

			// Regional & Geographic
			'rural', 'ruralarea', 'campo' => self::Rural,
			'urban', 'city', 'urbano' => self::Urban,
			'suburban', 'suburbs', 'suburbio', 'subúrbio' => self::Suburban,
			'remotearea', 'remoteregion' => self::RemoteArea,
			'underservedregion', 'underservedarea' => self::UnderservedRegion,

			// Other Marginalized Groups
			'formerlyincarcerated', 'exoffender', 'exdetento' => self::FormerlyIncarcerated,
			'justiceinvolved', 'justiceimpacted' => self::JusticeInvolved,
			'homeless', 'homelessness', 'semteto', 'semtetol' => self::Homeless,
			'domesticviolencesurvivor', 'dvsurvivor' => self::DomesticViolenceSurvivor,
			'humantraffickingsurvivor', 'traffickingsurvivor' => self::HumanTraffickingSurvivor,
			'addictionrecovery', 'recoveringaddict' => self::AddictionRecovery,

			// Intersectional Categories
			'womenofcolor', 'woc' => self::WomenOfColor,
			'disabledwomen', 'womenwithdisabilities' => self::DisabledWomen,
			'lgbtqplusyouth', 'lgbtqyouth' => self::LGBTQPlusYouth,
			'indigenouswomen', 'nativewomen' => self::IndigenousWomen,
			'disabledveteran', 'veteranwithdisability' => self::DisabledVeteran,

			// General
			'diversebackground', 'diverse' => self::DiverseBackground,
			'underrepresented', 'underrep' => self::Underrepresented,
			'marginalized', 'marginalised' => self::Marginalized,
			'minority', 'minorias', 'minorias' => self::Minority,
			'protectedclass', 'protectedgroup' => self::ProtectedClass,

			default => null,
		};
	}

	/**
	 * Get labels in specified language
	 */
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

	/**
	 * Get label for this DEI category in specified language
	 */
	public function label($lang = DatabaseConstants::DEFAULT_LANG): string
	{
		$labels = self::labels($lang);
		return $labels[$this->value] ?? ucfirst(str_replace('_', ' ', $this->value));
	}

	/**
	 * Get color for UI display
	 */
	public function getColor(): string
	{
		return match ($this) {
			// Gender & Sexual Identity - rainbow colors
			self::Women, self::Lesbian => '#ec4899', // Pink
			self::Men => '#3b82f6', // Blue
			self::NonBinary, self::Genderqueer, self::Genderfluid => '#8b5cf6', // Purple
			self::Transgender => '#60a5fa', // Light blue
			self::LGBTQPlus, self::Gay, self::Bisexual, self::Queer,
			self::Asexual, self::Pansexual, self::TwoSpirit => '#6366f1', // Indigo
			self::Intersex, self::Agender => '#f59e0b', // Amber

			// Disability & Accessibility - green/teal
			self::Disabled, self::PhysicallyDisabled => '#10b981',
			self::VisuallyImpaired, self::HearingImpaired => '#14b8a6',
			self::Neurodiverse, self::Autistic, self::ADHD, self::Dyslexic => '#0d9488',
			self::MentalHealth, self::ChronicIllness => '#059669',
			self::MobilityImpairment, self::InvisibleDisability => '#047857',

			// Race & Ethnicity - brown/gold
			self::RacialMinority, self::EthnicMinority => '#92400e',
			self::Black, self::African, self::AfricanAmerican => '#000000',
			self::Asian => '#dc2626', // Red
			self::Hispanic, self::Latino => '#ea580c', // Orange
			self::Indigenous, self::NativeAmerican => '#b45309', // Amber
			self::PacificIslander => '#0369a1', // Blue
			self::MiddleEastern => '#7c2d12', // Brown
			self::Multiracial, self::MixedRace => '#ca8a04', // Yellow

			// Added (Brazil / India-specific)
			self::Quilombola => '#111827', // Near-black / charcoal
			self::ScheduledCaste => '#b45309', // Amber-brown
			self::ScheduledTribe => '#92400e', // Brown
			self::OBC => '#ca8a04', // Yellow

			// Age - purple/violet
			self::Youth, self::YoungAdult => '#7c3aed',
			self::Senior, self::BabyBoomer => '#6d28d9',
			self::AgeDiverse => '#8b5cf6',
			self::GenerationZ => '#9333ea',
			self::Millennial => '#a855f7',
			self::GenerationX => '#c084fc',

			// Nationality & Migration - blue
			self::Immigrant, self::Refugee, self::AsylumSeeker => '#1d4ed8',
			self::ForeignNational, self::International => '#2563eb',
			self::MigrantWorker, self::Diaspora => '#3b82f6',
			self::Stateless => '#60a5fa',

			// Religion & Belief - dark blue
			self::ReligiousMinority => '#1e40af',
			self::Muslim => '#009688', // Teal
			self::Jewish => '#ff9800', // Orange
			self::Hindu => '#ff5722', // Deep orange
			self::Buddhist => '#4caf50', // Green
			self::Sikh => '#ffc107', // Amber
			self::ChristianMinority => '#2196f3', // Blue
			self::Atheist, self::Agnostic => '#607d8b', // Blue grey
			self::Spiritual => '#9c27b0', // Purple

			// Socioeconomic - orange
			self::LowIncome, self::EconomicallyDisadvantaged => '#f97316',
			self::FirstGeneration, self::FirstGenerationProfessional,
			self::FirstGenerationCollege => '#ea580c',
			self::WorkingClass => '#dc2626',
			self::UnderrepresentedBackground, self::SocioeconomicallyDisadvantaged => '#c2410c',

			// Military & Veterans - green
			self::Veteran, self::ActiveDuty, self::Reservist => '#15803d',
			self::MilitaryFamily, self::MilitarySpouse => '#16a34a',
			self::GoldStarFamily => '#22c55e',

			// Education & Language - teal
			self::EnglishLanguageLearner, self::ESL, self::NonNativeSpeaker => '#0d9488',
			self::DifferentAbledLearner, self::AlternativeEducation => '#14b8a6',

			// Family & Caregiving - pink
			self::SingleParent, self::Caregiver => '#db2777',
			self::Parent => '#be185d',
			self::FosterYouth, self::Adoptee, self::Orphan => '#ec4899',

			// Regional & Geographic - gray
			self::Rural, self::Urban, self::Suburban => '#6b7280',
			self::RemoteArea, self::UnderservedRegion => '#4b5563',

			// Other Marginalized Groups - red
			self::FormerlyIncarcerated, self::JusticeInvolved => '#dc2626',
			self::Homeless => '#b91c1c',
			self::DomesticViolenceSurvivor, self::HumanTraffickingSurvivor => '#991b1b',
			self::AddictionRecovery => '#ef4444',

			// Intersectional Categories - multi-color/gradient
			self::WomenOfColor => '#9333ea', // Purple
			self::DisabledWomen => '#10b981', // Green
			self::LGBTQPlusYouth => '#3b82f6', // Blue
			self::IndigenousWomen => '#b45309', // Amber
			self::DisabledVeteran => '#15803d', // Green

			// General - neutral
			self::DiverseBackground, self::Underrepresented,
			self::Marginalized, self::Minority => '#6b7280',
			self::ProtectedClass => '#374151',

			default => '#9ca3af',
		};
	}

	/**
	 * Get icon for UI representation
	 */
	public function getIcon(): string
	{
		return match ($this) {
			// Gender & Sexual Identity
			self::Women => 'venus',
			self::Men => 'mars',
			self::NonBinary, self::Genderqueer, self::Genderfluid => 'transgender',
			self::Transgender => 'transgender-alt',
			self::LGBTQPlus, self::Gay, self::Lesbian, self::Bisexual,
			self::Queer, self::Asexual, self::Pansexual => 'rainbow',
			self::TwoSpirit => 'feather',
			self::Intersex => 'intersex',
			self::Agender => 'genderless',

			// Disability & Accessibility
			self::Disabled, self::PhysicallyDisabled => 'wheelchair',
			self::VisuallyImpaired => 'eye-slash',
			self::HearingImpaired => 'deaf',
			self::Neurodiverse, self::Autistic => 'brain',
			self::ADHD => 'bolt',
			self::Dyslexic => 'spell-check',
			self::MentalHealth => 'heartbeat',
			self::MobilityImpairment => 'walking',
			self::ChronicIllness => 'stethoscope',
			self::InvisibleDisability => 'user-injured',

			// Race & Ethnicity
			self::RacialMinority, self::EthnicMinority => 'users',
			self::Black, self::African, self::AfricanAmerican => 'user-friends',
			self::Asian => 'globe-asia',
			self::Hispanic, self::Latino => 'flag',
			self::Indigenous, self::NativeAmerican => 'feather-alt',
			self::PacificIslander => 'umbrella-beach',
			self::MiddleEastern => 'mosque',
			self::Multiracial, self::MixedRace => 'blender',

			// Added (Brazil / India-specific)
			self::Quilombola => 'users',
			self::ScheduledCaste => 'layer-group',
			self::ScheduledTribe => 'people-arrows',
			self::OBC => 'users-cog',

			// Age
			self::Youth, self::YoungAdult => 'child',
			self::Senior => 'user-alt',
			self::AgeDiverse => 'users',
			self::GenerationZ, self::Millennial, self::GenerationX,
			self::BabyBoomer => 'calendar-alt',

			// Nationality & Migration
			self::Immigrant, self::Refugee, self::AsylumSeeker => 'passport',
			self::ForeignNational, self::International => 'globe',
			self::MigrantWorker => 'briefcase',
			self::Diaspora => 'route',
			self::Stateless => 'user-slash',

			// Religion & Belief
			self::ReligiousMinority => 'pray',
			self::Muslim => 'star-and-crescent',
			self::Jewish => 'star-of-david',
			self::Hindu => 'om',
			self::Buddhist => 'yin-yang',
			self::Sikh => 'khanda',
			self::ChristianMinority => 'cross',
			self::Atheist, self::Agnostic => 'question-circle',
			self::Spiritual => 'seedling',

			// Socioeconomic
			self::LowIncome, self::EconomicallyDisadvantaged => 'money-bill-wave',
			self::FirstGeneration, self::FirstGenerationProfessional,
			self::FirstGenerationCollege => 'user-graduate',
			self::WorkingClass => 'tools',
			self::UnderrepresentedBackground, self::SocioeconomicallyDisadvantaged => 'handshake',

			// Military & Veterans
			self::Veteran, self::ActiveDuty, self::Reservist => 'medal',
			self::MilitaryFamily, self::MilitarySpouse => 'home',
			self::GoldStarFamily => 'star',

			// Education & Language
			self::EnglishLanguageLearner, self::ESL, self::NonNativeSpeaker => 'language',
			self::DifferentAbledLearner => 'user-graduate',
			self::AlternativeEducation => 'school',

			// Family & Caregiving
			self::SingleParent, self::Parent => 'users',
			self::Caregiver => 'hands-helping',
			self::FosterYouth, self::Adoptee => 'heart',
			self::Orphan => 'home',

			// Regional & Geographic
			self::Rural => 'tractor',
			self::Urban => 'city',
			self::Suburban => 'home',
			self::RemoteArea => 'mountain',
			self::UnderservedRegion => 'map-marker-alt',

			// Other Marginalized Groups
			self::FormerlyIncarcerated, self::JusticeInvolved => 'gavel',
			self::Homeless => 'house-damage',
			self::DomesticViolenceSurvivor => 'shield-alt',
			self::HumanTraffickingSurvivor => 'hands',
			self::AddictionRecovery => 'hand-holding-heart',

			// Intersectional Categories
			self::WomenOfColor => 'venus-double',
			self::DisabledWomen => 'female wheelchair',
			self::LGBTQPlusYouth => 'child rainbow',
			self::IndigenousWomen => 'female feather',
			self::DisabledVeteran => 'wheelchair medal',

			// General
			self::DiverseBackground, self::Underrepresented,
			self::Marginalized, self::Minority => 'users',
			self::ProtectedClass => 'shield',

			default => 'user-friends',
		};
	}

	/**
	 * Check if this is a gender/sexual identity category
	 */
	public function isGenderIdentity(): bool
	{
		return in_array($this, [
			self::Women,
			self::Men,
			self::NonBinary,
			self::Transgender,
			self::Genderqueer,
			self::Genderfluid,
			self::Agender,
			self::Intersex,
			self::LGBTQPlus,
			self::Lesbian,
			self::Gay,
			self::Bisexual,
			self::Queer,
			self::Asexual,
			self::Pansexual,
			self::TwoSpirit,
		], true);
	}

	/**
	 * Check if this is a disability/accessibility category
	 */
	public function isDisabilityCategory(): bool
	{
		return in_array($this, [
			self::Disabled,
			self::PhysicallyDisabled,
			self::VisuallyImpaired,
			self::HearingImpaired,
			self::Neurodiverse,
			self::Autistic,
			self::ADHD,
			self::Dyslexic,
			self::MentalHealth,
			self::MobilityImpairment,
			self::ChronicIllness,
			self::InvisibleDisability,
		], true);
	}

	/**
	 * Check if this is a race/ethnicity category
	 */
	public function isRaceEthnicity(): bool
	{
		return in_array($this, [
			self::RacialMinority,
			self::EthnicMinority,
			self::Black,
			self::African,
			self::AfricanAmerican,
			self::Asian,
			self::Hispanic,
			self::Latino,
			self::Indigenous,
			self::NativeAmerican,
			self::PacificIslander,
			self::MiddleEastern,
			self::Multiracial,
			self::MixedRace,

			// Added
			self::Quilombola,
			self::ScheduledCaste,
			self::ScheduledTribe,
			self::OBC,
		], true);
	}

	/**
	 * Check if this is a protected class (legally protected in many jurisdictions)
	 */
	public function isProtectedClass(): bool
	{
		return in_array($this, [
			// Gender
			self::Women,
			self::Men,

			// Disability
			self::Disabled,
			self::PhysicallyDisabled,
			self::VisuallyImpaired,
			self::HearingImpaired,

			// Race/Ethnicity
			self::Black,
			self::African,
			self::AfricanAmerican,
			self::Asian,
			self::Hispanic,
			self::Latino,
			self::Indigenous,
			self::NativeAmerican,
			self::PacificIslander,
			self::MiddleEastern,
			self::Multiracial,

			// Added (jurisdiction-dependent; treated as protected here for UI/business rules)
			self::Quilombola,
			self::ScheduledCaste,
			self::ScheduledTribe,
			self::OBC,

			// Age
			self::Senior,

			// National origin
			self::Immigrant,
			self::Refugee,
			self::ForeignNational,

			// Religion
			self::ReligiousMinority,
			self::Muslim,
			self::Jewish,
			self::Hindu,
			self::Buddhist,
			self::Sikh,
			self::ChristianMinority,

			// Other protected
			self::Veteran,
		], true);
	}

	/**
	 * Get DEI category type
	 */
	public function getCategoryType(): string
	{
		return match ($this) {
			// Gender & Sexual Identity
			self::Women, self::Men, self::NonBinary, self::Transgender,
			self::Genderqueer, self::Genderfluid, self::Agender, self::Intersex,
			self::LGBTQPlus, self::Lesbian, self::Gay, self::Bisexual,
			self::Queer, self::Asexual, self::Pansexual, self::TwoSpirit => 'gender_sexual_identity',

			// Disability & Accessibility
			self::Disabled, self::PhysicallyDisabled, self::VisuallyImpaired,
			self::HearingImpaired, self::Neurodiverse, self::Autistic, self::ADHD,
			self::Dyslexic, self::MentalHealth, self::MobilityImpairment,
			self::ChronicIllness, self::InvisibleDisability => 'disability_accessibility',

			// Race & Ethnicity
			self::RacialMinority, self::EthnicMinority, self::Black,
			self::African, self::AfricanAmerican, self::Asian, self::Hispanic,
			self::Latino, self::Indigenous, self::NativeAmerican,
			self::PacificIslander, self::MiddleEastern, self::Multiracial,
			self::MixedRace,

			// Added
			self::Quilombola,
			self::ScheduledCaste,
			self::ScheduledTribe,
			self::OBC => 'race_ethnicity',

			// Age
			self::Youth, self::YoungAdult, self::Senior, self::AgeDiverse,
			self::GenerationZ, self::Millennial, self::GenerationX,
			self::BabyBoomer => 'age',

			// Nationality & Migration
			self::Immigrant, self::Refugee, self::AsylumSeeker,
			self::ForeignNational, self::International, self::MigrantWorker,
			self::Diaspora, self::Stateless => 'nationality_migration',

			// Religion & Belief
			self::ReligiousMinority, self::Muslim, self::Jewish, self::Hindu,
			self::Buddhist, self::Sikh, self::ChristianMinority, self::Atheist,
			self::Agnostic, self::Spiritual => 'religion_belief',

			// Socioeconomic
			self::LowIncome, self::EconomicallyDisadvantaged, self::FirstGeneration,
			self::FirstGenerationProfessional, self::FirstGenerationCollege,
			self::WorkingClass, self::UnderrepresentedBackground,
			self::SocioeconomicallyDisadvantaged => 'socioeconomic',

			// Military & Veterans
			self::Veteran, self::MilitaryFamily, self::ActiveDuty,
			self::Reservist, self::MilitarySpouse, self::GoldStarFamily => 'military_veterans',

			// Education & Language
			self::EnglishLanguageLearner, self::ESL, self::NonNativeSpeaker,
			self::DifferentAbledLearner, self::AlternativeEducation => 'education_language',

			// Family & Caregiving
			self::SingleParent, self::Caregiver, self::Parent, self::FosterYouth,
			self::Adoptee, self::Orphan => 'family_caregiving',

			// Regional & Geographic
			self::Rural, self::Urban, self::Suburban, self::RemoteArea,
			self::UnderservedRegion => 'regional_geographic',

			// Other Marginalized Groups
			self::FormerlyIncarcerated, self::JusticeInvolved, self::Homeless,
			self::DomesticViolenceSurvivor, self::HumanTraffickingSurvivor,
			self::AddictionRecovery => 'other_marginalized',

			// Intersectional Categories
			self::WomenOfColor, self::DisabledWomen, self::LGBTQPlusYouth,
			self::IndigenousWomen, self::DisabledVeteran => 'intersectional',

			// General
			self::DiverseBackground, self::Underrepresented, self::Marginalized,
			self::Minority, self::ProtectedClass => 'general',

			default => 'other',
		};
	}

	/**
	 * Get typical accommodations or considerations for this category
	 */
	public function getAccommodations(): array
	{
		return match ($this) {
			// Disability categories
			self::Disabled, self::PhysicallyDisabled, self::MobilityImpairment => [
				'physical_accessibility',
				'assistive_technology',
				'flexible_work_arrangements',
				'accessible_restrooms',
				'parking_accommodations',
			],
			self::VisuallyImpaired => [
				'screen_readers',
				'braille_materials',
				'audio_description',
				'contrast_adjustments',
				'assistive_technology',
			],
			self::HearingImpaired => [
				'sign_language_interpreters',
				'captioning_services',
				'assistive_listening_devices',
				'visual_alerts',
				'communication_access',
			],
			self::Neurodiverse, self::Autistic, self::ADHD => [
				'quiet_workspaces',
				'noise_cancelling_headphones',
				'flexible_scheduling',
				'clear_communication',
				'structured_environment',
			],
			self::MentalHealth => [
				'mental_health_days',
				'flexible_work_hours',
				'employee_assistance_programs',
				'reduced_stress_environment',
				'therapy_access',
			],

			// Religious accommodations
			self::Muslim, self::Jewish, self::Hindu, self::Sikh,
			self::ReligiousMinority => [
				'prayer_spaces',
				'religious_holiday_accommodations',
				'dietary_accommodations',
				'dress_code_accommodations',
				'flexible_scheduling_for_worship',
			],

			// Family accommodations
			self::SingleParent, self::Parent, self::Caregiver => [
				'flexible_work_hours',
				'childcare_support',
				'parental_leave',
				'remote_work_options',
				'family_emergency_leave',
			],

			// Language accommodations
			self::EnglishLanguageLearner, self::ESL, self::NonNativeSpeaker => [
				'translation_services',
				'language_classes',
				'bilingual_materials',
				'cultural_orientation',
				'mentorship_programs',
			],

			// Age accommodations
			self::Senior => [
				'ergonomic_workstations',
				'flexible_retirement_options',
				'health_benefits',
				'intergenerational_mentoring',
				'age_diversity_training',
			],
			self::Youth, self::YoungAdult => [
				'mentorship_programs',
				'career_development',
				'student_loan_assistance',
				'skills_training',
				'internship_opportunities',
			],

			// Added (Brazil / India-specific)
			self::Quilombola => [
				'anti_discrimination_policy_enforcement',
				'community_partnerships',
				'mentorship_programs',
				'cultural_safety_training',
				'equitable_hiring_and_promotion_reviews',
			],
			self::ScheduledCaste, self::ScheduledTribe, self::OBC => [
				'anti_discrimination_policy_enforcement',
				'confidential_reporting_channels',
				'mentorship_programs',
				'fair_access_to_opportunities',
				'inclusive_practices',
			],

			default => ['general_accommodations', 'inclusive_practices'],
		};
	}


	/**
	 * Get legal protections applicable (examples by region)
	 */
	public function getLegalProtections(): string
	{
		return match ($this) {
			// US protections (Title VII, ADA, etc.)
			self::Women, self::Men => 'Title VII (Civil Rights Act) - Gender',
			self::Disabled, self::PhysicallyDisabled, self::VisuallyImpaired, self::HearingImpaired
			=> 'ADA (Americans with Disabilities Act)',
			self::Black, self::AfricanAmerican, self::Asian, self::Hispanic, self::NativeAmerican, self::PacificIslander
			=> 'Title VII - Race/Color',
			self::Immigrant, self::ForeignNational => 'Immigration and Nationality Act',
			self::ReligiousMinority, self::Muslim, self::Jewish, self::ChristianMinority => 'Title VII - Religion',
			self::Senior => 'ADEA (Age Discrimination in Employment Act)',
			self::Veteran => 'USERRA (Uniformed Services Employment and Reemployment Rights Act)',

			// European Union
			self::LGBTQPlus, self::Gay, self::Lesbian, self::Transgender => 'EU Equality Directive',

			// Brazil (added Quilombola here)
			self::Indigenous, self::Quilombola => 'Brazilian Constitution, Statute of Racial Equality',

			// South Africa
			self::Black, self::African => 'Employment Equity Act (South Africa)',

			// India (added SC/ST/OBC here)
			self::ScheduledCaste, self::ScheduledTribe, self::OBC => 'Indian Constitution - Reservation System',

			default => 'Various international human rights conventions and national laws',
		};
	}

	// English Labels
	public static function labelsEn(): array
	{
		return [
			// Gender & Sexual Identity
			self::Women->value => 'Women',
			self::Men->value => 'Men',
			self::NonBinary->value => 'Non-Binary',
			self::Transgender->value => 'Transgender',
			self::Genderqueer->value => 'Genderqueer',
			self::Genderfluid->value => 'Genderfluid',
			self::Agender->value => 'Agender',
			self::Intersex->value => 'Intersex',
			self::LGBTQPlus->value => 'LGBTQ+',
			self::Lesbian->value => 'Lesbian',
			self::Gay->value => 'Gay',
			self::Bisexual->value => 'Bisexual',
			self::Queer->value => 'Queer',
			self::Asexual->value => 'Asexual',
			self::Pansexual->value => 'Pansexual',
			self::TwoSpirit->value => 'Two-Spirit',

			// Disability & Accessibility
			self::Disabled->value => 'Persons with Disabilities',
			self::PhysicallyDisabled->value => 'Physically Disabled',
			self::VisuallyImpaired->value => 'Visually Impaired',
			self::HearingImpaired->value => 'Hearing Impaired',
			self::Neurodiverse->value => 'Neurodiverse',
			self::Autistic->value => 'Autistic',
			self::ADHD->value => 'ADHD',
			self::Dyslexic->value => 'Dyslexic',
			self::MentalHealth->value => 'Mental Health Conditions',
			self::MobilityImpairment->value => 'Mobility Impairment',
			self::ChronicIllness->value => 'Chronic Illness',
			self::InvisibleDisability->value => 'Invisible Disability',

			// Race & Ethnicity
			self::RacialMinority->value => 'Racial Minority',
			self::EthnicMinority->value => 'Ethnic Minority',
			self::Black->value => 'Black/African Descent',
			self::African->value => 'African',
			self::AfricanAmerican->value => 'African American',
			self::Asian->value => 'Asian',
			self::Hispanic->value => 'Hispanic',
			self::Latino->value => 'Latino/Latina',
			self::Indigenous->value => 'Indigenous Peoples',
			self::NativeAmerican->value => 'Native American',
			self::PacificIslander->value => 'Pacific Islander',
			self::MiddleEastern->value => 'Middle Eastern',
			self::Multiracial->value => 'Multiracial',
			self::MixedRace->value => 'Mixed Race',

			// Age
			self::Youth->value => 'Youth',
			self::YoungAdult->value => 'Young Adult',
			self::Senior->value => 'Senior (55+)',
			self::AgeDiverse->value => 'Age Diverse',
			self::GenerationZ->value => 'Generation Z',
			self::Millennial->value => 'Millennial',
			self::GenerationX->value => 'Generation X',
			self::BabyBoomer->value => 'Baby Boomer',

			// Nationality & Migration
			self::Immigrant->value => 'Immigrant',
			self::Refugee->value => 'Refugee',
			self::AsylumSeeker->value => 'Asylum Seeker',
			self::ForeignNational->value => 'Foreign National',
			self::International->value => 'International',
			self::MigrantWorker->value => 'Migrant Worker',
			self::Diaspora->value => 'Diaspora',
			self::Stateless->value => 'Stateless Person',

			// Religion & Belief
			self::ReligiousMinority->value => 'Religious Minority',
			self::Muslim->value => 'Muslim',
			self::Jewish->value => 'Jewish',
			self::Hindu->value => 'Hindu',
			self::Buddhist->value => 'Buddhist',
			self::Sikh->value => 'Sikh',
			self::ChristianMinority->value => 'Christian Minority',
			self::Atheist->value => 'Atheist',
			self::Agnostic->value => 'Agnostic',
			self::Spiritual->value => 'Spiritual',

			// Socioeconomic
			self::LowIncome->value => 'Low Income',
			self::EconomicallyDisadvantaged->value => 'Economically Disadvantaged',
			self::FirstGeneration->value => 'First Generation',
			self::FirstGenerationProfessional->value => 'First Generation Professional',
			self::FirstGenerationCollege->value => 'First Generation College Student',
			self::WorkingClass->value => 'Working Class',
			self::UnderrepresentedBackground->value => 'Underrepresented Background',
			self::SocioeconomicallyDisadvantaged->value => 'Socioeconomically Disadvantaged',

			// Military & Veterans
			self::Veteran->value => 'Veteran',
			self::MilitaryFamily->value => 'Military Family',
			self::ActiveDuty->value => 'Active Duty',
			self::Reservist->value => 'Reservist',
			self::MilitarySpouse->value => 'Military Spouse',
			self::GoldStarFamily->value => 'Gold Star Family',

			// Education & Language
			self::EnglishLanguageLearner->value => 'English Language Learner',
			self::ESL->value => 'ESL (English as Second Language)',
			self::NonNativeSpeaker->value => 'Non-Native Speaker',
			self::DifferentAbledLearner->value => 'Different-Abled Learner',
			self::AlternativeEducation->value => 'Alternative Education Background',

			// Family & Caregiving
			self::SingleParent->value => 'Single Parent',
			self::Caregiver->value => 'Caregiver',
			self::Parent->value => 'Parent',
			self::FosterYouth->value => 'Foster Youth',
			self::Adoptee->value => 'Adoptee',
			self::Orphan->value => 'Orphan',

			// Regional & Geographic
			self::Rural->value => 'Rural Background',
			self::Urban->value => 'Urban Background',
			self::Suburban->value => 'Suburban Background',
			self::RemoteArea->value => 'Remote Area',
			self::UnderservedRegion->value => 'Underserved Region',

			// Other Marginalized Groups
			self::FormerlyIncarcerated->value => 'Formerly Incarcerated',
			self::JusticeInvolved->value => 'Justice-Involved',
			self::Homeless->value => 'Homeless/Formerly Homeless',
			self::DomesticViolenceSurvivor->value => 'Domestic Violence Survivor',
			self::HumanTraffickingSurvivor->value => 'Human Trafficking Survivor',
			self::AddictionRecovery->value => 'Addiction Recovery',

			// Intersectional Categories
			self::WomenOfColor->value => 'Women of Color',
			self::DisabledWomen->value => 'Women with Disabilities',
			self::LGBTQPlusYouth->value => 'LGBTQ+ Youth',
			self::IndigenousWomen->value => 'Indigenous Women',
			self::DisabledVeteran->value => 'Disabled Veteran',

			// General
			self::DiverseBackground->value => 'Diverse Background',
			self::Underrepresented->value => 'Underrepresented Group',
			self::Marginalized->value => 'Marginalized Community',
			self::Minority->value => 'Minority Group',
			self::ProtectedClass->value => 'Protected Class',
			self::Quilombola->value => 'Quilombola',
			self::ScheduledCaste->value => 'Scheduled Caste (SC)',
			self::ScheduledTribe->value => 'Scheduled Tribe (ST)',
			self::OBC->value => 'Other Backward Class (OBC)',
		];
	}

	// Portuguese (Brazil) Labels
	public static function labelsPtBr(): array
	{
		return [
			// Gender & Sexual Identity
			self::Women->value => 'Mulheres',
			self::Men->value => 'Homens',
			self::NonBinary->value => 'Não-Binário',
			self::Transgender->value => 'Transgênero',
			self::Genderqueer->value => 'Genderqueer',
			self::Genderfluid->value => 'Genderfluid',
			self::Agender->value => 'Agênero',
			self::Intersex->value => 'Intersexo',
			self::LGBTQPlus->value => 'LGBTQ+',
			self::Lesbian->value => 'Lésbica',
			self::Gay->value => 'Gay',
			self::Bisexual->value => 'Bissexual',
			self::Queer->value => 'Queer',
			self::Asexual->value => 'Assexual',
			self::Pansexual->value => 'Pansexual',
			self::TwoSpirit->value => 'Dois-Espíritos',

			// Disability & Accessibility
			self::Disabled->value => 'Pessoas com Deficiência',
			self::PhysicallyDisabled->value => 'Deficiência Física',
			self::VisuallyImpaired->value => 'Deficiência Visual',
			self::HearingImpaired->value => 'Deficiência Auditiva',
			self::Neurodiverse->value => 'Neurodiverso',
			self::Autistic->value => 'Autista',
			self::ADHD->value => 'TDAH',
			self::Dyslexic->value => 'Disléxico',
			self::MentalHealth->value => 'Condições de Saúde Mental',
			self::MobilityImpairment->value => 'Comprometimento de Mobilidade',
			self::ChronicIllness->value => 'Doença Crônica',
			self::InvisibleDisability->value => 'Deficiência Invisível',

			// Race & Ethnicity
			self::RacialMinority->value => 'Minoria Racial',
			self::EthnicMinority->value => 'Minoria Étnica',
			self::Black->value => 'Negro/Afrodescendente',
			self::African->value => 'Africano',
			self::AfricanAmerican->value => 'Afro-americano',
			self::Asian->value => 'Asiático',
			self::Hispanic->value => 'Hispânico',
			self::Latino->value => 'Latino/Latina',
			self::Indigenous->value => 'Povos Indígenas',
			self::NativeAmerican->value => 'Nativo Americano',
			self::PacificIslander->value => 'Ilhas do Pacífico',
			self::MiddleEastern->value => 'Oriente Médio',
			self::Multiracial->value => 'Multirracial',
			self::MixedRace->value => 'Misto',

			// Age
			self::Youth->value => 'Jovens',
			self::YoungAdult->value => 'Jovem Adulto',
			self::Senior->value => 'Idoso (55+)',
			self::AgeDiverse->value => 'Diversidade Etária',
			self::GenerationZ->value => 'Geração Z',
			self::Millennial->value => 'Millennial',
			self::GenerationX->value => 'Geração X',
			self::BabyBoomer->value => 'Baby Boomer',

			// Nationality & Migration
			self::Immigrant->value => 'Imigrante',
			self::Refugee->value => 'Refugiado',
			self::AsylumSeeker->value => 'Solicitante de Asilo',
			self::ForeignNational->value => 'Estrangeiro',
			self::International->value => 'Internacional',
			self::MigrantWorker->value => 'Trabalhador Migrante',
			self::Diaspora->value => 'Diáspora',
			self::Stateless->value => 'Apátrida',

			// Religion & Belief
			self::ReligiousMinority->value => 'Minoria Religiosa',
			self::Muslim->value => 'Muçulmano',
			self::Jewish->value => 'Judeu',
			self::Hindu->value => 'Hindu',
			self::Buddhist->value => 'Budista',
			self::Sikh->value => 'Sikh',
			self::ChristianMinority->value => 'Minoria Cristã',
			self::Atheist->value => 'Ateu',
			self::Agnostic->value => 'Agnóstico',
			self::Spiritual->value => 'Espiritual',

			// Socioeconomic
			self::LowIncome->value => 'Baixa Renda',
			self::EconomicallyDisadvantaged->value => 'Economicamente Desfavorecido',
			self::FirstGeneration->value => 'Primeira Geração',
			self::FirstGenerationProfessional->value => 'Primeira Geração Profissional',
			self::FirstGenerationCollege->value => 'Primeira Geração Universitária',
			self::WorkingClass->value => 'Classe Trabalhadora',
			self::UnderrepresentedBackground->value => 'Sub-representado',
			self::SocioeconomicallyDisadvantaged->value => 'Socioeconomicamente Desfavorecido',

			// Military & Veterans
			self::Veteran->value => 'Veterano',
			self::MilitaryFamily->value => 'Família Militar',
			self::ActiveDuty->value => 'Serviço Ativo',
			self::Reservist->value => 'Reservista',
			self::MilitarySpouse->value => 'Cônjuge Militar',
			self::GoldStarFamily->value => 'Família Gold Star',

			// Education & Language
			self::EnglishLanguageLearner->value => 'Aprendiz de Inglês',
			self::ESL->value => 'Inglês como Segunda Língua',
			self::NonNativeSpeaker->value => 'Não Nativo',
			self::DifferentAbledLearner->value => 'Aprendiz com Diferenças',
			self::AlternativeEducation->value => 'Educação Alternativa',

			// Family & Caregiving
			self::SingleParent->value => 'Pai/Mãe Solteiro(a)',
			self::Caregiver->value => 'Cuidador',
			self::Parent->value => 'Pai/Mãe',
			self::FosterYouth->value => 'Jovem em Acolhimento Familiar',
			self::Adoptee->value => 'Adotado',
			self::Orphan->value => 'Órfão',

			// Regional & Geographic
			self::Rural->value => 'Origem Rural',
			self::Urban->value => 'Origem Urbana',
			self::Suburban->value => 'Origem Suburbana',
			self::RemoteArea->value => 'Área Remota',
			self::UnderservedRegion->value => 'Região Subatendida',

			// Other Marginalized Groups
			self::FormerlyIncarcerated->value => 'Ex-presidiário',
			self::JusticeInvolved->value => 'Envolvido com Justiça',
			self::Homeless->value => 'Sem-teto/Ex-sem-teto',
			self::DomesticViolenceSurvivor->value => 'Sobrevivente de Violência Doméstica',
			self::HumanTraffickingSurvivor->value => 'Sobrevivente de Tráfico Humano',
			self::AddictionRecovery->value => 'Recuperação de Dependência',

			// Intersectional Categories
			self::WomenOfColor->value => 'Mulheres de Cor',
			self::DisabledWomen->value => 'Mulheres com Deficiência',
			self::LGBTQPlusYouth->value => 'Jovens LGBTQ+',
			self::IndigenousWomen->value => 'Mulheres Indígenas',
			self::DisabledVeteran->value => 'Veterano com Deficiência',

			// General
			self::DiverseBackground->value => 'Diversidade',
			self::Underrepresented->value => 'Grupo Sub-representado',
			self::Marginalized->value => 'Comunidade Marginalizada',
			self::Minority->value => 'Grupo Minoritário',
			self::ProtectedClass->value => 'Classe Protegida',
			self::Quilombola->value => 'Quilombola',
			self::ScheduledCaste->value => 'Casta Programada (SC)',
			self::ScheduledTribe->value => 'Tribo Programada (ST)',
			self::OBC->value => 'Outras Classes Atrasadas (OBC)',
		];
	}

	// Spanish Labels
	public static function labelsEs(): array
	{
		return [
			// Gender & Sexual Identity
			self::Women->value => 'Mujeres',
			self::Men->value => 'Hombres',
			self::NonBinary->value => 'No Binario',
			self::Transgender->value => 'Transgénero',
			self::Genderqueer->value => 'Genderqueer',
			self::Genderfluid->value => 'Genderfluid',
			self::Agender->value => 'Agénero',
			self::Intersex->value => 'Intersexual',
			self::LGBTQPlus->value => 'LGBTQ+',
			self::Lesbian->value => 'Lesbiana',
			self::Gay->value => 'Gay',
			self::Bisexual->value => 'Bisexual',
			self::Queer->value => 'Queer',
			self::Asexual->value => 'Asexual',
			self::Pansexual->value => 'Pansexual',
			self::TwoSpirit->value => 'Dos-Espíritus',

			// Disability & Accessibility
			self::Disabled->value => 'Personas con Discapacidad',
			self::PhysicallyDisabled->value => 'Discapacidad Física',
			self::VisuallyImpaired->value => 'Discapacidad Visual',
			self::HearingImpaired->value => 'Discapacidad Auditiva',
			self::Neurodiverse->value => 'Neurodiverso',
			self::Autistic->value => 'Autista',
			self::ADHD->value => 'TDAH',
			self::Dyslexic->value => 'Disléxico',
			self::MentalHealth->value => 'Condiciones de Salud Mental',
			self::MobilityImpairment->value => 'Discapacidad Motora',
			self::ChronicIllness->value => 'Enfermedad Crónica',
			self::InvisibleDisability->value => 'Discapacidad Invisible',

			// Race & Ethnicity
			self::RacialMinority->value => 'Minoría Racial',
			self::EthnicMinority->value => 'Minoría Étnica',
			self::Black->value => 'Negro/Afrodescendiente',
			self::African->value => 'Africano',
			self::AfricanAmerican->value => 'Afroamericano',
			self::Asian->value => 'Asiático',
			self::Hispanic->value => 'Hispano',
			self::Latino->value => 'Latino/Latina',
			self::Indigenous->value => 'Pueblos Indígenas',
			self::NativeAmerican->value => 'Nativo Americano',
			self::PacificIslander->value => 'Isleño del Pacífico',
			self::MiddleEastern->value => 'Medio Oriente',
			self::Multiracial->value => 'Multirracial',
			self::MixedRace->value => 'Mestizo',

			// Age
			self::Youth->value => 'Juventud',
			self::YoungAdult->value => 'Joven Adulto',
			self::Senior->value => 'Adulto Mayor (55+)',
			self::AgeDiverse->value => 'Diversidad Etaria',
			self::GenerationZ->value => 'Generación Z',
			self::Millennial->value => 'Millennial',
			self::GenerationX->value => 'Generación X',
			self::BabyBoomer->value => 'Baby Boomer',

			// Nationality & Migration
			self::Immigrant->value => 'Inmigrante',
			self::Refugee->value => 'Refugiado',
			self::AsylumSeeker->value => 'Solicitante de Asilo',
			self::ForeignNational->value => 'Extranjero',
			self::International->value => 'Internacional',
			self::MigrantWorker->value => 'Trabajador Migrante',
			self::Diaspora->value => 'Diáspora',
			self::Stateless->value => 'Apátrida',

			// Religion & Belief
			self::ReligiousMinority->value => 'Minoría Religiosa',
			self::Muslim->value => 'Musulmán',
			self::Jewish->value => 'Judío',
			self::Hindu->value => 'Hindú',
			self::Buddhist->value => 'Budista',
			self::Sikh->value => 'Sikh',
			self::ChristianMinority->value => 'Minoría Cristiana',
			self::Atheist->value => 'Ateo',
			self::Agnostic->value => 'Agnóstico',
			self::Spiritual->value => 'Espiritual',

			// Socioeconomic
			self::LowIncome->value => 'Bajos Ingresos',
			self::EconomicallyDisadvantaged->value => 'Económicamente Desfavorecido',
			self::FirstGeneration->value => 'Primera Generación',
			self::FirstGenerationProfessional->value => 'Primera Generación Profesional',
			self::FirstGenerationCollege->value => 'Primera Generación Universitaria',
			self::WorkingClass->value => 'Clase Trabajadora',
			self::UnderrepresentedBackground->value => 'Subrepresentado',
			self::SocioeconomicallyDisadvantaged->value => 'Socioeconómicamente Desfavorecido',

			// Military & Veterans
			self::Veteran->value => 'Veterano',
			self::MilitaryFamily->value => 'Familia Militar',
			self::ActiveDuty->value => 'Servicio Activo',
			self::Reservist->value => 'Reservista',
			self::MilitarySpouse->value => 'Cónyuge Militar',
			self::GoldStarFamily->value => 'Familia Gold Star',

			// Education & Language
			self::EnglishLanguageLearner->value => 'Aprendiz de Inglés',
			self::ESL->value => 'Inglés como Segundo Idioma',
			self::NonNativeSpeaker->value => 'No Nativo',
			self::DifferentAbledLearner->value => 'Aprendiz con Diferencias',
			self::AlternativeEducation->value => 'Educación Alternativa',

			// Family & Caregiving
			self::SingleParent->value => 'Padre/Madre Soltero(a)',
			self::Caregiver->value => 'Cuidador',
			self::Parent->value => 'Padre/Madre',
			self::FosterYouth->value => 'Joven en Acogida',
			self::Adoptee->value => 'Adoptado',
			self::Orphan->value => 'Huérfano',

			// Regional & Geographic
			self::Rural->value => 'Origen Rural',
			self::Urban->value => 'Origen Urbano',
			self::Suburban->value => 'Origen Suburbano',
			self::RemoteArea->value => 'Área Remota',
			self::UnderservedRegion->value => 'Región Desatendida',

			// Other Marginalized Groups
			self::FormerlyIncarcerated->value => 'Ex-presidiario',
			self::JusticeInvolved->value => 'Involucrado con Justicia',
			self::Homeless->value => 'Sin Hogar/Ex-sin Hogar',
			self::DomesticViolenceSurvivor->value => 'Superviviente de Violencia Doméstica',
			self::HumanTraffickingSurvivor->value => 'Superviviente de Tráfico Humano',
			self::AddictionRecovery->value => 'Recuperación de Adicción',

			// Intersectional Categories
			self::WomenOfColor->value => 'Mujeres de Color',
			self::DisabledWomen->value => 'Mujeres con Discapacidad',
			self::LGBTQPlusYouth->value => 'Jóvenes LGBTQ+',
			self::IndigenousWomen->value => 'Mujeres Indígenas',
			self::DisabledVeteran->value => 'Veterano con Discapacidad',

			// General
			self::DiverseBackground->value => 'Diversidad',
			self::Underrepresented->value => 'Grupo Subrepresentado',
			self::Marginalized->value => 'Comunidad Marginalizada',
			self::Minority->value => 'Grupo Minoritario',
			self::ProtectedClass->value => 'Clase Protegida',
			self::Quilombola->value => 'Quilombola',
			self::ScheduledCaste->value => 'Casta Programada (SC)',
			self::ScheduledTribe->value => 'Tribu Programada (ST)',
			self::OBC->value => 'Otras Clases Atrasadas (OBC)',
		];
	}

	// German Labels
	public static function labelsDe(): array
	{
		return [
			// Gender & Sexual Identity
			self::Women->value => 'Frauen',
			self::Men->value => 'Männer',
			self::NonBinary->value => 'Nicht-Binär',
			self::Transgender->value => 'Transgender',
			self::Genderqueer->value => 'Genderqueer',
			self::Genderfluid->value => 'Genderfluid',
			self::Agender->value => 'Agender',
			self::Intersex->value => 'Intersexuell',
			self::LGBTQPlus->value => 'LGBTQ+',
			self::Lesbian->value => 'Lesbisch',
			self::Gay->value => 'Schwul',
			self::Bisexual->value => 'Bisexuell',
			self::Queer->value => 'Queer',
			self::Asexual->value => 'Asexuell',
			self::Pansexual->value => 'Pansexuell',
			self::TwoSpirit->value => 'Two-Spirit',

			// Disability & Accessibility
			self::Disabled->value => 'Menschen mit Behinderungen',
			self::PhysicallyDisabled->value => 'Körperbehindert',
			self::VisuallyImpaired->value => 'Sehbehindert',
			self::HearingImpaired->value => 'Hörbehindert',
			self::Neurodiverse->value => 'Neurodivers',
			self::Autistic->value => 'Autistisch',
			self::ADHD->value => 'ADHS',
			self::Dyslexic->value => 'Legastheniker',
			self::MentalHealth->value => 'Psychische Gesundheit',
			self::MobilityImpairment->value => 'Mobilitätseinschränkung',
			self::ChronicIllness->value => 'Chronisch Krank',
			self::InvisibleDisability->value => 'Unsichtbare Behinderung',

			// Race & Ethnicity
			self::RacialMinority->value => 'Rassische Minderheit',
			self::EthnicMinority->value => 'Ethnische Minderheit',
			self::Black->value => 'Schwarz/Afrikanische Abstammung',
			self::African->value => 'Afrikanisch',
			self::AfricanAmerican->value => 'Afroamerikanisch',
			self::Asian->value => 'Asiatisch',
			self::Hispanic->value => 'Hispanisch',
			self::Latino->value => 'Latino/Latina',
			self::Indigenous->value => 'Indigene Völker',
			self::NativeAmerican->value => 'Ureinwohner Amerikas',
			self::PacificIslander->value => 'Pazifikinsulaner',
			self::MiddleEastern->value => 'Mittlerer Osten',
			self::Multiracial->value => 'Mehrrassisch',
			self::MixedRace->value => 'Mischling',

			// Age
			self::Youth->value => 'Jugend',
			self::YoungAdult->value => 'Junger Erwachsener',
			self::Senior->value => 'Senior (55+)',
			self::AgeDiverse->value => 'Altersvielfalt',
			self::GenerationZ->value => 'Generation Z',
			self::Millennial->value => 'Millennial',
			self::GenerationX->value => 'Generation X',
			self::BabyBoomer->value => 'Baby Boomer',

			// Nationality & Migration
			self::Immigrant->value => 'Einwanderer',
			self::Refugee->value => 'Flüchtling',
			self::AsylumSeeker->value => 'Asylsuchender',
			self::ForeignNational->value => 'Ausländer',
			self::International->value => 'International',
			self::MigrantWorker->value => 'Migrantenarbeiter',
			self::Diaspora->value => 'Diaspora',
			self::Stateless->value => 'Staatenlos',

			// Religion & Belief
			self::ReligiousMinority->value => 'Religiöse Minderheit',
			self::Muslim->value => 'Muslimisch',
			self::Jewish->value => 'Jüdisch',
			self::Hindu->value => 'Hinduistisch',
			self::Buddhist->value => 'Buddhistisch',
			self::Sikh->value => 'Sikh',
			self::ChristianMinority->value => 'Christliche Minderheit',
			self::Atheist->value => 'Atheist',
			self::Agnostic->value => 'Agnostiker',
			self::Spiritual->value => 'Spirituell',

			// Socioeconomic
			self::LowIncome->value => 'Niedriges Einkommen',
			self::EconomicallyDisadvantaged->value => 'Wirtschaftlich Benachteiligt',
			self::FirstGeneration->value => 'Erste Generation',
			self::FirstGenerationProfessional->value => 'Erste Generation Berufstätige',
			self::FirstGenerationCollege->value => 'Erste Generation Studenten',
			self::WorkingClass->value => 'Arbeiterklasse',
			self::UnderrepresentedBackground->value => 'Unterrepräsentiert',
			self::SocioeconomicallyDisadvantaged->value => 'Sozioökonomisch Benachteiligt',

			// Military & Veterans
			self::Veteran->value => 'Veteran',
			self::MilitaryFamily->value => 'Militärfamilie',
			self::ActiveDuty->value => 'Aktiver Dienst',
			self::Reservist->value => 'Reservist',
			self::MilitarySpouse->value => 'Militärehepartner',
			self::GoldStarFamily->value => 'Gold Star Familie',

			// Education & Language
			self::EnglishLanguageLearner->value => 'Englischlerner',
			self::ESL->value => 'Englisch als Zweitsprache',
			self::NonNativeSpeaker->value => 'Nicht-Muttersprachler',
			self::DifferentAbledLearner->value => 'Anders Begabte Lernende',
			self::AlternativeEducation->value => 'Alternative Bildung',

			// Family & Caregiving
			self::SingleParent->value => 'Alleinerziehend',
			self::Caregiver->value => 'Pflegekraft',
			self::Parent->value => 'Elternteil',
			self::FosterYouth->value => 'Pflegekind',
			self::Adoptee->value => 'Adoptiert',
			self::Orphan->value => 'Waise',

			// Regional & Geographic
			self::Rural->value => 'Ländlicher Hintergrund',
			self::Urban->value => 'Städtischer Hintergrund',
			self::Suburban->value => 'Vorstädtischer Hintergrund',
			self::RemoteArea->value => 'Abgelegenes Gebiet',
			self::UnderservedRegion->value => 'Unterversorgte Region',

			// Other Marginalized Groups
			self::FormerlyIncarcerated->value => 'Ehemals Inhaftiert',
			self::JusticeInvolved->value => 'Justizbeteiligt',
			self::Homeless->value => 'Obdachlos/Ehemals Obdachlos',
			self::DomesticViolenceSurvivor->value => 'Überlebende(r) häuslicher Gewalt',
			self::HumanTraffickingSurvivor->value => 'Überlebende(r) Menschenhandel',
			self::AddictionRecovery->value => 'Suchtbewältigung',

			// Intersectional Categories
			self::WomenOfColor->value => 'Frauen of Color',
			self::DisabledWomen->value => 'Frauen mit Behinderungen',
			self::LGBTQPlusYouth->value => 'LGBTQ+ Jugendliche',
			self::IndigenousWomen->value => 'Indigene Frauen',
			self::DisabledVeteran->value => 'Behindeter Veteran',

			// General
			self::DiverseBackground->value => 'Diverser Hintergrund',
			self::Underrepresented->value => 'Unterrepräsentierte Gruppe',
			self::Marginalized->value => 'Marginalisierte Gemeinschaft',
			self::Minority->value => 'Minderheitengruppe',
			self::ProtectedClass->value => 'Geschützte Klasse',
			self::Quilombola->value => 'Quilombola',
			self::ScheduledCaste->value => 'Registrierte Kaste (SC)',
			self::ScheduledTribe->value => 'Registrierter Stamm (ST)',
			self::OBC->value => 'Sonstige rückständige Klasse (OBC)',
		];
	}

	// Arabic Labels
	public static function labelsAr(): array
	{
		return [
			// Gender & Sexual Identity
			self::Women->value => 'النساء',
			self::Men->value => 'الرجال',
			self::NonBinary->value => 'غير ثنائي',
			self::Transgender->value => 'عابر/عابرة جنسياً',
			self::Genderqueer->value => 'جندر كوير',
			self::Genderfluid->value => 'مرن/ة جندرياً',
			self::Agender->value => 'لَا جندري/ة',
			self::Intersex->value => 'ثنائي الجنس',
			self::LGBTQPlus->value => 'مجتمع الميم +',
			self::Lesbian->value => 'مثلية',
			self::Gay->value => 'مثلي',
			self::Bisexual->value => 'ثنائي الميول',
			self::Queer->value => 'كوير',
			self::Asexual->value => 'لاجنسي',
			self::Pansexual->value => 'بانجنسي',
			self::TwoSpirit->value => 'ذو روحين',

			// Disability & Accessibility
			self::Disabled->value => 'أشخاص ذوو إعاقة',
			self::PhysicallyDisabled->value => 'إعاقة جسدية',
			self::VisuallyImpaired->value => 'ضعف بصري',
			self::HearingImpaired->value => 'ضعف سمعي',
			self::Neurodiverse->value => 'تنوع عصبي',
			self::Autistic->value => 'توحد',
			self::ADHD->value => 'اضطراب فرط الحركة وتشتت الانتباه',
			self::Dyslexic->value => 'عُسر القراءة',
			self::MentalHealth->value => 'حالات الصحة النفسية',
			self::MobilityImpairment->value => 'إعاقة حركية',
			self::ChronicIllness->value => 'مرض مزمن',
			self::InvisibleDisability->value => 'إعاقة غير مرئية',

			// Race & Ethnicity
			self::RacialMinority->value => 'أقلية عرقية',
			self::EthnicMinority->value => 'أقلية إثنية',
			self::Black->value => 'أسود/من أصول أفريقية',
			self::African->value => 'أفريقي',
			self::AfricanAmerican->value => 'أميركي من أصل أفريقي',
			self::Asian->value => 'آسيوي',
			self::Hispanic->value => 'هسباني',
			self::Latino->value => 'لاتيني/لاتينية',
			self::Indigenous->value => 'شعوب أصلية',
			self::NativeAmerican->value => 'أميركي أصلي',
			self::PacificIslander->value => 'من جزر المحيط الهادئ',
			self::MiddleEastern->value => 'شرق أوسطي',
			self::Multiracial->value => 'متعدد الأعراق',
			self::MixedRace->value => 'مختلط العِرق',
			self::Quilombola->value => 'كيلومبولا',
			self::ScheduledCaste->value => 'الطبقة المُدرجة (SC)',
			self::ScheduledTribe->value => 'القبيلة المُدرجة (ST)',
			self::OBC->value => 'الفئات المتأخرة الأخرى (OBC)',

			// Age
			self::Youth->value => 'شباب',
			self::YoungAdult->value => 'شاب/ة بالغ/ة',
			self::Senior->value => 'كبير/ة سن',
			self::AgeDiverse->value => 'تنوع عمري',
			self::GenerationZ->value => 'جيل زد',
			self::Millennial->value => 'جيل الألفية',
			self::GenerationX->value => 'جيل إكس',
			self::BabyBoomer->value => 'جيل الطفرة',

			// Nationality & Migration
			self::Immigrant->value => 'مهاجر/ة',
			self::Refugee->value => 'لاجئ/ة',
			self::AsylumSeeker->value => 'طالب/ة لجوء',
			self::ForeignNational->value => 'أجنبي/ة',
			self::International->value => 'دولي',
			self::MigrantWorker->value => 'عامل/ة مهاجر/ة',
			self::Diaspora->value => 'شتات',
			self::Stateless->value => 'عديم/ة الجنسية',

			// Religion & Belief
			self::ReligiousMinority->value => 'أقلية دينية',
			self::Muslim->value => 'مسلم/ة',
			self::Jewish->value => 'يهودي/ة',
			self::Hindu->value => 'هندوسي/ة',
			self::Buddhist->value => 'بوذي/ة',
			self::Sikh->value => 'سيخي/ة',
			self::ChristianMinority->value => 'أقلية مسيحية',
			self::Atheist->value => 'ملحد/ة',
			self::Agnostic->value => 'لاأدري/ة',
			self::Spiritual->value => 'روحاني/ة',

			// Socioeconomic
			self::LowIncome->value => 'دخل منخفض',
			self::EconomicallyDisadvantaged->value => 'محروم/ة اقتصادياً',
			self::FirstGeneration->value => 'الجيل الأول',
			self::FirstGenerationProfessional->value => 'محترف/ة من الجيل الأول',
			self::FirstGenerationCollege->value => 'طالب/ة جامعي من الجيل الأول',
			self::WorkingClass->value => 'الطبقة العاملة',
			self::UnderrepresentedBackground->value => 'خلفية ممثلة تمثيلاً ناقصاً',
			self::SocioeconomicallyDisadvantaged->value => 'محروم/ة اجتماعياً واقتصادياً',

			// Military & Veterans
			self::Veteran->value => 'محارب قديم',
			self::MilitaryFamily->value => 'عائلة عسكرية',
			self::ActiveDuty->value => 'خدمة فعالة',
			self::Reservist->value => 'قوات احتياط',
			self::MilitarySpouse->value => 'زوج/ة عسكري/ة',
			self::GoldStarFamily->value => 'عائلة النجمة الذهبية',

			// Education & Language
			self::EnglishLanguageLearner->value => 'متعلم/ة اللغة الإنجليزية',
			self::ESL->value => 'الإنجليزية كلغة ثانية',
			self::NonNativeSpeaker->value => 'غير ناطق/ة أصلي/ة',
			self::DifferentAbledLearner->value => 'متعلم/ة بقدرات مختلفة',
			self::AlternativeEducation->value => 'خلفية تعليم بديل',

			// Family & Caregiving
			self::SingleParent->value => 'والد/ة وحيد/ة',
			self::Caregiver->value => 'مُقدّم/ة رعاية',
			self::Parent->value => 'والد/ة',
			self::FosterYouth->value => 'شباب في رعاية بديلة',
			self::Adoptee->value => 'مُتَبَنّى/ة',
			self::Orphan->value => 'يتيم/ة',

			// Regional & Geographic
			self::Rural->value => 'خلفية ريفية',
			self::Urban->value => 'خلفية حضرية',
			self::Suburban->value => 'خلفية ضواحي',
			self::RemoteArea->value => 'منطقة نائية',
			self::UnderservedRegion->value => 'منطقة محرومة من الخدمات',

			// Other Marginalized Groups
			self::FormerlyIncarcerated->value => 'سابقاً مسجون/ة',
			self::JusticeInvolved->value => 'على صلة بالنظام القضائي',
			self::Homeless->value => 'مشرد/ة أو سابقاً',
			self::DomesticViolenceSurvivor->value => 'ناجٍ/ية من العنف الأسري',
			self::HumanTraffickingSurvivor->value => 'ناجٍ/ية من الاتجار بالبشر',
			self::AddictionRecovery->value => 'التعافي من الإدمان',

			// Intersectional Categories
			self::WomenOfColor->value => 'نساء ذوات لون',
			self::DisabledWomen->value => 'نساء ذوات إعاقة',
			self::LGBTQPlusYouth->value => 'شباب مجتمع الميم +',
			self::IndigenousWomen->value => 'نساء من الشعوب الأصلية',
			self::DisabledVeteran->value => 'محارب قديم ذو إعاقة',

			// General
			self::DiverseBackground->value => 'خلفية متنوعة',
			self::Underrepresented->value => 'فئة ممثلة تمثيلاً ناقصاً',
			self::Marginalized->value => 'مجتمع مُهمَّش',
			self::Minority->value => 'أقلية',
			self::ProtectedClass->value => 'فئة محمية',
		];
	}

	// Danish Labels
	public static function labelsDa(): array
	{
		return [
			// Gender & Sexual Identity
			self::Women->value => 'Kvinder',
			self::Men->value => 'Mænd',
			self::NonBinary->value => 'Ikke-binær',
			self::Transgender->value => 'Transkønnet',
			self::Genderqueer->value => 'Genderqueer',
			self::Genderfluid->value => 'Genderfluid',
			self::Agender->value => 'Agender',
			self::Intersex->value => 'Interkønnet',
			self::LGBTQPlus->value => 'LGBTQ+',
			self::Lesbian->value => 'Lesbisk',
			self::Gay->value => 'Bøsse/Homo',
			self::Bisexual->value => 'Biseksuel',
			self::Queer->value => 'Queer',
			self::Asexual->value => 'Aseksuel',
			self::Pansexual->value => 'Panseksuel',
			self::TwoSpirit->value => 'Two-Spirit',

			// Disability & Accessibility
			self::Disabled->value => 'Personer med handicap',
			self::PhysicallyDisabled->value => 'Fysisk handicap',
			self::VisuallyImpaired->value => 'Synshandicap',
			self::HearingImpaired->value => 'Hørehæmmet',
			self::Neurodiverse->value => 'Neurodivers',
			self::Autistic->value => 'Autistisk',
			self::ADHD->value => 'ADHD',
			self::Dyslexic->value => 'Ordblind',
			self::MentalHealth->value => 'Psykiske helbredsforhold',
			self::MobilityImpairment->value => 'Nedsat mobilitet',
			self::ChronicIllness->value => 'Kronisk sygdom',
			self::InvisibleDisability->value => 'Usynligt handicap',

			// Race & Ethnicity
			self::RacialMinority->value => 'Racemæssig minoritet',
			self::EthnicMinority->value => 'Etnisk minoritet',
			self::Black->value => 'Sort/afrikansk afstamning',
			self::African->value => 'Afrikansk',
			self::AfricanAmerican->value => 'Afroamerikansk',
			self::Asian->value => 'Asiatisk',
			self::Hispanic->value => 'Hispanic',
			self::Latino->value => 'Latino/Latina',
			self::Indigenous->value => 'Oprindelige folk',
			self::NativeAmerican->value => 'Nordamerikansk oprindelig',
			self::PacificIslander->value => 'Stillehavsøbo',
			self::MiddleEastern->value => 'Mellemøstlig',
			self::Multiracial->value => 'Multietnisk',
			self::MixedRace->value => 'Blandet race',
			self::Quilombola->value => 'Quilombola',
			self::ScheduledCaste->value => 'Registreret kaste (SC)',
			self::ScheduledTribe->value => 'Registreret stamme (ST)',
			self::OBC->value => 'Andre tilbagestående klasser (OBC)',

			// Age
			self::Youth->value => 'Unge',
			self::YoungAdult->value => 'Ung voksen',
			self::Senior->value => 'Senior',
			self::AgeDiverse->value => 'Aldersdiversitet',
			self::GenerationZ->value => 'Generation Z',
			self::Millennial->value => 'Millennial',
			self::GenerationX->value => 'Generation X',
			self::BabyBoomer->value => 'Babyboomer',

			// Nationality & Migration
			self::Immigrant->value => 'Indvandrer',
			self::Refugee->value => 'Flygtning',
			self::AsylumSeeker->value => 'Asylansøger',
			self::ForeignNational->value => 'Udenlandsk statsborger',
			self::International->value => 'International',
			self::MigrantWorker->value => 'Migrerende arbejdstager',
			self::Diaspora->value => 'Diaspora',
			self::Stateless->value => 'Statsløs',

			// Religion & Belief
			self::ReligiousMinority->value => 'Religiøs minoritet',
			self::Muslim->value => 'Muslim',
			self::Jewish->value => 'Jødisk',
			self::Hindu->value => 'Hindu',
			self::Buddhist->value => 'Buddhist',
			self::Sikh->value => 'Sikh',
			self::ChristianMinority->value => 'Kristen minoritet',
			self::Atheist->value => 'Ateist',
			self::Agnostic->value => 'Agnostiker',
			self::Spiritual->value => 'Spirituel',

			// Socioeconomic
			self::LowIncome->value => 'Lav indkomst',
			self::EconomicallyDisadvantaged->value => 'Økonomisk udsat',
			self::FirstGeneration->value => 'Første generation',
			self::FirstGenerationProfessional->value => 'Første generation i job',
			self::FirstGenerationCollege->value => 'Første generation på universitet',
			self::WorkingClass->value => 'Arbejderklasse',
			self::UnderrepresentedBackground->value => 'Underrepræsenteret baggrund',
			self::SocioeconomicallyDisadvantaged->value => 'Socioøkonomisk udsat',

			// Military & Veterans
			self::Veteran->value => 'Veteran',
			self::MilitaryFamily->value => 'Militærfamilie',
			self::ActiveDuty->value => 'Aktiv tjeneste',
			self::Reservist->value => 'Reservist',
			self::MilitarySpouse->value => 'Militærægtefælle',
			self::GoldStarFamily->value => 'Gold Star-familie',

			// Education & Language
			self::EnglishLanguageLearner->value => 'Engelsklærende',
			self::ESL->value => 'Engelsk som andetsprog',
			self::NonNativeSpeaker->value => 'Ikke-modersmålstaler',
			self::DifferentAbledLearner->value => 'Lærende med særlige behov',
			self::AlternativeEducation->value => 'Alternativ uddannelsesbaggrund',

			// Family & Caregiving
			self::SingleParent->value => 'Enlig forælder',
			self::Caregiver->value => 'Omsorgsperson',
			self::Parent->value => 'Forælder',
			self::FosterYouth->value => 'Plejefamilie-ung',
			self::Adoptee->value => 'Adopteret',
			self::Orphan->value => 'Forældreløs',

			// Regional & Geographic
			self::Rural->value => 'Landlig baggrund',
			self::Urban->value => 'Bybaggrund',
			self::Suburban->value => 'Forstadsbaggrund',
			self::RemoteArea->value => 'Afsides område',
			self::UnderservedRegion->value => 'Underforsynet region',

			// Other Marginalized Groups
			self::FormerlyIncarcerated->value => 'Tidligere fængslet',
			self::JusticeInvolved->value => 'Involveret i retssystemet',
			self::Homeless->value => 'Hjemløs/tidligere hjemløs',
			self::DomesticViolenceSurvivor->value => 'Overlevende efter vold i hjemmet',
			self::HumanTraffickingSurvivor->value => 'Overlevende efter menneskehandel',
			self::AddictionRecovery->value => 'I afhængighedsrestitution',

			// Intersectional Categories
			self::WomenOfColor->value => 'Kvinder of Color',
			self::DisabledWomen->value => 'Kvinder med handicap',
			self::LGBTQPlusYouth->value => 'LGBTQ+ unge',
			self::IndigenousWomen->value => 'Oprindelige kvinder',
			self::DisabledVeteran->value => 'Veteran med handicap',

			// General
			self::DiverseBackground->value => 'Mangfoldig baggrund',
			self::Underrepresented->value => 'Underrepræsenteret gruppe',
			self::Marginalized->value => 'Marginaliseret fællesskab',
			self::Minority->value => 'Minoritetsgruppe',
			self::ProtectedClass->value => 'Beskyttet klasse',
		];
	}

	// French Labels
	public static function labelsFr(): array
	{
		return [
			// Gender & Sexual Identity
			self::Women->value => 'Femmes',
			self::Men->value => 'Hommes',
			self::NonBinary->value => 'Non binaire',
			self::Transgender->value => 'Transgenre',
			self::Genderqueer->value => 'Genderqueer',
			self::Genderfluid->value => 'Genderfluid',
			self::Agender->value => 'Agender',
			self::Intersex->value => 'Intersexe',
			self::LGBTQPlus->value => 'LGBTQ+',
			self::Lesbian->value => 'Lesbienne',
			self::Gay->value => 'Gay',
			self::Bisexual->value => 'Bisexuel(le)',
			self::Queer->value => 'Queer',
			self::Asexual->value => 'Asexuel(le)',
			self::Pansexual->value => 'Pansexuel(le)',
			self::TwoSpirit->value => 'Deux-Esprits',

			// Disability & Accessibility
			self::Disabled->value => 'Personnes en situation de handicap',
			self::PhysicallyDisabled->value => 'Handicap physique',
			self::VisuallyImpaired->value => 'Déficience visuelle',
			self::HearingImpaired->value => 'Déficience auditive',
			self::Neurodiverse->value => 'Neurodiversité',
			self::Autistic->value => 'Autiste',
			self::ADHD->value => 'TDAH',
			self::Dyslexic->value => 'Dyslexique',
			self::MentalHealth->value => 'Santé mentale',
			self::MobilityImpairment->value => 'Mobilité réduite',
			self::ChronicIllness->value => 'Maladie chronique',
			self::InvisibleDisability->value => 'Handicap invisible',

			// Race & Ethnicity
			self::RacialMinority->value => 'Minorité raciale',
			self::EthnicMinority->value => 'Minorité ethnique',
			self::Black->value => 'Noir(e)/d’ascendance africaine',
			self::African->value => 'Africain(e)',
			self::AfricanAmerican->value => 'Afro-américain(e)',
			self::Asian->value => 'Asiatique',
			self::Hispanic->value => 'Hispanique',
			self::Latino->value => 'Latino/Latina',
			self::Indigenous->value => 'Peuples autochtones',
			self::NativeAmerican->value => 'Amérindien(ne)',
			self::PacificIslander->value => 'Insulaire du Pacifique',
			self::MiddleEastern->value => 'Moyen-Orient',
			self::Multiracial->value => 'Multiracial(e)',
			self::MixedRace->value => 'Métis',
			self::Quilombola->value => 'Quilombola',
			self::ScheduledCaste->value => 'Caste répertoriée (SC)',
			self::ScheduledTribe->value => 'Tribu répertoriée (ST)',
			self::OBC->value => 'Autres classes défavorisées (OBC)',

			// Age
			self::Youth->value => 'Jeunesse',
			self::YoungAdult->value => 'Jeune adulte',
			self::Senior->value => 'Senior',
			self::AgeDiverse->value => 'Diversité d’âge',
			self::GenerationZ->value => 'Génération Z',
			self::Millennial->value => 'Millennials',
			self::GenerationX->value => 'Génération X',
			self::BabyBoomer->value => 'Baby-boomers',

			// Nationality & Migration
			self::Immigrant->value => 'Immigrant(e)',
			self::Refugee->value => 'Réfugié(e)',
			self::AsylumSeeker->value => 'Demandeur/demandeuse d’asile',
			self::ForeignNational->value => 'Ressortissant étranger',
			self::International->value => 'International',
			self::MigrantWorker->value => 'Travailleur/travailleuse migrant(e)',
			self::Diaspora->value => 'Diaspora',
			self::Stateless->value => 'Apatride',

			// Religion & Belief
			self::ReligiousMinority->value => 'Minorité religieuse',
			self::Muslim->value => 'Musulman(e)',
			self::Jewish->value => 'Juif/Juive',
			self::Hindu->value => 'Hindou(e)',
			self::Buddhist->value => 'Bouddhiste',
			self::Sikh->value => 'Sikh',
			self::ChristianMinority->value => 'Minorité chrétienne',
			self::Atheist->value => 'Athée',
			self::Agnostic->value => 'Agnostique',
			self::Spiritual->value => 'Spirituel(le)',

			// Socioeconomic
			self::LowIncome->value => 'Faibles revenus',
			self::EconomicallyDisadvantaged->value => 'Désavantagé(e) économiquement',
			self::FirstGeneration->value => 'Première génération',
			self::FirstGenerationProfessional->value => 'Professionnel(le) de première génération',
			self::FirstGenerationCollege->value => 'Étudiant(e) de première génération',
			self::WorkingClass->value => 'Classe ouvrière',
			self::UnderrepresentedBackground->value => 'Origine sous-représentée',
			self::SocioeconomicallyDisadvantaged->value => 'Désavantagé(e) socioéconomiquement',

			// Military & Veterans
			self::Veteran->value => 'Vétéran',
			self::MilitaryFamily->value => 'Famille militaire',
			self::ActiveDuty->value => 'Service actif',
			self::Reservist->value => 'Réserviste',
			self::MilitarySpouse->value => 'Conjoint(e) militaire',
			self::GoldStarFamily->value => 'Famille Gold Star',

			// Education & Language
			self::EnglishLanguageLearner->value => 'Apprenant(e) d’anglais',
			self::ESL->value => 'Anglais langue seconde',
			self::NonNativeSpeaker->value => 'Non natif/ive',
			self::DifferentAbledLearner->value => 'Apprenant(e) en situation de handicap',
			self::AlternativeEducation->value => 'Parcours éducatif alternatif',

			// Family & Caregiving
			self::SingleParent->value => 'Parent isolé',
			self::Caregiver->value => 'Aidant(e)',
			self::Parent->value => 'Parent',
			self::FosterYouth->value => 'Jeune placé(e)',
			self::Adoptee->value => 'Adopté(e)',
			self::Orphan->value => 'Orphelin(e)',

			// Regional & Geographic
			self::Rural->value => 'Origine rurale',
			self::Urban->value => 'Origine urbaine',
			self::Suburban->value => 'Origine périurbaine',
			self::RemoteArea->value => 'Zone isolée',
			self::UnderservedRegion->value => 'Région sous-desservie',

			// Other Marginalized Groups
			self::FormerlyIncarcerated->value => 'Ancien(ne) détenu(e)',
			self::JusticeInvolved->value => 'En lien avec la justice',
			self::Homeless->value => 'Sans-abri/ancien(ne) sans-abri',
			self::DomesticViolenceSurvivor->value => 'Survivant(e) de violences domestiques',
			self::HumanTraffickingSurvivor->value => 'Survivant(e) de la traite des êtres humains',
			self::AddictionRecovery->value => 'Rétablissement d’addiction',

			// Intersectional Categories
			self::WomenOfColor->value => 'Femmes racisées',
			self::DisabledWomen->value => 'Femmes en situation de handicap',
			self::LGBTQPlusYouth->value => 'Jeunes LGBTQ+',
			self::IndigenousWomen->value => 'Femmes autochtones',
			self::DisabledVeteran->value => 'Vétéran en situation de handicap',

			// General
			self::DiverseBackground->value => 'Origine diverse',
			self::Underrepresented->value => 'Groupe sous-représenté',
			self::Marginalized->value => 'Communauté marginalisée',
			self::Minority->value => 'Groupe minoritaire',
			self::ProtectedClass->value => 'Catégorie protégée',
		];
	}

	// Hebrew Labels
	public static function labelsHe(): array
	{
		return [
			// Gender & Sexual Identity
			self::Women->value => 'נשים',
			self::Men->value => 'גברים',
			self::NonBinary->value => 'לא בינארי/ת',
			self::Transgender->value => 'טרנסג׳נדר/ית',
			self::Genderqueer->value => 'ג׳נדרקוויר',
			self::Genderfluid->value => 'ג׳נדר פלואיד',
			self::Agender->value => 'אג׳נדר',
			self::Intersex->value => 'אינטרסקס',
			self::LGBTQPlus->value => 'להט״ב+',
			self::Lesbian->value => 'לסבית',
			self::Gay->value => 'הומו',
			self::Bisexual->value => 'ביסקסואל/ית',
			self::Queer->value => 'קוויר',
			self::Asexual->value => 'א-מיני/ת',
			self::Pansexual->value => 'פאנסקסואל/ית',
			self::TwoSpirit->value => 'שתי-רוחות',

			// Disability & Accessibility
			self::Disabled->value => 'אנשים עם מוגבלות',
			self::PhysicallyDisabled->value => 'מוגבלות פיזית',
			self::VisuallyImpaired->value => 'לקות ראייה',
			self::HearingImpaired->value => 'לקות שמיעה',
			self::Neurodiverse->value => 'נוירודיברסיות',
			self::Autistic->value => 'אוטיסט/ית',
			self::ADHD->value => 'ADHD',
			self::Dyslexic->value => 'דיסלקסיה',
			self::MentalHealth->value => 'בריאות הנפש',
			self::MobilityImpairment->value => 'מוגבלות ניידות',
			self::ChronicIllness->value => 'מחלה כרונית',
			self::InvisibleDisability->value => 'מוגבלות סמויה',

			// Race & Ethnicity
			self::RacialMinority->value => 'מיעוט גזעי',
			self::EthnicMinority->value => 'מיעוט אתני',
			self::Black->value => 'שחור/ת ממוצא אפריקאי',
			self::African->value => 'אפריקאי/ת',
			self::AfricanAmerican->value => 'אפרו-אמריקאי/ת',
			self::Asian->value => 'אסייתי/ת',
			self::Hispanic->value => 'היספני/ת',
			self::Latino->value => 'לטיני/ת',
			self::Indigenous->value => 'עמים ילידיים',
			self::NativeAmerican->value => 'יליד/ה אמריקאי/ת',
			self::PacificIslander->value => 'תושב/ת איי האוקיינוס השקט',
			self::MiddleEastern->value => 'מזרח תיכוני/ת',
			self::Multiracial->value => 'רב-גזעי/ת',
			self::MixedRace->value => 'מעורב/ת',
			self::Quilombola->value => 'קילומבולה',
			self::ScheduledCaste->value => 'קאסטה מתועדת (SC)',
			self::ScheduledTribe->value => 'שבט מתועד (ST)',
			self::OBC->value => 'מעמדות נחשלות אחרות (OBC)',

			// Age
			self::Youth->value => 'נוער',
			self::YoungAdult->value => 'צעיר/ה בוגר/ת',
			self::Senior->value => 'ותיק/ה',
			self::AgeDiverse->value => 'גיוון גילאי',
			self::GenerationZ->value => 'דור Z',
			self::Millennial->value => 'מילניאל',
			self::GenerationX->value => 'דור X',
			self::BabyBoomer->value => 'בייבי בומר',

			// Nationality & Migration
			self::Immigrant->value => 'מהגר/ת',
			self::Refugee->value => 'פליט/ה',
			self::AsylumSeeker->value => 'מבקש/ת מקלט',
			self::ForeignNational->value => 'אזרח/ית זר/ה',
			self::International->value => 'בינלאומי',
			self::MigrantWorker->value => 'עובד/ת מהגר/ת',
			self::Diaspora->value => 'דיאספורה',
			self::Stateless->value => 'חסר/ת אזרחות',

			// Religion & Belief
			self::ReligiousMinority->value => 'מיעוט דתי',
			self::Muslim->value => 'מוסלמי/ת',
			self::Jewish->value => 'יהודי/ה',
			self::Hindu->value => 'הינדי/ת',
			self::Buddhist->value => 'בודהיסט/ית',
			self::Sikh->value => 'סיקי/ת',
			self::ChristianMinority->value => 'מיעוט נוצרי',
			self::Atheist->value => 'אתאיסט/ית',
			self::Agnostic->value => 'אגנוסטי/ת',
			self::Spiritual->value => 'רוחני/ת',

			// Socioeconomic
			self::LowIncome->value => 'הכנסה נמוכה',
			self::EconomicallyDisadvantaged->value => 'מוחלש/ת כלכלית',
			self::FirstGeneration->value => 'דור ראשון',
			self::FirstGenerationProfessional->value => 'מקצועי/ת דור ראשון',
			self::FirstGenerationCollege->value => 'סטודנט/ית דור ראשון',
			self::WorkingClass->value => 'מעמד עובדים',
			self::UnderrepresentedBackground->value => 'רקע בתת-ייצוג',
			self::SocioeconomicallyDisadvantaged->value => 'מוחלש/ת סוציו-אקונומית',

			// Military & Veterans
			self::Veteran->value => 'ותיק/ת צבא',
			self::MilitaryFamily->value => 'משפחה צבאית',
			self::ActiveDuty->value => 'שירות פעיל',
			self::Reservist->value => 'מילואימניק/ית',
			self::MilitarySpouse->value => 'בן/בת זוג צבאי/ת',
			self::GoldStarFamily->value => 'משפחת כוכב זהב',

			// Education & Language
			self::EnglishLanguageLearner->value => 'לומד/ת אנגלית',
			self::ESL->value => 'אנגלית כשפה שנייה',
			self::NonNativeSpeaker->value => 'דובר/ת לא ילידי/ת',
			self::DifferentAbledLearner->value => 'לומד/ת עם מוגבלות',
			self::AlternativeEducation->value => 'רקע חינוכי חלופי',

			// Family & Caregiving
			self::SingleParent->value => 'הורה יחיד/ה',
			self::Caregiver->value => 'מטפל/ת',
			self::Parent->value => 'הורה',
			self::FosterYouth->value => 'נוער באומנה',
			self::Adoptee->value => 'מאומץ/ת',
			self::Orphan->value => 'יתום/ה',

			// Regional & Geographic
			self::Rural->value => 'רקע כפרי',
			self::Urban->value => 'רקע עירוני',
			self::Suburban->value => 'רקע פרברי',
			self::RemoteArea->value => 'אזור מרוחק',
			self::UnderservedRegion->value => 'אזור מוחלש שירותית',

			// Other Marginalized Groups
			self::FormerlyIncarcerated->value => 'אסיר/ה לשעבר',
			self::JusticeInvolved->value => 'מעורב/ת במערכת המשפט',
			self::Homeless->value => 'חסר/ת בית/לשעבר',
			self::DomesticViolenceSurvivor->value => 'שורד/ת אלימות במשפחה',
			self::HumanTraffickingSurvivor->value => 'שורד/ת סחר בבני אדם',
			self::AddictionRecovery->value => 'בהחלמה מהתמכרות',

			// Intersectional Categories
			self::WomenOfColor->value => 'נשים מקבוצות מיעוט',
			self::DisabledWomen->value => 'נשים עם מוגבלות',
			self::LGBTQPlusYouth->value => 'נוער להט״ב+',
			self::IndigenousWomen->value => 'נשים ילידיות',
			self::DisabledVeteran->value => 'ותיק/ת עם מוגבלות',

			// General
			self::DiverseBackground->value => 'רקע מגוון',
			self::Underrepresented->value => 'קבוצה בתת-ייצוג',
			self::Marginalized->value => 'קהילה מודרת',
			self::Minority->value => 'קבוצת מיעוט',
			self::ProtectedClass->value => 'קבוצה מוגנת',
		];
	}

	// Italian Labels
	public static function labelsIt(): array
	{
		return [
			// Gender & Sexual Identity
			self::Women->value => 'Donne',
			self::Men->value => 'Uomini',
			self::NonBinary->value => 'Non binario',
			self::Transgender->value => 'Transgender',
			self::Genderqueer->value => 'Genderqueer',
			self::Genderfluid->value => 'Genderfluid',
			self::Agender->value => 'Agender',
			self::Intersex->value => 'Intersessuale',
			self::LGBTQPlus->value => 'LGBTQ+',
			self::Lesbian->value => 'Lesbica',
			self::Gay->value => 'Gay',
			self::Bisexual->value => 'Bisessuale',
			self::Queer->value => 'Queer',
			self::Asexual->value => 'Asessuale',
			self::Pansexual->value => 'Pansessuale',
			self::TwoSpirit->value => 'Due-Spiriti',

			// Disability & Accessibility
			self::Disabled->value => 'Persone con disabilità',
			self::PhysicallyDisabled->value => 'Disabilità fisica',
			self::VisuallyImpaired->value => 'Disabilità visiva',
			self::HearingImpaired->value => 'Disabilità uditiva',
			self::Neurodiverse->value => 'Neurodiversità',
			self::Autistic->value => 'Autistico/a',
			self::ADHD->value => 'ADHD',
			self::Dyslexic->value => 'Dislessico/a',
			self::MentalHealth->value => 'Salute mentale',
			self::MobilityImpairment->value => 'Limitazione della mobilità',
			self::ChronicIllness->value => 'Malattia cronica',
			self::InvisibleDisability->value => 'Disabilità invisibile',

			// Race & Ethnicity
			self::RacialMinority->value => 'Minoranza razziale',
			self::EthnicMinority->value => 'Minoranza etnica',
			self::Black->value => 'Nero/a / discendenza africana',
			self::African->value => 'Africano/a',
			self::AfricanAmerican->value => 'Afroamericano/a',
			self::Asian->value => 'Asiatico/a',
			self::Hispanic->value => 'Ispanico/a',
			self::Latino->value => 'Latino/Latina',
			self::Indigenous->value => 'Popoli indigeni',
			self::NativeAmerican->value => 'Nativo/a americano/a',
			self::PacificIslander->value => 'Isolano del Pacifico',
			self::MiddleEastern->value => 'Medio Oriente',
			self::Multiracial->value => 'Multirazziale',
			self::MixedRace->value => 'Razza mista',
			self::Quilombola->value => 'Quilombola',
			self::ScheduledCaste->value => 'Casta registrata (SC)',
			self::ScheduledTribe->value => 'Tribu registrata (ST)',
			self::OBC->value => 'Altre classi svantaggiate (OBC)',

			// Age
			self::Youth->value => 'Giovani',
			self::YoungAdult->value => 'Giovane adulto',
			self::Senior->value => 'Senior',
			self::AgeDiverse->value => 'Diversità di età',
			self::GenerationZ->value => 'Generazione Z',
			self::Millennial->value => 'Millennial',
			self::GenerationX->value => 'Generazione X',
			self::BabyBoomer->value => 'Baby Boomer',

			// Nationality & Migration
			self::Immigrant->value => 'Immigrato/a',
			self::Refugee->value => 'Rifugiato/a',
			self::AsylumSeeker->value => 'Richiedente asilo',
			self::ForeignNational->value => 'Cittadino/a straniero/a',
			self::International->value => 'Internazionale',
			self::MigrantWorker->value => 'Lavoratore/trice migrante',
			self::Diaspora->value => 'Diaspora',
			self::Stateless->value => 'Apòlide',

			// Religion & Belief
			self::ReligiousMinority->value => 'Minoranza religiosa',
			self::Muslim->value => 'Musulmano/a',
			self::Jewish->value => 'Ebreo/a',
			self::Hindu->value => 'Indù',
			self::Buddhist->value => 'Buddista',
			self::Sikh->value => 'Sikh',
			self::ChristianMinority->value => 'Minoranza cristiana',
			self::Atheist->value => 'Ateo/a',
			self::Agnostic->value => 'Agnostico/a',
			self::Spiritual->value => 'Spirituale',

			// Socioeconomic
			self::LowIncome->value => 'Basso reddito',
			self::EconomicallyDisadvantaged->value => 'Svantaggiato/a economicamente',
			self::FirstGeneration->value => 'Prima generazione',
			self::FirstGenerationProfessional->value => 'Professionista di prima generazione',
			self::FirstGenerationCollege->value => 'Studente di prima generazione',
			self::WorkingClass->value => 'Classe lavoratrice',
			self::UnderrepresentedBackground->value => 'Contesto sottorappresentato',
			self::SocioeconomicallyDisadvantaged->value => 'Svantaggiato/a socioeconomicamente',

			// Military & Veterans
			self::Veteran->value => 'Veterano',
			self::MilitaryFamily->value => 'Famiglia militare',
			self::ActiveDuty->value => 'Servizio attivo',
			self::Reservist->value => 'Riservista',
			self::MilitarySpouse->value => 'Coniuge militare',
			self::GoldStarFamily->value => 'Famiglia Gold Star',

			// Education & Language
			self::EnglishLanguageLearner->value => 'Studente di inglese',
			self::ESL->value => 'Inglese come seconda lingua',
			self::NonNativeSpeaker->value => 'Non madrelingua',
			self::DifferentAbledLearner->value => 'Studente con disabilità',
			self::AlternativeEducation->value => 'Percorso educativo alternativo',

			// Family & Caregiving
			self::SingleParent->value => 'Genitore single',
			self::Caregiver->value => 'Caregiver',
			self::Parent->value => 'Genitore',
			self::FosterYouth->value => 'Giovane in affido',
			self::Adoptee->value => 'Adottato/a',
			self::Orphan->value => 'Orfano/a',

			// Regional & Geographic
			self::Rural->value => 'Contesto rurale',
			self::Urban->value => 'Contesto urbano',
			self::Suburban->value => 'Contesto suburbano',
			self::RemoteArea->value => 'Area remota',
			self::UnderservedRegion->value => 'Regione poco servita',

			// Other Marginalized Groups
			self::FormerlyIncarcerated->value => 'Ex detenuto/a',
			self::JusticeInvolved->value => 'Coinvolto/a nel sistema giudiziario',
			self::Homeless->value => 'Senza fissa dimora / ex',
			self::DomesticViolenceSurvivor->value => 'Sopravvissuto/a a violenza domestica',
			self::HumanTraffickingSurvivor->value => 'Sopravvissuto/a alla tratta di esseri umani',
			self::AddictionRecovery->value => 'Recupero da dipendenza',

			// Intersectional Categories
			self::WomenOfColor->value => 'Donne razzializzate',
			self::DisabledWomen->value => 'Donne con disabilità',
			self::LGBTQPlusYouth->value => 'Giovani LGBTQ+',
			self::IndigenousWomen->value => 'Donne indigene',
			self::DisabledVeteran->value => 'Veterano con disabilità',

			// General
			self::DiverseBackground->value => 'Contesto diverso',
			self::Underrepresented->value => 'Gruppo sottorappresentato',
			self::Marginalized->value => 'Comunità emarginata',
			self::Minority->value => 'Gruppo minoritario',
			self::ProtectedClass->value => 'Classe protetta',
		];
	}

	// Japanese Labels
	public static function labelsJa(): array
	{
		return [
			// Gender & Sexual Identity
			self::Women->value => '女性',
			self::Men->value => '男性',
			self::NonBinary->value => 'ノンバイナリー',
			self::Transgender->value => 'トランスジェンダー',
			self::Genderqueer->value => 'ジェンダークィア',
			self::Genderfluid->value => 'ジェンダーフルイド',
			self::Agender->value => 'アジェンダー',
			self::Intersex->value => 'インターセックス',
			self::LGBTQPlus->value => 'LGBTQ+',
			self::Lesbian->value => 'レズビアン',
			self::Gay->value => 'ゲイ',
			self::Bisexual->value => 'バイセクシュアル',
			self::Queer->value => 'クィア',
			self::Asexual->value => 'アセクシュアル',
			self::Pansexual->value => 'パンセクシュアル',
			self::TwoSpirit->value => 'ツースピリット',

			// Disability & Accessibility
			self::Disabled->value => '障害のある人',
			self::PhysicallyDisabled->value => '身体障害',
			self::VisuallyImpaired->value => '視覚障害',
			self::HearingImpaired->value => '聴覚障害',
			self::Neurodiverse->value => 'ニューロダイバーシティ',
			self::Autistic->value => '自閉スペクトラム',
			self::ADHD->value => 'ADHD',
			self::Dyslexic->value => 'ディスレクシア',
			self::MentalHealth->value => 'メンタルヘルス',
			self::MobilityImpairment->value => '移動障害',
			self::ChronicIllness->value => '慢性疾患',
			self::InvisibleDisability->value => '見えない障害',

			// Race & Ethnicity
			self::RacialMinority->value => '人種的マイノリティ',
			self::EthnicMinority->value => '民族的マイノリティ',
			self::Black->value => '黒人／アフリカ系',
			self::African->value => 'アフリカ系',
			self::AfricanAmerican->value => 'アフリカ系アメリカ人',
			self::Asian->value => 'アジア系',
			self::Hispanic->value => 'ヒスパニック',
			self::Latino->value => 'ラティーノ／ラティーナ',
			self::Indigenous->value => '先住民族',
			self::NativeAmerican->value => 'ネイティブアメリカン',
			self::PacificIslander->value => '太平洋諸島系',
			self::MiddleEastern->value => '中東系',
			self::Multiracial->value => '多民族／多人種',
			self::MixedRace->value => '混血',
			self::Quilombola->value => 'キロンボラ',
			self::ScheduledCaste->value => '指定カースト（SC）',
			self::ScheduledTribe->value => '指定部族（ST）',
			self::OBC->value => 'その他後進階級（OBC）',

			// Age
			self::Youth->value => '若者',
			self::YoungAdult->value => '若年成人',
			self::Senior->value => '高齢者',
			self::AgeDiverse->value => '年齢の多様性',
			self::GenerationZ->value => 'Z世代',
			self::Millennial->value => 'ミレニアル世代',
			self::GenerationX->value => 'X世代',
			self::BabyBoomer->value => 'ベビーブーマー',

			// Nationality & Migration
			self::Immigrant->value => '移民',
			self::Refugee->value => '難民',
			self::AsylumSeeker->value => '庇護申請者',
			self::ForeignNational->value => '外国籍',
			self::International->value => '国際的',
			self::MigrantWorker->value => '出稼ぎ労働者',
			self::Diaspora->value => 'ディアスポラ',
			self::Stateless->value => '無国籍',

			// Religion & Belief
			self::ReligiousMinority->value => '宗教的マイノリティ',
			self::Muslim->value => 'ムスリム',
			self::Jewish->value => 'ユダヤ教徒',
			self::Hindu->value => 'ヒンドゥー教徒',
			self::Buddhist->value => '仏教徒',
			self::Sikh->value => 'シク教徒',
			self::ChristianMinority->value => 'キリスト教マイノリティ',
			self::Atheist->value => '無神論者',
			self::Agnostic->value => '不可知論者',
			self::Spiritual->value => 'スピリチュアル',

			// Socioeconomic
			self::LowIncome->value => '低所得',
			self::EconomicallyDisadvantaged->value => '経済的困難',
			self::FirstGeneration->value => '第一世代',
			self::FirstGenerationProfessional->value => '第一世代の専門職',
			self::FirstGenerationCollege->value => '大学第一世代',
			self::WorkingClass->value => '労働者階級',
			self::UnderrepresentedBackground->value => '過小代表の背景',
			self::SocioeconomicallyDisadvantaged->value => '社会経済的困難',

			// Military & Veterans
			self::Veteran->value => '退役軍人',
			self::MilitaryFamily->value => '軍人家族',
			self::ActiveDuty->value => '現役',
			self::Reservist->value => '予備役',
			self::MilitarySpouse->value => '軍人配偶者',
			self::GoldStarFamily->value => 'ゴールドスター・ファミリー',

			// Education & Language
			self::EnglishLanguageLearner->value => '英語学習者',
			self::ESL->value => '第二言語としての英語',
			self::NonNativeSpeaker->value => '非母語話者',
			self::DifferentAbledLearner->value => '多様な学習支援ニーズ',
			self::AlternativeEducation->value => '代替教育の背景',

			// Family & Caregiving
			self::SingleParent->value => 'ひとり親',
			self::Caregiver->value => '介護者',
			self::Parent->value => '親',
			self::FosterYouth->value => '里親家庭の若者',
			self::Adoptee->value => '養子',
			self::Orphan->value => '孤児',

			// Regional & Geographic
			self::Rural->value => '農村出身',
			self::Urban->value => '都市出身',
			self::Suburban->value => '郊外出身',
			self::RemoteArea->value => '遠隔地',
			self::UnderservedRegion->value => '支援不足地域',

			// Other Marginalized Groups
			self::FormerlyIncarcerated->value => '元受刑者',
			self::JusticeInvolved->value => '司法関与',
			self::Homeless->value => 'ホームレス／元ホームレス',
			self::DomesticViolenceSurvivor->value => 'DV被害者（サバイバー）',
			self::HumanTraffickingSurvivor->value => '人身取引被害者（サバイバー）',
			self::AddictionRecovery->value => '依存症からの回復',

			// Intersectional Categories
			self::WomenOfColor->value => '有色人種の女性',
			self::DisabledWomen->value => '障害のある女性',
			self::LGBTQPlusYouth->value => 'LGBTQ+の若者',
			self::IndigenousWomen->value => '先住民族の女性',
			self::DisabledVeteran->value => '障害のある退役軍人',

			// General
			self::DiverseBackground->value => '多様な背景',
			self::Underrepresented->value => '過小代表グループ',
			self::Marginalized->value => '周縁化されたコミュニティ',
			self::Minority->value => 'マイノリティ',
			self::ProtectedClass->value => '保護対象クラス',
		];
	}

	// Dutch Labels
	public static function labelsNl(): array
	{
		return [
			self::Women->value => 'Vrouwen',
			self::Men->value => 'Mannen',
			self::NonBinary->value => 'Non-binair',
			self::Transgender->value => 'Transgender',
			self::Genderqueer->value => 'Genderqueer',
			self::Genderfluid->value => 'Genderfluid',
			self::Agender->value => 'Agender',
			self::Intersex->value => 'Intersekse',
			self::LGBTQPlus->value => 'LGBTQ+',
			self::Lesbian->value => 'Lesbisch',
			self::Gay->value => 'Homo',
			self::Bisexual->value => 'Biseksueel',
			self::Queer->value => 'Queer',
			self::Asexual->value => 'Aseksueel',
			self::Pansexual->value => 'Panseksueel',
			self::TwoSpirit->value => 'Two-Spirit',

			self::Disabled->value => 'Mensen met een beperking',
			self::PhysicallyDisabled->value => 'Lichamelijke beperking',
			self::VisuallyImpaired->value => 'Visuele beperking',
			self::HearingImpaired->value => 'Auditieve beperking',
			self::Neurodiverse->value => 'Neurodivers',
			self::Autistic->value => 'Autistisch',
			self::ADHD->value => 'ADHD',
			self::Dyslexic->value => 'Dyslectisch',
			self::MentalHealth->value => 'Mentale gezondheid',
			self::MobilityImpairment->value => 'Mobiliteitsbeperking',
			self::ChronicIllness->value => 'Chronische ziekte',
			self::InvisibleDisability->value => 'Onzichtbare beperking',

			self::RacialMinority->value => 'Raciale minderheid',
			self::EthnicMinority->value => 'Etnische minderheid',
			self::Black->value => 'Zwart/Afrikaanse afkomst',
			self::African->value => 'Afrikaans',
			self::AfricanAmerican->value => 'Afro-Amerikaans',
			self::Asian->value => 'Aziatisch',
			self::Hispanic->value => 'Hispanisch',
			self::Latino->value => 'Latino/Latina',
			self::Indigenous->value => 'Inheemse volkeren',
			self::NativeAmerican->value => 'Native American',
			self::PacificIslander->value => 'Pacific Islander',
			self::MiddleEastern->value => 'Midden-Oosters',
			self::Multiracial->value => 'Multiraciaal',
			self::MixedRace->value => 'Gemengde afkomst',
			self::Quilombola->value => 'Quilombola',
			self::ScheduledCaste->value => 'Geregistreerde kaste (SC)',
			self::ScheduledTribe->value => 'Geregistreerde stam (ST)',
			self::OBC->value => 'Andere achtergestelde klassen (OBC)',

			self::Youth->value => 'Jeugd',
			self::YoungAdult->value => 'Jongvolwassene',
			self::Senior->value => 'Senior',
			self::AgeDiverse->value => 'Leeftijdsdiversiteit',
			self::GenerationZ->value => 'Generatie Z',
			self::Millennial->value => 'Millennial',
			self::GenerationX->value => 'Generatie X',
			self::BabyBoomer->value => 'Babyboomer',

			self::Immigrant->value => 'Immigrant',
			self::Refugee->value => 'Vluchteling',
			self::AsylumSeeker->value => 'Asielzoeker',
			self::ForeignNational->value => 'Buitenlander',
			self::International->value => 'Internationaal',
			self::MigrantWorker->value => 'Migrerende werknemer',
			self::Diaspora->value => 'Diaspora',
			self::Stateless->value => 'Staatloos',

			self::ReligiousMinority->value => 'Religieuze minderheid',
			self::Muslim->value => 'Moslim',
			self::Jewish->value => 'Joods',
			self::Hindu->value => 'Hindoe',
			self::Buddhist->value => 'Boeddhist',
			self::Sikh->value => 'Sikh',
			self::ChristianMinority->value => 'Christelijke minderheid',
			self::Atheist->value => 'Atheïst',
			self::Agnostic->value => 'Agnost',
			self::Spiritual->value => 'Spiritueel',

			self::LowIncome->value => 'Laag inkomen',
			self::EconomicallyDisadvantaged->value => 'Economisch achtergesteld',
			self::FirstGeneration->value => 'Eerste generatie',
			self::FirstGenerationProfessional->value => 'Eerste generatie professional',
			self::FirstGenerationCollege->value => 'Eerste generatie student',
			self::WorkingClass->value => 'Arbeidersklasse',
			self::UnderrepresentedBackground->value => 'Ondervertegenwoordigde achtergrond',
			self::SocioeconomicallyDisadvantaged->value => 'Sociaal-economisch achtergesteld',

			self::Veteran->value => 'Veteraan',
			self::MilitaryFamily->value => 'Militaire familie',
			self::ActiveDuty->value => 'Actieve dienst',
			self::Reservist->value => 'Reservist',
			self::MilitarySpouse->value => 'Militaire partner',
			self::GoldStarFamily->value => 'Gold Star-familie',

			self::EnglishLanguageLearner->value => 'Leerling Engels',
			self::ESL->value => 'Engels als tweede taal',
			self::NonNativeSpeaker->value => 'Niet-moedertaalspreker',
			self::DifferentAbledLearner->value => 'Leerling met beperking',
			self::AlternativeEducation->value => 'Alternatieve onderwijsachtergrond',

			self::SingleParent->value => 'Alleenstaande ouder',
			self::Caregiver->value => 'Mantelzorger',
			self::Parent->value => 'Ouder',
			self::FosterYouth->value => 'Pleegzorgjongere',
			self::Adoptee->value => 'Geadopteerd',
			self::Orphan->value => 'Wees',

			self::Rural->value => 'Plattelandsachtergrond',
			self::Urban->value => 'Stedelijke achtergrond',
			self::Suburban->value => 'Voorstedelijke achtergrond',
			self::RemoteArea->value => 'Afgelegen gebied',
			self::UnderservedRegion->value => 'Onderbediende regio',

			self::FormerlyIncarcerated->value => 'Voorheen gedetineerd',
			self::JusticeInvolved->value => 'Betrokken bij justitie',
			self::Homeless->value => 'Dakloos/voorheen dakloos',
			self::DomesticViolenceSurvivor->value => 'Overlevende huiselijk geweld',
			self::HumanTraffickingSurvivor->value => 'Overlevende mensenhandel',
			self::AddictionRecovery->value => 'Herstel van verslaving',

			self::WomenOfColor->value => 'Vrouwen van kleur',
			self::DisabledWomen->value => 'Vrouwen met een beperking',
			self::LGBTQPlusYouth->value => 'LGBTQ+ jongeren',
			self::IndigenousWomen->value => 'Inheemse vrouwen',
			self::DisabledVeteran->value => 'Veteraan met een beperking',

			self::DiverseBackground->value => 'Diverse achtergrond',
			self::Underrepresented->value => 'Ondervertegenwoordigde groep',
			self::Marginalized->value => 'Gemarginaliseerde gemeenschap',
			self::Minority->value => 'Minderheidsgroep',
			self::ProtectedClass->value => 'Beschermde klasse',
		];
	}

	// Polish Labels
	public static function labelsPl(): array
	{
		return [
			self::Women->value => 'Kobiety',
			self::Men->value => 'Mężczyźni',
			self::NonBinary->value => 'Niebinarny/a',
			self::Transgender->value => 'Transpłciowy/a',
			self::Genderqueer->value => 'Genderqueer',
			self::Genderfluid->value => 'Genderfluid',
			self::Agender->value => 'Agender',
			self::Intersex->value => 'Interseks',
			self::LGBTQPlus->value => 'LGBTQ+',
			self::Lesbian->value => 'Lesbijka',
			self::Gay->value => 'Gej',
			self::Bisexual->value => 'Biseksualny/a',
			self::Queer->value => 'Queer',
			self::Asexual->value => 'Aseksualny/a',
			self::Pansexual->value => 'Panseksualny/a',
			self::TwoSpirit->value => 'Two-Spirit',

			self::Disabled->value => 'Osoby z niepełnosprawnościami',
			self::PhysicallyDisabled->value => 'Niepełnosprawność fizyczna',
			self::VisuallyImpaired->value => 'Niepełnosprawność wzroku',
			self::HearingImpaired->value => 'Niepełnosprawność słuchu',
			self::Neurodiverse->value => 'Neuroróżnorodność',
			self::Autistic->value => 'Autystyczny/a',
			self::ADHD->value => 'ADHD',
			self::Dyslexic->value => 'Dyslektyczny/a',
			self::MentalHealth->value => 'Zdrowie psychiczne',
			self::MobilityImpairment->value => 'Ograniczenie mobilności',
			self::ChronicIllness->value => 'Choroba przewlekła',
			self::InvisibleDisability->value => 'Niewidoczna niepełnosprawność',

			self::RacialMinority->value => 'Mniejszość rasowa',
			self::EthnicMinority->value => 'Mniejszość etniczna',
			self::Black->value => 'Czarnoskóry/a / pochodzenia afrykańskiego',
			self::African->value => 'Afrykański/a',
			self::AfricanAmerican->value => 'Afroamerykański/a',
			self::Asian->value => 'Azjatycki/a',
			self::Hispanic->value => 'Hiszpańskojęzyczny/a',
			self::Latino->value => 'Latynos/Latynoska',
			self::Indigenous->value => 'Ludy rdzenne',
			self::NativeAmerican->value => 'Rdzenni Amerykanie',
			self::PacificIslander->value => 'Mieszkaniec wysp Pacyfiku',
			self::MiddleEastern->value => 'Bliski Wschód',
			self::Multiracial->value => 'Wielorasowy/a',
			self::MixedRace->value => 'Mieszanej rasy',
			self::Quilombola->value => 'Quilombola',
			self::ScheduledCaste->value => 'Kasta rejestrowana (SC)',
			self::ScheduledTribe->value => 'Plemię rejestrowane (ST)',
			self::OBC->value => 'Inne klasy zacofane (OBC)',

			self::Youth->value => 'Młodzież',
			self::YoungAdult->value => 'Młody dorosły',
			self::Senior->value => 'Senior',
			self::AgeDiverse->value => 'Różnorodność wiekowa',
			self::GenerationZ->value => 'Pokolenie Z',
			self::Millennial->value => 'Millenialsi',
			self::GenerationX->value => 'Pokolenie X',
			self::BabyBoomer->value => 'Baby boomers',

			self::Immigrant->value => 'Imigrant/ka',
			self::Refugee->value => 'Uchodźca/uchodźczyni',
			self::AsylumSeeker->value => 'Osoba ubiegająca się o azyl',
			self::ForeignNational->value => 'Cudzoziemiec/cudzoziemka',
			self::International->value => 'Międzynarodowy/a',
			self::MigrantWorker->value => 'Pracownik migrujący',
			self::Diaspora->value => 'Diaspora',
			self::Stateless->value => 'Bezpaństwowiec',

			self::ReligiousMinority->value => 'Mniejszość religijna',
			self::Muslim->value => 'Muzułmanin/muzułmanka',
			self::Jewish->value => 'Żyd/Żydówka',
			self::Hindu->value => 'Hinduista/hinduistka',
			self::Buddhist->value => 'Budysta/budystka',
			self::Sikh->value => 'Sikh',
			self::ChristianMinority->value => 'Mniejszość chrześcijańska',
			self::Atheist->value => 'Ateista/ateistka',
			self::Agnostic->value => 'Agnostyk/agnostyczka',
			self::Spiritual->value => 'Duchowy/a',

			self::LowIncome->value => 'Niskie dochody',
			self::EconomicallyDisadvantaged->value => 'W trudnej sytuacji ekonomicznej',
			self::FirstGeneration->value => 'Pierwsze pokolenie',
			self::FirstGenerationProfessional->value => 'Profesjonalista pierwszego pokolenia',
			self::FirstGenerationCollege->value => 'Student pierwszego pokolenia',
			self::WorkingClass->value => 'Klasa pracująca',
			self::UnderrepresentedBackground->value => 'Niedoreprezentowane pochodzenie',
			self::SocioeconomicallyDisadvantaged->value => 'W trudnej sytuacji społeczno-ekonomicznej',

			self::Veteran->value => 'Weteran',
			self::MilitaryFamily->value => 'Rodzina wojskowa',
			self::ActiveDuty->value => 'Czynna służba',
			self::Reservist->value => 'Rezerwista',
			self::MilitarySpouse->value => 'Małżonek wojskowy',
			self::GoldStarFamily->value => 'Rodzina Gold Star',

			self::EnglishLanguageLearner->value => 'Uczący się angielskiego',
			self::ESL->value => 'Angielski jako drugi język',
			self::NonNativeSpeaker->value => 'Nienatywny użytkownik języka',
			self::DifferentAbledLearner->value => 'Uczeń z niepełnosprawnością',
			self::AlternativeEducation->value => 'Alternatywna ścieżka edukacji',

			self::SingleParent->value => 'Samotny rodzic',
			self::Caregiver->value => 'Opiekun/ka',
			self::Parent->value => 'Rodzic',
			self::FosterYouth->value => 'Młodzież w pieczy zastępczej',
			self::Adoptee->value => 'Osoba adoptowana',
			self::Orphan->value => 'Sierota',

			self::Rural->value => 'Pochodzenie wiejskie',
			self::Urban->value => 'Pochodzenie miejskie',
			self::Suburban->value => 'Pochodzenie podmiejskie',
			self::RemoteArea->value => 'Obszar odległy',
			self::UnderservedRegion->value => 'Region niedostatecznie obsługiwany',

			self::FormerlyIncarcerated->value => 'Były/a osadzony/a',
			self::JusticeInvolved->value => 'Osoba związana z wymiarem sprawiedliwości',
			self::Homeless->value => 'Bezdomny/a lub wcześniej bezdomny/a',
			self::DomesticViolenceSurvivor->value => 'Ocalały/a z przemocy domowej',
			self::HumanTraffickingSurvivor->value => 'Ocalały/a z handlu ludźmi',
			self::AddictionRecovery->value => 'W trakcie wychodzenia z uzależnienia',

			self::WomenOfColor->value => 'Kobiety kolorowe',
			self::DisabledWomen->value => 'Kobiety z niepełnosprawnościami',
			self::LGBTQPlusYouth->value => 'Młodzież LGBTQ+',
			self::IndigenousWomen->value => 'Rdzenne kobiety',
			self::DisabledVeteran->value => 'Weteran z niepełnosprawnością',

			self::DiverseBackground->value => 'Różnorodne pochodzenie',
			self::Underrepresented->value => 'Grupa niedoreprezentowana',
			self::Marginalized->value => 'Społeczność marginalizowana',
			self::Minority->value => 'Grupa mniejszościowa',
			self::ProtectedClass->value => 'Klasa chroniona',
		];
	}

	// Russian Labels
	public static function labelsRu(): array
	{
		return [
			self::Women->value => 'Женщины',
			self::Men->value => 'Мужчины',
			self::NonBinary->value => 'Небинарные',
			self::Transgender->value => 'Трансгендерные',
			self::Genderqueer->value => 'Джендерквир',
			self::Genderfluid->value => 'Гендерфлюид',
			self::Agender->value => 'Агендерные',
			self::Intersex->value => 'Интерсекс',
			self::LGBTQPlus->value => 'ЛГБТК+',
			self::Lesbian->value => 'Лесбиянки',
			self::Gay->value => 'Геи',
			self::Bisexual->value => 'Бисексуальные',
			self::Queer->value => 'Квир',
			self::Asexual->value => 'Асексуальные',
			self::Pansexual->value => 'Пансексуальные',
			self::TwoSpirit->value => 'Две души',

			self::Disabled->value => 'Люди с инвалидностью',
			self::PhysicallyDisabled->value => 'Физическая инвалидность',
			self::VisuallyImpaired->value => 'Нарушение зрения',
			self::HearingImpaired->value => 'Нарушение слуха',
			self::Neurodiverse->value => 'Нейроразнообразие',
			self::Autistic->value => 'Аутизм',
			self::ADHD->value => 'СДВГ',
			self::Dyslexic->value => 'Дислексия',
			self::MentalHealth->value => 'Психическое здоровье',
			self::MobilityImpairment->value => 'Нарушение мобильности',
			self::ChronicIllness->value => 'Хроническое заболевание',
			self::InvisibleDisability->value => 'Невидимая инвалидность',

			self::RacialMinority->value => 'Расовое меньшинство',
			self::EthnicMinority->value => 'Этническое меньшинство',
			self::Black->value => 'Чернокожие/африканского происхождения',
			self::African->value => 'Африканцы',
			self::AfricanAmerican->value => 'Афроамериканцы',
			self::Asian->value => 'Азиаты',
			self::Hispanic->value => 'Испаноязычные',
			self::Latino->value => 'Латино/латиноамериканцы',
			self::Indigenous->value => 'Коренные народы',
			self::NativeAmerican->value => 'Коренные американцы',
			self::PacificIslander->value => 'Жители островов Тихого океана',
			self::MiddleEastern->value => 'Ближний Восток',
			self::Multiracial->value => 'Многорасовые',
			self::MixedRace->value => 'Смешанная раса',
			self::Quilombola->value => 'Киломболы',
			self::ScheduledCaste->value => 'Зарегистрированная каста (SC)',
			self::ScheduledTribe->value => 'Зарегистрированное племя (ST)',
			self::OBC->value => 'Другие отсталые классы (OBC)',

			self::Youth->value => 'Молодёжь',
			self::YoungAdult->value => 'Молодой взрослый',
			self::Senior->value => 'Пожилые',
			self::AgeDiverse->value => 'Возрастное разнообразие',
			self::GenerationZ->value => 'Поколение Z',
			self::Millennial->value => 'Миллениалы',
			self::GenerationX->value => 'Поколение X',
			self::BabyBoomer->value => 'Бэби-бумеры',

			self::Immigrant->value => 'Иммигрант',
			self::Refugee->value => 'Беженец',
			self::AsylumSeeker->value => 'Проситель убежища',
			self::ForeignNational->value => 'Иностранный гражданин',
			self::International->value => 'Международный',
			self::MigrantWorker->value => 'Трудовой мигрант',
			self::Diaspora->value => 'Диаспора',
			self::Stateless->value => 'Лицо без гражданства',

			self::ReligiousMinority->value => 'Религиозное меньшинство',
			self::Muslim->value => 'Мусульмане',
			self::Jewish->value => 'Евреи',
			self::Hindu->value => 'Индуисты',
			self::Buddhist->value => 'Буддисты',
			self::Sikh->value => 'Сикхи',
			self::ChristianMinority->value => 'Христианское меньшинство',
			self::Atheist->value => 'Атеисты',
			self::Agnostic->value => 'Агностики',
			self::Spiritual->value => 'Духовные',

			self::LowIncome->value => 'Низкий доход',
			self::EconomicallyDisadvantaged->value => 'Экономически уязвимые',
			self::FirstGeneration->value => 'Первое поколение',
			self::FirstGenerationProfessional->value => 'Профессионал первого поколения',
			self::FirstGenerationCollege->value => 'Студент первого поколения',
			self::WorkingClass->value => 'Рабочий класс',
			self::UnderrepresentedBackground->value => 'Недопредставленный бэкграунд',
			self::SocioeconomicallyDisadvantaged->value => 'Социально-экономически уязвимые',

			self::Veteran->value => 'Ветераны',
			self::MilitaryFamily->value => 'Военная семья',
			self::ActiveDuty->value => 'Действующая служба',
			self::Reservist->value => 'Резервист',
			self::MilitarySpouse->value => 'Супруг(а) военнослужащего',
			self::GoldStarFamily->value => 'Семья Gold Star',

			self::EnglishLanguageLearner->value => 'Изучающие английский',
			self::ESL->value => 'Английский как второй язык',
			self::NonNativeSpeaker->value => 'Неноситель языка',
			self::DifferentAbledLearner->value => 'Учащийся с инвалидностью',
			self::AlternativeEducation->value => 'Альтернативное образование',

			self::SingleParent->value => 'Одинокий родитель',
			self::Caregiver->value => 'Ухаживающий/опекун',
			self::Parent->value => 'Родитель',
			self::FosterYouth->value => 'Приёмная молодёжь',
			self::Adoptee->value => 'Усыновлённый/ая',
			self::Orphan->value => 'Сирота',

			self::Rural->value => 'Сельский бэкграунд',
			self::Urban->value => 'Городской бэкграунд',
			self::Suburban->value => 'Пригородный бэкграунд',
			self::RemoteArea->value => 'Удалённая местность',
			self::UnderservedRegion->value => 'Недостаточно обслуживаемый регион',

			self::FormerlyIncarcerated->value => 'Ранее заключённые',
			self::JusticeInvolved->value => 'Связанные с системой правосудия',
			self::Homeless->value => 'Бездомные/ранее бездомные',
			self::DomesticViolenceSurvivor->value => 'Пережившие домашнее насилие',
			self::HumanTraffickingSurvivor->value => 'Пережившие торговлю людьми',
			self::AddictionRecovery->value => 'Выздоровление от зависимости',

			self::WomenOfColor->value => 'Женщины небелого происхождения',
			self::DisabledWomen->value => 'Женщины с инвалидностью',
			self::LGBTQPlusYouth->value => 'ЛГБТК+ молодёжь',
			self::IndigenousWomen->value => 'Женщины коренных народов',
			self::DisabledVeteran->value => 'Ветеран с инвалидностью',

			self::DiverseBackground->value => 'Разнообразный бэкграунд',
			self::Underrepresented->value => 'Недопредставленная группа',
			self::Marginalized->value => 'Маргинализированное сообщество',
			self::Minority->value => 'Группа меньшинства',
			self::ProtectedClass->value => 'Защищённая категория',
		];
	}

	// Turkish Labels
	public static function labelsTr(): array
	{
		return [
			self::Women->value => 'Kadınlar',
			self::Men->value => 'Erkekler',
			self::NonBinary->value => 'İkili olmayan',
			self::Transgender->value => 'Transgender',
			self::Genderqueer->value => 'Genderqueer',
			self::Genderfluid->value => 'Genderfluid',
			self::Agender->value => 'Agender',
			self::Intersex->value => 'İnterseks',
			self::LGBTQPlus->value => 'LGBTQ+',
			self::Lesbian->value => 'Lezbiyen',
			self::Gay->value => 'Gay',
			self::Bisexual->value => 'Biseksüel',
			self::Queer->value => 'Queer',
			self::Asexual->value => 'Aseksüel',
			self::Pansexual->value => 'Panseksüel',
			self::TwoSpirit->value => 'İki Ruh',

			self::Disabled->value => 'Engelli bireyler',
			self::PhysicallyDisabled->value => 'Fiziksel engel',
			self::VisuallyImpaired->value => 'Görme engeli',
			self::HearingImpaired->value => 'İşitme engeli',
			self::Neurodiverse->value => 'Nöroçeşitlilik',
			self::Autistic->value => 'Otistik',
			self::ADHD->value => 'DEHB',
			self::Dyslexic->value => 'Disleksi',
			self::MentalHealth->value => 'Ruh sağlığı',
			self::MobilityImpairment->value => 'Hareket kısıtlılığı',
			self::ChronicIllness->value => 'Kronik hastalık',
			self::InvisibleDisability->value => 'Görünmez engel',

			self::RacialMinority->value => 'Irksal azınlık',
			self::EthnicMinority->value => 'Etnik azınlık',
			self::Black->value => 'Siyah / Afrika kökenli',
			self::African->value => 'Afrikalı',
			self::AfricanAmerican->value => 'Afrika kökenli Amerikalı',
			self::Asian->value => 'Asyalı',
			self::Hispanic->value => 'Hispanik',
			self::Latino->value => 'Latino/Latina',
			self::Indigenous->value => 'Yerel halklar',
			self::NativeAmerican->value => 'Kızılderili',
			self::PacificIslander->value => 'Pasifik Adalı',
			self::MiddleEastern->value => 'Orta Doğulu',
			self::Multiracial->value => 'Çok ırklı',
			self::MixedRace->value => 'Melez',
			self::Quilombola->value => 'Quilombola',
			self::ScheduledCaste->value => 'Kayıtlı kast (SC)',
			self::ScheduledTribe->value => 'Kayıtlı kabile (ST)',
			self::OBC->value => 'Diğer geri sınıflar (OBC)',

			self::Youth->value => 'Genç',
			self::YoungAdult->value => 'Genç yetişkin',
			self::Senior->value => 'Kıdemli',
			self::AgeDiverse->value => 'Yaş çeşitliliği',
			self::GenerationZ->value => 'Z Kuşağı',
			self::Millennial->value => 'Y Kuşağı (Millennial)',
			self::GenerationX->value => 'X Kuşağı',
			self::BabyBoomer->value => 'Baby Boomer',

			self::Immigrant->value => 'Göçmen',
			self::Refugee->value => 'Mülteci',
			self::AsylumSeeker->value => 'Sığınma talep eden',
			self::ForeignNational->value => 'Yabancı uyruklu',
			self::International->value => 'Uluslararası',
			self::MigrantWorker->value => 'Göçmen işçi',
			self::Diaspora->value => 'Diaspora',
			self::Stateless->value => 'Vatansız',

			self::ReligiousMinority->value => 'Dini azınlık',
			self::Muslim->value => 'Müslüman',
			self::Jewish->value => 'Yahudi',
			self::Hindu->value => 'Hindu',
			self::Buddhist->value => 'Budist',
			self::Sikh->value => 'Sih',
			self::ChristianMinority->value => 'Hristiyan azınlık',
			self::Atheist->value => 'Ateist',
			self::Agnostic->value => 'Agnostik',
			self::Spiritual->value => 'Spiritüel',

			self::LowIncome->value => 'Düşük gelir',
			self::EconomicallyDisadvantaged->value => 'Ekonomik olarak dezavantajlı',
			self::FirstGeneration->value => 'İlk nesil',
			self::FirstGenerationProfessional->value => 'İlk nesil profesyonel',
			self::FirstGenerationCollege->value => 'İlk nesil üniversiteli',
			self::WorkingClass->value => 'İşçi sınıfı',
			self::UnderrepresentedBackground->value => 'Az temsil edilen arka plan',
			self::SocioeconomicallyDisadvantaged->value => 'Sosyoekonomik dezavantajlı',

			self::Veteran->value => 'Gazi',
			self::MilitaryFamily->value => 'Askeri aile',
			self::ActiveDuty->value => 'Aktif görev',
			self::Reservist->value => 'Yedek',
			self::MilitarySpouse->value => 'Asker eşi',
			self::GoldStarFamily->value => 'Gold Star ailesi',

			self::EnglishLanguageLearner->value => 'İngilizce öğrenen',
			self::ESL->value => 'İkinci dil olarak İngilizce',
			self::NonNativeSpeaker->value => 'Ana dili İngilizce olmayan',
			self::DifferentAbledLearner->value => 'Engelli öğrenci',
			self::AlternativeEducation->value => 'Alternatif eğitim geçmişi',

			self::SingleParent->value => 'Tek ebeveyn',
			self::Caregiver->value => 'Bakıcı',
			self::Parent->value => 'Ebeveyn',
			self::FosterYouth->value => 'Koruyucu aile genci',
			self::Adoptee->value => 'Evlat edinilmiş',
			self::Orphan->value => 'Yetim',

			self::Rural->value => 'Kırsal köken',
			self::Urban->value => 'Kentsel köken',
			self::Suburban->value => 'Banliyö köken',
			self::RemoteArea->value => 'Uzak bölge',
			self::UnderservedRegion->value => 'Yetersiz hizmet alan bölge',

			self::FormerlyIncarcerated->value => 'Eski hükümlü',
			self::JusticeInvolved->value => 'Adalet sistemiyle ilişkili',
			self::Homeless->value => 'Evsiz / eskiden evsiz',
			self::DomesticViolenceSurvivor->value => 'Aile içi şiddet mağduru (hayatta kalan)',
			self::HumanTraffickingSurvivor->value => 'İnsan ticareti mağduru (hayatta kalan)',
			self::AddictionRecovery->value => 'Bağımlılıktan iyileşme',

			self::WomenOfColor->value => 'Renkli kadınlar',
			self::DisabledWomen->value => 'Engelli kadınlar',
			self::LGBTQPlusYouth->value => 'LGBTQ+ gençler',
			self::IndigenousWomen->value => 'Yerel kadınlar',
			self::DisabledVeteran->value => 'Engelli gazi',

			self::DiverseBackground->value => 'Çeşitli arka plan',
			self::Underrepresented->value => 'Az temsil edilen grup',
			self::Marginalized->value => 'Marjinalleştirilmiş topluluk',
			self::Minority->value => 'Azınlık grubu',
			self::ProtectedClass->value => 'Korunan sınıf',
		];
	}

	// Chinese (Simplified) Labels
	public static function labelsZh(): array
	{
		return [
			self::Women->value => '女性',
			self::Men->value => '男性',
			self::NonBinary->value => '非二元性别',
			self::Transgender->value => '跨性别',
			self::Genderqueer->value => '酷儿性别',
			self::Genderfluid->value => '性别流动',
			self::Agender->value => '无性别',
			self::Intersex->value => '双性',
			self::LGBTQPlus->value => 'LGBTQ+',
			self::Lesbian->value => '女同性恋',
			self::Gay->value => '男同性恋',
			self::Bisexual->value => '双性恋',
			self::Queer->value => '酷儿',
			self::Asexual->value => '无性恋',
			self::Pansexual->value => '泛性恋',
			self::TwoSpirit->value => '双灵（Two-Spirit）',

			self::Disabled->value => '残障人士',
			self::PhysicallyDisabled->value => '肢体残障',
			self::VisuallyImpaired->value => '视力障碍',
			self::HearingImpaired->value => '听力障碍',
			self::Neurodiverse->value => '神经多样性',
			self::Autistic->value => '自闭谱系',
			self::ADHD->value => '注意缺陷多动障碍（ADHD）',
			self::Dyslexic->value => '阅读障碍（诵读困难）',
			self::MentalHealth->value => '心理健康',
			self::MobilityImpairment->value => '行动障碍',
			self::ChronicIllness->value => '慢性疾病',
			self::InvisibleDisability->value => '隐性残障',

			self::RacialMinority->value => '种族少数群体',
			self::EthnicMinority->value => '族裔少数群体',
			self::Black->value => '黑人／非洲裔',
			self::African->value => '非洲裔',
			self::AfricanAmerican->value => '非裔美国人',
			self::Asian->value => '亚裔',
			self::Hispanic->value => '西班牙裔',
			self::Latino->value => '拉丁裔',
			self::Indigenous->value => '原住民',
			self::NativeAmerican->value => '美洲原住民',
			self::PacificIslander->value => '太平洋岛民',
			self::MiddleEastern->value => '中东裔',
			self::Multiracial->value => '多种族',
			self::MixedRace->value => '混血',
			self::Quilombola->value => '基隆博拉（Quilombola）',
			self::ScheduledCaste->value => '列入种姓（SC）',
			self::ScheduledTribe->value => '列入部族（ST）',
			self::OBC->value => '其他落后阶层（OBC）',

			self::Youth->value => '青年',
			self::YoungAdult->value => '青年成人',
			self::Senior->value => '老年人',
			self::AgeDiverse->value => '年龄多样性',
			self::GenerationZ->value => 'Z世代',
			self::Millennial->value => '千禧一代',
			self::GenerationX->value => 'X世代',
			self::BabyBoomer->value => '婴儿潮一代',

			self::Immigrant->value => '移民',
			self::Refugee->value => '难民',
			self::AsylumSeeker->value => '寻求庇护者',
			self::ForeignNational->value => '外国国民',
			self::International->value => '国际人士',
			self::MigrantWorker->value => '移民工人',
			self::Diaspora->value => '侨民／离散社群',
			self::Stateless->value => '无国籍人士',

			self::ReligiousMinority->value => '宗教少数群体',
			self::Muslim->value => '穆斯林',
			self::Jewish->value => '犹太人',
			self::Hindu->value => '印度教徒',
			self::Buddhist->value => '佛教徒',
			self::Sikh->value => '锡克教徒',
			self::ChristianMinority->value => '基督教少数群体',
			self::Atheist->value => '无神论者',
			self::Agnostic->value => '不可知论者',
			self::Spiritual->value => '灵性／精神信仰',

			self::LowIncome->value => '低收入',
			self::EconomicallyDisadvantaged->value => '经济弱势',
			self::FirstGeneration->value => '第一代',
			self::FirstGenerationProfessional->value => '第一代职业人士',
			self::FirstGenerationCollege->value => '第一代大学生',
			self::WorkingClass->value => '工人阶级',
			self::UnderrepresentedBackground->value => '代表性不足背景',
			self::SocioeconomicallyDisadvantaged->value => '社会经济弱势',

			self::Veteran->value => '退伍军人',
			self::MilitaryFamily->value => '军人家庭',
			self::ActiveDuty->value => '现役',
			self::Reservist->value => '预备役',
			self::MilitarySpouse->value => '军人配偶',
			self::GoldStarFamily->value => '金星家庭（Gold Star）',

			self::EnglishLanguageLearner->value => '英语学习者',
			self::ESL->value => '英语作为第二语言（ESL）',
			self::NonNativeSpeaker->value => '非母语者',
			self::DifferentAbledLearner->value => '有障碍的学习者',
			self::AlternativeEducation->value => '替代教育背景',

			self::SingleParent->value => '单亲家长',
			self::Caregiver->value => '照护者',
			self::Parent->value => '家长',
			self::FosterYouth->value => '寄养青年',
			self::Adoptee->value => '被收养者',
			self::Orphan->value => '孤儿',

			self::Rural->value => '农村背景',
			self::Urban->value => '城市背景',
			self::Suburban->value => '郊区背景',
			self::RemoteArea->value => '偏远地区',
			self::UnderservedRegion->value => '服务不足地区',

			self::FormerlyIncarcerated->value => '曾被监禁者',
			self::JusticeInvolved->value => '涉司法系统人群',
			self::Homeless->value => '无家可归／曾无家可归',
			self::DomesticViolenceSurvivor->value => '家庭暴力幸存者',
			self::HumanTraffickingSurvivor->value => '人口贩运幸存者',
			self::AddictionRecovery->value => '成瘾康复',

			self::WomenOfColor->value => '有色人种女性',
			self::DisabledWomen->value => '残障女性',
			self::LGBTQPlusYouth->value => 'LGBTQ+ 青年',
			self::IndigenousWomen->value => '原住民女性',
			self::DisabledVeteran->value => '残障退伍军人',

			self::DiverseBackground->value => '多元背景',
			self::Underrepresented->value => '代表性不足群体',
			self::Marginalized->value => '边缘化群体',
			self::Minority->value => '少数群体',
			self::ProtectedClass->value => '受保护群体',
		];
	}
}
