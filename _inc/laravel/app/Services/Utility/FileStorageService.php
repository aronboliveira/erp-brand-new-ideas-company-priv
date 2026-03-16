<?php

declare(strict_types=1);

namespace App\Services\Utility;

use App\Config\Constants\{
    DatabaseConstants as DC,
    SettingsConstants as SC,
    UsersConstants as UC,
};
use App\Helpers\SafeConsoleOutput;
use App\Models\{Plan, User, Utility};
use Illuminate\Database\QueryException;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\{DB, File, Log, Storage, Validator};

/**
 * FileStorageService — extracted from Utility.php
 *
 * Handles file upload, download, deletion, and storage-limit
 * accounting across local, Wasabi and S3 drivers.
 *
 * @see \App\Models\Utility — delegates to this service
 */
class FileStorageService
{
    // ─────────────────────────────────────────────────────────
    //  Filesystem disk config-key constants (mirror of Utility)
    // ─────────────────────────────────────────────────────────
    private const FST_DSK         = 'filesystems.disks';
    private const FST_DSK_WSB     = self::FST_DSK . '.wasabi.';
    private const FST_DSK_S3      = self::FST_DSK . '.s3.';
    private const FST_DSK_WSB_K   = self::FST_DSK_WSB . 'key';
    private const FST_DSK_WSB_SC  = self::FST_DSK_WSB . 'secret';
    private const FST_DSK_WSB_RG  = self::FST_DSK_WSB . 'region';
    private const FST_DSK_WSB_BK  = self::FST_DSK_WSB . 'bucket';
    private const FST_DSK_WSB_EP  = self::FST_DSK_WSB . 'endpoint';
    private const FST_DSK_S3_K    = self::FST_DSK_S3 . 'key';
    private const FST_DSK_S3_SC   = self::FST_DSK_S3 . 'secret';
    private const FST_DSK_S3_RG   = self::FST_DSK_S3 . 'region';
    private const FST_DSK_S3_BK   = self::FST_DSK_S3 . 'bucket';
    private const FST_DSK_S3_EP   = self::FST_DSK_S3 . 'use_path_style_endpoint';

    // ─────────────────────────────────────────────────────────
    //  Directory & file existence helpers
    // ─────────────────────────────────────────────────────────

    public static function deleteDirectory(string $dir): bool
    {
        if (!file_exists($dir)) return true;
        if (!is_dir($dir)) return unlink($dir);
        $items = array_diff(scandir($dir), ['.', '..']);
        foreach ($items as $item)
            if (!self::deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) return false;
        return rmdir($dir);
    }

    public static function checkFileExistsAndDelete(array $files): bool
    {
        foreach ($files as $file)
            if (Storage::exists($file) && !Storage::delete($file))
                return false;
        return true;
    }

    // ─────────────────────────────────────────────────────────
    //  Upload
    // ─────────────────────────────────────────────────────────

    public static function uploadFile($request, string $keyName, string $name, string $path, array $customValidation = []): array
    {
        try {
            $settings = self::getStorageSetting();
            if (empty($settings[SC::STR_STT]))
                return ['flag' => 0, 'msg' => __('Please set proper configuration for storage.')];
            $settingType = $settings[SC::STR_STT] ?? SC::LC;
            $diskConfig = [];
            if ($settingType === SC::WSB) {
                $diskConfig = [
                    self::FST_DSK_WSB_K  => $settings[SC::WSB_K]    ?? '',
                    self::FST_DSK_WSB_SC => $settings[SC::WSB_SC]   ?? '',
                    self::FST_DSK_WSB_RG => $settings[SC::WSB_RG]   ?? '',
                    self::FST_DSK_WSB_BK => $settings[SC::WSB_BK]   ?? '',
                    self::FST_DSK_WSB_EP => 'https://s3.' . ($settings[SC::WSB_RG] ?? '') . '.wasabisys.com',
                ];
                $maxSize = $settings[SC::WSB_M_UP]    ?? SC::MAX_U_SIZE_DEF;
                $mimes  = $settings[SC::WSB_STG_VL] ?? '';
            } elseif ($settingType === SC::S3) {
                $diskConfig = [
                    self::FST_DSK_S3_K   => $settings[SC::S3_K]    ?? '',
                    self::FST_DSK_S3_SC  => $settings[SC::S3_SC]   ?? '',
                    self::FST_DSK_S3_RG  => $settings[SC::S3_RG]   ?? '',
                    self::FST_DSK_S3_BK  => $settings[SC::S3_BK]   ?? '',
                    self::FST_DSK_S3_EP  => false,
                ];
                $maxSize = $settings[SC::S3_M_UP]    ?? SC::MAX_U_SIZE_DEF;
                $mimes  = $settings[SC::S3_STG_VL] ?? '';
            } else {
                $maxSize = $settings[SC::LC_ST_M_UP] ?? SC::MAX_U_SIZE_DEF;
                $mimes  = $settings[SC::LC_ST_VL]   ?? '';
            }
            if (!empty($diskConfig))
                config($diskConfig);
            if (!$request->hasFile($keyName))
                return ['flag' => 0, 'msg' => __('No file provided.')];
            $file = $request->file($keyName);
            $rules = count($customValidation) > 0
                ? $customValidation
                : ['mimes:' . $mimes, 'max:' . $maxSize];
            $validator = Validator::make($request->all(), [$keyName => $rules]);
            if ($validator->fails())
                return ['flag' => 0, 'msg' => $validator->messages()->first()];
            if ($settingType === 'local') {
                $file->move(storage_path($path), $name);
                $resultPath = $path . $name;
            } else {
                /** @var FilesystemAdapter $disk */
                $disk      = Storage::disk($settingType);
                $resultPath = $disk->putFileAs($path, $file, $name);
            }
            return ['flag' => 1, 'msg' => 'success', 'url' => $resultPath];
        } catch (\Throwable $e) {
            return ['flag' => 0, 'msg' => $e->getMessage()];
        }
    }

    public static function uploadCustomFile($request, string $keyName, string $name, string $path, string $dataKey, array $customValidation = []): array
    {
        try {
            $settings = self::getStorageSetting();
            if (empty($settings[SC::STR_STT]))
                return ['flag' => 0, 'msg' => __('Please set proper configuration for storage.')];
            $settingType = $settings[SC::STR_STT] ?? SC::LC;
            $diskConfig = [];
            if ($settingType === SC::WSB) {
                $diskConfig = [
                    self::FST_DSK_WSB_K  => $settings[SC::WSB_K]    ?? '',
                    self::FST_DSK_WSB_SC => $settings[SC::WSB_SC]   ?? '',
                    self::FST_DSK_WSB_RG => $settings[SC::WSB_RG]   ?? '',
                    self::FST_DSK_WSB_BK => $settings[SC::WSB_BK]   ?? '',
                    self::FST_DSK_WSB_EP => 'https://s3.' . ($settings[SC::WSB_RG] ?? '') . '.wasabisys.com',
                ];
                $maxSize = $settings[SC::WSB_M_UP]    ?? SC::MAX_U_SIZE_DEF;
                $mimes  = $settings[SC::WSB_STG_VL] ?? '';
            } elseif ($settingType === 's3') {
                $diskConfig = [
                    self::FST_DSK_S3_K   => $settings[SC::S3_K]    ?? '',
                    self::FST_DSK_S3_SC  => $settings[SC::S3_SC]   ?? '',
                    self::FST_DSK_S3_RG  => $settings[SC::S3_RG]   ?? '',
                    self::FST_DSK_S3_BK  => $settings[SC::S3_BK]   ?? '',
                    self::FST_DSK_S3_EP => false,
                ];
                $maxSize = $settings[SC::S3_M_UP]    ?? SC::MAX_U_SIZE_DEF;
                $mimes  = $settings[SC::S3_STG_VL] ?? '';
            } else {
                $maxSize = $settings[SC::LC_ST_M_UP] ?? SC::MAX_U_SIZE_DEF;
                $mimes  = $settings[SC::LC_ST_VL]   ?? '';
            }
            if (!empty($diskConfig))
                config($diskConfig);
            if (!$request->hasFile($keyName) || !isset($request->file($keyName)[$dataKey]))
                return ['flag' => 0, 'msg' => __('No file provided.')];
            $file = $request->file($keyName)[$dataKey];
            $rules = count($customValidation) > 0
                ? $customValidation
                : ['mimes:' . $mimes, 'max:' . $maxSize];
            $validator = Validator::make($request->all(), [$dataKey => $rules]);
            if ($validator->fails())
                return ['flag' => 0, 'msg' => $validator->messages()->first()];
            if ($settingType === 'local') {
                $file->move(storage_path($path), $name);
                $resultPath = $path . $name;
            } else {
                /** @var FilesystemAdapter $disk */
                $disk      = Storage::disk($settingType);
                $resultPath = $disk->putFileAs($path, $file, $name);
            }
            return ['flag' => 1, 'msg' => 'success', 'url' => $resultPath];
        } catch (\Throwable $e) {
            return ['flag' => 0, 'msg' => $e->getMessage()];
        }
    }

    /**
     * Store an UploadedFile using the configured storage driver,
     * returning the final stored filename (with extension).
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string  $directory   e.g. 'uploads/document'
     * @param  string  $baseName    Desired name without extension
     * @return string  The stored filename
     */
    public static function uploadFileGeneric(
        \Illuminate\Http\UploadedFile $file,
        string $directory,
        string $baseName
    ): string {
        $extension = $file->getClientOriginalExtension();
        $fileName  = $baseName . ($extension ? ".{$extension}" : '');

        $settings   = self::getStorageSetting();
        $driverType = $settings[SC::STR_STT] ?? SC::LC;

        if ($driverType === 'local') {
            $file->move(storage_path($directory), $fileName);
        } else {
            /** @var FilesystemAdapter $disk */
            $disk = Storage::disk($driverType);
            $disk->putFileAs($directory, $file, $fileName);
        }

        return $fileName;
    }

    // ─────────────────────────────────────────────────────────
    //  Download / URL
    // ─────────────────────────────────────────────────────────

    public static function getFile(string $path = 'uploads/logo', mixed $settings = null): string
    {
        $output = SafeConsoleOutput::make();
        $class  = class_basename(self::class);
        $method = __FUNCTION__;
        $tag    = "{$class}::{$method}";
        Log::debug("{$tag} called", ['path' => $path]);
        $output->writeln("## [{$tag}] Retrieving file URL for path: {$path}");
        try {
            if (!$settings) {
                Log::debug("{$tag} no settings provided, loading via Utility::settings()");
                $output->writeln("## [{$tag}] Loading settings…");
                $settings = Utility::settings();
                Log::debug("{$tag} settings loaded", ['keys' => array_keys($settings)]);
            }
            $storageType = $settings[SC::STR_STT] ?? SC::LC;
            Log::debug("{$tag} storage type determined", ['storageType' => $storageType]);
            $output->writeln("## [{$tag}] Using disk: {$storageType}");
            if ($storageType === SC::WSB) {
                Log::debug("{$tag} configuring Wasabi disk", [
                    'region' => $settings[SC::WSB_RG] ?? null,
                    'bucket' => $settings[SC::WSB_BK] ?? null,
                ]);
                config([
                    self::FST_DSK_WSB_K  => $settings[SC::WSB_K]  ?? '',
                    self::FST_DSK_WSB_SC => $settings[SC::WSB_SC] ?? '',
                    self::FST_DSK_WSB_RG => $settings[SC::WSB_RG] ?? '',
                    self::FST_DSK_WSB_BK => $settings[SC::WSB_BK] ?? '',
                    self::FST_DSK_WSB_EP => 'https://s3.' . ($settings[SC::WSB_RG] ?? '') . '.wasabisys.com',
                ]);
            } elseif ($storageType === SC::S3) {
                Log::debug("{$tag} configuring S3 disk", [
                    'region' => $settings[SC::S3_RG] ?? null,
                    'bucket' => $settings[SC::S3_BK] ?? null,
                ]);
                config([
                    self::FST_DSK_S3_K  => $settings[SC::S3_K]  ?? '',
                    self::FST_DSK_S3_SC => $settings[SC::S3_SC] ?? '',
                    self::FST_DSK_S3_RG => $settings[SC::S3_RG] ?? '',
                    self::FST_DSK_S3_BK => $settings[SC::S3_BK] ?? '',
                    self::FST_DSK_S3_EP => false,
                ]);
            }
            /** @var FilesystemAdapter $disk */
            $disk = Storage::disk($storageType);
            Log::debug("{$tag} obtained disk", [
                'disk'    => $storageType,
                'adapter' => get_class($disk),
            ]);
            $output->writeln("## [{$tag}] Using disk adapter " . get_class($disk));
            $url = $disk->url($path);
            Log::debug("{$tag} URL generated", ['url' => $url]);
            $output->writeln("## [{$tag}] URL: {$url}");
            return $url;
        } catch (QueryException $qe) {
            Log::error("{$tag} QueryException", [
                'message' => $qe->getMessage(),
                'file'    => $qe->getFile(),
                'line'    => $qe->getLine(),
            ]);
            $output->writeln("## [{$tag}] DB error: " . $qe->getMessage());
            return '';
        } catch (\Throwable $e) {
            Log::error("{$tag} unexpected error", [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);
            $output->writeln("## [{$tag}] Exception: " . $e->getMessage());
            return '';
        }
    }

    // ─────────────────────────────────────────────────────────
    //  Delete
    // ─────────────────────────────────────────────────────────

    /**
     * Delete a file from the configured storage driver.
     *
     * @param  string $path  Relative storage path (e.g. 'uploads/documentUpload/file.pdf')
     * @return array{flag: int, msg: string}
     */
    public static function deleteFile(string $path): array
    {
        try {
            $settings   = self::getStorageSetting();
            $driverType = $settings[SC::STR_STT] ?? SC::LC;

            if ($driverType === 'local') {
                $fullPath = storage_path($path);
                if (!file_exists($fullPath)) {
                    return ['flag' => 1, 'msg' => 'File does not exist, nothing to delete.'];
                }
                if (!unlink($fullPath)) {
                    return ['flag' => 0, 'msg' => 'Failed to delete local file.'];
                }
            } else {
                /** @var FilesystemAdapter $disk */
                $disk = Storage::disk($driverType);
                if (!$disk->exists($path)) {
                    return ['flag' => 1, 'msg' => 'File does not exist, nothing to delete.'];
                }
                if (!$disk->delete($path)) {
                    return ['flag' => 0, 'msg' => "Failed to delete file from {$driverType}."];
                }
            }

            return ['flag' => 1, 'msg' => 'success'];
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' failed', ['path' => $path, 'error' => $e->getMessage()]);
            return ['flag' => 0, 'msg' => $e->getMessage()];
        }
    }

    // ─────────────────────────────────────────────────────────
    //  Storage settings & limits
    // ─────────────────────────────────────────────────────────

    public static function getStorageSetting(): array
    {
        $rows = DB::table(DC::TABLE_SETTINGS)->where(UC::COL_USER_ID, DC::DEFAULT_UUID)->get();
        $defaults = [
            SC::STR_STT          => SC::LC,
            SC::LC_ST_VL         => SC::FMTS_UP_DEF,
            SC::LC_ST_M_UP       => SC::MAX_U_SIZE_DEF,
            SC::S3_K             => '',
            SC::S3_SC            => '',
            SC::S3_RG            => '',
            SC::S3_BK            => '',
            SC::S3_URL           => '',
            SC::S3_EP            => '',
            SC::S3_M_UP          => '',
            SC::S3_STG_VL        => '',
            SC::WSB_K            => '',
            SC::WSB_SC           => '',
            SC::WSB_RG           => '',
            SC::WSB_BK           => '',
            SC::WSB_URL          => '',
            SC::WSB_RT           => '',
            SC::WSB_M_UP         => '',
            SC::WSB_STG_VL       => '',
        ];
        foreach ($rows as $row)
            $defaults[$row->name] = $row->value;
        return $defaults;
    }

    public static function updateStorageLimit(string|int $companyId, float $imageSize): string|int
    {
        try {
            return DB::transaction(function () use ($companyId, $imageSize) {
                $user = User::find($companyId);
                if (!$user) return __('User not found.');
                $plan = Plan::find($user?->plan);
                if (!$plan) return __('Plan not found.');
                $newTotal = $user?->storage_limit + ($imageSize / 1048576);
                if ($plan->storage_limit != -1 && $newTotal > $plan->storage_limit)
                    return __('Plan storage limit is over so please upgrade the plan.');
                $user->storage_limit = $newTotal;
                $user?->save();
                return 1;
            });
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }

    public static function changeStorageLimit(string|int $companyId, string $filePath): bool
    {
        try {
            return DB::transaction(function () use ($companyId, $filePath) {
                $files = File::glob(storage_path($filePath));
                $totalSize = array_reduce($files, function ($carry, $file) {
                    return $carry + (File::exists($file) ? File::size($file) : 0);
                }, 0);
                $user = User::find($companyId);
                if (!$user) return false;
                $plan = Plan::find($user?->plan);
                if (!$plan) return false;
                $user->storage_limit - ($totalSize / 1048576);
                $user?->save();
                foreach ($files as $file)
                    if (File::exists($file)) File::delete($file);
                return true;
            });
        } catch (\Throwable $e) {
            return false;
        }
    }
}
