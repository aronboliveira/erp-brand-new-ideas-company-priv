@php
    try {
$lang = Utility::fetchUserLang();
        $createName    = ViewsConstants::CLT . '.create';
        $createRoute   = Route::has($createName)
            ? route($createName)
            : (Route::has(Str::kebab($createName))
                ? route(Str::kebab($createName))
                : '#');
        $createGuardMsg = Utility::fetchLinkMessage(
            $lang,
            ViewsConstants::CLT,
            'client_create_route_unavailable'
        ) ?? 'Create Client route is unavailable. Please contact technical support or your domain administrator.';
    } catch (\Throwable $e) {
        \Log::error('clients/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{ __('Manage Client') }}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
           {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Client') }}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can(PermissionsConstants::CR_CLT)
            <a
                href="#"
                id="createClientBtn"
                data-url="{{ $createRoute }}"
                data-guard-msg="{{ base64_encode($createGuardMsg) }}"
                data-listener-alias="create-client"
                data-size="md"
                data-ajax-popup="true"
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
                class="{{ VC::BT_SM_PM }}"
            >
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="col-xxl-12">
            <div class="{{ VC::RW }}">
                @php
                    $clientList = ((is_array($clients ?? null) && count($clients ?? [])) || (($clients ?? null) instanceof Collection && ($clients)->isNotEmpty())) ? $clients : [];
@endphp
                @forelse($clientList as $client)
                    @php
                        try {
                            $cid = data_get($client,'id');
                            $resetName = 'clients.reset';
                            $resetUrl = ($cid && Route::has($resetName)) ? route($resetName, Crypt::encrypt($cid)) : '#';
                            $resetGuard = Utility::fetchLinkMessage($lang, ViewsConstants::CLT, 'client_reset_route_unavailable') ?? __('No reset route available');
                            $avatar = data_get($client,'avatar');
                            $avatarSrc = $avatar ? asset(Storage::url('uploads/avatar/'.$avatar)) : asset(Storage::url('uploads/avatar/avatar.png'));
                            $dealRel = data_get($client,'clientDeals') ?? null;
                            $dealCount = ((is_array($dealRel) && count($dealRel)) || ($dealRel instanceof Collection && $dealRel->isNotEmpty())) ? (is_array($dealRel) ? count($dealRel) : $dealRel->count()) : 0;
                            $projRel = data_get($client,'clientProjects') ?? null;
                            $projCount = ((is_array($projRel) && count($projRel)) || ($projRel instanceof Collection && $projRel->isNotEmpty())) ? (is_array($projRel) ? count($projRel) : $projRel->count()) : 0;
                        } catch (\Throwable $e) {
                            \Log::error('clients/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                        }
@endphp
                    <div class="{{ VC::CM3 }}">
                        <div class="{{ VC::CD }} text-center">
                            <div class="{{ VC::CD_HD }} border-0 pb-0">
                                <div class="card-header-right">
                                    <div class="btn-group card-option">
                                        <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="{{ VC::TI_DRP }}"></i></button>
                                        <div class="{{ VC::DRP_MN_EM }}">
                                            @can('edit client')
                                                @php
                                                    try {
                                                        $editName = ViewsConstants::CLT . '.edit';
                                                        $editUrl = ($cid && Route::has($editName)) ? route($editName, $cid) : (( $cid && Route::has(Str::kebab($editName)) ) ? route(Str::kebab($editName), $cid) : '#');
                                                        $editGuard = Utility::fetchLinkMessage($lang, ViewsConstants::CLT, 'client_edit_route_unavailable') ?? __('No edit route available');
                                                    } catch (\Throwable $e) {
                                                        \Log::error('clients/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                    }
@endphp
                                                <a href="#" class="{{ VC::DRP_IT }} edit-icon" id="editClientBtn_{{ $cid }}" data-url="{{ $editUrl }}" data-guard-msg="{{ base64_encode($editGuard) }}" data-listener-alias="edit-client" data-size="md" data-ajax-popup="true"><i class="{{ VC::TI_PC }}"></i><span>{{ __('Edit') }}</span></a>
                                            @endcan
                                            @can('delete client')
                                                @php
                                                    try {
                                                        $destroyName = ViewsConstants::CLT . '.destroy';
                                                        $destroyUrl = ($cid && Route::has($destroyName)) ? route($destroyName, $cid) : (( $cid && Route::has(Str::kebab($destroyName)) ) ? route(Str::kebab($destroyName), $cid) : '#');
                                                        $destroyGuard = Utility::fetchLinkMessage($lang, ViewsConstants::CLT, 'client_destroy_route_unavailable') ?? __('No delete route available');
                                                        $deleteFormId = 'delete-form-' . $cid;
                                                    } catch (\Throwable $e) {
                                                        \Log::error('clients/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                    }
@endphp
                                                {!! Form::open([
                                                    'method'         => 'DELETE',
                                                    'url'            => $destroyUrl,
                                                    'id'             => $deleteFormId,
                                                    'data-url'       => $destroyUrl,
                                                    'data-guard-msg' => $destroyGuard,
                                                ]) !!}
                                                <a href="#" class="{{ VC::DRP_IT }} delete-icon" data-listener-alias="delete-client" data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}" data-confirm-yes="document.getElementById('{{ $deleteFormId }}').submit();"><i class="{{ VC::TI_TRS }}"></i><span>{{ ((int) (data_get($client,'delete_status') ?? 1)) != 0 ? __('Delete') : __('Restore') }}</span></a>
                                                {!! Form::close() !!}
                                            @endcan
                                            @php
                                                $resetBtnId = 'resetClientBtn_' . $cid;
@endphp
                                            <a href="#" class="{{ VC::DRP_IT }} reset-icon" id="{{ $resetBtnId }}" data-url="{{ $resetUrl }}" data-guard-msg="{{ base64_encode($resetGuard) }}" data-listener-alias="reset-client" data-ajax-popup="true"><i class="{{ VC::TI_ADJ }}"></i><span>{{ __('Reset Password') }}</span></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="{{ VC::CD_MT }} full-card">
                                <div class="card-avatar"><img src="{{ $avatarSrc }}" class="img-user wid-80 rounded-circle"></div>
                                <h4 class="{{ VC::MT2 }} {{ VC::TX_PM }}">{{ data_get($client,'name') ?: __('No client name available') }}</h4>
                                <div class="{{ VC::DFL_AIC_JCB }}">
                                    <div class="me-4 {{ VC::TX_PM }}">{{ data_get($client,'email') ?: __('No email available') }}</div>
                                </div>
                                <div class="{{ VC::MT2 }} h6" data-bs-toggle="tooltip" title="{{ __('Last Login') }}">{{ data_get($client,'last_login_at') ?: __('No last login available') }}</div>
                            </div>
                            <div class="card-footer p-3">
                                <div class="{{ VC::DFL_JCB }}">
                                    <div>
                                        <h6 class="{{ VC::MB0 }}">{{ $dealCount }}</h6>
                                        <p class="{{ VC::TXT_MT_TXSM_MB0 }}">{{ __('Deals') }}</p>
                                    </div>
                                    <div>
                                        <h6 class="{{ VC::MB0 }}">{{ $projCount }}</h6>
                                        <p class="{{ VC::TXT_MT_TXSM_MB0 }}">{{ __('Projects') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="{{ VC::CM12 }}"><div class="{{ VC::CD }}"><div class="{{ VC::CD_BD }}"><p class="{{ VC::TXCT }}">{{ __('No clients available') }}</p></div></div></div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/routes/clients/index.js') }}"></script>
@endpush
