<?php

namespace Modules\LandingPage\Entities;

use App\Config\Constants\{
    DatabaseConstants,
    LandingPageConstants,
    SettingsConstants
};
use App\Models\Utility;
use App\Traits\UsesUuids;
use Modules\LandingPage\Config\Constants\SettingsConstants as LPC;
use Database\Factories\LandingPageSettingFactory;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Log, Storage, Validator};

class LandingPageSetting extends Model
{
    use HasFactory, UsesUuids;

    private static $settings = null;
    protected $table = DatabaseConstants::TABLE_LPS;
    protected $fillable = [
        "query_key",
        LandingPageConstants::COL_LPS_NM,
        LandingPageConstants::COL_LPS_V
    ];

    protected static function newFactory(): LandingPageSettingFactory
    {
        return LandingPageSettingFactory::new();
    }

    public static function settings(): array
    {
        Log::debug('[LandingPageSetting Model] Loading landing page settings', ['method' => __METHOD__]);
        $defaults = LPC::LANDING_PAGE_SETTINGS;
        $uuidSettings = [
            LPC::FT_OF_FTS_K,
            LPC::OT_FTS_K,
            LPC::MB_PG_K,
            LPC::SC_SHTS_K,
            LPC::FAQ_FQS_K,
            LPC::TM_TMS_K,
            'discovers'
        ];
        $overrides = self::whereNotIn(LandingPageConstants::COL_LPS_NM, $uuidSettings)
            ->pluck(
                LandingPageConstants::COL_LPS_V,
                LandingPageConstants::COL_LPS_NM
            )->toArray();
        foreach ($uuidSettings as $key) {
            $items = self::where(LandingPageConstants::COL_LPS_NM, $key)
                ->whereNotNull('query_key')
                ->get();
            if ($items->isNotEmpty())
                $overrides[$key] = json_encode($items->mapWithKeys(function ($item) {
                    return [$item->query_key => json_decode($item->value, true)];
                })->toArray());
        }
        return array_merge($defaults, $overrides);
    }

    public static function landingPageSetting(): array
    {
        Log::debug('[LandingPageSetting Model] Fetching landing page settings', ['method' => __METHOD__]);
        if (self::$settings === null) self::$settings = self::settings();
        Log::debug(self::$settings);
        return self::$settings;
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

    public static function keyWiseUploadFile(
        Request $request,
        string  $keyName,
        string  $name,
        string  $path,
        int|string     $dataKey,
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
