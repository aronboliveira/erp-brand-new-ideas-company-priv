@php
    use App\Config\Constants\{
        PlansConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\Str;
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);
    $dlStoreBaseRouteName   = ViewsConstants::DL;
    $dlStoreKebabRouteName  = Str::kebab($dlStoreBaseRouteName);
    $dlStoreResolvedName    = Route::has($dlStoreBaseRouteName)
        ? $dlStoreBaseRouteName
        : (Route::has($dlStoreKebabRouteName) ? $dlStoreKebabRouteName : null);
    $dlStoreUrl             = $dlStoreResolvedName ? route($dlStoreResolvedName) : '#';
    $dlStoreFormId          = 'deal-store-form';
    $userLang               = isset($lang) ? $lang : Utility::fetchUserLang();
    $dlStoreGuardMessage    = Utility::fetchLinkMessage($userLang, ViewsConstants::DL, 'store_deal_route_unavailable')
        ?? 'Store Deal route is unavailable. Please contact technical support or your domain administrator.';
@endphp
{{ Form::open([
    'method'            => 'POST',
    'url'               => $dlStoreUrl,
    'id'                => $dlStoreFormId,
    'data-url'          => $dlStoreUrl,
    'data-guard-msg'    => $dlStoreGuardMessage,
    'data-sv-localized' => 'true',
]) }}
    @csrf
    <div class="modal-body">
        @php $plan = Utility::getChatGPTSettings(); @endphp
        @if($plan?->{PlansConstants::COL_GPT} == 1)
            <div class="text-end">
                @php
                    $generateRoute = Route::has('generate')
                        ? route('generate', ['deal' => $deal->id])
                        : '#';
                    $generateGuardMsg = Utility::fetchLinkMessage(
                        $lang,
                        ViewsConstants::DL,
                        'generate_route_unavailable'
                    ) ?? 'Generate content for deals with AI route is unavailable. Please contact technical support or your domain administrator.';
                @endphp
                <a
                    id="generate-ai-btn-{{ $deal->id }}"
                    href="{{ $generateRoute }}"
                    data-url="{{ $generateRoute }}"
                    data-guard-msg="{{ $generateGuardMsg }}"
                    data-size="md"
                    class="{{ VC::BT_PRM }} btn-icon btn-sm"
                    data-ajax-popup-over="true"
                    data-bs-placement="top"
                    data-title="{{ __('Generate content with AI') }}"
                >
                    <i class="{{ VC::FAS_RB }}"></i> <span>{{ __('Generate with AI') }}</span>
                </a>
                <script defer>
                    (() => {
                        const btn = document.getElementById('generate-ai-btn-{{ $deal->id }}');
                        if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                        btn.setAttribute('data-listener-active','true');
                        btn.addEventListener('click', e => {
                            try {
                                const url = btn.getAttribute('data-url') ?? '#';
                                if (url !== '#') return;
                                e.preventDefault();
                                const msg = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                                const bs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                let container = document.getElementById('toast-container');
                                if (!container) {
                                    container = document.createElement('div');
                                    container.id = 'toast-container';
                                    document.body.appendChild(container);
                                }
                                if (bs) {
                                    const toast = document.createElement('div');
                                    toast.className = 'toast';
                                    toast.setAttribute('role','alert');
                                    toast.setAttribute('aria-live','assertive');
                                    toast.setAttribute('aria-atomic','true');
                                    const body = document.createElement('div');
                                    body.className = 'toast-body';
                                    body.textContent = msg;
                                    toast.appendChild(body);
                                    container.appendChild(toast);
                                    bootstrap.Toast.getOrCreateInstance(toast).show();
                                } else {
                                    alert(msg);
                                }
                                btn.setAttribute('data-failed-route','true');
                            } catch {}
                        });
                    })();
                </script>
            </div>
        @endif
        <div class="{{ VC::RW }}">
            <div class="col-6 {{ VC::FM_G }}">
                {{ Form::label('name', __('Deal Name'), ['class' => VC::FM_LB]) }}
                {{ Form::text('name', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>
            <div class="col-6 {{ VC::FM_G }}">
                {{ Form::label('phone', __('Phone'), ['class' => VC::FM_LB]) }}
                {{ Form::text('phone', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>
            <div class="col-6 {{ VC::FM_G }}">
                {{ Form::label('price', __('Price'), ['class' => VC::FM_LB]) }}
                {{ Form::number('price', 0, ['class' => VC::FM_CT, 'min' => 0]) }}
            </div>
            <div class="col-6 {{ VC::FM_G }}">
                {{ Form::label('clients', __('Clients'), ['class' => VC::FM_LB]) }}
                {{ Form::select('clients', @if((is_array($clients) && count($clients)) || $clients instanceof Collection && $clients->isNotEmpty()) $clients @else [__('No clients available.')] @endif, null, [
                    'class'    => VC::FM_CT . ' select2',
                    'multiple' => '',
                    'id'       => 'choices-multiple1',
                    'required' => 'required'
                ]) }}
                @if(((is_array($clients) && count($clients) <= 0) || ($clients instanceof Collection && $clients->isEmpty())) && strtolower($user?->{UsersConstants::COL_TP}) == 'owner')
                    @php
                        $clientsIndexRoute = Route::has(VW::CLT.'.index')
                            ? route(VW::CLT.'.index')
                            : '#';
                        $clientsIndexGuardMsg = Utility::fetchLinkMessage(
                            $lang,
                            ViewsConstants::DL,
                            'clients_index_route_unavailable'
                        ) ?? 'Clients index route is unavailable. Please contact technical support or your domain administrator.';
                    @endphp
                    <div class="{{ VC::TXT_MT }} {{ VC::TXSM }}">
                        {{ __('Please create new clients') }} <a
                            id="clients-index-link"
                            href="{{ $clientsIndexRoute }}"
                            data-url="{{ $clientsIndexRoute }}"
                            data-guard-msg="{{ $clientsIndexGuardMsg }}"
                        >{{ __('here') }}</a>.
                    </div>
                    <script defer src="{{ asset('assets/js/routes/deals/storeIndex.js') }}"></script>
                @endif
            </div>
        </div>
    </div>
    <div class="{{ VC::DFL }} modal-footer">
        <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Create') }}</button>
    </div>
    <script defer src="{{ asset('assets/js/routes/deals/storeCreate.js') }}"></script>
{{ Form::close() }}
