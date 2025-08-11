<?php

namespace Modules\LandingPage\Entities;

use App\Config\Constants\{
    DatabaseConstants,
    LandingPageConstants,
    SettingsConstants
};
use App\Models\Utility;
use App\Traits\UsesUuids;
use Modules\LandingPage\Config\Constants\SettingsConstants as LandingPageSettingsConstants;
use Database\Factories\LandingPageSettingFactory;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Storage, Validator};

class LandingPageSetting extends Model
{
    use HasFactory, UsesUuids;

    private static $settings = null;
    protected $table = DatabaseConstants::TABLE_LPS;
    protected $fillable = [
        LandingPageConstants::COL_LPS_NM,
        LandingPageConstants::COL_LPS_V
    ];

    protected static function newFactory(): LandingPageSettingFactory
    {
        return LandingPageSettingFactory::new();
    }

    public static function settings(): array
    {
        $defaults = LandingPageSettingsConstants::LANDING_PAGE_SETTINGS;
        $overrides = LandingPageSetting::pluck(
            LandingPageConstants::COL_LPS_V,
            LandingPageConstants::COL_LPS_NM
        )->toArray();
        return array_merge($defaults, $overrides);
    }

    public static function uploadFile(
        Request $request,
        string  $keyName,
        string  $name,
        string  $path,
        array   $customValidation = []
    ): array {
        try {
            $cfg   = Utility::getStorageSetting();
            $disk  = $cfg[SettingsConstants::STR_STT] ?? SettingsConstants::LC;
            $maxKey = $disk . '_max_upload_size';
            $mimeKey = $disk . '_storage_validation';
            $max   = $cfg[$maxKey]   ?? SettingsConstants::MAX_U_SIZE_DEF;
            $mimes = $cfg[$mimeKey]  ?? '';
            $file  = $request->$keyName;
            $rules = $customValidation ?: ["mimes:$mimes", "max:$max"];
            $v     = Validator::make($request->all(), [$keyName => $rules]);
            if ($v->fails()) return ['flag' => 0, 'msg' => $v->messages()->first()];
            if ($disk === 'local') {
                $request->$keyName->move(storage_path($path), $name);
                $url = $path . $name;
            } else {
                /** @var \Illuminate\Filesystem\FilesystemAdapter $drv */
                $drv = Storage::disk($disk);
                $url = $drv->putFileAs($path, $file, $name);
            }
            return ['flag' => 1, 'msg' => 'success', 'url' => $url];
        } catch (\Throwable $e) {
            return ['flag' => 0, 'msg' => $e->getMessage()];
        }
    }

    public static function landingPageSetting(): array
    {
        if (self::$settings === null) self::$settings = self::settings();
        return self::$settings;
    }

    public static function keyWiseUploadFile(
        Request $request,
        string  $keyName,
        string  $name,
        string  $path,
        int     $dataKey,
        array   $customValidation = []
    ): array {
        try {
            $cfg   = Utility::getStorageSetting();
            $disk  = $cfg[SettingsConstants::STR_STT] ?? SettingsConstants::LC;
            $maxKey = $disk . '_max_upload_size';
            $mimeKey = $disk . '_storage_validation';
            $max   = $cfg[$maxKey]   ?? SettingsConstants::MAX_U_SIZE_DEF;
            $mimes = $cfg[$mimeKey]  ?? '';
            $file  = $request->file($keyName)[$dataKey][$keyName] ?? null;
            $multi = [$keyName => $file];
            $rules = $customValidation ?: ["mimes:$mimes", "max:$max"];
            $v     = Validator::make($multi, [$keyName => $rules]);
            if ($v->fails()) return ['flag' => 0, 'msg' => $v->messages()->first()];
            if ($disk === 'local') {
                Storage::putFileAs($path, $file, $name);
                $url = $name;
            } else {
                /** @var \Illuminate\Filesystem\FilesystemAdapter $drv */
                $drv = Storage::disk($disk);
                $url = $drv->putFileAs($path, $file, $name);
            }
            return ['flag' => 1, 'msg' => 'success', 'url' => $url];
        } catch (\Throwable $e) {
            return ['flag' => 0, 'msg' => $e->getMessage()];
        }
    }
}
