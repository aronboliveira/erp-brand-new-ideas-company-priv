@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Crypt, Route};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
    $profile= Utility::getFile('uploads/avatar/');
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@push(StacksConstants::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/routes/customers/index.js') }}"></script>
@endpush
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Customers')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Customer')}}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
    @php
        $importRoute = Route::has(ViewsConstants::CST . '.file.import')
            ? route(ViewsConstants::CST . '.file.import')
            : '#';
        $importGuardMsg = Utility::fetchLinkMessage(
            $lang,
            ViewsConstants::CST,
            'customers_import_route_unavailable'
        ) ?? 'Customer CSV import route is unavailable. Please contact technical support or your domain administrator.';
        $exportRoute = Route::has(ViewsConstants::CST . '.export')
            ? route(ViewsConstants::CST . '.export')
            : '#';
        $exportGuardMsg = Utility::fetchLinkMessage(
            $lang,
            ViewsConstants::CST,
            'customers_export_route_unavailable'
        ) ?? 'Customer export route is unavailable. Please contact technical support or your domain administrator.';
        $createRoute = Route::has(ViewsConstants::CST . '.create')
            ? route(ViewsConstants::CST . '.create')
            : '#';
        $createGuardMsg = Utility::fetchLinkMessage(
            $lang,
            ViewsConstants::CST,
            'customers_create_route_unavailable'
        ) ?? 'Customer create route is unavailable. Please contact technical support or your domain administrator.';
    @endphp
    <div class="{{ VC::FEND }}">
        <a
            id="customer-import-btn"
            href="{{ $importRoute }}"
            data-url="{{ $importRoute }}"
            data-guard-msg="{{ $importGuardMsg }}"
            data-size="md"
            data-ajax-popup="true"
            data-bs-toggle="tooltip"
            title="{{ __('Import') }}"
            data-title="{{ __('Import customer CSV file') }}"
            class="{{ VC::BT_SM_PM }}"
        >
            <i class="{{ VC::TI_IMP }}"></i>
        </a>

        <a
            id="customer-export-btn"
            href="{{ $exportRoute }}"
            data-url="{{ $exportRoute }}"
            data-guard-msg="{{ $exportGuardMsg }}"
            data-bs-toggle="tooltip"
            title="{{ __('Export') }}"
            class="{{ VC::BT_SM_PM }}"
        >
            <i class="{{ VC::TI_EXP }}"></i>
        </a>

        <a
            id="customer-create-btn"
            href="{{ $createRoute }}"
            data-url="{{ $createRoute }}"
            data-guard-msg="{{ $createGuardMsg }}"
            data-size="lg"
            data-ajax-popup="true"
            data-bs-toggle="tooltip"
            title="{{ __('Create') }}"
            data-title="{{ __('Create Customer') }}"
            class="{{ VC::BT_SM_PM }}"
        >
            <i class="{{ VC::TI_PLS }}"></i>
        </a>
    </div>
    @push(StacksConstants::ADM_SCR_PG)
        <script defer>
            (() => {
                const btn = document.getElementById('customer-import-btn');
                if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                btn.setAttribute('data-listener-active', 'true');
                btn.addEventListener('click', e => {
                    try {
                        const url = btn.getAttribute('data-url') || '#';
                        if (url !== '#') return;
                        e.preventDefault();
                        const msg = btn.getAttribute('data-guard-msg') || '# ERROR';
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
                        btn.setAttribute('data-failed-route', 'true');
                    } catch {}
                });
            })();
        </script>
        <script defer>
            (() => {
                const btn = document.getElementById('customer-export-btn');
                if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                btn.setAttribute('data-listener-active', 'true');
                btn.addEventListener('click', e => {
                    try {
                        const url = btn.getAttribute('data-url') || '#';
                        if (url !== '#') return;
                        e.preventDefault();
                        const msg = btn.getAttribute('data-guard-msg') || '# ERROR';
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
                        btn.setAttribute('data-failed-route', 'true');
                    } catch {}
                });
            })();
        </script>
        <script defer>
            (() => {
                const btn = document.getElementById('customer-create-btn');
                if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                btn.setAttribute('data-listener-active', 'true');
                btn.addEventListener('click', e => {
                    try {
                        const url = btn.getAttribute('data-url') || '#';
                        if (url !== '#') return;
                        e.preventDefault();
                        const msg = btn.getAttribute('data-guard-msg') || '# ERROR';
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
                        btn.setAttribute('data-failed-route', 'true');
                    } catch {}
                });
            })();
        </script>
    @endpush
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::C12 }}">
            <div class="{{ VC::CD }}">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Contact') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Balance') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if((is_array($customers) && count($customers)) || ($customers instanceof Collection && $customers->isNotEmpty()))
                                    @foreach($customers as $customer)
                                        @php
                                            $ns = ViewsConstants::CST;
                                            $cid = isset($customer['id']) ? (string) $customer['id'] : '';
                                            $encId = $cid !== '' ? Crypt::encrypt($cid) : '';
                                            $showHref = $encId !== '' ? route("{$ns}.show", $encId) : '#';
                                            $showGuardMsg = Utility::fetchLinkMessage($lang, $ns, 'customers_show_route_unavailable') ?? __('Customer show route is unavailable. Please contact technical support or your domain administrator.');
                                            $isCustomerNumberFormatAvailable = method_exists($user, 'customerNumberFormat');
                                            $isPriceFormatAvailable = method_exists($user, 'priceFormat');
                                            $num = $customer['customer_id'] ?? null;
                                            $name = isset($customer['name']) && $customer['name'] !== '' ? $customer['name'] : __('No customer name available');
                                            $contact = isset($customer['contact']) && $customer['contact'] !== '' ? $customer['contact'] : __('No contact available');
                                            $email = isset($customer['email']) && $customer['email'] !== '' ? $customer['email'] : __('No email available');
                                            $balance = isset($customer['balance']) ? $customer['balance'] : null;
                                            $isActive = !empty($customer['is_active']);
                                        @endphp
                                        <tr class="cust_tr" data-url="{{ $showHref }}" data-id="{{ $cid }}">
                                            <td>
                                                @can('show customer')
                                                    <a id="customer-show-btn-{{ $cid }}" href="{{ $showHref }}" data-url="{{ $showHref }}" data-guard-msg="{{ $showGuardMsg }}" class="{{ VC::BT_OUTPM }}">
                                                        {{ $num !== null ? ($isCustomerNumberFormatAvailable ? $user?->customerNumberFormat($num) : __('Failed to format customer number')) : __('No customer number available') }}
                                                    </a>
                                                @else
                                                    <a id="customer-show-btn-{{ $cid }}" href="#" data-url="#" data-guard-msg="{{ $showGuardMsg }}" class="{{ VC::BT_OUTPM }}">
                                                        {{ $num !== null ? ($isCustomerNumberFormatAvailable ? $user?->customerNumberFormat($num) : __('Failed to format customer number')) : __('No customer number available') }}
                                                    </a>
                                                @endcan
                                            </td>
                                            <td class="font-style">{{ $name }}</td>
                                            <td>{{ $contact }}</td>
                                            <td>{{ $email }}</td>
                                            <td>{{ is_numeric($balance) ? ($isPriceFormatAvailable ? $user?->priceFormat($balance) : __('Failed to format balance')) : __('No balance available') }}</td>
                                            <td class="action">
                                                @if($isActive)
                                                    @can('show customer')
                                                        <div class="{{ VC::ACT_BTN_INF }}">
                                                            <a id="customer-view-btn-{{ $cid }}" href="{{ $showHref }}" data-url="{{ $showHref }}" data-guard-msg="{{ $showGuardMsg }}" class="{{ VC::BT_SM_FL_CT }}" data-bs-toggle="tooltip" title="{{ __('View') }}">
                                                                <i class="{{ VC::TI_EYE_WT }}"></i>
                                                            </a>
                                                        </div>
                                                    @endcan
                                                    @can('edit customer')
                                                        @php
                                                            $editHref = $cid !== '' ? route("{$ns}.edit", $cid) : '#';
                                                            $editGuardMsg = Utility::fetchLinkMessage($lang, $ns, 'customers_edit_route_unavailable') ?? __('Customer edit route is unavailable. Please contact technical support or your domain administrator.');
                                                        @endphp
                                                        <div class="{{ VC::ACT_BTN_PRIM }}">
                                                            <a id="customer-edit-btn-{{ $cid }}" href="{{ $editHref }}" data-url="{{ $editHref }}" data-guard-msg="{{ $editGuardMsg }}" data-ajax-popup="true" data-size="lg" class="{{ VC::BT_SM_FL_CT }}" data-bs-toggle="tooltip" title="{{ __('Edit') }}" data-title="{{ __('Edit Customer') }}">
                                                                <i class="{{ VC::TI_PC_WT }}"></i>
                                                            </a>
                                                        </div>
                                                    @endcan
                                                    @can('delete customer')
                                                        @php
                                                            $destroyHref = $cid !== '' ? route("{$ns}.destroy", $cid) : '#';
                                                            $destroyGuardMsg = Utility::fetchLinkMessage($lang, $ns, 'customers_destroy_route_unavailable') ?? __('Customer delete route is unavailable. Please contact technical support or your domain administrator.');
                                                            $delFormId = 'delete-form-' . $cid;
                                                        @endphp
                                                        <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                            {!! Form::open(['method' => 'DELETE', 'url' => $destroyHref, 'id' => $delFormId]) !!}
                                                                <a id="customer-delete-btn-{{ $cid }}" href="#" data-url="{{ $destroyHref }}" data-guard-msg="{{ $destroyGuardMsg }}" class="{{ VC::BT_SM_CT_PR }}" data-bs-toggle="tooltip" title="{{ __('Delete') }}">
                                                                    <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                </a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    @endcan
                                                @else
                                                    <i class="ti ti-lock" title="{{ __('Inactive') }}"></i>
                                                @endif
                                            </td>
                                        </tr>
                                        @push(StacksConstants::ADM_SCR_PG)
                                            <script defer>
                                                (()=>{try{
                                                    function guardClick(anchor){
                                                        if(!anchor||anchor.getAttribute('data-listener-active')==='true')return;
                                                        anchor.setAttribute('data-listener-active','true');
                                                        anchor.addEventListener('click',e=>{
                                                            const url=(anchor.getAttribute('data-url')||'').trim();
                                                            if(url && url!=='#')return;
                                                            e.preventDefault();
                                                            const msg=anchor.getAttribute('data-guard-msg')||'#';
                                                            try{
                                                                if(window.bootstrap&&window.bootstrap.Toast){
                                                                    let t=document.getElementById('route-guard-toast');
                                                                    if(!t){
                                                                        t=document.createElement('div');
                                                                        t.id='route-guard-toast';
                                                                        t.className='toast align-items-center text-bg-danger border-0 position-fixed bottom-0 end-0 m-3';
                                                                        t.setAttribute('role','alert');t.setAttribute('aria-live','assertive');t.setAttribute('aria-atomic','true');
                                                                        t.innerHTML='<div class="d-flex"><div class="toast-body"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
                                                                        document.body.appendChild(t);
                                                                    }
                                                                    t.querySelector('.toast-body').textContent=msg;
                                                                    new bootstrap.Toast(t,{delay:4000}).show();
                                                                }else{ alert(msg); }
                                                            }catch(_){ alert(msg); }
                                                        });
                                                    }
                                                    ['customer-show-btn-{{ $cid }}','customer-view-btn-{{ $cid }}','customer-edit-btn-{{ $cid }}','customer-delete-btn-{{ $cid }}'].forEach(id=>guardClick(document.getElementById(id)));
                                                }catch(_){}})();
                                            </script>
                                        @endpush
                                    @endforeach
                                    @push(StacksConstants::ADM_SCR_PG)
                                        @can('show customer')
                                            <script defer src="{{ asset('assets/js/routes/customers/show.js') }}"></script>
                                        @endcan
                                        @can('edit customer')
                                            <script defer src="{{ asset('assets/js/routes/customers/edit.js') }}"></script>
                                        @endcan
                                        @can('delete customer')
                                            <script defer src="{{ asset('assets/js/routes/customers/delete.js') }}"></script>
                                        @endcan
                                    @endpush
                                @else
                                    <tr>
                                        <td colspan="6">
                                            <div class="text-center">
                                                {{ __('No customers found.') }}
                                            </div>
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