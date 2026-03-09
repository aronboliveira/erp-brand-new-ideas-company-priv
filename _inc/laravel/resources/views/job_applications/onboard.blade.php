@php
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang(user: $user);

        $createBase     = VW::JB . '.on.board.create';
        $createKebab    = Str::kebab($createBase);
        $createResolved = Route::has($createBase) ? $createBase : (Route::has($createKebab) ? $createKebab : null);
        $createUrl      = $createResolved ? route($createResolved, 0) : '#';
        $createGuard    = Utility::fetchLinkMessage($lang, VW::JB, 'on_board_create_route_unavailable')
                            ?? __('Create Job On Board route is unavailable. Please contact technical support or your domain administrator.');
        $canFormatDate = is_callable([$user, 'dateFormat']);
    } catch (\Throwable $e) {
        \Log::error('job_applications/onboard — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
    $lang ??= 'en';
    $createUrl ??= '#';
    $createGuard ??= '';
    $canFormatDate ??= false;
    $jobOnBoards ??= collect();
@endphp

@extends(EL::ADM)

@section(YW::ADM_PG_TTL)
    {{ __('Manage Job On-boarding') }}
@endsection

@section(YW::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}" {{ Route::has('dashboard') ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Job On-boarding') }}</li>
@endsection

@section(YW::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can(PermissionsConstants::CR_ITV_SCHD)
            <a href="{{ $createUrl }}"
               data-url="{{ $createUrl }}"
               data-bs-toggle="tooltip"
               title="{{ __('Create') }}"
               data-ajax-popup="true"
               class="{{ VC::BT_SM_PM }}"
               data-title="{{ __('Create New Job OnBoard') }}"
               data-guard-msg="{{ base64_encode($createGuard) }}"
               data-sv-localized="true">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection

@section(YW::ADM_CTT)
    <div class="row">
        <div class="{{ VC::CM12 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_BD_TB_BD }}">
                    <div class="{{ VC::TB_RSP }}">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Job') }}</th>
                                    <th>{{ __('Branch') }}</th>
                                    <th>{{ __('Applied at') }}</th>
                                    <th>{{ __('Joining at') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="font-style">
                                @if(Utility::isFilled($jobOnBoards) ?? [])
                                    @foreach ($jobOnBoards as $job)
                                        @php
                                            try {
                                                $jobId = data_get($job, 'id');

                                                $convertBase     = VW::JB . '.on.board.convert';
                                                $convertKebab    = Str::kebab($convertBase);
                                                $convertResolved = Route::has($convertBase) ? $convertBase : (Route::has($convertKebab) ? $convertKebab : null);
                                                $convertUrl      = ($convertResolved && $jobId) ? route($convertResolved, $jobId) : '#';
                                                $convertGuard    = Utility::fetchLinkMessage($lang, VW::JB, 'on_board_convert_route_unavailable')
                                                                        ?? __('Convert to Employee route is unavailable. Please contact technical support or your domain administrator.');

                                                $editBase     = VW::JB . '.on.board.edit';
                                                $editKebab    = Str::kebab($editBase);
                                                $editResolved = Route::has($editBase) ? $editBase : (Route::has($editKebab) ? $editKebab : null);
                                                $editUrl      = ($editResolved && $jobId) ? route($editResolved, $jobId) : '#';
                                                $editGuard    = Utility::fetchLinkMessage($lang, VW::JB, 'on_board_edit_route_unavailable')
                                                                    ?? __('Edit Job On Board route is unavailable. Please contact technical support or your domain administrator.');

                                                $deleteBase     = VW::JB . '.on.board.delete';
                                                $deleteKebab    = Str::kebab($deleteBase);
                                                $deleteResolved = Route::has($deleteBase) ? $deleteBase : (Route::has($deleteKebab) ? $deleteKebab : null);
                                                $deleteUrl      = ($deleteResolved && $jobId) ? route($deleteResolved, $jobId) : '#';
                                                $deleteGuard    = Utility::fetchLinkMessage($lang, VW::JB, 'on_board_delete_route_unavailable')
                                                                    ?? __('Delete Job On Board route is unavailable. Please contact technical support or your domain administrator.');

                                                $empShowBase     = VW::EMP . '.show';
                                                $empShowKebab    = Str::kebab($empShowBase);
                                                $empShowResolved = Route::has($empShowBase) ? $empShowBase : (Route::has($empShowKebab) ? $empShowKebab : null);
                                                $empIdEncrypted  = data_get($job, 'convert_to_employee') ? Crypt::encrypt($job->convert_to_employee) : null;
                                                $empShowUrl      = ($empShowResolved && $empIdEncrypted) ? route($empShowResolved, $empIdEncrypted) : '#';
                                                $empShowGuard    = Utility::fetchLinkMessage($lang, VW::EMP, 'show_employee_route_unavailable')
                                                                        ?? __('Employee Detail route is unavailable. Please contact technical support or your domain administrator.');

                                                $offerPdfBase     = 'offer_letter.download.pdf';
                                                $offerPdfUrl      = Route::has($offerPdfBase) ? route($offerPdfBase, $jobId) : '#';
                                                $offerPdfGuard    = Utility::fetchLinkMessage($lang, 'offer_letter', 'download_pdf_route_unavailable')
                                                                        ?? __('Offer Letter PDF route is unavailable. Please contact technical support or your domain administrator.');

                                                $offerDocBase     = 'offer_letter.download.doc';
                                                $offerDocUrl      = Route::has($offerDocBase) ? route($offerDocBase, $jobId) : '#';
                                                $offerDocGuard    = Utility::fetchLinkMessage($lang, 'offer_letter', 'download_doc_route_unavailable')
                                                                        ?? __('Offer Letter DOC route is unavailable. Please contact technical support or your domain administrator.');
                                            } catch (\Throwable $e) {
                                                \Log::error('job_applications/onboard — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                            }
@endphp
                                        <tr>
                                            <td>{{ data_get($job, 'applications.name', __('No name available for application')) }}</td>
                                            <td>{{ data_get($job, 'applications.jobs.title', __('No title available for job')) }}</td>
                                            <td>{{ data_get($job, 'applications.jobs.branches.name', __('No name available for branch')) }}</td>
                                            <td>{{ $canFormatDate ? $user?->dateFormat(data_get($job, 'applications.created_at', __('No creation date available'))) : __('Failed to format creation date') }}</td>
                                            <td>{{ $canFormatDate ? (!empty($job->joining_date) ? $user?->dateFormat($job->joining_date) : __('No joining date available')) : __('Failed to format joining date') }}</td>
                                            <td>
                                                @php
 $st = !empty(JobOnBoard::$status[$job->status]) ? (JobOnBoard::$status[$job->status] ?? $job->status) : $job->status;
@endphp
                                                @if($job->status === 'pending')
                                                    <span class="badge bg-warning p-2 {{ VC::PX3 }} rounded">{{ $st }}</span>
                                                @elseif($job->status === 'cancel')
                                                    <span class="badge bg-danger p-2 {{ VC::PX3 }} rounded">{{ $st }}</span>
                                                @else
                                                    <span class="badge {{ VC::BG_P }} p-2 {{ VC::PX3 }} rounded">{{ $st }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(!empty($job->status) && !empty($job->convert_to_employee))
                                                    @if($job->status === 'confirm' && (int) $job->convert_to_employee === 0)
                                                        <div class="{{ VC::ACT_BTN_WRN }} {{ VC::MS2 }}">
                                                            {!! Form::open([
                                                                'method'            => 'GET',
                                                                'url'               => $convertUrl,
                                                                'id'                => 'job-form-' . $jobId,
                                                                'data-guard-msg'    => $convertGuard,
                                                                'data-sv-localized' => 'true'
                                                            ]) !!}
                                                                <a href="#"
                                                                class="{{ VC::BT_SM_CT_PR }}"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('Convert to Employee') }}"
                                                                data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                                data-confirm-yes="document.getElementById('job-form-{{$jobId}}').submit();">
                                                                    <i class="ti ti-exchange {{ VC::TXT_WT }}"></i>
                                                                </a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    @elseif($job->status === 'confirm' && (int) $job->convert_to_employee !== 0)
                                                        <div class="{{ VC::ACT_BTN_INF }} {{ VC::MS2 }}">
                                                            <a href="{{ $empShowUrl }}"
                                                            class="{{ VC::BT_SM_CT }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('View') }}"
                                                            data-guard-msg="{{ base64_encode($empShowGuard) }}"
                                                            data-sv-localized="true">
                                                                <i class="{{ VC::TI_EYE_WT }}"></i>
                                                            </a>
                                                        </div>
                                                    @endif
                                                @else
                                                    <div><small>{{ __('No conversion status information available') }}</small></div>
                                                @endif
                                                <div class="{{ VC::ACT_BTN_PRIM }}">
                                                    <a href="#"
                                                    class="{{ VC::BT_SM_FL_CT }}"
                                                    data-url="{{ $editUrl }}"
                                                    data-ajax-popup="true"
                                                    data-bs-toggle="tooltip"
                                                    title="{{ __('Edit') }}"
                                                    data-guard-msg="{{ base64_encode($editGuard) }}"
                                                    data-sv-localized="true">
                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                    </a>
                                                </div>
                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                    {!! Form::open([
                                                        'method'            => 'DELETE',
                                                        'url'               => $deleteUrl,
                                                        'id'                => 'delete-form-' . $jobId,
                                                        'data-guard-msg'    => $deleteGuard,
                                                        'data-sv-localized' => 'true'
                                                    ]) !!}
                                                        <a href="#"
                                                        class="{{ VC::BT_SM_CT_PR }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Delete') }}"
                                                        data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                        data-confirm-yes="document.getElementById('delete-form-{{$jobId}}').submit();">
                                                            <i class="{{ VC::TI_TRS_WT }}"></i>
                                                        </a>
                                                    {!! Form::close() !!}
                                                </div>
                                                @if (!empty($job->status) && $job->status === 'confirm')
                                                    <div class="{{ VC::ACT_BTN }} bg-secondary {{ VC::MS2 }}">
                                                        <a href="{{ $offerPdfUrl }}"
                                                        class="{{ VC::BT_SM_CT }}"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="{{ __('OfferLetter PDF') }}"
                                                        target="_blank"
                                                        data-guard-msg="{{ base64_encode($offerPdfGuard) }}"
                                                        data-sv-localized="true">
                                                            <i class="{{ VC::TI_DWN }} {{ VC::TXT_WT }}"></i>
                                                        </a>
                                                    </div>
                                                    <div class="{{ VC::ACT_BTN }} bg-secondary {{ VC::MS2 }}">
                                                        <a href="{{ $offerDocUrl }}"
                                                        class="{{ VC::BT_SM_CT }}"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="{{ __('OfferLetter DOC') }}"
                                                        target="_blank"
                                                        data-guard-msg="{{ base64_encode($offerDocGuard) }}"
                                                        data-sv-localized="true">
                                                            <i class="{{ VC::TI_DWN }} {{ VC::TXT_WT }}"></i>
                                                        </a>
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="7">
                                            <div class="{{ VC::TXCT }}">
                                                <p>{{ __('No job on board records found.') }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

                                            {{--                                            <a href="{{route(VW::JB.'.on.board.convert', $job->id)}}" class="{{ ViewClassNamesConstants::BT_SM_CT_PR }}"--}}
                                            {{--                                               data-confirm="Are You Sure?|This action can not be undone. Do you want to continue?"--}}
                                            {{--                                               data-bs-toggle="tooltip" data-confirm-yes="document.getElementById('archive-form-{{$job->id}}').submit();"--}}
                                            {{--                                               data-original-title="{{__('Convert to Employee')}}">--}}
                                            {{--                                                <i class="ti ti-exchange {{ VC::TXT_WT }}"></i>--}}
                                            {{--                                            </a>--}}
