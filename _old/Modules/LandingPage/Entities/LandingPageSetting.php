<?php

namespace Modules\LandingPage\Entities;

use App\Models\Utility;
use Illuminate\Support\Facades\{Log, Storage, Validator};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LandingPageSetting extends Model
{
    use HasFactory;

    protected $table = 'landing_page_settings';
    private static $settings = NULL;
    private const FS_DSK = 'filesystems.disks.';
    private const LC = 'local';
    private const WSB = 'wasabi';
    private const S3 = 's3';
    private const FS_WSB = self::FS_DSK . self::WSB;
    private const FS_S3  = self::FS_DSK . self::S3;
    private const STG_ST = 'storage_setting';
    private const MAX_UP = 'max_upload_size';
    private const STG_VD = 'storage_validation';

    protected $fillable = [
        'name',
        'value'
    ];

    protected static function newFactory()
    {
        return \Modules\LandingPage\Database\factories\LandingPageSettingFactory::new();
    }

    public static function settings(): array
    {
        try {
            $data = LandingPageSetting::get();
            $settings = [
                "topbar_status" => "on",
                "topbar_notification_msg" => "70% Special Offer. Don’t Miss it. The offer ends in 72 hours.",

                "menubar_status" => "on",
                "menubar_page" => '',
                "site_logo" => '',
                "site_description" => '',

                "home_status" => "on",
                "home_offer_text" => "",
                "home_title" => "Home",
                "home_heading" => "",
                "home_description" => "",
                "home_trusted_by" => "",
                "home_live_demo_link" => "",
                "home_buy_now_link" => "",
                "home_banner" => "",
                "home_logo" => "",

                "feature_status" => "on",
                "feature_title" => "Features",
                "feature_heading" => "",
                "feature_description" => "",
                "feature_buy_now_link" => "",

                "feature_of_features" => "",

                // "feature_banner_heading"=>"",
                // "feature_banner_description"=>"",
                // "feature_banner"=>"",

                "highlight_feature_heading" => "",
                "highlight_feature_description" => "",
                "highlight_feature_image" => "",

                "other_features" => "",

                "discover_status" => "on",
                "discover_heading" => "",
                "discover_description" => "",
                "discover_live_demo_link" => "",
                "discover_buy_now_link" => "",

                "discover_of_features" => "",

                "screenshots_status" => "on",
                "screenshots_heading" => "",
                "screenshots_description" => "",

                "screenshots" => "",

                "plan_status" => "on",
                "plan_title" => "Plan",
                "plan_heading" => "",
                "plan_description" => "",

                "faq_status" => "on",
                "faq_title" => "Faq",
                "faq_heading" => "",
                "faq_description" => "",
                "faqs" => "",

                "testimonials_status" => "on",
                "testimonials_heading" => "",
                "testimonials_description" => "",
                "testimonials_long_description" => "",
                "testimonials" => "",

                "footer_status" => "on",

                "joinus_status" => "on",
                "joinus_heading" => "",
                "joinus_description" => "",




            ];
            foreach ($data as $row) {
                try {
                    if (!isset($row->name)) {
                        Log::debug(__METHOD__ . " failed adding new setting", ['name' => $row->name, 'value' => $row->value]);
                        continue;
                    }
                    $settings[$row->name] = $row->value;
                } catch (\Throwable $e) {
                    Log::error("Error processing setting", ['name' => $row->name ?? 'undefined', 'error' => $e->getMessage()]);
                }
            }
            return $settings;
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . " failed fetching settings", ['error' => $e->getMessage()]);
            return [];
        }
    }


    public static function upload_file($request, $key_name, $name, $path, $custom_validation = [])
    {
        try {
            $settings = Utility::getStorageSetting();
            if (!empty($settings[self::STG_ST])) {
                if ($settings[self::STG_ST] == self::WSB) {
                    config(
                        [
                            self::FS_WSB . '.key' => $settings[self::WSB . '_key'],
                            self::FS_WSB . '.secret' => $settings[self::WSB . '_secret'],
                            self::FS_WSB . '.region' => $settings[self::WSB . '_region'],
                            self::FS_WSB . '.bucket' => $settings[self::WSB . '_bucket'],
                            self::FS_WSB . '.endpoint' => 'https://s3.' . $settings[self::WSB . '_region'] . '.wasabisys.com'
                        ]
                    );
                    $max_size = !empty($settings[self::WSB . '_' . self::MAX_UP]) ? $settings[self::WSB . '_' . self::MAX_UP] : '2048';
                    $mimes =  !empty($settings[self::WSB . '_' . self::STG_VD]) ? $settings[self::WSB . '_' . self::STG_VD] : '';
                } else if ($settings[self::STG_ST] == self::S3) {
                    config(
                        [
                            self::FS_S3 . '.key' => $settings[self::S3 . '_key'],
                            self::FS_S3 . '.secret' => $settings[self::S3 . '_secret'],
                            self::FS_S3 . '.region' => $settings[self::S3 . '_region'],
                            self::FS_S3 . '.bucket' => $settings[self::S3 . '_bucket'],
                            self::FS_S3 . '.use_path_style_endpoint' => false,
                        ]
                    );
                    $max_size = !empty($settings[self::S3 . '_' . self::MAX_UP]) ? $settings[self::S3 . '_' . self::MAX_UP] : '2048';
                    $mimes =  !empty($settings[self::S3 . '_' . self::STG_VD]) ? $settings[self::S3 . '_' . self::STG_VD] : '';
                } else {
                    $max_size = !empty($settings[self::LC . '_storage_' . self::MAX_UP]) ? $settings[self::LC . '_storage_' . self::MAX_UP] : '20480000000';
                    $mimes =  !empty($settings[self::LC . '_' . self::STG_VD]) ? $settings[self::LC . '_' . self::STG_VD] : '';
                }
                $file = $request->$key_name;
                if (count($custom_validation) > 0) {
                    $validation = $custom_validation;
                } else {
                    $validation = [
                        'mimes:' . $mimes,
                        'max:' . $max_size,
                    ];
                }
                $validator = Validator::make($request->all(), [
                    $key_name => $validation
                ]);
                if ($validator->fails()) {
                    $res = [
                        'flag' => 0,
                        'msg' => $validator->messages()->first(),
                    ];
                    return $res;
                } else {
                    $name = $name;
                    if ($settings[self::STG_ST] == self::LC) {
                        $request->$key_name->move(storage_path($path), $name);
                        $path = $path . $name;
                    } else if ($settings[self::STG_ST] == self::WSB) {
                        /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
                        $storage = Storage::disk(self::WSB);
                        $path = $storage->putFileAs(
                            $path,
                            $file,
                            $name
                        );
                    } else if ($settings[self::STG_ST] == self::S3) {
                        /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
                        $storage = Storage::disk(self::S3);
                        $path = $storage->putFileAs(
                            $path,
                            $file,
                            $name
                        );
                    }
                    $res = [
                        'flag' => 1,
                        'msg'  => 'success',
                        'url'  => $path
                    ];
                    return $res;
                }
            } else {
                $res = [
                    'flag' => 0,
                    'msg' => __('Please set proper configuration for storage.'),
                ];
                return $res;
            }
        } catch (\Exception $e) {

            $res = [
                'flag' => 0,
                'msg' => $e->getMessage(),
            ];
            return $res;
        }
    }

    public static function landingPageSetting()
    {
        if (self::$settings == null) {
            $setting     = LandingPageSetting::settings();
            self::$settings = $setting;
        }

        return self::$settings;
    }

    public static function keyWiseUpload_file($request, $key_name, $name, $path, $data_key, $custom_validation = [])
    {
        try {
            $multifile = [
                $key_name => $request->file($key_name)[$data_key][$key_name],
            ];
            $settings = Utility::getStorageSetting();
            if (!empty($settings[self::STG_ST])) {
                if ($settings[self::STG_ST] == self::WSB) {
                    config(
                        [
                            self::FS_WSB . '.key' => $settings[self::WSB . '_key'],
                            self::FS_WSB . '.secret' => $settings[self::WSB . '_secret'],
                            self::FS_WSB . '.region' => $settings[self::WSB . '_region'],
                            self::FS_WSB . '.bucket' => $settings[self::WSB . '_bucket'],
                            self::FS_WSB . '.endpoint' => 'https://s3.' . $settings[self::WSB . '_region'] . '.wasabisys.com'
                        ]
                    );

                    $max_size = !empty($settings[self::WSB . '_' . self::MAX_UP]) ? $settings[self::WSB . '_' . self::MAX_UP] : '2048';
                    $mimes =  !empty($settings[self::WSB . '_' . self::STG_VD]) ? $settings[self::WSB . '_' . self::STG_VD] : '';
                } else if ($settings[self::STG_ST] == self::S3) {
                    config(
                        [
                            self::FS_S3 . '.key' => $settings[self::S3 . '_key'],
                            self::FS_S3 . '.secret' => $settings[self::S3 . '_secret'],
                            self::FS_S3 . '.region' => $settings[self::S3 . '_region'],
                            self::FS_S3 . '.bucket' => $settings[self::S3 . '_bucket'],
                            self::FS_S3 . '.use_path_style_endpoint' => false,
                        ]
                    );
                    $max_size = !empty($settings[self::S3 . '_' . self::MAX_UP]) ? $settings[self::S3 . '_' . self::MAX_UP] : '2048';
                    $mimes =  !empty($settings[self::S3 . '_' . self::STG_VD]) ? $settings[self::S3 . '_' . self::STG_VD] : '';
                } else {
                    $max_size = !empty($settings[self::LC . '_storage_' . self::MAX_UP]) ? $settings[self::LC . '_storage_' . self::MAX_UP] : '2048';

                    $mimes =  !empty($settings[self::LC . '_' . self::STG_VD]) ? $settings[self::LC . '_' . self::STG_VD] : '';
                }
                $file = $request->$key_name;
                if (count($custom_validation) > 0) {
                    $validation = $custom_validation;
                } else {
                    $validation = [
                        'mimes:' . $mimes,
                        'max:' . $max_size,
                    ];
                }
                $validator = Validator::make($multifile, [
                    $key_name => $validation
                ]);


                if ($validator->fails()) {
                    $res = [
                        'flag' => 0,
                        'msg' => $validator->messages()->first(),
                    ];
                    return $res;
                } else {
                    $name = $name;
                    if ($settings[self::STG_ST] == self::LC) {
                        $storage = Storage::disk(self::LC);
                        /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
                        $storage->putFileAs(
                            $path,
                            $request->file($key_name)[$data_key][$key_name],
                            $name
                        );
                        $path = $name;
                    } else if ($settings[self::STG_ST] == self::WSB) {
                        /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
                        $storage = Storage::disk(self::WSB);
                        $path = $storage->putFileAs(
                            $path,
                            $file,
                            $name
                        );
                        // $path = $path.$name;
                    } else if ($settings[self::STG_ST] == self::S3) {
                        /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
                        $storage = Storage::disk(self::S3);
                        $path = $storage->putFileAs(
                            $path,
                            $file,
                            $name
                        );
                    }
                    $res = [
                        'flag' => 1,
                        'msg'  => 'success',
                        'url'  => $path
                    ];
                    return $res;
                }
            } else {
                $res = [
                    'flag' => 0,
                    'msg' => __('Please set proper configuration for storage.'),
                ];
                return $res;
            }
        } catch (\Exception $e) {
            $res = [
                'flag' => 0,
                'msg' => $e->getMessage(),
            ];
            return $res;
        }
    }
}
