@php
    try {
$user    = Auth::user();
        $lang    = Utility::fetchUserLang(user: $user);
        $profile = Utility::getFile('uploads/avatar');

        $avatar = (string) (data_get($userDetail ?? null, UC::COL_AV) ?? __('No details available'));
        $name   = (string) (data_get($userDetail ?? null, UC::COL_NM) ?? __('No name available'));
        $type   = (string) (data_get($userDetail ?? null, UC::COL_TP) ?? __('No user type available'));
        $email  = (string) (data_get($userDetail ?? null, UC::COL_EM) ?? __('No email available'));

        $tabsRaw = $tabs ?? [
            [
                'id'      => 'personal-info',
                'label'   => __('Personal Info'),
                'base'    => VW::VND . '.update.profile',
                'method'  => 'post',
                'enctype' => 'multipart/form-data',
                'guard'   => Utility::fetchLinkMessage($lang, VW::VND, 'update_profile_route_unavailable') ?? 'Update profile route is unavailable. Please contact technical support or your domain administrator.',
                'fields'  => [
                    ['name'=>'name','type'=>'text','label'=>__('Name'),'cols'=>6,'attrs'=>['class'=>'form-control font-style','placeholder'=>__('Enter User Name')],'error'=>'name'],
                    ['name'=>'email','type'=>'text','label'=>__('Email'),'cols'=>6,'attrs'=>['class'=>'form-control','placeholder'=>__('Enter User Email')],'error'=>'email'],
                    ['name'=>'contact','type'=>'text','label'=>__('Contact'),'cols'=>6,'attrs'=>['class'=>'form-control','placeholder'=>__('Enter User Contact')],'error'=>'contact'],
                    ['name'=>'profile','type'=>'file','label'=>__('Choose file here'),'cols'=>6,'fileWrapper'=>true,'fileInput'=>['id'=>'avatar','data-filename'=>'profiles']],
                ],
            ],
            [
                'id'      => 'billing-info',
                'label'   => __('Billing Info'),
                'base'    => VW::VND . '.update.billing.info',
                'method'  => 'post',
                'guard'   => Utility::fetchLinkMessage($lang, VW::VND, 'update_billing_info_route_unavailable') ?? 'Update billing info route is unavailable. Please contact technical support or your domain administrator.',
                'fields'  => [
                    ['name'=>'billing_name','type'=>'text','label'=>__('Billing Name'),'cols'=>4,'attrs'=>['class'=>'form-control','placeholder'=>__('Enter Billing Name')],'error'=>'billing_name'],
                    ['name'=>'billing_phone','type'=>'text','label'=>__('Billing Phone'),'cols'=>4,'attrs'=>['class'=>'form-control','placeholder'=>__('Enter Billing Phone')],'error'=>'billing_phone'],
                    ['name'=>'billing_zip','type'=>'text','label'=>__('Billing Zip'),'cols'=>4,'attrs'=>['class'=>'form-control','placeholder'=>__('Enter Billing Zip')],'error'=>'billing_zip'],
                    ['name'=>'billing_country','type'=>'text','label'=>__('Billing Country'),'cols'=>4,'attrs'=>['class'=>'form-control','placeholder'=>__('Enter Billing Country')],'error'=>'billing_country'],
                    ['name'=>'billing_state','type'=>'text','label'=>__('Billing State'),'cols'=>4,'attrs'=>['class'=>'form-control','placeholder'=>__('Enter Billing State')],'error'=>'billing_state'],
                    ['name'=>'billing_city','type'=>'text','label'=>__('Billing City'),'cols'=>4,'attrs'=>['class'=>'form-control','placeholder'=>__('Enter Billing City')],'error'=>'billing_city'],
                    ['name'=>'billing_address','type'=>'textarea','label'=>__('Billing Address'),'cols'=>12,'attrs'=>['class'=>'form-control','rows'=>3,'placeholder'=>__('Enter Billing Address')],'error'=>'billing_address'],
                ],
            ],
            [
                'id'      => 'shipping-info',
                'label'   => __('Shipping Info'),
                'base'    => VW::VND . '.update.shipping.info',
                'method'  => 'post',
                'guard'   => Utility::fetchLinkMessage($lang, VW::VND, 'update_shipping_info_route_unavailable') ?? 'Update shipping info route is unavailable. Please contact technical support or your domain administrator.',
                'fields'  => [
                    ['name'=>'shipping_name','type'=>'text','label'=>__('Shipping Name'),'cols'=>4,'attrs'=>['class'=>'form-control','placeholder'=>__('Enter Shipping Name')],'error'=>'shipping_name'],
                    ['name'=>'shipping_phone','type'=>'text','label'=>__('Shipping Phone'),'cols'=>4,'attrs'=>['class'=>'form-control','placeholder'=>__('Enter Shipping Phone')],'error'=>'shipping_phone'],
                    ['name'=>'shipping_zip','type'=>'text','label'=>__('Shipping Zip'),'cols'=>4,'attrs'=>['class'=>'form-control','placeholder'=>__('Enter Shipping Zip')],'error'=>'shipping_zip'],
                    ['name'=>'shipping_country','type'=>'text','label'=>__('Shipping Country'),'cols'=>4,'attrs'=>['class'=>'form-control','placeholder'=>__('Enter Shipping Country')],'error'=>'shipping_country'],
                    ['name'=>'shipping_state','type'=>'text','label'=>__('Shipping State'),'cols'=>4,'attrs'=>['class'=>'form-control','placeholder'=>__('Enter Shipping State')],'error'=>'shipping_state'],
                    ['name'=>'shipping_city','type'=>'text','label'=>__('Shipping City'),'cols'=>4,'attrs'=>['class'=>'form-control','placeholder'=>__('Enter Shipping City')],'error'=>'shipping_city'],
                    ['name'=>'shipping_address','type'=>'textarea','label'=>__('Shipping Address'),'cols'=>12,'attrs'=>['class'=>'form-control','rows'=>3,'placeholder'=>__('Enter Shipping Address')],'error'=>'shipping_address'],
                ],
            ],
            [
                'id'      => 'change-password',
                'label'   => __('Change Password'),
                'base'    => VW::VND . '.update.password',
                'method'  => 'post',
                'params'  => [(string) (data_get($userDetail ?? null, 'id') ?? '')],
                'guard'   => Utility::fetchLinkMessage($lang, VW::VND, 'update_password_route_unavailable') ?? 'Update password route is unavailable. Please contact technical support or your domain administrator.',
                'fields'  => [
                    ['name'=>'current_password','type'=>'password','label'=>__('Current Password'),'cols'=>6,'attrs'=>['class'=>'form-control','placeholder'=>__('Enter Current Password')],'error'=>'current_password'],
                    ['name'=>'new_password','type'=>'password','label'=>__('New Password'),'cols'=>6,'attrs'=>['class'=>'form-control','placeholder'=>__('Enter New Password')],'error'=>'new_password'],
                    ['name'=>'confirm_password','type'=>'password','label'=>__('Re-type New Password'),'cols'=>6,'attrs'=>['class'=>'form-control','placeholder'=>__('Enter Re-type New Password')],'error'=>'confirm_password'],
                ],
            ],
        ];

        $tabsList = Utility::isFilled($tabsRaw) ? (array ?? []) $tabsRaw : [];
        $tabs = [];
        foreach ($tabsList as $t) {
            $id     = (string) (data_get($t, 'id') ?? '');
            $label  = (string) (data_get($t, 'label') ?? __('No label available'));
            $base   = (string) (data_get($t, 'base') ?? '');
            $method = (string) (data_get($t, 'method') ?? 'post');
            if ($id === '' || $base === '') continue;

            $kebab   = Str::kebab($base);
            $name    = Route::has($base) ? $base : (Route::has($kebab) ? $kebab : null);
            $params  = (array) (data_get($t, 'params', []));
            $hasVoid = !empty($params) && in_array('', array_map(static fn($v) => (string) $v, $params), true);
            $url     = $name && !$hasVoid ? route($name, $params) : '#';

            $guard = (string) (data_get($t, 'guard') ?? 'This action route is unavailable. Please contact technical support or your domain administrator.');
            $enct  = data_get($t, 'enctype');
            $fields = (array) (data_get($t, 'fields', []));

            $tabs[] = [
                'id'      => $id,
                'label'   => $label,
                'url'     => $url,
                'guard'   => $guard,
                'method'  => $method,
                'enctype' => $enct,
                'fields'  => $fields,
                'form_id' => 'profile-tab-form-' . $id,
            ];
        }
    } catch (\Throwable $e) {
        \Log::error('vendors/profile — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@extends(EL::ADM)

@section(YW::ADM_PG_TTL)
    {{ __('Profile Account') }}
@endsection

@section(YW::ADM_CTT)
    <div class="row">
        <div class="{{ VC::CXL3 }} {{ VC::CLM4 }} {{ VC::CS12 }}">
            <div class="card profile-card">
                <div class="icon-user {{ VC::AV_CC }}">
                    <img alt="{{ $name }}" src="{{ $avatar !== '' ? ($profile . '/' . $avatar) : ($profile . '/avatar.png') }}">
                </div>
                <h4 class="h4 {{ VC::MB0 }} {{ VC::MT2 }}">{{ $name }}</h4>
                <div class="sal-right-card">
                    <span class="badge badge-pill badge-blue">{{ $type }}</span>
                </div>
                <h6 class="office-time {{ VC::MB0 }} {{ VC::MT4 }}">{{ $email }}</h6>
            </div>
        </div>

        <div class="col-xl-9 {{ VC::CL8 }} {{ VC::CM8 }} {{ VC::CS12 }}">
            <section class="{{ VC::CL12 }} pricing-plan card">
                <div class="our-system password-card p-3">
                    <div class="row">
                        @if(!empty($tabs))
                            <ul class="{{ VC::NAV_TB_MY4 }}">
                                @foreach($tabs as $tab)
                                    <li class="{{ !$loop->first ? 'annual-billing' : '' }}">
                                        <a data-toggle="tab" href="#{{ $tab['id'] }}" class="{{ $loop->first ? 'active' : '' }}">
                                            {{ $tab['label'] }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="tab-content {{ VC::W100 }}">
                                @foreach($tabs as $tab)
                                    <div id="{{ $tab['id'] }}" class="tab-pane fade {{ $loop->first ? 'in active show' : '' }}">
                                        {!! Form::model($userDetail, [
                                            'url'                  => $tab['url'],
                                            'method'               => $tab['method'],
                                            'enctype'              => $tab['enctype'] ?? null,
                                            'id'                   => $tab['form_id'],
                                            'data-resolved-action' => $tab['url'],
                                            'data-guard-msg'       => $tab['guard'],
                                            'data-sv-localized'    => 'true',
                                        ]) !!}
                                            <div class="row">
                                                @foreach((array) $tab['fields'] as $f)
                                                    @php
                                                        try {
                                                            $fname  = (string) (data_get($f, 'name') ?? __('Field to get name'));
                                                            $ftype  = (string) (data_get($f, 'type') ?? 'text');
                                                            $flabel = (string) (data_get($f, 'label') ?? __('No label available'));
                                                            $fcols  = (int) (data_get($f, 'cols') ?? 12);
                                                            $fattrs = (array) (data_get($f, 'attrs') ?? ['class'=>'form-control']);
                                                            $ferr   = (string) (data_get($f, 'error') ?? '');
                                                            $fileW  = (bool)  (data_get($f, 'fileWrapper') ?? false);
                                                            $fInp   = (array) (data_get($f, 'fileInput') ?? []);
                                                            $fid    = (string) (data_get($fInp, 'id') ?? 'file');
                                                            $fdfn   = (string) (data_get($fInp, 'data-filename') ?? 'upload_file');
                                                        } catch (\Throwable $e) {
                                                            \Log::error('vendors/profile — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                        }
@endphp
                                                    <div class="{{ VC::FM_G }} col-md-{{ $fcols }}">
                                                        {{ Form::label($fname !== '' ? $fname : 'field', $flabel, ['class'=>'form-label']) }}
                                                        @if($ftype === 'textarea')
                                                            {{ Form::textarea($fname, null, $fattrs) }}
                                                        @elseif($fileW === true)
                                                            <div class="choose-file">
                                                                <label for="{{ $fid }}">
                                                                    <div>{{ $flabel }}</div>
                                                                    <input type="file" name="{{ $fname }}" id="{{ $fid }}" class="{{ VC::FM_CT }}" data-filename="{{ $fdfn }}">
                                                                </label>
                                                                <p class="{{ $fdfn }}"></p>
                                                            </div>
                                                        @else
                                                            {{ Form::{$ftype}($fname, null, $fattrs) }}
                                                        @endif
                                                        @if($ferr !== '')
                                                            @error($ferr)
                                                                <span class="{{ VC::TX_DNG }}" role="alert"><strong>{{ $message }}</strong></span>
                                                            @enderror
                                                        @endif
                                                    </div>
                                                @endforeach

                                                @if(($tab['id'] ?? '') === 'personal-info' && !empty($customFields) && (method_exists($customFields, 'isEmpty') ? !$customFields->isEmpty() : (is_array($customFields ?? null) && !empty($customFields))))
                                                    <div class="{{ VC::CM6 }}">
                                                        <div class="{{ VC::TAB_FD_SH }}" id="tab-2" role="tabpanel">
                                                            @include(VW::CST_FD . '.formBuilder')
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="{{ VC::CL12 }} {{ VC::TX_END }} {{ VC::MT3 }}">
                                                <button type="submit" class="btn-create badge-blue">{{ __('Save Changes') }}</button>
                                            </div>

                                            <script>
                                                (() => {
                                                    try {
                                                        const f = document.getElementById(@json($tab['form_id']));
                                                        if (!f || f.getAttribute('data-listener-active') === 'true') return;
                                                        f.setAttribute('data-listener-active', 'true');
                                                        const resolved = f.getAttribute('data-resolved-action') || '#';
                                                        if ((f.getAttribute('action') === '#' || !f.getAttribute('action')) && resolved !== '#') {
                                                            f.setAttribute('action', resolved);
                                                        }
                                                        f.addEventListener('submit', (e) => {
                                                            const action = f.getAttribute('action') || '#';
                                                            if (action && action !== '#') return;
                                                            e.preventDefault();
                                                            const msg = f.getAttribute('data-guard-msg') || 'This action route is unavailable. Please contact technical support or your domain administrator.';
                                                            let c = document.getElementById('toast-container');
                                                            if (!c) { c = document.createElement('div'); c.id = 'toast-container'; document.body.appendChild(c); }
                                                            const ok = document.querySelector('link[href*="bootstrap"]') && window.bootstrap && window.bootstrap.Toast;
                                                            if (ok) {
                                                                const t = document.createElement('div');
                                                                t.className = 'toast';
                                                                t.setAttribute('role', 'alert');
                                                                t.setAttribute('aria-live', 'assertive');
                                                                t.setAttribute('aria-atomic', 'true');
                                                                const b = document.createElement('div');
                                                                b.className = 'toast-body';
                                                                b.textContent = msg;
                                                                t.appendChild(b);
                                                                c.appendChild(t);
                                                                try { window.bootstrap.Toast.getOrCreateInstance(t).show(); } catch { alert(msg); }
                                                            } else {
                                                                alert(msg);
                                                            }
                                                            f.setAttribute('data-failed-route', 'true');
                                                        });
                                                    } catch {}
                                                })();
                                            </script>
                                        {!! Form::close() !!}
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="{{ VC::C12 }}">
                                <p class="{{ VC::TXCT_MT }}">{{ __('No profile sections available') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
