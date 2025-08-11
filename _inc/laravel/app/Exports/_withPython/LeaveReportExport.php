<?php

namespace App\Exports;

use App\Traits\ChecksLogin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class LeaveReportExport
{
    use ChecksLogin;

    private const PY_ENDPOINT = '/api/python/leave_report_export';

    public function export(Request $request): BinaryFileResponse|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;

        Log::info(__CLASS__ . '::' . __FUNCTION__ . ' started', ['user_id' => $user?->id]);
        try {
            $response = Http::timeout(60)
                ->get(URL::to(self::PY_ENDPOINT), [
                    'creatorId' => $user?->creatorId()
                ]);

            if (!$response->ok())
                Log::error(
                    __CLASS__ . '::' . __FUNCTION__ . ' python export failed',
                    ['status' => $response->status()]
                );
            else
                Log::info(
                    __CLASS__ . '::' . __FUNCTION__ . ' python export succeeded',
                    ['status' => $response->status()]
                );

            $tmp = tmpfile();
            fwrite($tmp, $response->body());
            $path = stream_get_meta_data($tmp)['uri'];

            Log::info(
                __CLASS__ . '::' . __FUNCTION__ . ' returning download',
                ['path' => $path]
            );
            return response()->download(
                $path,
                'leave_report.xlsx',
                ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
            );
        } catch (\Throwable $e) {
            Log::error(
                __CLASS__ . '::' . __FUNCTION__ . ' export error',
                ['error' => $e->getMessage()]
            );
            abort(500);
        }
    }
}
