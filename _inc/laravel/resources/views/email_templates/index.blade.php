@php
    try {
$user = Auth::user();
        $lang = Utility::fetchUserLang(user: $user);
    } catch (\Throwable $e) {
        \Log::error('email_templates/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
    $lang ??= 'en';
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@push(StacksConstants::ADM_SCR_PG)
    <script async src="{{ asset('assets/js/routes/emailTemplates/lang/toggle.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/emailTemplates/toggle.js') }}"></script>
@endpush
@section(YieldingConstants::ADM_PG_TTL)
    @if($user?->{UsersConstants::COL_TP} == PermissionsConstants::SA ||
        $user?->{UsersConstants::COL_TP} == PermissionsConstants::CPN)
        {{__('Email Notification')}}
    @else
        {{__('Email Templates')}}
    @endif
@endsection
@section(YieldingConstants::ADM_PG_TTL)
    <div class="d-inline-block">
        @if($user?->{UsersConstants::COL_TP} == PermissionsConstants::SA ||
                                        $user?->{UsersConstants::COL_TP} == PermissionsConstants::CPN)
            <h5 class="h4 d-inline-block font-weight-400 {{ VC::MB0 }}">{{__('Email Notification')}}</h5>
        @else
            <h5 class="h4 d-inline-block font-weight-400 {{ VC::MB0 }}">{{__('Email Templates')}}</h5>
        @endif
    </div>
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    @if($user?->{UsersConstants::COL_TP} == PermissionsConstants::SA ||
        $user?->{UsersConstants::COL_TP} == PermissionsConstants::CPN)
        <li class="{{ VC::BCI_ACT }}" aria-current="page">{{__('Email Notification')}}</li>
    @else
        <li class="{{ VC::BCI_ACT }}" aria-current="page">{{__('Email Template')}}</li>
    @endif
@endsection
{{--@section('action-btn')--}}
{{--    <div class="{{ VC::FEND }}">--}}
{{--        <a href="#" class="{{ VC::BT_SM_PM }}" data-ajax-popup="true"--}}
{{--                   data-title="{{__('Create New Email Template')}}" title="{{__('Create')}}" data-url="{{route('email_template.create')}}">--}}
{{--                    <i class="{{ VC::TI_PLS }}"></i> </a>--}}
{{--    </div>--}}

{{--@endsection--}}
@section(YieldingConstants::ADM_CTT)
    @php
        try {
            $isSA   = isset($user) && ($user?->{UsersConstants::COL_TP} === PermissionsConstants::SA);
            $isCPN  = isset($user) && ($user?->{UsersConstants::COL_TP} === PermissionsConstants::CPN);
            $locale = $user->lang ?? app()->getLocale();
            $isArray       = is_array($EmailTemplates ?? null) && count($EmailTemplates ?? []) > 0;
            $isCollection  = ($EmailTemplates ?? null) instanceof Collection && ($EmailTemplates->isNotEmpty());
            $list          = ($isArray || $isCollection) ? $EmailTemplates : [];
        } catch (\Throwable $e) {
            \Log::error('email_templates/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
@endphp

    <div class="{{ VC::CXL12 }}">
        <div class="{{ VC::CD }}">
            <div class="{{ VC::CD }}-header {{ VC::CD }}-body table-border-style">
                <h5></h5>
                <div class="{{ VC::TB_RSP }}">
                    <table class="{{ VC::TB }}" id="pc-dt-simple">
                        <thead>
                            <tr>
                                <th scope="col" class="sort" data-sort="name">{{ __('Name') }}</th>
                                @if($isSA || $isCPN)
                                    <th class="{{ VC::TX_END }}">{{ __('On / Off') }}</th>
                                @else
                                    <th class="{{ VC::TX_END }}">{{ __('Action') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($list as $EmailTemplate)
                                @php
                                    try {
                                        $hasTemplate = isset($EmailTemplate->template) && is_object($EmailTemplate->template);
                                        $tplId       = $hasTemplate && isset($EmailTemplate->template->id) ? $EmailTemplate->template->id : null;
                                        $isActive    = $hasTemplate && isset($EmailTemplate->template->is_active) && ((int) $EmailTemplate->template->is_active === 1);
                                    } catch (\Throwable $e) {
                                        \Log::error('email_templates/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                    }
@endphp
                                <tr>
                                    <td>{{ $EmailTemplate->name ?? __('No name available for template') }}</td>
                                    <td>
                                        @if($isSA)
                                            <div class="{{ VC::TX_END }} {{ VC::MB2 }}">
                                                <div class="{{ VC::ACT_BTN_WRN }}">
                                                    <a href="{{ route(VW::EMLS . '.manage.language', [$EmailTemplate->id, $locale]) }}"
                                                       class="{{ VC::BT_SM_FL_CT }}"
                                                       data-bs-toggle="tooltip"
                                                       title="{{ __('View') }}">
                                                        <i class="{{ VC::TI_EYE_WT }}"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        @endif

                                        @if($isSA || $isCPN)
                                            <div class="{{ VC::TX_END }}">
                                                <div class="{{ VC::FM_CHK }} form-switch d-inline-block">
                                                    <label class="form-check-label form-switch">
                                                        <input
                                                            type="checkbox"
                                                            class="form-check-input email-template-checkbox"
                                                            id="email_template_{{ $tplId ?? 'na' }}"
                                                            {{ $isActive ? 'checked' : '' }}
                                                            value="{{ $isActive ? 1 : 0 }}"
                                                            data-url="{{ $tplId ? route(VW::EML.'.status.language', [$tplId]) : '' }}"
                                                            {{ $tplId ? '' : 'disabled' }}
                                                        />
                                                        <span class="slider1 round"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        @else
                                            <div class="{{ VC::TX_END }}">—</div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="{{ VC::TXCT_MT }}">{{ __('No Email Templates Found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
