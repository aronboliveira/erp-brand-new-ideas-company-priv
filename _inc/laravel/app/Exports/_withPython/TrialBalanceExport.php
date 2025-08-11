<?php

namespace App\Exports;

use App\Traits\ChecksLogin;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{Http, Log, URL};
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class TrialBalanceExport
{
    use ChecksLogin;

    private const PY_ENDPOINT = '/api/python/trial_balance_export';

    public function export(Request $request): BinaryFileResponse|RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;

        Log::info(__CLASS__ . '::export started', ['user_id' => $user?->id]);
        try {
            $response = Http::timeout(60)
                ->get(URL::to(self::PY_ENDPOINT), [
                    'startDate'   => $request->input('startDate'),
                    'endDate'     => $request->input('endDate'),
                    'companyName' => $request->input('companyName')
                ]);

            if (!$response->ok())
                Log::error(
                    __CLASS__ . '::export python failed',
                    ['status' => $response->status()]
                );
            else
                Log::info(
                    __CLASS__ . '::export python succeeded',
                    ['status' => $response->status()]
                );

            $tmp = tmpfile();
            fwrite($tmp, $response->body());
            $path = stream_get_meta_data($tmp)['uri'];

            Log::info(__CLASS__ . '::export download ready', ['path' => $path]);
            return response()->download(
                $path,
                'trial_balance.xlsx',
                ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
            );
        } catch (\Throwable $e) {
            Log::error(
                __CLASS__ . '::export error',
                ['error' => $e->getMessage()]
            );
            abort(500);
        }
    }
}
