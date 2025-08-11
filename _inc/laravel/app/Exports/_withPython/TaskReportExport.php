<?php

namespace App\Exports;

use App\Traits\ChecksLogin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{Auth, Http, Log};
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings};

final class TaskReportExport implements FromCollection, WithHeadings
{
    use ChecksLogin;

    private const API_ENDPOINT = '/api/task_report_export';
    private const TIMEOUT     = 30;

    private int $projectId;

    public function __construct(int $id)
    {
        $this->projectId = $id;
    }

    public function collection(): Collection
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return collect();
        $user = $userOrRedirect;

        Log::info(__METHOD__ . ' invoking python export', ['projectId' => $this->projectId, 'user_id' => $user?->id]);

        try {
            $resp = Http::timeout(self::TIMEOUT)
                ->get(url(self::API_ENDPOINT), [
                    'project_id' => $this->projectId,
                    'user_id'   => $user?->creatorId()
                ]);

            if (!$resp->ok()) {
                Log::warning(__METHOD__ . ' bad response', ['status' => $resp->status()]);
                return collect();
            }

            $data = $resp->json('data') ?? [];
            Log::info(__METHOD__ . ' success', ['count' => count($data)]);
            return collect($data);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' exception', ['error' => $e->getMessage()]);
            return collect();
        }
    }

    public function headings(): array
    {
        return [
            'ID',
            'Title',
            'Description',
            'Start Date',
            'End Date',
            'Priority',
            'Assign To',
            'Milestone',
            'Status'
        ];
    }
}
