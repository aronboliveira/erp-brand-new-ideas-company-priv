@php
    try {
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
    } catch (\Throwable $e) {
        \Log::error('notification_templates/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
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
        <li class="{{ VC::BCI }}">
            <a href="{{ $dashUrl }}"
               data-url="{{ $dashUrl }}"
               data-sv-localized="true"
               data-guard-msg="{{ base64_encode($dashGuard) }}"
               {{ $dashUrl !== '#' ? '' : 'aria-disabled=true' }}>
                {{ __('Dashboard') }}
            </a>
        </li>
        <li class="{{ VC::BCI_ACT }}" aria-current="page">{{ __('Notification Template') }}</li>
    @endsection

    @push('pre-purpose-css-page')
        <link rel="stylesheet" href="{{ asset('css/summernote/summernote-bs4.css') }}">
    @endpush

    @section(YD::ADM_ACT_BTN)
        <div class="row">
            <div class="{{ VC::TX_END }} {{ VC::MB3 }}">
                <div class="{{ VC::TX_END }}">
                    <div class="{{ VC::DFL_JCE }} drp-languages">
                        @php
                            try {
                                $hasLanguages = (is_array($languages ?? null) && count($languages ?? []) > 0) || (($languages ?? null) instanceof Collection && $languages->isNotEmpty());
                                $currLangCode = isset($curr_noti_tempLang) && is_object($curr_noti_tempLang) && isset($curr_noti_tempLang->lang) ? $curr_noti_tempLang->lang : '';
                                $displayLangName = $currLangCode !== '' && isset($languages[$currLangCode]) ? ucfirst($languages[$currLangCode]) : __('Select Language');
                            } catch (\Throwable $e) {
                                \Log::error('notification_templates/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                            }
@endphp
                        @if($hasLanguages)
                            <ul class="{{ VC::LST_UNSTL }} {{ VC::MB0 }} m-2 {{ VC::ME0 }}">
                                <li class="{{ VC::LNG_DD_IT }}">
                                    <a class="{{ VC::EM_DRP_NO_ARROW }}" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false" id="dropdownLanguage">
                                        <span class="drp-text hide-mob {{ VC::TX_PM }} me-2">{{ $displayLangName }}</span>
                                        <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                                    </a>
                                    <div class="{{ VC::DRP_MN_DSH_END }}" aria-labelledby="dropdownLanguage">
                                        @foreach ($languages as $code => $language)
                                            @php
                                                try {
                                                    $nid = $notification_template->id ?? null;
                                                    $isActive = ($currLangCode === $code);
                                                    $langUrl = ($nid && $indexHas) ? route(VW::NTF_TMP.'.index', [$nid, $code]) : '#';
                                                } catch (\Throwable $e) {
                                                    \Log::error('notification_templates/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                }
@endphp
                                            @if(!empty($code) && !empty($language) && !empty($nid))
                                                <a href="{{ $langUrl }}"
                                                   class="{{ VC::DRP_IT }} {{ $isActive ? 'text-primary' : '' }}"
                                                   data-url="{{ $langUrl }}"
                                                   data-sv-localized="true"
                                                   data-guard-msg="{{ base64_encode($indexGuard) }}">
                                                    {{ ucfirst($language) }}
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                </li>
                            </ul>
                        @endif

                        @php
                            try {
                                $hasTemplates = (is_array($notification_templates ?? null) && count($notification_templates ?? []) > 0) || (($notification_templates ?? null) instanceof Collection && $notification_templates->isNotEmpty());
                                $displayTempName = $tempName !== '' ? $tempName : __('No Template');
                                $reqSeg = method_exists(Request::class, 'segment') ? (Request::segment(3) ?? '') : '';
                                $userLang = isset($user) && is_object($user) && isset($user->lang) ? $user->lang : '';
                                $defaultLang = defined(DatabaseConstants::class.'::DEFAULT_LANG') ? constant(DatabaseConstants::class.'::DEFAULT_LANG') : 'en';
                                $languageParam = $reqSeg !== '' ? $reqSeg : ($userLang !== '' ? $userLang : $defaultLang);
                            } catch (\Throwable $e) {
                                \Log::error('notification_templates/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                            }
@endphp
                        @if($hasTemplates)
                            <ul class="{{ VC::LST_UNSTL }} {{ VC::MB0 }} m-2 me-2">
                                <li class="{{ VC::LNG_DD_IT }}">
                                    <a class="{{ VC::EM_DRP_NO_ARROW }}" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false" id="dropdownTemplate">
                                        <span class="drp-text hide-mob {{ VC::TX_PM }}">{{ __('Template: ') }}{{ $displayTempName }}</span>
                                        <i class="ti ti-chevron-down drp-arrow nocolor"></i>
                                    </a>
                                    <div class="{{ VC::DRP_MN_EM }}" aria-labelledby="dropdownTemplate">
                                        @foreach ($notification_templates as $notification)
                                            @php
                                                try {
                                                    $nid = isset($notification) && is_object($notification) ? ($notification->id ?? null) : null;
                                                    $nname = isset($notification) && is_object($notification) && isset($notification->name) ? $notification->name : __('Unnamed Template');
                                                    $isActiveTemplate = ($tempName !== '' && $nname === $tempName);
                                                    $tplUrl = ($nid && $indexHas) ? route(VW::NTF_TMP.'.index', [$nid, $languageParam]) : '#';
                                                } catch (\Throwable $e) {
                                                    \Log::error('notification_templates/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                }
@endphp
                                            @if(!empty($nid))
                                                <a href="{{ $tplUrl }}"
                                                   class="{{ VC::DRP_IT }} {{ $isActiveTemplate ? 'text-primary' : '' }}"
                                                   data-url="{{ $tplUrl }}"
                                                   data-sv-localized="true"
                                                   data-guard-msg="{{ base64_encode($indexGuard) }}">
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
                                $showAiButton ??= false;
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
                                    try {
                                        $generateHas = Route::has('generate');
                                        $generateGuard = ($hasFetch ? Utility::fetchLinkMessage($lang, VW::NTF_TMP, 'generate_route_unavailable') : null) ?? __('Generate route is unavailable. Please contact technical support or your domain administrator.');
                                        $generateUrl = Route::has('generate') ? route('generate', ['notification template']) : '#';
                                    } catch (\Throwable $e) {
                                        \Log::error('notification_templates/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                    }
@endphp
                                <div class="{{ VC::FEND }}">
                                    <a href="#"
                                       data-size="md"
                                       class="{{ VC::BT_PRM }} btn-icon btn-sm"
                                       data-ajax-popup-over="true"
                                       data-url="{{ $generateUrl }}"
                                       data-sv-localized="true"
                                       data-guard-msg="{{ base64_encode($generateGuard) }}"
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
            <div class="{{ VC::CXL12 }}">
                <div class="card">
                    <div class="{{ VC::CD_BD }}">
                        <h5 class="font-weight-bold pb-3">{{ __('Placeholders') }}</h5>
                        <div class="{{ VC::CL12 }} {{ VC::CM12 }} {{ VC::CS12 }}">
                            <div class="card">
                                <div class="{{ VC::CD_HD }} {{ VC::CD_BD }}">
                                    <div class="row {{ VC::TXS }}">
                                        <h6 class="font-weight-bold {{ VC::MB4 }}">{{ __('Variables') }}</h6>
                                        @php
                                            $variables ??= [];
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
                                                    <div class="{{ VC::C6 }} pb-1">
                                                        <p class="{{ VC::MB1 }}">
                                                            {{ __($key) }} :
                                                            <span class="pull-right {{ VC::TX_PM }}">{{ '{'.$var.'}' }}</span>
                                                        </p>
                                                    </div>
                                                @endif
                                            @endforeach
                                        @else
                                            <div class="{{ VC::C12 }}">
                                                <p class="{{ VC::MB1 }} {{ VC::TXT_MT }}">{{ __('No variables available') }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if(isset($curr_noti_tempLang) && is_object($curr_noti_tempLang))
                            @php
                                try {
                                    $parentId = $curr_noti_tempLang->parent_id ?? null;
                                    $formContent = $curr_noti_tempLang->content ?? '';
                                    $formLang = $curr_noti_tempLang->lang ?? '';
                                    $updateUrl = $updateHas && !empty($parentId) ? route(VW::NTF_TMP.'.update', $parentId) : '#';
                                } catch (\Throwable $e) {
                                    \Log::error('notification_templates/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                }
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
                                    <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                                        {!! Form::label('content', __('Notification Message'), ['class' => 'form-label text-dark']) !!}
                                        {!! Form::textarea('content', e($formContent), [
                                            'class' => 'form-control',
                                            'required' => 'required',
                                            'rows' => '04',
                                            'placeholder' => __('EX. Hello, {company_name}')
                                        ]) !!}
                                        <small>
                                            {{ __('A variable is to be used in such a way.') }}
                                            <span class="{{ VC::TX_PM }}">{{ __('Ex. Hello, {user_name}') }}</span>
                                        </small>
                                    </div>
                                </div>
                                <hr>
                                <div class="{{ VC::CM12 }} {{ VC::TX_END }}">
                                    {!! Form::hidden('lang', e($formLang)) !!}
                                    <input type="submit" value="{{ __('Save Changes') }}" class="{{ VC::BT_PR_PRM10 }}">
                                </div>
                                {!! Form::close() !!}
                            @else
                                <div class="{{ VC::ALT_WRN }}">
                                    {{ __('Unable to load notification template form. Missing template ID.') }}
                                </div>
                            @endif
                        @else
                            <div class="{{ VC::ALT_DNG }}">
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
