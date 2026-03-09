@php
    try {
function resolveRoute(string $base): ?string {
            $k = Str::kebab($base);
            return Route::has($base) ? $base : (Route::has($k) ? $k : null);
        }

        $lang = Utility::fetchUserLang();

        $leadStoreBase = VW::LD . '.store';
        $leadStoreResolved = resolveRoute($leadStoreBase);
        $leadStoreUrl = $leadStoreResolved ? route($leadStoreResolved) : '#';
        $leadStoreGuard = __(Utility::fetchLinkMessage($lang, VW::LD, 'lead_store_route_unavailable') ?? 'Lead store route is unavailable. Please contact technical support or your domain administrator.');

        $usrIndexBase = VW::USR . '.index';
        $usrIndexResolved = resolveRoute($usrIndexBase);
        $usrIndexUrl = $usrIndexResolved ? route($usrIndexResolved) : '#';
        $usrIndexGuard = __(Utility::fetchLinkMessage($lang, VW::USR, 'user_index_route_unavailable') ?? 'Users index route is unavailable. Please contact technical support or your domain administrator.');

        $plan = Utility::getChatGPTSettings();
    } catch (\Throwable $e) {
        \Log::error('leads/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@if($leadStoreResolved)
    @php
        $formOpen = ['route' => [$leadStoreResolved], 'id' => 'lead-create-form', 'data-url' => $leadStoreUrl, 'data-guard-msg' => $leadStoreGuard, 'data-sv-localized' => 'true'];
@endphp
@else
    @php
        $formOpen = ['url' => '#', 'id' => 'lead-create-form', 'data-url' => '#', 'data-guard-msg' => $leadStoreGuard, 'data-sv-localized' => 'true'];
@endphp
@endif

{{ Form::open($formOpen) }}
    <div class="modal-body">
        @if(($plan?->{PlansConstants::COL_GPT} ?? 0) == 1)
            @php
                $genBase ??= 'generate';
                try {
                    $genResolved = resolveRoute($genBase);
                    $genUrl = $genResolved ? route($genResolved, ['lead']) : '#';
                    $genGuard = __(Utility::fetchLinkMessage($lang, 'generics', 'ai_generate_unavailable') ?? 'AI generate route is unavailable. Please contact technical support or your domain administrator.');
                } catch (\Throwable $e) {
                    \Log::error('leads/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            <div class="{{ VC::TX_END }}">
                <a href="{{ $genUrl }}"
                   data-url="{{ $genUrl }}"
                   data-guard-msg="{{ base64_encode($genGuard) }}"
                   data-sv-localized="true"
                   data-size="md"
                   class="{{ VC::BT }} {{ VC::BT_PM }} btn-icon {{ VC::BT_SM }} ld-route-guard"
                   data-ajax-popup-over="true"
                   data-bs-placement="top"
                   data-title="{{ __('Generate content with AI') }}">
                    <i class="{{ VC::FAS_RB }}"></i> <span>{{ __('Generate with AI') }}</span>
                </a>
            </div>
        @else
            <div class="{{ VC::TX_END }} {{ VC::TXT_MT }} {{ VC::TXS }}">{{ __('AI module is unavailable for your plan.') }}</div>
        @endif

        <div class="{{ VC::RW }}">
            <div class="{{ VC::C6 }} {{ VC::FM_G }}">
                {{ Form::label('subject', __('Subject'),['class'=> VC::FM_LB]) }}
                {{ Form::text('subject', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>
            <div class="{{ VC::C6 }} {{ VC::FM_G }}">
                {{ Form::label('user_id', __('User'),['class'=> VC::FM_LB]) }}
                {{ Form::select('user_id', $users ?? [], null, ['class' => VC::FM_CT_SL, 'required' => 'required']) }}
                @if(is_countable($users ?? []) && count($users ?? []) == 1)
                    <div class="{{ VC::TXT_MT }} {{ VC::TXS }}">
                        {{ __('Please create new users') }} <a href="{{ $usrIndexUrl }}" class="ld-route-guard" data-url="{{ $usrIndexUrl }}" data-guard-msg="{{ base64_encode($usrIndexGuard) }}" data-sv-localized="true">{{ __('here') }}</a>.
                    </div>
                @endif
            </div>
            <div class="{{ VC::C6 }} {{ VC::FM_G }}">
                {{ Form::label('name', __('Name'),['class'=> VC::FM_LB]) }}
                {{ Form::text('name', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>
            <div class="{{ VC::C6 }} {{ VC::FM_G }}">
                {{ Form::label('email', __('Email'),['class'=> VC::FM_LB]) }}
                {{ Form::text('email', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>
            <div class="{{ VC::C6 }} {{ VC::FM_G }}">
                {{ Form::label('phone', __('Phone'),['class'=> VC::FM_LB]) }}
                {{ Form::text('phone', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" id="lead-submit" value="{{ __('Create') }}" class="{{ VC::BT_PM }} {{ VC::BT_SM }} ld-submit" data-url="{{ $leadStoreUrl }}" data-guard-msg="{{ base64_encode($leadStoreGuard) }}" data-sv-localized="true">
    </div>
    <script async src="{{ asset('assets/js/routes/leads/lang/create.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/leads/create.js') }}"></script>
{{ Form::close() }}

@if(!$leadStoreResolved)
    <div class="{{ VC::TXCT_MT }} py-3">{{ __('Failed to mount the form because the submit route was not available.') }}</div>
@endif
