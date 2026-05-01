<?php

namespace Modules\LandingPage\Database\Seeders;

use App\Config\Constants\DatabaseConstants as DC;
use Illuminate\Database\{Eloquent\Model, Seeder};
use Illuminate\Support\Facades\Log;
use Illuminate\Support\{Collection, Str};
use Modules\LandingPage\{
    Config\Constants\RoutesResourcesConstants as RRC,
    Config\Constants\SettingsConstants as LPSC,
    Entities\LandingPageSetting
};
use Modules\LandingPage\Entities\JoinUs;

class LandingPageDataTableSeeder extends Seeder
{
    public const DEFAULT_DATA_ARR = [
        LPSC::TB_STT_K     => LPSC::TB_STT_DEF,
        LPSC::TB_NTF_MSG_K => 'Technology assistance and support with over 30 years of tradition. Talk to Brand New Ideas Company and protect your data and devices today.',
        LPSC::MB_STT_K     => LPSC::MB_STT_DEF,
        LPSC::SL_K => 'site_logo.png',
        LPSC::SD_K => 'Technology assistance and support focusing on digital security, stability, and humanized service for businesses and end users.',
        LPSC::HM_STT_K      => LPSC::HM_STT_DEF,
        LPSC::HM_OFF_TXT_K  => 'Assisted protection in every solution',
        LPSC::HM_TTL_K      => LPSC::HM_TTL_DEF,
        LPSC::HM_HDG_K      => 'Technology assistance and support with over 30 years of tradition',
        LPSC::HM_DESC_K     => 'We combine Support, DevOps, and Web Development teams to deliver security, infrastructure, and cloud service solutions that drive your business.',
        LPSC::HM_TRST_BY_K  => '10,000+ success stories and equipment serviced',
        LPSC::HM_DEMO_LNK_K => 'https://brandnewideascompany.com/services/',
        LPSC::HM_BUY_LNK_K  => 'https://brandnewideascompany.com/site/contact/',
        LPSC::HM_BNR_K      => 'home_banner.png',
        LPSC::HM_LGO_K      => 'home_logo.png',
        LPSC::FT_STT_K      => LPSC::FT_STT_DEF,
        LPSC::FT_TTL_K      => LPSC::FT_TTL_DEF,
        LPSC::FT_HDG_K      => 'Why choose Brand New Ideas Company assistance?',
        LPSC::FT_DESC_K     => 'User-centered services, stability and experience, humanized service, and security as a priority in all layers of the solutions.',
        LPSC::FT_BUY_LNK_K  => 'https://brandnewideascompany.com/site/contact/',
        LPSC::FT_OF_FTS_K   => 'Unable to load the service list at the moment. Please try again later.',
        LPSC::HF_HDG_K      => 'Assisted protection in every solution!',
        LPSC::HF_DESC_K     => 'Complete infrastructure solutions, data security, backup, virtualization, and mature IT support for businesses that require high availability.',
        LPSC::HF_IMG_K      => 'highlight_feature_image.png',
        LPSC::DC_OF_FTS_K   => 'Unable to load the details of the technologies.',
        LPSC::OT_FTS_K      => 'Unable to load other services.',
        LPSC::DC_STT_K      => LPSC::DC_STT_DEF,
        LPSC::DC_HDG_K      => 'IT technical support services for your business',
        LPSC::DC_DESC_K     => 'Cybersecurity management, solution development, data protection and persistence, technology product quotes, operating system preparation, and mature IT support.',
        LPSC::DC_DEMO_LNK_K => 'https://brandnewideascompany.com/services/',
        LPSC::DC_BUY_LNK_K  => 'https://brandnewideascompany.com/site/contact/',
        LPSC::SC_STT_K   => LPSC::SC_STT_DEF,
        LPSC::SC_HDG_K   => 'Technologies and platforms we serve',
        LPSC::SC_DESC_K  => 'Windows, Linux, Office 365, Fortinet, Panda Security, Nextcloud, Azure, VMware, Zimbra, WordPress, Next.js, Oracle Linux, and other strategic technologies for your business.',
        LPSC::SC_SHTS_K  => 'Unable to load the technology gallery.',
        LPSC::PN_STT_K   => LPSC::PN_STT_DEF,
        LPSC::PN_TTL_K   => LPSC::PN_TTL_DEF,
        LPSC::PN_HDG_K   => 'Service plans and technology support contracts',
        LPSC::PN_DESC_K  => 'Contact us to build a custom plan for technical assistance, monitoring, infrastructure projects, and managed services.',
        LPSC::FAQ_STT_K  => LPSC::FAQ_STT_DEF,
        LPSC::FAQ_TTL_K  => LPSC::FAQ_TTL_DEF,
        LPSC::FAQ_HDG_K  => 'Frequently asked questions about technology assistance and support',
        LPSC::FAQ_DESC_K => 'We have gathered answers about service, support contracts, scheduling of remote and on-site services, SLA, and information security.',
        LPSC::FAQ_FQS_K  => 'Unable to load the frequently asked questions.',
        LPSC::TM_STT_K       => LPSC::TM_STT_DEF,
        LPSC::TM_HDG_K       => 'Success stories with Brand New Ideas Company',
        LPSC::TM_DESC_K      => 'Testimonials from clients who transformed their IT infrastructure, security, and availability with our hardware, virtualization, and firewall solutions.',
        LPSC::TM_LONG_DESC_K => 'Our complete infrastructure solutions, with physical servers, virtual machines, network storage, and next-generation firewalls, deliver secure, scalable, and stable environments for businesses of different sizes.',
        LPSC::TM_TMS_K       => 'Unable to load the testimonials.',
        LPSC::FTR_STT_K  => LPSC::FTR_STT_DEF,
        LPSC::JU_STT_K   => LPSC::JU_STT_DEF,
        LPSC::JU_HDG_K   => 'Talk to Brand New Ideas Company',
        LPSC::JU_DESC_K  => 'We are ready to support your business with technical assistance, infrastructure projects, cybersecurity, and solution development. Contact us and let us understand your needs.',
        'email'         => 'comercial@brandnewideascompany.com',
    ];
    public function run(): void
    {
        $jsonDirs = module_path('LandingPage') . "/Config/blobs/";
        Model::unguard();
        $lang = app()->getLocale() ?? DC::DEFAULT_LANG;
        $data = $this->getLandingPageData();
        $jsonFiles = [
            [
                'file' => 'menubar',
                'key' => LPSC::MB_PG_K,
                'name' => 'menubar',
                'uuid' => true
            ],
            [
                'file' => 'features',
                'key' => LPSC::FT_OF_FTS_K,
                'name' => 'features',
                'uuid' => true
            ],
            [
                'file' => 'other_features',
                'key' => LPSC::OT_FTS_K,
                'name' => 'other features',
                'uuid' => true
            ],
            [
                'file' => 'discover',
                'key' => LPSC::DC_OF_FTS_K,
                'name' => RRC::DV,
                'uuid' => true
            ],
            [
                'file' => 'screenshots',
                'key' => LPSC::SC_SHTS_K,
                'name' => RRC::SST,
                'uuid' => true
            ],
            [
                'file' => 'faq',
                'key' => LPSC::FAQ_FQS_K,
                'name' => 'FAQ',
                'uuid' => true
            ],
            [
                'file' => 'testimonials',
                'key' => LPSC::TM_TMS_K,
                'name' => 'testimonials',
                'uuid' => true
            ],
            [
                'file' => 'join_us',
                'key' => 'email',
                'name' => RRC::JU,
                'uuid' => true
            ]
        ];
        foreach ($jsonFiles as $config) {
            try {
                if (!file_exists($jsonDirs . $config['file'] . "/{$lang}.json") || empty(file_get_contents($jsonDirs . $config['file'] . "/{$lang}.json"))) {
                    if (!file_exists($jsonDirs . $config['file'] . "/en.json") || empty(file_get_contents($jsonDirs . $config['file'] . "/en.json"))) {
                        if (!file_exists($jsonDirs . $config['file'] . ".json") || empty(file_get_contents($jsonDirs . $config['file'] . ".json")))
                            throw new \RuntimeException("{$config['file']} file for locale {$lang} not found");
                        else
                            $content = file_get_contents($jsonDirs . $config['file'] . ".json");
                    } else $content = file_get_contents($jsonDirs . $config['file'] . "/en.json");
                } else $content = file_get_contents($jsonDirs . $config['file'] . "/{$lang}.json");
                if (!$content)
                    throw new \RuntimeException("Failed to read {$config['file']} file");
                $rawData = json_decode($content, associative: true, flags: JSON_THROW_ON_ERROR);
                if ($config['uuid']) {
                    LandingPageSetting::where('name', $config['key'])
                        ->whereNull('query_key')
                        ->delete();
                    foreach ($rawData as $item) {
                        $startTime = microtime(true);
                        do $itemKey = Str::uuid()->toString();
                        while (LandingPageSetting::where('query_key', $itemKey)->exists() && (microtime(true) - $startTime) < 20);
                        LandingPageSetting::updateOrCreate(
                            ['query_key' => $itemKey],
                            [
                                'name' => $config['key'],
                                'created_by' => DC::DEFAULT_UUID,
                                'value' => json_encode($item, JSON_THROW_ON_ERROR)
                            ]
                        );
                        $rest = is_array($item) ? collect($item)->except('email')->toArray() : ($item instanceof Collection ? $item->except('email')->toArray() : []);
                        $config['key'] === 'email' && JoinUs::create([
                            'query_key' => $itemKey,
                            'created_by' => DC::DEFAULT_UUID,
                            'email' => $item['email'],
                            ...$rest,
                        ]);
                    }
                } else $data[$config['key']] = $rawData;
            } catch (\JsonException $e) {
                Log::error("Invalid JSON in {$config['file']}", [
                    'error' => $e->getMessage(),
                    'file' => $config['file'],
                    'constant' => $config['key']
                ]);
                $data[$config['key']] = "Invalid {$config['name']} data format";
            } catch (\RuntimeException $e) {
                Log::error("Error loading {$config['name']} data", [
                    'error' => $e->getMessage(),
                    'file' => $config['file'],
                    'constant' => $config['key']
                ]);
                $data[$config['key']] = "{$config['name']} file not found or unreadable";
            } catch (\Exception $e) {
                Log::error("Unexpected error loading {$config['name']}", [
                    'error' => $e->getMessage(),
                    'file' => $config['file'],
                    'constant' => $config['key']
                ]);
                $data[$config['key']] = "Error loading {$config['name']} data";
            }
        }
        foreach ($data as $key => $value) {
            try {
                $bindValue = is_array($value)
                    ? json_encode($value, JSON_THROW_ON_ERROR)
                    : $value;
                !LandingPageSetting::where('name', '=', $key)->exists() &&
                    LandingPageSetting::updateOrCreate(
                        ['name' => $key],
                        [
                            'value' => $bindValue,
                            'query_key' => Str::uuid()->toString()
                        ]
                    );
            } catch (\Throwable $e) {
                Log::error("Failed to set {$key} for " . LandingPageSetting::class, [
                    "error" => $e->getMessage(),
                ]);
            }
        }
    }
    private function getLandingPageData(): array
    {
        static $cache = [];
        $locale = app()->getLocale() ?? DC::DEFAULT_LANG;
        if (isset($cache[$locale])) return $cache[$locale];
        try {
            $data = match ($locale) {
                'en' => self::DEFAULT_DATA_ARR,
                'zh', 'pt-br', 'fr', 'es', 'da', 'he', 'ja', 'pt', 'ru', 'pl',
                'de', 'it', 'ar', 'tr', 'nl' => function () use ($locale) {
                    $jsonPath = module_path('LandingPage') . '/Config/blobs/landing/' . $locale . '.json';
                    if (!file_exists($jsonPath))
                        $jsonPath = public_path("assets/json/landing_page/{$locale}.json");
                    if (!file_exists($jsonPath))
                        throw new \Exception("Language file for {$locale} not found");
                    $jsonContent = file_get_contents($jsonPath);
                    $decodedData = json_decode($jsonContent, true, 512, JSON_THROW_ON_ERROR);
                    if (!is_array($decodedData))
                        throw new \Exception("Invalid JSON structure for {$locale}");
                    return $decodedData;
                },
                default => throw new \Exception("Unsupported locale: {$locale}"),
            };
            if ($data instanceof \Closure)
                $data = $data();
            $cache[$locale] = $data;
            return $data;
        } catch (\Exception $e) {
            Log::error("Failed to load landing page data for locale: {$locale}", [
                'error' => $e->getMessage(),
                'locale' => $locale
            ]);
            $englishData = $this->getLandingPageDataForLocale('en');
            $cache[$locale] = $englishData;
            return $englishData;
        }
    }
    private function getLandingPageDataForLocale(string $locale): array
    {
        return match ($locale) {
            'en' => self::DEFAULT_DATA_ARR,
            default => self::DEFAULT_DATA_ARR,
        };
    }
}
