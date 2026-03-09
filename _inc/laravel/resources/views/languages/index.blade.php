@php
    try {
$lang = Utility::fetchUserLang();
    } catch (\Throwable $e) {
        \Log::error('languages/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@push(StacksConstants::ADM_CSS)
@endpush
@push(StacksConstants::ADM_SCR_PG)
@endpush
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Language')}}
@endsection
@section(YieldingConstants::ADM_PG_TTL)
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 {{ VC::MB0 }}">{{__('Language')}}</h5>
    </div>
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI_ACT }}" aria-current="page">{{__('Language')}}</li>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/routes/languages/index.js') }}"></script>
@endpush
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @if(
            $currentLang !==
            (!empty($settings[SettingsConstants::DEF_LNG])
                ? $settings[SettingsConstants::DEF_LNG]
                : DatabaseConstants::DEFAULT_LANG)
        )
            <div class="{{ VC::ACT_BTN }} pb-0">
                <div class="{{ VC::FM_CHK }} form-switch custom-switch-v1 {{ VC::MB0 }}">
                    <input type="hidden" name="disable_lang" value="off">
                    <input
                        type="checkbox"
                        class="form-check-input input-primary"
                        name="disable_lang"
                        data-bs-placement="top"
                        title="{{ __('Enable/Disable') }}"
                        id="disable_lang"
                        data-bs-toggle="tooltip"
                        {{ !in_array($currentLang, $disabledLang) ? 'checked' : '' }}
                    >
                    <label class="form-check-label" for="disable_lang"></label>
                </div>
            </div>
            @php
                try {
                    $langDestroyRoute = Route::has(VW::LNG.'.destroy')
                        ? route(VW::LNG.'.destroy', $currentLang)
                        : '#';
                    $linkId = 'lang-destroy-link';
                    $langDestroyMsg = Utility::fetchLinkMessage(
                        app()->getLocale(),
                        VW::LNG,
                        'language_destroy_route_unavailable'
                    ) ?? 'Language delete route is unavailable. Please contact technical support or your domain administrator.';
                } catch (\Throwable $e) {
                    \Log::error('languages/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            <div class="{{ VC::ACT_BTN_DNG }}">
                {!! Form::open([
                    'method' => 'DELETE',
                    'url'    => $langDestroyRoute,
                    'id'     => 'langDestroyForm'
                ]) !!}
                    <a
                        id="{{ $linkId }}"
                        href="{{ $langDestroyRoute }}"
                        data-url="{{ $langDestroyRoute }}"
                        data-sv-localized="true"
                        data-guard-msg="{{ base64_encode($langDestroyMsg) }}"
                        class="{{ VC::BT_SM_DG }} btn-icon bs-pass-para"
                        data-bs-toggle="tooltip"
                        title="{{ __('Delete') }}"
                    >
                        <i class="{{ VC::TI_TRS_WT }}"></i>
                    </a>
                {!! Form::close() !!}
            </div>
            @push(StacksConstants::ADM_SCR_PG)
                <script defer>
                    (() => {
                        const listenerAttr = 'data-lang-destroy-listener-active';
                        const el = document.getElementById('{{ $linkId }}');
                        if (!el || el.getAttribute(listenerAttr) === 'true') return;
                        el.setAttribute(listenerAttr, 'true');

                        const url  = el.getAttribute('data-url');
                        const href = el.href.replace(window.location.origin, '').replace(window.location.pathname, '');
                        const msg  = el.getAttribute('data-guard-msg') ?? '# ERROR';
                        const RG = window.RouteGuard || {};
                        const showToast = RG.showToast || (m => alert(m));

                        if ((!url || url === '#') && (!href || href === '#')) {
                            el.addEventListener('click', event => {
                                event.preventDefault();
                                showToast(msg);
                                el.setAttribute('data-failed-route', 'true');
                            });
                            return;
                        }

                        el.addEventListener('click', event => {
                            event.preventDefault();
                            const form = el.closest('form');
                            if (!form) return;
                            form.submit();
                        });

                        const observer = new MutationObserver(() => {
                            if (!document.getElementById('{{ $linkId }}')) observer.disconnect();
                        });
                        observer.observe(document.body, { childList: true, subtree: true });
                    })();
                </script>
            @endpush
        @endif
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="{{ VC::CXL2 }} {{ VC::CM3 }}">
            <div class="{{ VC::CD_STK }}" style="top:30px">
                <div class="{{ VC::LG_FLSH }}" id="useradd-sidenav">
                    @foreach ($languages as $code => $langName)
                            @php
                                try {
                                    $manageRoute = Route::has(VW::LNG.'.manage')
                                        ? route(VW::LNG.'.manage', ['lang' => $code])
                                        : '#';
                                    $linkId = 'language-manage-' . $code . '-link';
                                    $message = Utility::fetchLinkMessage(
                                        app()->getLocale(),
                                        VW::LNG,
                                        'language_manage_route_unavailable'
                                    ) ?? 'Manage Language route is unavailable. Please contact technical support or your domain administrator.';
                                } catch (\Throwable $e) {
                                    \Log::error('languages/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                }
@endphp
                            <a
                                id="{{ $linkId }}"
                                class="{{ VC::LGI_ACT_NBD }} {{ $currentLang === $code ? 'active' : '' }}"
                                href="{{ $manageRoute }}"
                                data-url="{{ $manageRoute }}"
                                data-sv-localized="true"
                                data-guard-msg="{{ base64_encode($message) }}"
                            >
                                {{ ucfirst($langName) }}
                                <div class="{{ VC::FEND }}">
                                    <i class="{{ VC::TI_CHV_RT }}"></i>
                                </div>
                            </a>
                    @endforeach
                    @push(StacksConstants::ADM_SCR_PG)
                        <script defer>
                            (() => {
                                const setupGuard = (id) => {
                                    const listenerAttr = `data-${id}-listener-active`;
                                    const el = document.getElementById(id);
                                    if (!el || el.getAttribute(listenerAttr) === 'true') return;
                                    el.setAttribute(listenerAttr, 'true');
                                    el.addEventListener('click', event => {
                                        try {
                                            const url = el.getAttribute('data-url');
                                            const href = el.href.replace(window.location.origin, '').replace(window.location.pathname, '');
                                            const RG = window.RouteGuard || {};
                                            const showToast = RG.showToast || (m => alert(m));
                                            if ((!url || url === '#') && (!href || href === '#')) {
                                                event.preventDefault();
                                                const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                                (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                el.setAttribute('data-failed-route', 'true');
                                            }
                                        } catch {}
                                    });
                                    const observer = new MutationObserver(() => {
                                        if (!document.getElementById(id)) observer.disconnect();
                                    });
                                    observer.observe(document.body, { childList: true, subtree: true });
                                };

                                @foreach ($languages as $code => $langName)
                                    setupGuard('language-manage-{{ $code }}-link');
                                @endforeach
                            })();
                        </script>
                    @endpush
                </div>
            </div>
        </div>
        <div class="{{ VC::CXL10 }} {{ VC::CM9 }}">
            <div class="p-3 card">
                <ul class="{{ VC::NAV_PL }} nav-fill" id="pills-tab" role="tablist">
                    <li class="{{ VC::NV_IT }}" role="presentation">
                        <button class="{{ VC::NV_LK }} active" id="pills-user-tab-1" data-bs-toggle="pill" data-bs-target="#pills-user-1" type="button">
                            {{__('Labels')}}
                        </button>
                    </li>
                    <li class="{{ VC::NV_IT }}" role="presentation">
                        <button class="{{ VC::NV_LK }}" id="pills-user-tab-2" data-bs-toggle="pill" data-bs-target="#pills-user-2" type="button">
                            {{__('Messages')}}
                        </button>
                    </li>
                </ul>
            </div>
            <div class="{{ VC::CD_FL }}">
                <div class="{{ VC::CD_BD }}" style="position: relative;">
                    <div class="tab-content no-padding" id="myTab2Content">
                        <div class="{{ VC::TAB_FD_SH }} active" id="lang1" role="tabpanel" aria-labelledby="home-tab4">
                            <div class="tab-content" id="myTabContent">
                                <div class="{{ VC::TAB_FD_SH }} active" id="pills-user-1" role="tabpanel" aria-labelledby="pills-user-tab-1">
                                    @php
                                        try {
                                            $storeDataRoute = Route::has(VW::LNG.'.store.data')
                                                ? route(VW::LNG.'.store.data', [$currentLang])
                                                : '#';
                                            $formId = 'langStoreForm';
                                            $storeDataMsg = Utility::fetchLinkMessage(
                                                app()->getLocale(),
                                                VW::LNG,
                                                'languages_store_data_route_unavailable'
                                            ) ?? 'Language store data route is unavailable. Please contact technical support or your domain administrator.';
                                        } catch (\Throwable $e) {
                                            \Log::error('languages/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                        }
@endphp
                                    <form
                                        method="post"
                                        action="{{ $storeDataRoute }}"
                                        id="{{ $formId }}"
                                        data-action="{{ $storeDataRoute }}"
                                        data-url="{{ $storeDataRoute }}"
                                        data-sv-localized="true"
                                        data-guard-msg="{{ base64_encode($storeDataMsg) }}"
                                    >
                                        @csrf
                                        <div class="row">
                                            @php
                                                $labelPairs ??= [];
                                                try {
                                                    if (is_array($arrLabel ?? null) && count($arrLabel)) {
                                                        $labelPairs = $arrLabel;
                                                    } elseif (($arrLabel ?? null) instanceof Collection && $arrLabel->isNotEmpty()) {
                                                        $labelPairs = $arrLabel->toArray();
                                                    }
                                                } catch (\Throwable $e) {
                                                    \Log::error('languages/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                }
@endphp
                                            @forelse($labelPairs as $label => $value)
                                                @php
                                                    try {
                                                        $safeLabel  = isset($label) && $label !== '' ? (string) $label : __('(unnamed label)');
                                                        $inputKey   = isset($label) && $label !== '' ? (string) $label : ('label_' . $loop->index);
                                                        $hasValue   = isset($value) && $value !== '';
                                                    } catch (\Throwable $e) {
                                                        \Log::error('languages/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                    }
@endphp
                                                <div class="{{ VC::CM6 }}">
                                                    <div class="{{ VC::FM_G }}">
                                                        <label class="{{ VC::FM_LB }}">{{ $safeLabel }}</label>
                                                        <input
                                                            type="text"
                                                            class="{{ VC::FM_CT }}"
                                                            name="label[{{ $inputKey }}]"
                                                            value="{{ $hasValue ? $value : '' }}"
                                                            placeholder="{{ $hasValue ? '' : __('(not set)') }}"
                                                        >
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="{{ VC::C12 }} {{ VC::TXCT_MT }}">
                                                    {{ __('No labels found.') }}
                                                </div>
                                            @endforelse
                                            <div class="{{ VC::CL12 }} {{ VC::TX_END }}">
                                                <button class="{{ VC::BT_PRM }}" type="submit">
                                                    {{ __('Save Changes') }}
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer src="{{ asset('assets/js/routes/languages/indexLabelStore.js') }}"></script>
                                    @endpush
                                </div>
                                <div class="tab-pane fade" id="pills-user-2" role="tabpanel" aria-labelledby="pills-user-tab-2">
                                    @php
                                        try {
                                            $storeDataRoute = Route::has(VW::LNG.'.store.data')
                                                ? route(VW::LNG.'.store.data', [$currentLang])
                                                : '#';
                                            $formId = 'languages-store-data-form';
                                            $storeDataMsg = Utility::fetchLinkMessage(
                                                app()->getLocale(),
                                                VW::LNG,
                                                'languages_store_data_route_unavailable'
                                            ) ?? 'Language store data route is unavailable. Please contact technical support or your domain administrator.';
                                        } catch (\Throwable $e) {
                                            \Log::error('languages/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                        }
@endphp
                                    <form
                                        method="post"
                                        action="{{ $storeDataRoute }}"
                                        id="{{ $formId }}"
                                        data-url="{{ $storeDataRoute }}"
                                        data-sv-localized="true"
                                        data-guard-msg="{{ base64_encode($storeDataMsg) }}"
                                    >
                                        @csrf
                                        <div class="row">
                                            @php
                                                try {
                                                    $root = ($arrMessage instanceof Collection)
                                                                ? $arrMessage->toArray()
                                                                : (is_array($arrMessage ?? null) ? $arrMessage : []);
                                                } catch (\Throwable $e) {
                                                    \Log::error('languages/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                }
@endphp
                                            @forelse($root as $fileName => $fileValue)
                                                @php
                                                    $fileTitle = isset($fileName) && $fileName !== '' ? ucfirst((string) $fileName) : __('Untitled file');
                                                    $lvl1 = is_array($fileValue ?? null) ? $fileValue : [];
@endphp

                                                <div class="{{ VC::CL12 }}">
                                                    <h5>{{ $fileTitle }}</h5>
                                                </div>

                                                @forelse($lvl1 as $label => $value)
                                                    @if(Utility::isFilled($value) ?? [])
                                                        @php
 $lvl2 = $value;
@endphp
                                                        @forelse($lvl2 as $label2 => $value2)
                                                            @if(Utility::isFilled($value2) ?? [])
                                                                @php
 $lvl3 = $value2;
@endphp
                                                                @forelse($lvl3 as $label3 => $value3)
                                                                    @if(Utility::isFilled($value3) ?? [])
                                                                        @php
 $lvl4 = $value3;
@endphp
                                                                        @forelse($lvl4 as $label4 => $value4)
                                                                            @if(Utility::isFilled($value4) ?? [])
                                                                                @php
 $lvl5 = $value4;
@endphp
                                                                                @forelse($lvl5 as $label5 => $value5)
                                                                                    @php
                                                                                        $namePath ??= "message[{$fileName}][{$label}][{$label2}][{$label3}][{$label4}][{$label5}]";
                                                                                        $display  ??= "{$fileName}.{$label}.{$label2}.{$label3}.{$label4}.{$label5}";
                                                                                        try {
                                                                                            $valSet   = isset($value5) && $value5 !== '';
                                                                                        } catch (\Throwable $e) {
                                                                                            \Log::error('languages/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                                        }
@endphp
                                                                                    <div class="{{ VC::CM6 }}">
                                                                                        <div class="{{ VC::FM_G }}">
                                                                                            <label>{{ $display }}</label>
                                                                                            <input
                                                                                                type="text"
                                                                                                class="{{ VC::FM_CT }}"
                                                                                                name="{{ $namePath }}"
                                                                                                value="{{ $valSet ? $value5 : '' }}"
                                                                                                placeholder="{{ $valSet ? '' : __('(not set)') }}"
                                                                                            >
                                                                                        </div>
                                                                                    </div>
                                                                                @empty
                                                                                    <div class="{{ VC::C12 }} {{ VC::TXT_MT }} {{ VC::TXCT }}">
                                                                                        {{ __('No data found for :path', ['path' => "{$fileName}.{$label}.{$label2}.{$label3}.{$label4}"]) }}
                                                                                    </div>
                                                                                @endforelse
                                                                            @else
                                                                                @php
                                                                                    $namePath ??= "message[{$fileName}][{$label}][{$label2}][{$label3}][{$label4}]";
                                                                                    $display  ??= "{$fileName}.{$label}.{$label2}.{$label3}.{$label4}";
                                                                                    try {
                                                                                        $valSet   = isset($value4) && $value4 !== '';
                                                                                    } catch (\Throwable $e) {
                                                                                        \Log::error('languages/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                                    }
@endphp
                                                                                <div class="{{ VC::CL6 }}">
                                                                                    <div class="{{ VC::FM_G }}">
                                                                                        <label>{{ $display }}</label>
                                                                                        <input
                                                                                            type="text"
                                                                                            class="{{ VC::FM_CT }}"
                                                                                            name="{{ $namePath }}"
                                                                                            value="{{ $valSet ? $value4 : '' }}"
                                                                                            placeholder="{{ $valSet ? '' : __('(not set)') }}"
                                                                                        >
                                                                                    </div>
                                                                                </div>
                                                                            @endif
                                                                        @empty
                                                                            <div class="{{ VC::C12 }} {{ VC::TXT_MT }} {{ VC::TXCT }}">
                                                                                {{ __('No data found for :path', ['path' => "{$fileName}.{$label}.{$label2}.{$label3}"]) }}
                                                                            </div>
                                                                        @endforelse
                                                                    @else
                                                                        @php
                                                                            $namePath ??= "message[{$fileName}][{$label}][{$label2}][{$label3}]";
                                                                            $display  ??= "{$fileName}.{$label}.{$label2}.{$label3}";
                                                                            try {
                                                                                $valSet   = isset($value3) && $value3 !== '';
                                                                            } catch (\Throwable $e) {
                                                                                \Log::error('languages/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                            }
@endphp
                                                                        <div class="{{ VC::CL6 }}">
                                                                            <div class="{{ VC::FM_G }}">
                                                                                <label>{{ $display }}</label>
                                                                                <input
                                                                                    type="text"
                                                                                    class="{{ VC::FM_CT }}"
                                                                                    name="{{ $namePath }}"
                                                                                    value="{{ $valSet ? $value3 : '' }}"
                                                                                    placeholder="{{ $valSet ? '' : __('(not set)') }}"
                                                                                >
                                                                            </div>
                                                                        </div>
                                                                    @endif
                                                                @empty
                                                                    <div class="{{ VC::C12 }} {{ VC::TXT_MT }} {{ VC::TXCT }}">
                                                                        {{ __('No data found for :path', ['path' => "{$fileName}.{$label}.{$label2}"]) }}
                                                                    </div>
                                                                @endforelse
                                                            @else
                                                                @php
                                                                    $namePath ??= "message[{$fileName}][{$label}][{$label2}]";
                                                                    $display  ??= "{$fileName}.{$label}.{$label2}";
                                                                    try {
                                                                        $valSet   = isset($value2) && $value2 !== '';
                                                                    } catch (\Throwable $e) {
                                                                        \Log::error('languages/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                    }
@endphp
                                                                <div class="{{ VC::CL6 }}">
                                                                    <div class="{{ VC::FM_G }}">
                                                                        <label>{{ $display }}</label>
                                                                        <input
                                                                            type="text"
                                                                            class="{{ VC::FM_CT }}"
                                                                            name="{{ $namePath }}"
                                                                            value="{{ $valSet ? $value2 : '' }}"
                                                                            placeholder="{{ $valSet ? '' : __('(not set)') }}"
                                                                        >
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        @empty
                                                            <div class="{{ VC::C12 }} {{ VC::TXT_MT }} {{ VC::TXCT }}">
                                                                {{ __('No data found for :path', ['path' => "{$fileName}.{$label}"]) }}
                                                            </div>
                                                        @endforelse
                                                    @else
                                                        @php
                                                            $namePath ??= "message[{$fileName}][{$label}]";
                                                            $display  ??= "{$fileName}.{$label}";
                                                            try {
                                                                $valSet   = isset($value) && $value !== '';
                                                            } catch (\Throwable $e) {
                                                                \Log::error('languages/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <div class="{{ VC::CL6 }}">
                                                            <div class="{{ VC::FM_G }}">
                                                                <label>{{ $display }}</label>
                                                                <input
                                                                    type="text"
                                                                    class="{{ VC::FM_CT }}"
                                                                    name="{{ $namePath }}"
                                                                    value="{{ $valSet ? $value : '' }}"
                                                                    placeholder="{{ $valSet ? '' : __('(not set)') }}"
                                                                >
                                                            </div>
                                                        </div>
                                                    @endif
                                                @empty
                                                    <div class="{{ VC::C12 }} {{ VC::TXT_MT }} {{ VC::TXCT }}">
                                                        {{ __('No data found for :file', ['file' => $fileTitle]) }}
                                                    </div>
                                                @endforelse
                                            @empty
                                                <div class="{{ VC::C12 }} {{ VC::TXT_MT }} {{ VC::TXCT }}">
                                                    {{ __('No message entries found.') }}
                                                </div>
                                            @endforelse
                                        </div>
                                        <div class="{{ VC::CL12 }} {{ VC::TX_END }}">
                                            <button class="{{ VC::BT_PRM }}" type="submit">{{ __('Save Changes')}}</button>
                                        </div>
                                    </form>
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer src="{{ asset('assets/js/routes/languages/indexFileStore.js') }}"></script>
                                    @endpush
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
