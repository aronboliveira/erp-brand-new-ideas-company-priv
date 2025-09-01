@php
    use Illuminate\Support\Collection;
    use Illuminate\Support\Facades\Auth;
    use App\Config\Constants\ViewClassNamesConstants as VC;
    $auth = Auth::user();
    $canStart = (is_object($zoomMeeting ?? null) && method_exists($zoomMeeting,'checkDateTime')) ? (bool) $zoomMeeting->checkDateTime() : false;
@endphp
<div class="modal-body">
    <div class="tab-content tab-bordered">
        <div class="tab-pane fade show active" id="tab-1" role="tabpanel">
            <div class="{{ VC::RW }}">
                <div class="col-lg-8">
                    <div class="{{ VC::CD }}">
                        <div class="card-body">
                            <dl class="{{ VC::RW }}">
                                <dt class="col-sm-4">
                                    <span class="{{ VC::H6.' '.VC::TXSM.' '.VC::MB0 }}">{{ __('Name') }}</span>
                                </dt>
                                <dd class="col-sm-8">
                                    <span class="{{ VC::TXSM }}">{{ (string) (data_get($zoomMeeting,'title') ?? __('No meeting name available')) }}</span>
                                </dd>
                                <dt class="col-sm-4">
                                    <span class="{{ VC::H6.' '.VC::TXSM.' '.VC::MB0 }}">{{ __('Meeting Id') }}</span>
                                </dt>
                                <dd class="col-sm-8">
                                    <span class="{{ VC::TXSM }}">{{ (string) (data_get($zoomMeeting,'meeting_id') ?? __('No meeting id available')) }}</span>
                                </dd>
                                <dt class="col-sm-4">
                                    <span class="{{ VC::H6.' '.VC::TXSM.' '.VC::MB0 }}">{{ __('Client') }}</span>
                                </dt>
                                <dd class="col-sm-8">
                                    <span class="{{ VC::TXSM }}">{{ (string) (data_get($zoomMeeting,'client_name') ?: __('No client available')) }}</span>
                                </dd>
                                <dt class="col-sm-4">
                                    <span class="{{ VC::H6.' '.VC::TXSM.' '.VC::MB0 }}">{{ __('Start Date') }}</span>
                                </dt>
                                <dd class="col-sm-8">
                                    <span class="{{ VC::TXSM }}">{{ data_get($zoomMeeting,'start_date') ? ($auth?->dateFormat(data_get($zoomMeeting,'start_date')) ?? __('Failed to get start date')) : __('No start date available') }}</span>
                                </dd>
                                <dt class="col-sm-4">
                                    <span class="{{ VC::H6.' '.VC::TXSM.' '.VC::MB0 }}">{{ __('Duration') }}</span>
                                </dt>
                                <dd class="col-sm-8">
                                    <span class="{{ VC::TXSM }}">{{ (string) (data_get($zoomMeeting,'duration') ?? __('No duration available')) }}</span>
                                </dd>
                                <dt class="col-sm-4">
                                    <span class="{{ VC::H6.' '.VC::TXSM.' '.VC::MB0 }}">{{ __('Start URl') }}</span>
                                </dt>
                                <dd class="col-sm-8">
                                    <span class="{{ VC::TXSM }}">
                                        @php
                                            $createdBy = (string) (data_get($zoomMeeting,'created_by') ?? '');
                                            $startUrl = (string) (data_get($zoomMeeting,'start_url') ?? '#');
                                            $joinUrl = (string) (data_get($zoomMeeting,'join_url') ?? '#');
                                        @endphp
                                        @if($canStart)
                                            @if((string) ($auth?->id ?? '') === $createdBy && $startUrl !== '#')
                                                <a href="{{ $startUrl }}" target="_blank">{{ __('Start meeting') }} <i class="ti ti-external-link-square-alt"></i></a>
                                            @elseif($joinUrl !== '#')
                                                <a href="{{ $joinUrl }}" target="_blank">{{ __('Join meeting') }} <i class="ti ti-external-link-square-alt"></i></a>
                                            @else
                                                {{ __('No meeting link available') }}
                                            @endif
                                        @else
                                            {{ __('No meeting link available') }}
                                        @endif
                                    </span>
                                </dd>
                                <dt class="col-sm-4">
                                    <span class="{{ VC::H6.' '.VC::TXSM.' '.VC::MB0 }}">{{ __('Status') }}</span>
                                </dt>
                                <dd class="col-sm-8">
                                    @php $status = (string) (data_get($zoomMeeting,'status') ?? ''); @endphp
                                    @if($canStart)
                                        @if($status === 'waiting')
                                            <span class="{{ VC::BDG }} badge-info">{{ ucfirst($status) }}</span>
                                        @else
                                            <span class="{{ VC::BDG }} badge-success">{{ $status !== '' ? ucfirst($status) : __('No status available') }}</span>
                                        @endif
                                    @else
                                        <span class="{{ VC::BDG }} badge-danger">{{ __('End') }}</span>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="{{ VC::CD }}">
                        <div class="card-footer py-0">
                            <ul class="{{ VC::LG_FLSH }}">
                                <li class="list-group-item px-0">
                                    <div class="{{ VC::R_ALC }}">
                                        <dt class="col-sm-12">
                                            <span class="{{ VC::H6.' '.VC::TXSM.' '.VC::MB0 }}">{{ __('Assigned Client') }}</span>
                                        </dt>
                                        <dd class="col-sm-12">
                                            <span class="{{ VC::TXSM }}">{{ (string) (data_get($zoomMeeting,'client_name') ?: __('No client available')) }}</span>
                                        </dd>
                                        <dt class="col-sm-12">
                                            <span class="{{ VC::H6.' '.VC::TXSM.' '.VC::MB0 }}">{{ __('Created') }}</span>
                                        </dt>
                                        <dd class="col-sm-12">
                                            <span class="{{ VC::TXSM }}">{{ data_get($zoomMeeting,'created_at') ? ($auth?->dateFormat(data_get($zoomMeeting,'created_at')) ?? __('Failed to get created date')) : __('No created date available') }}</span>
                                        </dd>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
