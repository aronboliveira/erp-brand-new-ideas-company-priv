@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants,
        YieldingConstants
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth,Route};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user)
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Journal Detail')}}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item"><a href="{{route(ViewsConstants::JRN_ET.'.index')}}">{{__('Journal Entry')}}</a></li>
    <li class="breadcrumb-item">{{ $user?->journalNumberFormat($journalEntry->journal_id) }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="invoice">
                        <div class="invoice-print">
                            <div class="row invoice-title mt-2">
                                <div class="col-xs-12 col-sm-12 col-nd-6 col-lg-6 col-12">
                                    <h2>{{__('Journal')}}</h2>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-nd-6 col-lg-6 col-12 text-end">
                                    <h3 class="invoice-number">{{ $user?->journalNumberFormat($journalEntry->journal_id) }}</h3>
                                </div>
                                <div class="col-12">
                                    <hr>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="font-style">
                                        <strong>{{__('To')}} :</strong><br>
                                        {{!empty($settings['company_name'])?$settings['company_name']:''}}<br>
                                        {{!empty($settings['company_telephone'])?$settings['company_telephone']:''}}<br>
                                        {{!empty($settings['company_address'])?$settings['company_address']:''}}<br>
                                        {{!empty($settings['company_city'])?$settings['company_city']:'' .', '}}  {{!empty($settings['company_state'])?$settings['company_state']:'' .', '}}  {{!empty($settings['company_country'])?$settings['company_country']:'' .'.'}}
                                    </small>
                                </div>
                                <div class="col-md-6 text-end">
                                    <small>
                                        <strong>{{__('Journal No')}} :</strong>
                                        {{$user?->journalNumberFormat($journalEntry->journal_id)}}
                                    </small><br>
                                    <small>
                                        <strong>{{__('Journal Ref')}} :</strong>
                                        {{$journalEntry->reference}}
                                    </small> <br>
                                    <small>
                                        <strong>{{__('Journal Date')}} :</strong>
                                        {{$user?->dateFormat($journalEntry->date)}}
                                    </small>
                                </div>
                            </div>
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <div class="font-weight-bold">{{__('Journal Account Summary')}}</div>
                                    <div class="table-responsive mt-2">
                                        <table class="table mb-0 ">
                                            <tr>
                                                <th data-width="40" class="text-dark">#</th>
                                                <th class="text-dark">{{__('Account')}}</th>
                                                <th class="text-dark" width="25%">{{__('Description')}}</th>
                                                <th class="text-dark">{{__('Debit')}}</th>
                                                <th class="text-dark">{{__('Credit')}}</th>
                                                <th class="text-dark">{{__('Amount')}}</th>
                                                <th></th>
                                            </tr>
                                            @foreach($accounts as $key =>$account)
                                                <tr>
                                                    <td>{{$key+1}}</td>
                                                    <td>{{!empty($account->accounts)?$account->accounts->code.' - '.$account->accounts->name:''}}</td>
                                                    <td>{{!empty($account->description)?$account->description:'-'}}</td>
                                                    <td>{{$user?->priceFormat($account->debit)}}</td>
                                                    <td>{{$user?->priceFormat($account->credit)}}</td>
                                                    <td >
                                                        @if($account->debit!=0)
                                                            {{$user?->priceFormat($account->debit)}}
                                                        @else
                                                            {{$user?->priceFormat($account->credit)}}
                                                        @endif
                                                    </td>
                                                    @php
                                                        $linkId = 'delete-link-'.$account->id;
                                                        $formId = 'delete-form-'.$account->id;
                                                    @endphp
                                                    <td>
                                                        <div class="{{ ViewClassNamesConstants::ACT_BTN_DNG_2 }}">
                                                            {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'route' => Route::has(ViewsConstants::JRN.'.destroy') ? [ViewsConstants::JRN.'.destroy', $account->id] : ['#'],'id'=>'{{ $formId }}']) !!}
                                                            <a href="#" class="{{ ViewClassNamesConstants::TRS_PARA }}" data-bs-toggle="tooltip" id="{{ $linkId }} " title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}" data-confirm-yes="document.getElementById('{{ $formId }}').submit();">
                                                                <i class="{{ ViewClassNamesConstants::TI_TRS_WT }}"></i>
                                                            </a>
                                                            {!! Collective\Html\FormFacade::close() !!}
                                                        </div>
                                                        @push(StacksConstants::ADM_SCR_PG)
                                                            <script defer>
                                                                (() => {
                                                                    const link = document.getElementById('{{ $linkId }}'),
                                                                    alias = 'data-listening-deleteclick';
                                                                    if (!link.hasAttribute(alias)) {
                                                                        link.addEventListener('click', event => {
                                                                            if (link.getAttribute(alias) !== 'true') return;
                                                                            const form = document.getElementById('{{ $formId }}');
                                                                            const dataAction = form.getAttribute('data-action');
                                                                            const url = form.getAttribute('data-url');
                                                                            const action = form.getAttribute('action');
                                                                            if ((!dataAction || dataAction === "#") && (!url || url === "#") && (!action || action === '#')) {
                                                                                const hasBS = Array.from(document.scripts)
                                                                                    .some(s => s.src && s.src.includes('bootstrap.min.js') && window.bootstrap && typeof window.bootstrap.Modal === 'function');
                                                                                const msg = 'Delete route is unavailable. Please contact technical support or your domain administrator.';
                                                                                if (hasBS) {
                                                                                    const wrapper = document.createElement('div');
                                                                                    wrapper.innerHTML = `
                                                                                        <div class="modal fade" tabindex="-1">
                                                                                            <div class="modal-dialog modal-sm">
                                                                                                <div class="modal-content">
                                                                                                    <div class="modal-header">
                                                                                                        <h5 class="modal-title">Error</h5>
                                                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                                                    </div>
                                                                                                    <div class="modal-body">
                                                                                                        <p>${msg}</p>
                                                                                                    </div>
                                                                                                    <div class="modal-footer">
                                                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>`;
                                                                                    document.body.appendChild(wrapper);
                                                                                    new window.bootstrap.Modal(wrapper.querySelector('.modal')).show();
                                                                                } else {
                                                                                    alert(msg);
                                                                                }
                                                                                return;
                                                                            }
                                                                        });
                                                                        link.setAttribute(alias, 'true');
                                                                    }
                                                                })();
                                                            </script>
                                                        @endpush
                                                    </td>
                                                </tr>
                                            @endforeach
                                            <tfoot>
                                            <tr>
                                                <td colspan="4"></td>
                                                <td><b>{{__('Total Credit')}}</b></td>
                                                <td>{{$user?->priceFormat($journalEntry->totalCredit())}}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="4"></td>
                                                <td><b>{{__('Total Debit')}}</b></td>
                                                <td>{{$user?->priceFormat($journalEntry->totalDebit())}}</td>
                                            </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    <div class="font-bold mt-2">
                                        {{__('Description')}} : <br>
                                    </div>
                                    <small>{{$journalEntry->description}}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
