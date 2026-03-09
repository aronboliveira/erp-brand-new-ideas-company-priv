<?php

namespace Modules\LandingPage\Entities;

use App\Config\Constants\{
    DatabaseConstants as DC,
    LandingPageConstants as LPGC,
    SettingsConstants as SC
};
use App\Models\{Utility};
use App\Traits\{UsesUuids};
use Modules\LandingPage\Config\Constants\{
    RoutesResourcesConstants as RRC,
    SettingsConstants as LPSC
};
use Database\Factories\LandingPageSettingFactory;
use Illuminate\Database\Eloquent\{Factories\HasFactory, Model};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Log, Storage, Validator};

class LandingPageSetting extends Model
{
    use HasFactory, UsesUuids;

    private static $settings = null;

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->query_key)) {
                $model->query_key = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }
    protected $table = DC::TABLE_LPS;
    protected $fillable = [
        "query_key",
        LPGC::COL_LPS_NM,
        LPGC::COL_LPS_V,
        DC::COL_TABLE_CREATOR
    ];

    protected static function newFactory(): LandingPageSettingFactory
    {
        return LandingPageSettingFactory::new();
    }

    public static function settings(): array
    {
        try {
            $defaults = LPSC::LANDING_PAGE_SETTINGS;
            $uuidSettings = [
                LPSC::FT_OF_FTS_K,
                LPSC::OT_FTS_K,
                LPSC::MB_PG_K,
                LPSC::SC_SHTS_K,
                LPSC::FAQ_FQS_K,
                LPSC::TM_TMS_K,
                LPSC::DC_OF_FTS_K
            ];
            $overrides = self::whereNotIn(LPGC::COL_LPS_NM, $uuidSettings)
                ->pluck(
                    LPGC::COL_LPS_V,
                    LPGC::COL_LPS_NM
                )->toArray();
            foreach ($uuidSettings as $key) {
                $items = self::where(LPGC::COL_LPS_NM, $key)
                    ->whereNotNull('query_key')
                    ->get();
                if ($items->isNotEmpty())
                    $overrides[$key] = json_encode($items->mapWithKeys(function ($item) {
                        return [$item->query_key => json_decode($item->value, true)];
                    })->toArray());
            }
            return array_merge($defaults, $overrides);
        } catch (\Throwable $e) {
            Log::error(static::class . '::settings — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return [];
        }
    }

    public static function landingPageSetting(): array
    {
        try {
            if (self::$settings === null) self::$settings = self::settings();
            return self::$settings;
        } catch (\Throwable $e) {
            Log::error(static::class . '::landingPageSetting — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            return [];
        }
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
            $disk  = $cfg[SC::STR_STT] ?? SC::LC;
            $maxKey = $disk . '_max_upload_size';
            $mimeKey = $disk . '_storage_validation';
            $max   = $cfg[$maxKey]   ?? SC::MAX_U_SIZE_DEF;
            $mimes = $cfg[$mimeKey]  ?? '';
            $file  = $request->$keyName;
            $rules = $customValidation ?: ["mimes:$mimes", "max:$max"];
            $v     = Validator::make($request->all(), [$keyName => $rules]);
            if ($v->fails()) return ['flag' => 0, 'msg' => $v->messages()->first()];
            if ($disk === 'local') {
                $request->$keyName->move(storage_path($path), $name);
                $url = $path . $name;
            } else {
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
            $disk  = $cfg[SC::STR_STT] ?? SC::LC;
            $maxKey = $disk . '_max_upload_size';
            $mimeKey = $disk . '_storage_validation';
            $max   = $cfg[$maxKey]   ?? SC::MAX_U_SIZE_DEF;
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
                $drv = Storage::disk($disk);
                $url = $drv->putFileAs($path, $file, $name);
            }
            return ['flag' => 1, 'msg' => 'success', 'url' => $url];
        } catch (\Throwable $e) {
            return ['flag' => 0, 'msg' => $e->getMessage()];
        }
    }
}
