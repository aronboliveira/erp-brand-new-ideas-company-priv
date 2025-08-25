@php
    use App\Config\Constants\ViewClassNamesConstants as VC;

    $leaves     = isset($leaves) && !empty($leaves) ? $leaves : [];
    $leaveData  = isset($leaveData) && !empty($leaveData) ? $leaveData : [];
@endphp
<div class="modal-body">
    <div class="{{ VC::RW }}">
        @foreach($leaves as $leave)
            <div class="col text-center">
                <div class="{{ VC::CD_POS }}">
                    <h6 class="{{ VC::RPT_TX_GR }}">{{ data_get($leave,'title','-') }} :</h6>
                    <h6 class="{{ VC::RPT_TX_DEF }}">{{ data_get($leave,'total','-') }}</h6>
                </div>
            </div>
        @endforeach
    </div>
    <div class="row mt-2">
        <table class="{{ VC::TB }} datatable">
            <thead>
                <tr>
                    <th>{{ __('Leave Type') }}</th>
                    <th>{{ __('Leave Date') }}</th>
                    <th>{{ __('Leave Days') }}</th>
                    <th>{{ __('Leave Reason') }}</th>
                </tr>
            </thead>
            <tbody class="list">
                @forelse($leaveData as $leave)
                    @php
                        $startDateStr      = data_get($leave,'start_date');
                        $endDateStr        = data_get($leave,'end_date');
                        $startDate         = $startDateStr ? new \DateTime($startDateStr) : null;
                        $endDate           = $endDateStr ? new \DateTime($endDateStr) : null;
                        $total_leave_days  = ($startDate && $endDate) ? $startDate->diff($endDate)->days : 0;
                    @endphp
                    <tr>
                        <td>{{ data_get($leave,'leaveType.title','') }}</td>
                        <td>{{ ($startDateStr ?? '') . ' to ' . ($endDateStr ?? '') }}</td>
                        <td>{{ $total_leave_days }}</td>
                        <td>{{ data_get($leave,'leave_reason','') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">{{ __('No Data Found.!') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
