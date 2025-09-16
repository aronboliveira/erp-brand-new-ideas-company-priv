@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants as EL,
        PlansConstants,
        StacksConstants as ST,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants as YD,
        DatabaseConstants
    };
    use App\Models\{Plan, User, Utility};
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route, Request};
    use Illuminate\Support\Collection;

    $user = Auth::user();
    $hasFetch = is_callable([Utility::class, 'fetchLinkMessage']);
    $lang = is_callable([Utility::class, 'fetchUserLang']) ? Utility::fetchUserLang(user:$user) : app()->getLocale();

    $dashUrl = Route::has('dashboard') ? route('dashboard') : '#';
    $dashGuard = ($hasFetch ? Utility::fetchLinkMessage($lang, 'generics', 'dashboard_unavailable') : null)
        ?? __('Dashboard route is unavailable. Please contact technical support or your domain administrator.');

    $indexHas = Route::has(VW::NTF_TMP.'.index');
    $indexGuard = ($hasFetch ? Utility::fetchLinkMessage($lang, VW::NTF_TMP, 'index_route_unavailable') : null)
        ?? __('Template index route is unavailable. Please contact technical support or your domain administrator.');

    $updateHas = Route::has(VW::NTF_TMP.'.update');
    $updateGuard = ($hasFetch ? Utility::fetchLinkMessage($lang, VW::NTF_TMP, 'update_route_unavailable') : null)
        ?? __('Update route is unavailable. Please contact technical support or your domain administrator.');
@endphp

@extends(EL::ADM)

@if(isset($notification_template) && !empty($notification_template))
    @php
        $tempName = $notification_template->name ?? '';
    @endphp

    @section(YD::ADM_PG_TTL)
        {{ $tempName !== '' ? $tempName : __('Notification Template') }}
    @endsection

    @section(YD::ADM_BDC)
        <li class="breadcrumb-item">
            <a href="{{ $dashUrl }}"
               data-url="{{ $dashUrl }}"
               data-sv-localized="true"
               data-guard-msg="{{ $dashGuard }}"
               {{ $dashUrl !== '#' ? '' : 'aria-disabled=true' }}>
                {{ __('Dashboard') }}
            </a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">{{ __('Notification Template') }}</li>
    @endsection

    @push('pre-purpose-css-page')
        <link rel="stylesheet" href="{{ asset('css/summernote/summernote-bs4.css') }}">
    @endpush

    @section(YD::ADM_ACT_BTN)
        <div class="row">
            <div class="text-end mb-3">
                <div class="text-end">
                    <div class="d-flex justify-content-end drp-languages">
                        @php
                            $hasLanguages = (is_array($languages ?? null) && count($languages ?? []) > 0) || (($languages ?? null) instanceof Collection && $languages->isNotEmpty());
                            $currLangCode = isset($curr_noti_tempLang) && is_object($curr_noti_tempLang) && isset($curr_noti_tempLang->lang) ? $curr_noti_tempLang->lang : '';
                            $displayLangName = $currLangCode !== '' && isset($languages[$currLangCode]) ? ucfirst($languages[$currLangCode]) : __('Select Language');
                        @endphp
                        @if($hasLanguages)
                            <ul class="list-unstyled mb-0 m-2 me-0">
                                <li class="{{ VC::LNG_DD_IT }}">
                                    <a class="{{ VC::EM_DRP_NO_ARROW }}" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false" id="dropdownLanguage">
                                        <span class="drp-text hide-mob text-primary me-2">{{ $displayLangName }}</span>
                                        <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                                    </a>
                                    <div class="{{ VC::DRP_MN_DSH_END }}" aria-labelledby="dropdownLanguage">
                                        @foreach ($languages as $code => $language)
                                            @php
                                                $nid = $notification_template->id ?? null;
                                                $isActive = ($currLangCode === $code);
                                                $langUrl = ($nid && $indexHas) ? route(VW::NTF_TMP.'.index', [$nid, $code]) : '#';
                                            @endphp
                                            @if(!empty($code) && !empty($language) && !empty($nid))
                                                <a href="{{ $langUrl }}"
                                                   class="dropdown-item {{ $isActive ? 'text-primary' : '' }}"
                                                   data-url="{{ $langUrl }}"
                                                   data-sv-localized="true"
                                                   data-guard-msg="{{ $indexGuard }}">
                                                    {{ ucfirst($language) }}
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                </li>
                            </ul>
                        @endif

                        @php
                            $hasTemplates = (is_array($notification_templates ?? null) && count($notification_templates ?? []) > 0) || (($notification_templates ?? null) instanceof Collection && $notification_templates->isNotEmpty());
                            $displayTempName = $tempName !== '' ? $tempName : __('No Template');
                            $reqSeg = method_exists(Request::class, 'segment') ? (Request::segment(3) ?? '') : '';
                            $userLang = isset($user) && is_object($user) && isset($user->lang) ? $user->lang : '';
                            $defaultLang = defined(DatabaseConstants::class.'::DEFAULT_LANG') ? constant(DatabaseConstants::class.'::DEFAULT_LANG') : 'en';
                            $languageParam = $reqSeg !== '' ? $reqSeg : ($userLang !== '' ? $userLang : $defaultLang);
                        @endphp
                        @if($hasTemplates)
                            <ul class="list-unstyled mb-0 m-2 me-2">
                                <li class="{{ VC::LNG_DD_IT }}">
                                    <a class="{{ VC::EM_DRP_NO_ARROW }}" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false" id="dropdownTemplate">
                                        <span class="drp-text hide-mob text-primary">{{ __('Template: ') }}{{ $displayTempName }}</span>
                                        <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                                    </a>
                                    <div class="{{ VC::DRP_MN_EM }}" aria-labelledby="dropdownTemplate">
                                        @foreach ($notification_templates as $notification)
                                            @php
                                                $nid = isset($notification) && is_object($notification) ? ($notification->id ?? null) : null;
                                                $nname = isset($notification) && is_object($notification) && isset($notification->name) ? $notification->name : __('Unnamed Template');
                                                $isActiveTemplate = ($tempName !== '' && $nname === $tempName);
                                                $tplUrl = ($nid && $indexHas) ? route(VW::NTF_TMP.'.index', [$nid, $languageParam]) : '#';
                                            @endphp
                                            @if(!empty($nid))
                                                <a href="{{ $tplUrl }}"
                                                   class="dropdown-item {{ $isActiveTemplate ? 'text-primary' : '' }}"
                                                   data-url="{{ $tplUrl }}"
                                                   data-sv-localized="true"
                                                   data-guard-msg="{{ $indexGuard }}">
                                                    {{ $nname }}
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                </li>
                            </ul>
                        @endif

                        @if(isset($user) && is_object($user) && method_exists($user, 'creatorId'))
                            @php
                                $showAiButton = false;
                                try {
                                    $creatorId = $user->creatorId();
                                    $planUser = $creatorId ? User::find($creatorId) : null;
                                    $userPlanId = $planUser && isset($planUser->plan) ? $planUser->plan : (defined(DatabaseConstants::class.'::DEFAULT_PLAN') ? constant(DatabaseConstants::class.'::DEFAULT_PLAN') : 1);
                                    $plan = method_exists(Plan::class, 'getPlan') ? Plan::getPlan($userPlanId) : null;
                                    $colGpt = defined(PlansConstants::class.'::COL_GPT') ? constant(PlansConstants::class.'::COL_GPT') : 'gpt';
                                    $gptEnabled = $plan ? (int)($plan->{$colGpt} ?? 0) : 0;
                                    $showAiButton = ($gptEnabled === 1);
                                } catch (\Exception $e) {
                                    $showAiButton = false;
                                }
                            @endphp
                            @if($showAiButton)
                                @php
                                    $generateHas = Route::has('generate');
                                    $generateGuard = ($hasFetch ? Utility::fetchLinkMessage($lang, VW::NTF_TMP, 'generate_route_unavailable') : null) ?? __('Generate route is unavailable. Please contact technical support or your domain administrator.');
                                    $generateUrl = Route::has('generate') ? route('generate', ['notification template']) : '#';
                                @endphp
                                <div class="float-end">
                                    <a href="#"
                                       data-size="md"
                                       class="btn btn-primary btn-icon btn-sm"
                                       data-ajax-popup-over="true"
                                       data-url="{{ $generateUrl }}"
                                       data-sv-localized="true"
                                       data-guard-msg="{{ $generateGuard }}"
                                       data-bs-placement="top"
                                       data-title="{{ __('Generate content with AI') }}">
                                        <i class="{{ VC::FAS_RB }}"></i>
                                        <span>{{ __('Generate with AI') }}</span>
                                    </a>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endsection

    @section(YD::ADM_CTT)
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="font-weight-bold pb-3">{{ __('Placeholders') }}</h5>
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            <div class="card">
                                <div class="card-header card-body">
                                    <div class="row text-xs">
                                        <h6 class="font-weight-bold mb-4">{{ __('Variables') }}</h6>
                                        @php
                                            $variables = [];
                                            $variablesRaw = isset($curr_noti_tempLang) && is_object($curr_noti_tempLang) && isset($curr_noti_tempLang->variables) ? $curr_noti_tempLang->variables : '';
                                            if ($variablesRaw !== '') {
                                                try {
                                                    $decoded = json_decode($variablesRaw, true);
                                                    $variables = is_array($decoded) ? $decoded : [];
                                                } catch (\Exception $e) {
                                                    $variables = [];
                                                }
                                            }
                                        @endphp
                                        @if(is_array($variables) && count($variables) > 0)
                                            @foreach($variables as $key => $var)
                                                @if(!empty($key) && !empty($var))
                                                    <div class="col-6 pb-1">
                                                        <p class="mb-1">
                                                            {{ __($key) }} :
                                                            <span class="pull-right text-primary">{{ '{'.$var.'}' }}</span>
                                                        </p>
                                                    </div>
                                                @endif
                                            @endforeach
                                        @else
                                            <div class="col-12">
                                                <p class="mb-1 text-muted">{{ __('No variables available') }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if(isset($curr_noti_tempLang) && is_object($curr_noti_tempLang))
                            @php
                                $parentId = $curr_noti_tempLang->parent_id ?? null;
                                $formContent = $curr_noti_tempLang->content ?? '';
                                $formLang = $curr_noti_tempLang->lang ?? '';
                                $updateUrl = $updateHas && !empty($parentId) ? route(VW::NTF_TMP.'.update', $parentId) : '#';
                            @endphp

                            @if(!empty($parentId))
                                {!! Form::model($curr_noti_tempLang, [
                                    $updateHas ? 'route' : 'url' => $updateHas ? [VW::NTF_TMP.'.update', $parentId] : $updateUrl,
                                    'method' => 'PUT',
                                    'data-url' => $updateUrl,
                                    'data-sv-localized' => 'true',
                                    'data-guard-msg' => $updateGuard
                                ]) !!}
                                <div class="row">
                                    <div class="form-group col-12">
                                        {!! Form::label('content', __('Notification Message'), ['class' => 'form-label text-dark']) !!}
                                        {!! Form::textarea('content', e($formContent), [
                                            'class' => 'form-control',
                                            'required' => 'required',
                                            'rows' => '04',
                                            'placeholder' => __('EX. Hello, {company_name}')
                                        ]) !!}
                                        <small>
                                            {{ __('A variable is to be used in such a way.') }}
                                            <span class="text-primary">{{ __('Ex. Hello, {user_name}') }}</span>
                                        </small>
                                    </div>
                                </div>
                                <hr>
                                <div class="col-md-12 text-end">
                                    {!! Form::hidden('lang', e($formLang)) !!}
                                    <input type="submit" value="{{ __('Save Changes') }}" class="{{ VC::BT_PR_PRM10 }}">
                                </div>
                                {!! Form::close() !!}
                            @else
                                <div class="alert alert-warning">
                                    {{ __('Unable to load notification template form. Missing template ID.') }}
                                </div>
                            @endif
                        @else
                            <div class="alert alert-danger">
                                {{ __('Notification template data is not available.') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endsection

    @push(ST::ADM_SCR_PG)
        <script defer src="{{ asset('assets/js/routes/notificationTemplates/index.js') }}"></script>
    @endpush
@else
    <p>{{ __('No notification template found.') }}</p>
@endif
