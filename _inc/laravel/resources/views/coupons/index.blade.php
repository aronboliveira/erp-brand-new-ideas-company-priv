@php
    try {
$lang = Utility::fetchUserLang();
        $couponCreateRoute  = Route::has(ViewsConstants::CPN . '.create')
            ? route(ViewsConstants::CPN . '.create')
            : '#';
        $couponCreateBtnId  = 'coupon-create-btn';
        $couponCreateMsg    = Utility::fetchLinkMessage(
            $lang,
            ViewsConstants::CPN,
            'coupon_create_route_unavailable'
        ) ?? 'Coupon create route is unavailable. Please contact technical support or your domain administrator.';
    } catch (\Throwable $e) {
        \Log::error('coupons/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@push(StacksConstants::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/core/route-guard.js') }}"></script>
    <script defer>
        (() => {
            let errorMessage = '';
            const showError = msg => window.RouteGuard?.showToast?.(msg, 'error') ?? alert(msg);
            const getMsg = (key, el) => window.RouteGuard?.getMsg?.(el, key) ?? el?.getAttribute?.('data-guard-msg') ?? '# ERROR';
            const onErrorPointerUp = () => { if (errorMessage) { showError(errorMessage); errorMessage = ''; } };
            document.addEventListener('pointerup', onErrorPointerUp);
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('.code').forEach(el => {
                    if (el.dataset.listenerAttached === 'true') return;
                    el.dataset.listenerAttached = 'true';
                    el.addEventListener('click', () => {
                        try {
                            const manual = document.getElementById('manual');
                            const auto = document.getElementById('auto');
                            if (!manual || !auto) throw new Error('code_toggle_failed');
                            if (el.value === 'manual') {
                                manual.classList.replace('d-none', 'd-block');
                                auto.classList.replace('d-block', 'd-none');
                            } else {
                                auto.classList.replace('d-none', 'd-block');
                                manual.classList.replace('d-block', 'd-none');
                            }
                        } catch { errorMessage = getMsg('code_toggle_failed', el); }
                    });
                });
                const genBtn = document.getElementById('code-generate');
                if (genBtn && genBtn.dataset.listenerAttached !== 'true') {
                    genBtn.dataset.listenerAttached = 'true';
                    genBtn.addEventListener('click', () => {
                        try {
                            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                            const code = Array.from({ length: 10 }, () => chars.charAt(Math.floor(Math.random() * chars.length))).join('');
                            const autoInput = document.getElementById('auto-code');
                            if (!autoInput) throw new Error('code_generation_failed');
                            autoInput.value = code;
                        } catch { errorMessage = getMsg('code_generation_failed', genBtn); }
                    });
                }
            });
        })();
    </script>
@endpush
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Coupon')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{__('Coupon')}}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create coupon')
            <a
                id="{{ $couponCreateBtnId }}"
                href="#"
                data-url="{{ $couponCreateRoute }}"
                data-guard-msg="{{ base64_encode($couponCreateMsg) }}"
                data-size="lg"
                data-ajax-popup="true"
                data-title="{{ __('Create New Coupon') }}"
                class="{{ VC::BT_SM_PM }}"
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
            >
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
            @push(StacksConstants::ADM_SCR_PG)
                <script defer src="{{ asset('assets/js/routes/coupons/create.js') }}"></script>
            @endpush
        @endcan
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CS12 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_BD_TB_BD }}">
                    <div class="{{ VC::TB_RSP }}">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Code') }}</th>
                                    <th>{{ __('Discount (%)') }}</th>
                                    <th>{{ __('Limit') }}</th>
                                    <th>{{ __('Used') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(Utility::isFilled($coupons) ?? [])
                                    @foreach($coupons as $coupon)
                                        @php
                                            try {
                                                $name     = !empty($coupon->name) ? $coupon->name : __('No coupon name available');
                                                $code     = !empty($coupon->code) ? $coupon->code : __('No coupon code available');
                                                $discount = is_numeric($coupon->discount) ? $coupon->discount : __('No discount available');
                                                $limit    = is_numeric($coupon->limit) ? $coupon->limit : __('No limit available');
                                                $used     = method_exists($coupon,'used_coupon') ? $coupon->used_coupon() : __('No used count available');
                                                $listLang  = $lang ?? Utility::fetchUserLang();
                                                $rowId         = (string) $coupon->id;
                                                $showName = VW::CPN . '.show';
                                                $showHref = Route::has($showName) ? route($showName, $coupon->id) : '#';
                                                $showBtnId     = 'coupon-show-btn-'    . $rowId;
                                                $guardShow = Utility::fetchLinkMessage($listLang, VW::CPN, 'coupon_show_route_unavailable')    ?? 'Coupon show route is unavailable. Please contact technical support or your domain administrator.';
                                            } catch (\Throwable $e) {
                                                \Log::error('coupons/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                            }
@endphp
                                        <tr class="font-style">
                                            <td>{{ $name }}</td>
                                            <td>{{ $code }}</td>
                                            <td>{{ $discount }}</td>
                                            <td>{{ $limit }}</td>
                                            <td>{{ $used }}</td>
                                            <td class="action {{ VC::TX_END }}">
                                                <span>
                                                    <div class="{{ VC::ACT_BTN_WRN }}">
                                                        <a id="{{ $showBtnId }}"
                                                        href="{{ $showHref }}"
                                                        class="{{ VC::BT_SM_FL_CT }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('View') }}"
                                                        data-sv-localized="true"
                                                        data-guard-msg="{{ base64_encode($guardShow) }}">
                                                            <i class="{{ VC::TI_EYE_WT }}"></i>
                                                        </a>
                                                    </div>

                                                    @can('edit coupon')
                                                        @php
                                                            try {
                                                                $editName = VW::CPN . '.edit';
                                                                $editUrl  = Route::has($editName) ? route($editName, $coupon->id) : '#';
                                                                $editBtnId     = 'coupon-edit-btn-'    . $rowId;
                                                                $guardEdit = Utility::fetchLinkMessage($listLang, VW::CPN, 'coupon_edit_route_unavailable')    ?? 'Coupon edit route is unavailable. Please contact technical support or your domain administrator.';
                                                            } catch (\Throwable $e) {
                                                                \Log::error('coupons/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <div class="{{ VC::ACT_BTN_PRIM }}">
                                                            <a id="{{ $editBtnId }}"
                                                            href="#"
                                                            class="{{ VC::BT_SM_FL_CT }}"
                                                            data-url="{{ $editUrl }}"
                                                            data-ajax-popup="true"
                                                            data-size="md"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Edit') }}"
                                                            data-title="{{ __('Edit Coupon') }}"
                                                            data-sv-localized="true"
                                                            data-guard-msg="{{ base64_encode($guardEdit) }}">
                                                                <i class="{{ VC::TI_PC_WT }}"></i>
                                                            </a>
                                                        </div>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (function(){
                                                                    try{
                                                                        function toastOrAlert(msg){
                                                                            try{
                                                                                if(window.bootstrap && window.bootstrap.Toast){
                                                                                    var t=document.getElementById('route-guard-toast');
                                                                                    if(!t){
                                                                                        t=document.createElement('div');
                                                                                        t.id='route-guard-toast';
                                                                                        t.className='toast align-items-center text-bg-danger border-0 position-fixed bottom-0 end-0 m-3';
                                                                                        t.setAttribute('role','alert');t.setAttribute('aria-live','assertive');t.setAttribute('aria-atomic','true');
                                                                                        t.innerHTML='<div class="{{ VC::DFL }}"><div class="toast-body"></div><button type="button" class="{{ VC::BT_CL }} btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="{{ __('Close') }}"></button></div>';
                                                                                        document.body.appendChild(t);
                                                                                    }
                                                                                    t.querySelector('.toast-body').textContent=msg;
                                                                                    new bootstrap.Toast(t,{delay:4000}).show();
                                                                                }else{ alert(msg); }
                                                                            }catch(e){ alert(msg); }
                                                                        }
                                                                        var editBtn=document.getElementById('{{ $editBtnId }}');
                                                                        if(editBtn && editBtn.getAttribute('data-listener-active')!=='true'){
                                                                            editBtn.setAttribute('data-listener-active','true');
                                                                            editBtn.addEventListener('click',function(e){
                                                                                var url=(editBtn.getAttribute('data-url')||'').trim();
                                                                                if(!url || url==='#'){ e.preventDefault(); toastOrAlert(editBtn.getAttribute('data-guard-msg')||'#'); }
                                                                            });
                                                                        }
                                                                        var showBtn=document.getElementById('{{ $showBtnId }}');
                                                                        if(showBtn && showBtn.getAttribute('data-listener-active')!=='true'){
                                                                            showBtn.setAttribute('data-listener-active','true');
                                                                            showBtn.addEventListener('click',function(e){
                                                                                var href=(showBtn.getAttribute('href')||'').trim();
                                                                                if(!href || href==='#'){ e.preventDefault(); toastOrAlert(showBtn.getAttribute('data-guard-msg')||'#'); }
                                                                            });
                                                                        }
                                                                    }catch(_){}
                                                                })();
                                                            </script>
                                                        @endpush
                                                    @endcan

                                                    @can('delete coupon')
                                                        @php
                                                            try {
                                                                $delName  = VW::CPN . '.destroy';
                                                                $delHas   = Route::has($delName);
                                                                $destroyBtnId  = 'coupon-destroy-btn-' . $rowId;
                                                                $destroyFormId = 'delete-form-'        . $rowId;
                                                                $guardDel  = Utility::fetchLinkMessage($listLang, VW::CPN, 'coupon_destroy_route_unavailable') ?? 'Coupon delete route is unavailable. Please contact technical support or your domain administrator.';
                                                            } catch (\Throwable $e) {
                                                                \Log::error('coupons/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                            }
@endphp
                                                        <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                            @php
                                                                try {
                                                                    $delFormAttrs = ['method' => 'DELETE', 'id' => $destroyFormId, 'data-sv-localized' => 'true', 'data-guard-msg' => $guardDel];
                                                                    if($delHas){ $delFormAttrs['route'] = [$delName, $coupon->id]; }
                                                                    else{ $delFormAttrs['url'] = '#'; }
                                                                } catch (\Throwable $e) {
                                                                    \Log::error('coupons/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                }
@endphp
                                                            {!! Form::open($delFormAttrs) !!}
                                                                <a id="{{ $destroyBtnId }}"
                                                                href="#"
                                                                class="{{ VC::BT_SM_CT_PR }}"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('Delete') }}"
                                                                data-url="{{ $delHas ? route($delName, $coupon->id) : '#' }}"
                                                                data-guard-msg="{{ base64_encode($guardDel) }}"
                                                                data-confirm="{{ __(Utility::fetchLinkMessage($listLang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($listLang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                                data-confirm-yes="document.getElementById('{{ $destroyFormId }}').submit();">
                                                                    <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                </a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (function(){
                                                                    try{
                                                                        function toastOrAlert(msg){
                                                                            try{
                                                                                if(window.bootstrap && window.bootstrap.Toast){
                                                                                    var t=document.getElementById('route-guard-toast');
                                                                                    if(!t){
                                                                                        t=document.createElement('div');
                                                                                        t.id='route-guard-toast';
                                                                                        t.className='toast align-items-center text-bg-danger border-0 position-fixed bottom-0 end-0 m-3';
                                                                                        t.setAttribute('role','alert');t.setAttribute('aria-live','assertive');t.setAttribute('aria-atomic','true');
                                                                                        t.innerHTML='<div class="{{ VC::DFL }}"><div class="toast-body"></div><button type="button" class="{{ VC::BT_CL }} btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="{{ __('Close') }}"></button></div>';
                                                                                        document.body.appendChild(t);
                                                                                    }
                                                                                    t.querySelector('.toast-body').textContent=msg;
                                                                                    new bootstrap.Toast(t,{delay:4000}).show();
                                                                                }else{ alert(msg); }
                                                                            }catch(e){ alert(msg); }
                                                                        }
                                                                        var delBtn=document.getElementById('{{ $destroyBtnId }}');
                                                                        if(delBtn && delBtn.getAttribute('data-listener-active')!=='true'){
                                                                            delBtn.setAttribute('data-listener-active','true');
                                                                            delBtn.addEventListener('click',function(e){
                                                                                var url=(delBtn.getAttribute('data-url')||'').trim();
                                                                                if(!url || url==='#'){ e.preventDefault(); toastOrAlert(delBtn.getAttribute('data-guard-msg')||'#'); }
                                                                            });
                                                                        }
                                                                    }catch(_){}
                                                                })();
                                                            </script>
                                                        @endpush
                                                    @endcan
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6">
                                            <div class="{{ VC::TXCT }} font-style">{{ __('No coupons available') }}</div>
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
