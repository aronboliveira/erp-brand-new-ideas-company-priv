<?php

namespace Modules\LandingPage\Database\Seeders;

use Illuminate\Database\{Eloquent\Model, Seeder};
use Illuminate\Support\Facades\Log;
use Modules\LandingPage\{
    Config\Constants\SettingsConstants,
    Entities\LandingPageSetting
};

class LandingPageDataTableSeeder extends Seeder
{
    public function run(): void
    {
        $jsonDirs = module_path('LandingPage') . "/Config/blobs/";
        Model::unguard();
        // $this->call(cbin"OthersTableSeeder");
        $data[SettingsConstants::TB_STT_K] = SettingsConstants::TB_STT_DEF;
        $data[SettingsConstants::TB_NTF_MSG_K] = '70% Special Offer. Don’t Miss it. The offer ends in 72 hours.';
        $data[SettingsConstants::MB_STT_K] = SettingsConstants::MB_STT_DEF;
        $data[SettingsConstants::MB_PG_K] = 'Error loading menubar data';
        try {
            $menubarContent = file_get_contents($jsonDirs . 'menubar.json');
            if (!$menubarContent)
                throw new \RuntimeException('Failed to read menubar.json file');
            $data[SettingsConstants::MB_PG_K] = json_decode(
                $menubarContent,
                associative: true,
                flags: JSON_THROW_ON_ERROR
            );
        } catch (\JsonException $e) {
            Log::error('Invalid JSON in menubar.json', ['error' => $e->getMessage(), 'file' => 'menubar.json']);
            $data[SettingsConstants::MB_PG_K] = 'Invalid menubar data format';
        } catch (\RuntimeException $e) {
            Log::error('Error loading menubar data', ['error' => $e->getMessage(), 'file' => 'menubar.json']);
            $data[SettingsConstants::MB_PG_K] = 'Menubar file not found or unreadable';
        } catch (\Exception $e) {
            Log::error('Unexpected error loading menubar', ['error' => $e->getMessage(), 'file' => 'menubar.json']);
            $data[SettingsConstants::MB_PG_K] = 'Error loading menubar data';
        }
        $data[SettingsConstants::SL_K] = 'site_logo.png';
        $data[SettingsConstants::SD_K] = 'We build modern web tools to help you jump-start your daily business work.';
        $data[SettingsConstants::HM_STT_K] = SettingsConstants::HM_STT_DEF;
        $data[SettingsConstants::HM_OFF_TXT_K] = '70% Special Offer';
        $data[SettingsConstants::HM_TTL_K] = SettingsConstants::HM_TTL_DEF;
        $data[SettingsConstants::HM_HDG_K] = 'ERPNovaPrestech All In One Business ERP With Project, Account, HRM, CRM';
        $data[SettingsConstants::HM_DESC_K] = 'Use these awesome forms to login or create new account in your project for free.';
        $data[SettingsConstants::HM_TRST_BY_K] = '1000+ Customer';
        $data[SettingsConstants::HM_DEMO_LNK_K] = 'https://demo.rajodiya.com/erpgo-saas/login';
        $data[SettingsConstants::HM_BUY_LNK_K] = 'https://codecanyon.net/item/erpgo-saas-all-in-one-business-erp-with-project-account-hrm-crm-pos/33263426';
        $data[SettingsConstants::HM_BNR_K] = 'home_banner.png';
        $data[SettingsConstants::HM_LGO_K] = 'home_logo.png';
        $data[SettingsConstants::FT_STT_K] = SettingsConstants::FT_STT_DEF;
        $data[SettingsConstants::FT_TTL_K] = SettingsConstants::FT_TTL_DEF;
        $data[SettingsConstants::FT_HDG_K] = 'All In One Place CRM System';
        $data[SettingsConstants::FT_DESC_K] = 'Use these awesome forms to login or create new account in your project for free. Use these awesome forms to login or create new account in your project for free.';
        $data[SettingsConstants::FT_BUY_LNK_K] = 'https://codecanyon.net/item/erpgo-saas-all-in-one-business-erp-with-project-account-hrm-crm-pos/33263426';
        $data[SettingsConstants::FT_OF_FTS_K] = 'Error loading features data';
        try {
            $featuresContent = file_get_contents($jsonDirs . 'features.json');
            if (!$featuresContent)
                throw new \RuntimeException('Failed to read features.json file');
            $data[SettingsConstants::FT_OF_FTS_K] = json_decode(
                $featuresContent,
                associative: true,
                flags: JSON_THROW_ON_ERROR
            );
        } catch (\JsonException $e) {
            Log::error('Invalid JSON in features.json', ['error' => $e->getMessage(), 'file' => 'features.json']);
            $data[SettingsConstants::FT_OF_FTS_K] = 'Invalid features data format';
        } catch (\RuntimeException $e) {
            Log::error('Error loading features data', ['error' => $e->getMessage(), 'file' => 'features.json']);
            $data[SettingsConstants::FT_OF_FTS_K] = 'Features file not found or unreadable';
        } catch (\Exception $e) {
            Log::error('Unexpected error loading features', ['error' => $e->getMessage(), 'file' => 'features.json']);
            $data[SettingsConstants::FT_OF_FTS_K] = 'Error loading features data';
        }
        $data[SettingsConstants::HF_HDG_K] = 'ERPNovaPrestech All In One Business ERP With Project, Account, HRM, CRM';
        $data[SettingsConstants::HF_DESC_K] = 'Use these awesome forms to login or create new account in your project for free.';
        $data[SettingsConstants::HF_IMG_K] = 'highlight_feature_image.png';
        $data[SettingsConstants::DC_OF_FTS_K] = 'Error loading discover features data';
        $data[SettingsConstants::OT_FTS_K] = 'Error loading other features data';
        try {
            $otherFeaturesContent = file_get_contents($jsonDirs . 'other_features.json');
            if (!$otherFeaturesContent)
                throw new \RuntimeException('Failed to read other_features.json file');
            $data[SettingsConstants::OT_FTS_K] = json_decode(
                $otherFeaturesContent,
                associative: true,
                flags: JSON_THROW_ON_ERROR
            );
        } catch (\JsonException $e) {
            Log::error('Invalid JSON in other_features.json', ['error' => $e->getMessage(), 'file' => 'other_features.json']);
            $data[SettingsConstants::OT_FTS_K] = 'Invalid other features data format';
        } catch (\RuntimeException $e) {
            Log::error('Error loading other features data', ['error' => $e->getMessage(), 'file' => 'other_features.json']);
            $data[SettingsConstants::OT_FTS_K] = 'Other features file not found or unreadable';
        } catch (\Exception $e) {
            Log::error('Unexpected error loading other features', ['error' => $e->getMessage(), 'file' => 'other_features.json']);
            $data[SettingsConstants::OT_FTS_K] = 'Error loading other features data';
        }
        $data[SettingsConstants::DC_STT_K] = SettingsConstants::DC_STT_DEF;
        $data[SettingsConstants::DC_HDG_K] = 'ERPNovaPrestech All In One Business ERP With Project, Account, HRM, CRM';
        $data[SettingsConstants::DC_DESC_K] = 'Use these awesome forms to login or create new account in your project for free.';
        $data[SettingsConstants::DC_DEMO_LNK_K] = 'https://demo.rajodiya.com/erpgo-saas/login';
        $data[SettingsConstants::DC_BUY_LNK_K] = 'https://codecanyon.net/item/erpgo-saas-all-in-one-business-erp-with-project-account-hrm-crm-pos/33263426';
        $data[SettingsConstants::SC_SHTS_K] = 'Error loading discover data';
        try {
            $discoverContent = file_get_contents($jsonDirs . 'discover.json');
            if (!$discoverContent)
                throw new \RuntimeException('Failed to read discover.json file');
            $data[SettingsConstants::SC_SHTS_K] = json_decode(
                $discoverContent,
                associative: true,
                flags: JSON_THROW_ON_ERROR
            );
        } catch (\JsonException $e) {
            Log::error('Invalid JSON in discover.json', ['error' => $e->getMessage(), 'file' => 'discover.json', 'constant' => 'SC_SHTS_K']);
            $data[SettingsConstants::SC_SHTS_K] = 'Invalid discover data format';
        } catch (\RuntimeException $e) {
            Log::error('Error loading discover data (SC_SHTS_K)', ['error' => $e->getMessage(), 'file' => 'discover.json', 'constant' => 'SC_SHTS_K']);
            $data[SettingsConstants::SC_SHTS_K] = 'Discover file not found or unreadable';
        } catch (\Exception $e) {
            Log::error('Unexpected error loading discover data', ['error' => $e->getMessage(), 'file' => 'discover.json', 'constant' => 'SC_SHTS_K']);
            $data[SettingsConstants::SC_SHTS_K] = 'Error loading discover data';
        }
        $data[SettingsConstants::SC_STT_K] = SettingsConstants::SC_STT_DEF;
        $data[SettingsConstants::SC_HDG_K] = 'ERPNovaPrestech All In One Business ERP With Project, Account, HRM, CRM';
        $data[SettingsConstants::SC_DESC_K] = 'Use these awesome forms to login or create new account in your project for free.';
        $data[SettingsConstants::SC_SHTS_K] = 'Error loading discover data';
        try {
            $screenshotsContent = file_get_contents($jsonDirs . 'screenshots.json');
            if (!$screenshotsContent)
                throw new \RuntimeException('Failed to read screenshots.json file');
            $data[SettingsConstants::SC_SHTS_K] = json_decode(
                $screenshotsContent,
                associative: true,
                flags: JSON_THROW_ON_ERROR
            );
        } catch (\JsonException $e) {
            Log::error('Invalid JSON in screenshots.json', ['error' => $e->getMessage(), 'file' => 'screenshots.json', 'constant' => 'SC_SHTS_K']);
            $data[SettingsConstants::SC_SHTS_K] = 'Invalid screenshots data format';
        } catch (\RuntimeException $e) {
            Log::error('Error loading screenshots data (SC_SHTS_K)', ['error' => $e->getMessage(), 'file' => 'screenshots.json', 'constant' => 'SC_SHTS_K']);
            $data[SettingsConstants::SC_SHTS_K] = 'screenshots file not found or unreadable';
        } catch (\Exception $e) {
            Log::error('Unexpected error loading screenshots data', ['error' => $e->getMessage(), 'file' => 'screenshots.json', 'constant' => 'SC_SHTS_K']);
            $data[SettingsConstants::SC_SHTS_K] = 'Error loading screenshots data';
        }
        $data[SettingsConstants::PN_STT_K] = SettingsConstants::PN_STT_DEF;
        $data[SettingsConstants::PN_TTL_K] = SettingsConstants::PN_TTL_DEF;
        $data[SettingsConstants::PN_HDG_K] = 'ERPNovaPrestech All In One Business ERP With Project, Account, HRM, CRM';
        $data[SettingsConstants::PN_DESC_K] = 'Use these awesome forms to login or create new account in your project for free.';
        $data[SettingsConstants::FAQ_STT_K] = SettingsConstants::FAQ_STT_DEF;
        $data[SettingsConstants::FAQ_TTL_K] = SettingsConstants::FAQ_TTL_DEF;
        $data[SettingsConstants::FAQ_HDG_K] = 'ERPNovaPrestech All In One Business ERP With Project, Account, HRM, CRM';
        $data[SettingsConstants::FAQ_DESC_K] = 'Use these awesome forms to login or create new account in your project for free.';
        $data[SettingsConstants::FAQ_FQS_K] = 'Error loading FAQ data';
        try {
            $faqContent = file_get_contents($jsonDirs . 'faq.json');
            if (!$faqContent)
                throw new \RuntimeException('Failed to read faq.json file');
            $data[SettingsConstants::FAQ_FQS_K] = json_decode(
                $faqContent,
                associative: true,
                flags: JSON_THROW_ON_ERROR
            );
        } catch (\JsonException $e) {
            Log::error('Invalid JSON in faq.json', ['error' => $e->getMessage(), 'file' => 'faq.json']);
            $data[SettingsConstants::FAQ_FQS_K] = 'Invalid FAQ data format';
        } catch (\RuntimeException $e) {
            Log::error('Error loading FAQ data', ['error' => $e->getMessage(), 'file' => 'faq.json']);
            $data[SettingsConstants::FAQ_FQS_K] = 'FAQ file not found or unreadable';
        } catch (\Exception $e) {
            Log::error('Unexpected error loading FAQ', ['error' => $e->getMessage(), 'file' => 'faq.json']);
            $data[SettingsConstants::FAQ_FQS_K] = 'Error loading FAQ data';
        }
        $data[SettingsConstants::TM_STT_K] = SettingsConstants::TM_STT_DEF;
        $data[SettingsConstants::TM_HDG_K] = 'From our Clients';
        $data[SettingsConstants::TM_DESC_K] = 'Use these awesome forms to login or create new account in your project for free.';
        $data[SettingsConstants::TM_LONG_DESC_K] = 'WorkDo seCommerce package offers you a “sales-ready.”secure online store. The package puts all the key pieces together, from design to payment processing. This gives you a headstart in your eCommerce venture. Every store is built using a reliable PHP framework -laravel. Thisspeeds up the development process while increasing the store’s security and performance.Additionally, thanks to the accompanying mobile app, you and your team can manage the store on the go. What’s more, because the app works both for you and your customers, you can use it to reach a wider audience.And, unlike popular eCommerce platforms, it doesn’t bind you to any terms and conditions or recurring fees. You get to choose where you host it or which payment gateway you use. Lastly, you getcomplete control over the looks of the store. And if it lacks any functionalities that you need, just reach out, and let’s discuss customization possibilities';
        $data[SettingsConstants::TM_TMS_K] = 'Error loading testimonials data';
        try {
            $testimonialContent = file_get_contents($jsonDirs . 'testimonials.json');
            if (!$testimonialContent)
                throw new \RuntimeException('Failed to read testimonials.json file');
            $data[SettingsConstants::TM_TMS_K] = json_decode(
                $testimonialContent,
                associative: true,
                flags: JSON_THROW_ON_ERROR
            );
        } catch (\JsonException $e) {
            Log::error('Invalid JSON in testimonials.json', ['error' => $e->getMessage(), 'file' => 'testimonials.json']);
            $data[SettingsConstants::TM_TMS_K] = 'Invalid testimonials data format';
        } catch (\RuntimeException $e) {
            Log::error('Error loading testimonials data', ['error' => $e->getMessage(), 'file' => 'testimonials.json']);
            $data[SettingsConstants::TM_TMS_K] = 'Testimonials file not found or unreadable';
        } catch (\Exception $e) {
            Log::error('Unexpected error loading testimonials', ['error' => $e->getMessage(), 'file' => 'testimonials.json']);
            $data[SettingsConstants::TM_TMS_K] = 'Error loading testimonials data';
        }
        $data[SettingsConstants::FTR_STT_K] = SettingsConstants::FTR_STT_DEF;
        $data[SettingsConstants::JU_STT_K] = SettingsConstants::JU_STT_DEF;
        $data[SettingsConstants::JU_HDG_K] = 'Join Our Community';
        $data[SettingsConstants::JU_DESC_K] = 'We build modern web tools to help you jump-start your daily business work.';
        foreach ($data as $key => $value) {
            try {
                $bindValue = is_array($value)
                    ? json_encode($value, JSON_THROW_ON_ERROR)
                    : $value;
                !LandingPageSetting::where('name', '=', $key)->exists() &&
                    LandingPageSetting::updateOrCreate(['name' =>  $key], ['value' => $bindValue]);
            } catch (\Throwable $e) {
                Log::error("Failed to set {$key} for " . LandingPageSetting::class, [
                    "error" => $e->getMessage(),
                ]);
            }
        }
    }
}
