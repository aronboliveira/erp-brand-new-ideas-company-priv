@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth,Route};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Bank Account')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Bank Account')}}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @can('create bank account')
            @php
                $bankAccountCreateRoute = Route::has(ViewsConstants::BNK_ACC.'.create')
                    ? route(ViewsConstants::BNK_ACC.'.create')
                    : Route::has(Str::kebab(ViewsConstants::BNK_ACC.'.create'))
                        ? route(Str::kebab(ViewsConstants::BNK_ACC.'.create'))
                        : '#';
                $createLinkId = 'bank-account-create-link';
                $createMsg = Utility::fetchLinkMessage(
                    $lang,
                    ViewsConstants::BNK_ACC,
                    'bank_account_create_route_unavailable'
                ) ?? 'Create New Bank Account route is unavailable. Please contact technical support or your domain administrator.';
                $guardIds = [$createLinkId];
            @endphp
            <a
                id="{{ $createLinkId }}"
                href="#"
                data-url="{{ $bankAccountCreateRoute }}"
                data-sv-localized="true"
                data-guard-msg="{{ $createMsg }}"
                data-ajax-popup="true"
                data-size="lg"
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
                data-title="{{ __('Create New Bank Account') }}"
                class="{{ ViewClassNamesConstants::BT_SM_PM }}"
            >
                <i class="{{ ViewClassNamesConstants::TI_PLS }}"></i>
            </a>
            
            @push(StacksConstants::ADM_SCR_PG)
                <script defer>
                    (() => {
                        const ids = {!! json_encode($guardIds) !!};
                        const flagAttr = 'data-listener-active';
                        ids.forEach(id => {
                            const el = document.getElementById(id);
                            if (!el || el.getAttribute(flagAttr) === 'true') return;
                            el.setAttribute(flagAttr, 'true');
                            el.addEventListener('click', event => {
                                try {
                                    const url = el.getAttribute('data-url');
                                    const href = el.href;
                                    if ((!url || url === '#') && (!href || href === '#')) {
                                        event.preventDefault();
                                        const msg = el.getAttribute('data-guard-msg') ?? '# ERROR';
                                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                                        let container = document.getElementById('toast-container');
                                        if (!container) {
                                            container = document.createElement('div');
                                            container.id = 'toast-container';
                                            document.body.appendChild(container);
                                        }
                                        if (bootstrapLink && window.bootstrap) {
                                            const toastEl = document.createElement('div');
                                            toastEl.className = 'toast';
                                            toastEl.setAttribute('role', 'alert');
                                            toastEl.setAttribute('aria-live', 'assertive');
                                            toastEl.setAttribute('aria-atomic', 'true');
                                            const body = document.createElement('div');
                                            body.className = 'toast-body';
                                            body.textContent = msg;
                                            toastEl.appendChild(body);
                                            container.appendChild(toastEl);
                                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                                        } else {
                                            alert(msg);
                                        }
                                        el.setAttribute('data-failed-route', 'true');
                                    }
                                } catch {}
                            });
                            const observer = new MutationObserver(() => {
                                if (!document.getElementById(id)) observer.disconnect();
                            });
                            observer.observe(document.body, { childList: true, subtree: true });
                        });
                    })();
                </script>
            @endpush
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="{{ ViewClassNamesConstants::RW }}">
        <div class="{{ ViewClassNamesConstants::C12 }}">
            <div class="{{ ViewClassNamesConstants::CD }}">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="{{ ViewClassNamesConstants::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Chart Of Account') }}</th>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Bank') }}</th>
                                    <th>{{ __('Account Number') }}</th>
                                    <th>{{ __('Current Balance') }}</th>
                                    <th>{{ __('Contact Number') }}</th>
                                    <th>{{ __('Bank Branch') }}</th>
                                    <th width="10%">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($accounts as $account)
                                    <tr class="font-style">
                                        <td>{{ $account->chartAccount->name ?? '-' }}</td>
                                        <td>{{ $account->holder_name }}</td>
                                        <td>{{ $account->bank_name }}</td>
                                        <td>{{ $account->account_number }}</td>
                                        <td>{{ $user->priceFormat($account->opening_balance) }}</td>
                                        <td>{{ $account->contact_number }}</td>
                                        <td>{{ $account->bank_address }}</td>
                                        @if(Gate::check('edit bank account') || Gate::check('delete bank account'))
                                            <td class="Action">
                                                <span>
                                                    @if($account->holder_name!='Cash')
                                                        @can('edit bank account')
                                                            <div class="{{ ViewClassNamesConstants::ACT_BTN }} {{ ViewClassNamesConstants::BG_P }} {{ ViewClassNamesConstants::MS2 }}">
                                                                <a href="#"
                                                                   class="{{ ViewClassNamesConstants::BT_SM_MX3 }} {{ ViewClassNamesConstants::AL_IT_CT }}"
                                                                   data-url="{{ route(ViewsConstants::BNK_ACC.'.edit',$account->id) }}"
                                                                   data-ajax-popup="true"
                                                                   data-title="{{ __('Edit Bank Account') }}"
                                                                   data-bs-toggle="tooltip"
                                                                   data-size="lg"
                                                                   title="{{ __('Edit') }}">
                                                                    <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                                                </a>
                                                            </div>
                                                        @endcan
                                                        @can('delete bank account')
                                                            <div class="{{ ViewClassNamesConstants::ACT_BTN_DNG_2 }}">
                                                                {!! Collective\Html\FormFacade::open([
                                                                    'method'=>'DELETE',
                                                                    'route'=>[ViewsConstants::BNK_ACC.'.destroy',$account->id],
                                                                    'id'=>'delete-form-'.$account->id
                                                                ]) !!}
                                                                <a href="#"
                                                                   class="{{ ViewClassNamesConstants::BT_SM_CT_PR }}"
                                                                   data-bs-toggle="tooltip"
                                                                   title="{{ __('Delete') }}"
                                                                   data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}"
                                                                   data-confirm-yes="document.getElementById('delete-form-{{$account->id}}').submit();">
                                                                    <i class="{{ ViewClassNamesConstants::TI_TRS_WT }}"></i>
                                                                </a>
                                                                {!! Collective\Html\FormFacade::close() !!}
                                                            </div>
                                                        @endcan
                                                    @else
                                                        -
                                                    @endif
                                                </span>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection