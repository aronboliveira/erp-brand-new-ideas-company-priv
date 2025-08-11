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
    {{__('Bank Balance Transfer')}}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Bank Balance Transfer')}}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        {{--        <a class="btn btn-sm btn-primary" data-bs-toggle="collapse" href="#multiCollapseExample1" role="button" aria-expanded="false" aria-controls="multiCollapseExample1" data-bs-toggle="tooltip" title="{{__('Filter')}}">--}}
        {{--            <i class="ti ti-filter"></i>--}}
        {{--        </a>--}}
        @can('create bank transfer')
        @php
            $bankTransferCreateUrl = Route::has(ViewsConstants::BNK_TRF.'.create')
                ? route(ViewsConstants::BNK_TRF.'.create')
                : '#';
            $createBankTransferMsg = Utility::fetchLinkMessage(
                $lang,
                [ViewsConstants::BNK_TRF, 'create_bank_transfer_unavailable']
            ) ?? 'Create bank transfer route is unavailable. Please contact technical support or your domain administrator.';
        @endphp
        <a
            href="{{ $bankTransferCreateUrl }}"
            data-url="{{ $bankTransferCreateUrl }}"
            data-ajax-popup="true"
            data-title="{{ __('Create Bank-Transfer') }}"
            data-unavailable-msg="{{ $createBankTransferMsg }}"
            data-create-listener-added="false"
            class="{{ ViewClassNamesConstants::BT_SM_PM }}"
            data-bs-toggle="tooltip"
            title="{{ __('Create') }}"
        >
            <i class="{{ ViewClassNamesConstants::TI_PLS }}"></i>
        </a>
        @endcan
    </div>
@endsection

@push(StacksConstants::ADM_SCR_PG) 
    <script defer>
        (() => {
            try {
                const btn = document.querySelector('a.{{ ViewClassNamesConstants::BT_SM_PM }}[data-ajax-popup]');
                const listenerAttr = 'data-create-listener-added';
                if (btn && btn.getAttribute(listenerAttr) !== 'true') {
                    btn.setAttribute(listenerAttr, 'true');
                    btn.addEventListener('click', (e) => {
                        e.preventDefault();
                        const url = btn.getAttribute('data-url');
                        const href = btn.getAttribute('href');
                        if ((!url || url === '#') && (!href || href === '#')) {
                            const msg = btn.getAttribute('data-unavailable-msg');
                            const toastEl = document.querySelector('.toast');
                            if (
                                toastEl
                                && window.bootstrap
                                && typeof bootstrap.Toast === 'function'
                            ) {
                                const toast = new bootstrap.Toast(toastEl);
                                const body = toastEl.querySelector('.toast-body');
                                if (body) {
                                    body.textContent = msg;
                                }
                                toast.show();
                            } else {
                                alert(msg);
                            }
                            btn.setAttribute('data-failed-route', 'true');
                            return;
                        }
                        window.location.href = url;
                    });
                }
            } catch (error) {
            }
        })();
    </script>
@endpush

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Collective\Html\FormFacade::open(array('route' => array(ViewsConstants::BNK_TRF.'.index'),'method' => 'GET','id'=>'transfer_form')) }}
                        <div class="row align-items-center justify-content-end">
                            <div class="col-xl-10">
                                <div class="row">
                                    <div class="col-3">
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 month">
                                        <div class="btn-box">
                                            {{ Collective\Html\FormFacade::label('date', __('Date'),['class'=>'form-label'])}}
                                            {{ Collective\Html\FormFacade::text('date', isset($_GET['date'])?$_GET['date']:null, array('class' => 'form-control month-btn','id'=>'pc-daterangepicker-1','readonly')) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 date">
                                        <div class="btn-box">
                                            {{ Collective\Html\FormFacade::label('f_account', __('From Account'),['class'=>'form-label'])}}
                                            {{ Collective\Html\FormFacade::select('f_account',$account,isset($_GET['f_account'])?$_GET['f_account']:'', array('class' => 'form-control select')) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Collective\Html\FormFacade::label('t_account', __('To Account'),['class'=>'form-label'])}}
                                            {{ Collective\Html\FormFacade::select('t_account', $account,isset($_GET['t_account'])?$_GET['t_account']:'', array('class' => 'form-control select')) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto mt-4">
                                <div class="row">
                                    <div class="col-auto">
                                        <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('transfer_form').submit(); return false;" data-bs-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>
                                        <a href="{{route(ViewsConstants::BNK_TRF.'.index')}}" class="btn btn-sm btn-danger" data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off"></i></span>
                                        </a>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    {{ Collective\Html\FormFacade::close() }}
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <h5></h5>
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th> {{__('Date')}}</th>
                                <th> {{__('From Account')}}</th>
                                <th> {{__('To Account')}}</th>
                                <th> {{__('Amount')}}</th>
                                <th> {{__('Reference')}}</th>
                                <th> {{__('Description')}}</th>
                                @if(Gate::check('edit transfer') || Gate::check('delete transfer'))
                                    <th width="10%"> {{__('Action')}}</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($transfers as $transfer)
                                <tr class="font-style">
                                    <td>{{ $user?->dateFormat( $transfer->date) }}</td>
                                    <td>{{ !empty($transfer->fromBankAccount())? $transfer->fromBankAccount()->bank_name.' '.$transfer->fromBankAccount()->holder_name:''}}</td>
                                    <td>{{!empty( $transfer->toBankAccount())? $transfer->toBankAccount()->bank_name.' '. $transfer->toBankAccount()->holder_name:''}}</td>
                                    <td>{{ $user?->priceFormat( $transfer->amount)}}</td>
                                    <td>{{  $transfer->reference}}</td>
                                    <td>{{  $transfer->description}}</td>
                                    @if(Gate::check('edit transfer') || Gate::check('delete transfer'))
                                        <td class="Action">
                                            <span>
                                            @can('edit transfer')
                                                    <div class="action-btn bg-primary ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center" data-url="{{ route(ViewsConstants::BNK_TRF.'.edit',$transfer->id) }}" data-ajax-popup="true" title="{{__('Edit')}}" data-title="{{__('Edit Transfer')}}" data-bs-toggle="tooltip" data-original-title="{{__('Edit')}}">
                                                            <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('delete transfer')
                                                    <div class="{{ ViewClassNamesConstants::ACT_BTN_DNG_2 }}">
                                                    {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'route' => [ViewsConstants::BNK_TRF.'.destroy', $transfer->id],'id'=>'delete-form-'.$transfer->id]) !!}
                                                        <a href="#" class="{{ ViewClassNamesConstants::BT_SM_CT_PR }}" data-bs-toggle="tooltip" data-original-title="{{__('Delete')}}" title="{{__('Delete')}}" data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}" data-confirm-yes="document.getElementById('delete-form-{{$transfer->id}}').submit();">
                                                            <i class="ti ti-trash text-white"></i>
                                                        </a>
                                                    {!! Collective\Html\FormFacade::close() !!}
                                                    </div>
                                                @endcan
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

