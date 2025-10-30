<?php

namespace Modules\LandingPage\Database\Seeders;

use Illuminate\Database\{Eloquent\Model, Seeder};
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\LandingPage\{
    Config\Constants\SettingsConstants as LPC,
    Entities\LandingPageSetting
};

class LandingPageDataTableSeeder extends Seeder
{
    public function run(): void
    {
        $jsonDirs = module_path('LandingPage') . "/Config/blobs/";
        Model::unguard();
        $data = [
            // Top Bar & Menu Bar
            LPC::TB_STT_K => LPC::TB_STT_DEF,
            LPC::TB_NTF_MSG_K => '70% Special Offer. Don\'t Miss it. The offer ends in 72 hours.',
            LPC::MB_STT_K => LPC::MB_STT_DEF,

            // Site Logo & Description
            LPC::SL_K => 'site_logo.png',
            LPC::SD_K => 'We build modern web tools to help you jump-start your daily business work.',

            // Home Section
            LPC::HM_STT_K => LPC::HM_STT_DEF,
            LPC::HM_OFF_TXT_K => '70% Special Offer',
            LPC::HM_TTL_K => LPC::HM_TTL_DEF,
            LPC::HM_HDG_K => 'ERPNovaPrestech All In One Business ERP With Project, Account, HRM, CRM',
            LPC::HM_DESC_K => 'Use these awesome forms to login or create new account in your project for free.',
            LPC::HM_TRST_BY_K => '1000+ Customer',
            LPC::HM_DEMO_LNK_K => 'https://demo.rajodiya.com/erpgo-saas/login',
            LPC::HM_BUY_LNK_K => 'https://codecanyon.net/item/erpgo-saas-all-in-one-business-erp-with-project-account-hrm-crm-pos/33263426',
            LPC::HM_BNR_K => 'home_banner.png',
            LPC::HM_LGO_K => 'home_logo.png',

            // Features Section
            LPC::FT_STT_K => LPC::FT_STT_DEF,
            LPC::FT_TTL_K => LPC::FT_TTL_DEF,
            LPC::FT_HDG_K => 'All In One Place CRM System',
            LPC::FT_DESC_K => 'Use these awesome forms to login or create new account in your project for free. Use these awesome forms to login or create new account in your project for free.',
            LPC::FT_BUY_LNK_K => 'https://codecanyon.net/item/erpgo-saas-all-in-one-business-erp-with-project-account-hrm-crm-pos/33263426',
            LPC::FT_OF_FTS_K => 'Error loading features data',

            // Highlight Feature
            LPC::HF_HDG_K => 'ERPNovaPrestech All In One Business ERP With Project, Account, HRM, CRM',
            LPC::HF_DESC_K => 'Use these awesome forms to login or create new account in your project for free.',
            LPC::HF_IMG_K => 'highlight_feature_image.png',
            LPC::DC_OF_FTS_K => 'Error loading discover features data',
            LPC::OT_FTS_K => 'Error loading other features data',

            // Discover Section
            LPC::DC_STT_K => LPC::DC_STT_DEF,
            LPC::DC_HDG_K => 'ERPNovaPrestech All In One Business ERP With Project, Account, HRM, CRM',
            LPC::DC_DESC_K => 'Use these awesome forms to login or create new account in your project for free.',
            LPC::DC_DEMO_LNK_K => 'https://demo.rajodiya.com/erpgo-saas/login',
            LPC::DC_BUY_LNK_K => 'https://codecanyon.net/item/erpgo-saas-all-in-one-business-erp-with-project-account-hrm-crm-pos/33263426',

            // Screenshots Section
            LPC::SC_STT_K => LPC::SC_STT_DEF,
            LPC::SC_HDG_K => 'ERPNovaPrestech All In One Business ERP With Project, Account, HRM, CRM',
            LPC::SC_DESC_K => 'Use these awesome forms to login or create new account in your project for free.',
            LPC::SC_SHTS_K => 'Error loading discover data',

            // Pricing & FAQ Section
            LPC::PN_STT_K => LPC::PN_STT_DEF,
            LPC::PN_TTL_K => LPC::PN_TTL_DEF,
            LPC::PN_HDG_K => 'ERPNovaPrestech All In One Business ERP With Project, Account, HRM, CRM',
            LPC::PN_DESC_K => 'Use these awesome forms to login or create new account in your project for free.',
            LPC::FAQ_STT_K => LPC::FAQ_STT_DEF,
            LPC::FAQ_TTL_K => LPC::FAQ_TTL_DEF,
            LPC::FAQ_HDG_K => 'ERPNovaPrestech All In One Business ERP With Project, Account, HRM, CRM',
            LPC::FAQ_DESC_K => 'Use these awesome forms to login or create new account in your project for free.',
            LPC::FAQ_FQS_K => 'Error loading FAQ data',

            // Testimonials Section
            LPC::TM_STT_K => LPC::TM_STT_DEF,
            LPC::TM_HDG_K => 'From our Clients',
            LPC::TM_DESC_K => 'Use these awesome forms to login or create new account in your project for free.',
            LPC::TM_LONG_DESC_K => 'WorkDo seCommerce package offers you a "sales-ready."secure online store. The package puts all the key pieces together, from design to payment processing. This gives you a headstart in your eCommerce venture. Every store is built using a reliable PHP framework -laravel. Thisspeeds up the development process while increasing the store\'s security and performance.Additionally, thanks to the accompanying mobile app, you and your team can manage the store on the go. What\'s more, because the app works both for you and your customers, you can use it to reach a wider audience.And, unlike popular eCommerce platforms, it doesn\'t bind you to any terms and conditions or recurring fees. You get to choose where you host it or which payment gateway you use. Lastly, you getcomplete control over the looks of the store. And if it lacks any functionalities that you need, just reach out, and let\'s discuss customization possibilities',
            LPC::TM_TMS_K => 'Error loading testimonials data',

            // Footer & Join Us Section
            LPC::FTR_STT_K => LPC::FTR_STT_DEF,
            LPC::JU_STT_K => LPC::JU_STT_DEF,
            LPC::JU_HDG_K => 'Join Our Community',
            LPC::JU_DESC_K => 'We build modern web tools to help you jump-start your daily business work.',
        ];
        $jsonFiles = [
            [
                'file' => 'menubar.json',
                'key' => LPC::MB_PG_K,
                'name' => 'menubar',
                'uuid' => true
            ],
            [
                'file' => 'features.json',
                'key' => LPC::FT_OF_FTS_K,
                'name' => 'features',
                'uuid' => true
            ],
            [
                'file' => 'other_features.json',
                'key' => LPC::OT_FTS_K,
                'name' => 'other features',
                'uuid' => true
            ],
            [
                'file' => 'discover.json',
                'key' => 'discovers',
                'name' => 'discover',
                'uuid' => true
            ],
            [
                'file' => 'screenshots.json',
                'key' => LPC::SC_SHTS_K,
                'name' => 'screenshots',
                'uuid' => true
            ],
            [
                'file' => 'faq.json',
                'key' => LPC::FAQ_FQS_K,
                'name' => 'FAQ',
                'uuid' => true
            ],
            [
                'file' => 'testimonials.json',
                'key' => LPC::TM_TMS_K,
                'name' => 'testimonials',
                'uuid' => true
            ]
        ];
        foreach ($jsonFiles as $config) {
            try {
                $content = file_get_contents($jsonDirs . $config['file']);
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
                                'value' => json_encode($item, JSON_THROW_ON_ERROR)
                            ]
                        );
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
}
