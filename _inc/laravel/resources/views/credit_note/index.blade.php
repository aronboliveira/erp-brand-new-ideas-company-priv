@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use Illuminate\Support\Facades\Route;
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Credit Notes')}}
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script>
        $(document).on('change', '#invoice', function () {

            var id = $(this).val();
            var url = "{{route(ViewsConstants::INV.'.get')}}";

            $.ajax({
                url: url,
                type: 'get',
                cache: false,
                data: {
                    'id': id,

                },
                success: function (data) {
                    $('#amount').val(data)
                },

            });

        })
    </script>
@endpush
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Credit Note')}}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @can('create credit note')
            <a href="#" data-url="{{ route(ViewsConstants::INV.'.custom.credit.note') }}"data-bs-toggle="tooltip" title="{{__('Create')}}" data-ajax-popup="true" data-title="{{__('Create New Credit Note')}}" class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style mt-2">
                    <h5></h5>
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th> {{__('Invoice')}}</th>
                                <th> {{__('Customer')}}</th>
                                <th> {{__('Date')}}</th>
                                <th> {{__('Amount')}}</th>
                                <th> {{__('Description')}}</th>
                                <th width="10%"> {{__('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($invoices as $invoice)
                                @if(!empty($invoice->creditNote))
                                    @foreach ($invoice->creditNote as $creditNote)
                                        <tr>
                                            <td class="Id">
                                                <a href="{{ route(ViewsConstants::INV.'.show',\Crypt::encrypt($creditNote->invoice)) }}" class="btn btn-outline-primary">{{ AUth::user()->invoiceNumberFormat($invoice->invoice_id) }}</a>
                                            </td>
                                            <td>{{ (!empty($invoice->customer)?$invoice->customer->name:'-') }}</td>
                                            <td>{{ Auth::user()->dateFormat($creditNote->date) }}</td>
                                            <td>{{ Auth::user()->priceFormat($creditNote->amount) }}</td>
                                            <td>{{!empty($creditNote->description)?$creditNote->description:'-'}}</td>
                                            <td>
                                                @can('edit credit note')
                                                    <div class="action-btn bg-primary ms-2">
                                                        <a data-url="{{ route(ViewsConstants::INV.'.edit.credit.note',[$creditNote->invoice,$creditNote->id]) }}" data-ajax-popup="true" data-title="{{__('Edit Credit Note')}}" href="#" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                                                            <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('edit credit note')
                                                        <div class="action-btn bg-danger ms-2">
                                                            {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'route' => array(ViewsConstants::INV.'.delete.credit.note', $creditNote->invoice,$creditNote->id),'class'=>'delete-form-btn','id'=>'delete-form-'.$creditNote->id]) !!}
                                                                <a href="#" class="{{ ViewClassNamesConstants::BT_SM_CT_PR }}" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$creditNote->id}}').submit();">
                                                                    <i class="ti ti-trash text-white"></i>
                                                                </a>
                                                            {!! Collective\Html\FormFacade::close() !!}
                                                        </div>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
