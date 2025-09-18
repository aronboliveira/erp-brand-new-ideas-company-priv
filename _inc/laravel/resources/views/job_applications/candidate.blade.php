@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth, Route, Crypt};
    use Illuminate\Support\Collection;

    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
    $canFetchMsg = is_callable([Utility::class,'fetchLinkMessage']);
    $canFormatDate = is_callable([$user,'dateFormat']);
    $dashUrl   = Route::has('dashboard') ? route('dashboard') : '#';
    $dashGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, 'generics', 'dashboard_unavailable') : 'Dashboard route is unavailable. Please contact technical support or your domain administrator.') ?? __('Dashboard route is unavailable. Please contact technical support or your domain administrator.');

    $showRouteName = VW::JB_APL.'.show';
    $showGuard = ($canFetchMsg ? Utility::fetchLinkMessage($lang, VW::JB_APL, 'job_application_show_route_unavailable') : 'Job application details route is unavailable. Please contact technical support or your domain administrator.') ?? __('Job application details route is unavailable. Please contact technical support or your domain administrator.');

    $resumesBase = Utility::getFile('uploads/job/resume');

    $apps = (is_array($archive_application ?? null) && count($archive_application ?? []))
        ? $archive_application
        : (($archive_application ?? null) instanceof Collection && $archive_application->isNotEmpty() ? $archive_application : []);
@endphp

@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Archive Application') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ $dashUrl }}"
           data-url="{{ $dashUrl }}"
           data-sv-localized="true"
           data-guard-msg="{{ $dashGuard }}"
           {{ $dashUrl !== '#' ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{ __('Archive Application') }}</li>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CM12 }}">
            <div class="{{ VC::CD }}">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Applied For') }}</th>
                                    <th>{{ __('Rating') }}</th>
                                    <th>{{ __('Applied at') }}</th>
                                    <th>{{ __('CV / Resume') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="font-style">
                                @if(!empty($apps))
                                    @foreach ($apps as $application)
                                        @php
                                            $showUrl = Route::has($showRouteName) ? route($showRouteName, Crypt::encrypt($application->id)) : '#';
                                            $appliedFor = !empty($application->jobs) && !empty($application->jobs->title) ? $application->jobs->title : __('No title available for application');
                                            $appliedAt = $canFormatDate ? (!empty($application->created_at) ? $user?->dateFormat($application->created_at) : __('No creation date available')) : __('Failed to format date. Please contact technical support or your domain administrator.');
                                            $hasResume = !empty($application->resume);
                                        @endphp
                                        <tr>
                                            <td>
                                                <a href="{{ $showUrl }}"
                                                   class="job-app-show-link"
                                                   data-url="{{ $showUrl }}"
                                                   data-sv-localized="true"
                                                   data-guard-msg="{{ $showGuard }}">
                                                    {{ !empty($application->name) ?: __('No name available for application') }}
                                                </a>
                                            </td>
                                            <td>{{ $appliedFor }}</td>
                                            <td>
                                                <span class="static-rating static-rating-sm d-block">
                                                    @for($i=1; $i<=5; $i++)
                                                        @if($i <= (int) $application->rating)
                                                            <i class="star ti ti-star voted"></i>
                                                        @else
                                                            <i class="star ti ti-star"></i>
                                                        @endif
                                                    @endfor
                                                </span>
                                            </td>
                                            <td>{{ $appliedAt }}</td>
                                            <td>
                                                @if($hasResume)
                                                    @php
                                                        $basePath           = isset($resumesBase) ? (string) $resumesBase : '';
                                                        $resumeName         = (string) data_get($application ?? null, 'resume', '');

                                                        $resumeUrl          = ($basePath !== '' && $resumeName !== '') ? ($basePath.'/'.$resumeName) : '#';

                                                        $dlId               = 'resume-download-btn-'.(string) data_get($application ?? null, 'id', 'x');
                                                        $pvId               = 'resume-preview-btn-'.(string) data_get($application ?? null, 'id', 'x');

                                                        $downloadGuardMsg   = Utility::fetchLinkMessage($lang, VW::JB_APL, 'resume_download_unavailable') ?? 'Resume download is unavailable. Please contact technical support or your domain administrator.';
                                                        $previewGuardMsg    = Utility::fetchLinkMessage($lang, VW::JB_APL, 'resume_preview_unavailable')  ?? 'Resume preview is unavailable. Please contact technical support or your domain administrator.';
                                                    @endphp
                                                    <div class="{{ VC::DFL }}">
                                                        <div class="{{ VC::ACT_BTN_PRIM }}">
                                                            <a
                                                                id="{{ $dlId }}"
                                                                class="{{ VC::BT_SM_CT }}"
                                                                href="{{ $resumeUrl }}"
                                                                download
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('Download') }}"
                                                                data-url="{{ $resumeUrl }}"
                                                                data-guard-msg="{{ $downloadGuardMsg }}"
                                                                data-sv-localized="true"
                                                                {{ $resumeUrl === '#' ? 'aria-disabled=true' : '' }}
                                                            >
                                                                <i class="{{ VC::TI_DWN }} {{ VC::TXT_WT }}"></i>
                                                            </a>
                                                        </div>
                                                        <div class="{{ VC::ACT_BTN_INF }}">
                                                            <a
                                                                id="{{ $pvId }}"
                                                                class="{{ VC::BT_SM_CT }}"
                                                                href="{{ $resumeUrl }}"
                                                                target="_blank"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('Preview') }}"
                                                                data-url="{{ $resumeUrl }}"
                                                                data-guard-msg="{{ $previewGuardMsg }}"
                                                                data-sv-localized="true"
                                                                {{ $resumeUrl === '#' ? 'aria-disabled=true' : '' }}
                                                            >
                                                                <i class="{{ VC::TI_EYE_WT }}"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    @push(ST::ADM_SCR_PG)
                                                        <script defer src="{{ asset('assets/js/routes/jobs/applications/resumeDownload.js') }}"></script>
                                                        <script defer src="{{ asset('assets/js/routes/jobs/applications/resumePreview.js') }}"></script>
                                                    @endpush
                                                @else
                                                    <div class="text-mute">{{ __('No resume available') }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                @can('show job application')
                                                    <div class="{{ VC::ACT_BTN_INF }}">
                                                        <a href="{{ $showUrl }}"
                                                           class="{{ VC::BT_SM_CT }} job-app-show-link"
                                                           data-url="{{ $showUrl }}"
                                                           data-sv-localized="true"
                                                           data-guard-msg="{{ $showGuard }}"
                                                           data-bs-toggle="tooltip"
                                                           title="{{ __('View') }}"
                                                           data-title="{{ __('Details') }}">
                                                            <i class="{{ VC::TI_EYE_WT }}"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6" class="text-center">{{ __('No archived applications found.') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @push(StacksConstants::ADM_SCR_PG)
                <script defer src="{{ asset('assets/js/routes/jobs/applications/candidate.js') }}"></script>
            @endpush
        </div>
    </div>
@endsection
