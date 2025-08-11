@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use Illuminate\Support\Facades\{Auth, Crypt, Route};
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@push(StacksConstants::ADM_SCR_PG)
@endpush
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Customer-Detail')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item"><a href="{{route('customer.index')}}">{{__('Customer')}}</a></li>
    <li class="breadcrumb-item">{{$customer['name']}}</li>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script>
        function copyToClipboard(element) {
            var copyText = element.id;
            navigator.clipboard.writeText(copyText);
            // document.addEventListener('copy', function (e) {
            //     e.clipboardData.setData('text/plain', copyText);
            //     e.preventDefault();
            // }, true);
            //
            // document.execCommand('copy');
            show_toastr('success', 'Url copied to clipboard', 'success');
        }
    </script>
@endpush

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @can('create invoice')
            <a href="{{ route(ViewsConstants::INV.'.create',$customer->id) }}" class="btn btn-sm btn-primary">
                {{__('Create Invoice')}}
            </a>
        @endcan
        @can('create proposal')
            <a href="{{ route(ViewsConstants::PPS . '.create',$customer->id) }}" class="btn btn-sm btn-primary">
                {{__('Create Proposal')}}
            </a>
        @endcan
        @can('edit customer')
            <a href="#" data-size="lg" data-url="{{ route('customer.edit',$customer['id']) }}" data-ajax-popup="true" title="{{__('Edit Customer')}}" data-bs-toggle="tooltip" data-original-title="{{__('Edit')}}" class="btn btn-sm btn-primary">
                <i class="ti ti-pencil"></i>
            </a>
        @endcan
        @can('delete customer')
            {!! Collective\Html\FormFacade::open(['method' => 'DELETE','class' => 'delete-form-btn', 'route' => ['customer.destroy', $customer['id']]]) !!}
                <a href="#" data-bs-toggle="tooltip" title="{{__('Delete Customer')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{ $customer['id']}}').submit();" class="btn btn-sm btn-danger bs-pass-para">
                    <i class="ti ti-trash text-white"></i>
                </a>
            {!! Collective\Html\FormFacade::close() !!}
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    @php
        $customerInfoSections = [
            [
                'title' => 'Customer Info',
                'fields' => [
                    $customer['name'],
                    $customer['email'],
                    $customer['contact']
                ]
            ],
            [
                'title' => 'Billing Info',
                'fields' => [
                    $customer['billing_name'],
                    $customer['billing_address'],
                    $customer['billing_city'] . ', ' . $customer['billing_state'] . ', ' . $customer['billing_zip'],
                    $customer['billing_country'],
                    $customer['billing_phone']
                ]
            ],
            [
                'title' => 'Shipping Info',
                'fields' => [
                    $customer['shipping_name'],
                    $customer['shipping_address'],
                    $customer['shipping_city'] . ', ' . $customer['shipping_state'] . ', ' . $customer['shipping_zip'],
                    $customer['shipping_country'],
                    $customer['shipping_phone']
                ]
            ]
        ];
    @endphp
    <div class="row">
        @foreach($customerInfoSections as $section)
            <div class="col-md-4 col-lg-4 col-xl-4 mb-4">
                <div class="card customer-detail-box customer_card">
                    <div class="card-body">
                        <h5 class="card-title">{{ __($section['title']) }}</h5>
                        @foreach($section['fields'] as $field)
                            @if($field)
                                <p class="card-text mb-0">{{ $field }}</p>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @php
        $totalInvoiceSum = $customer->customerTotalInvoiceSum($customer['id']);
        $totalInvoice = $customer->customerTotalInvoice($customer['id']);
        $averageSale = ($totalInvoiceSum != 0) ? $totalInvoiceSum / $totalInvoice : 0;
        $companyInfoStats = [
            [
                'label' => 'Customer Id',
                'value' => $user?->customerNumberFormat($customer['customer_id']),
                'secondLabel' => 'Total Sum of Invoices',
                'secondValue' => $user?->priceFormat($totalInvoiceSum)
            ],
            [
                'label' => 'Date of Creation',
                'value' => $user?->dateFormat($customer['created_at']),
                'secondLabel' => 'Quantity of Invoice',
                'secondValue' => $totalInvoice
            ],
            [
                'label' => 'Balance',
                'value' => $user?->priceFormat($customer['balance']),
                'secondLabel' => 'Average Sales',
                'secondValue' => $user?->priceFormat($averageSale)
            ],
            [
                'label' => 'Overdue',
                'value' => $user?->priceFormat($customer->customerOverdue($customer['id'])),
                'secondLabel' => null,
                'secondValue' => null
            ]
        ];
    @endphp
    <div class="row">
        <div class="col-md-12">
            <div class="card pb-0">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Company Info') }}</h5>
                    <div class="row">
                        @foreach($companyInfoStats as $stat)
                            <div class="col-md-3 col-sm-6">
                                <div class="p-4">
                                    <p class="card-text mb-0">{{ __($stat['label']) }}</p>
                                    <h6 class="report-text mb-3">{{ $stat['value'] }}</h6>
                                    @if($stat['secondLabel'])
                                        <p class="card-text mb-0">{{ __($stat['secondLabel']) }}</p>
                                        <h6 class="report-text mb-0">{{ $stat['secondValue'] }}</h6>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-border-style table-border-style">
                    <h5 class="d-inline-block mb-5">{{__('Proposal')}}</h5>
                    <div class="table-responsive">
                        <table class="table ">
                            <thead>
                            <tr>
                                <th>{{__('Proposal')}}</th>
                                <th>{{__('Issue Date')}}</th>
                                <th>{{__('Amount')}}</th>
                                <th>{{__('Status')}}</th>
                                @if(Gate::check('edit proposal') || Gate::check('delete proposal') || Gate::check('show proposal'))
                                    <th width="10%"> {{__('Action')}}</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($customer->customerProposal($customer->id) as $proposal)
                                <tr>
                                    <td class="Id">
                                        <a href="{{ route(ViewsConstants::PPS . '.show',Crypt::encrypt($proposal->id)) }}" class="btn btn-outline-primary">{{ $user?->proposalNumberFormat($proposal->proposal_id) }}
                                        </a>
                                    </td>
                                    <td>{{ $user?->dateFormat($proposal->issue_date) }}</td>
                                    <td>{{ $user?->priceFormat($proposal->getTotal()) }}</td>
                                    @php
                                        $statusBadgeClasses = [
                                            0 => 'bg-primary',
                                            1 => 'bg-warning', 
                                            2 => 'bg-danger',
                                            3 => 'bg-info',
                                            4 => 'bg-primary'
                                        ];
                                    @endphp
                                    <td>
                                        <span class="badge {{ $statusBadgeClasses[$proposal->status] ?? 'bg-secondary' }} p-2 px-3 rounded status_badge">
                                            {{ __(\App\Models\Proposal::$statuses[$proposal->status]) }}
                                        </span>
                                    </td>
                                    @if(Gate::check('edit proposal') || Gate::check('delete proposal') || Gate::check('show proposal'))
                                        <td class="Action">
                                            <span>
                                              @if($proposal->is_convert==0)
                                                    @can('convert invoice')
                                                        <div class="action-btn bg-warning ms-2">
                                                        {!! Collective\Html\FormFacade::open(['method' => 'get', 'route' => [ViewsConstants::PPS . '.convert', $proposal->id],'id'=>'proposal-form-'.$proposal->id]) !!}
                                                            <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" data-original-title="{{__('Convert to Invoice')}}" title="{{__('Convert to Invoice')}}" data-confirm="You want to confirm convert to invoice. Press Yes to continue or Cancel to go back" data-confirm-yes="document.getElementById('proposal-form-{{$proposal->id}}').submit();">
                                                                <i class="ti ti-exchange text-white"></i>
                                                            </a>
                                                         {!! Collective\Html\FormFacade::close() !!}
                                                    </div>
                                                    @endcan
                                                @else
                                                    @can('convert invoice')
                                                        <div class="action-btn bg-warning ms-2">
                                                        <a href="{{ route(ViewsConstants::INV.'.show',Crypt::encrypt($proposal->converted_invoice_id)) }}"
                                                           class="mx-3 btn btn-sm  align-items-center" data-bs-toggle="tooltip" title="{{__('Already convert to Invoice')}}" data-original-title="{{__('Already convert to Invoice')}}" >
                                                            <i class="ti ti-file text-white"></i>
                                                        </a>
                                                    </div>
                                                    @endcan
                                                @endif
                                                @can('duplicate proposal')
                                                    <div class="action-btn bg-primary ms-2">
                                                    {!! Collective\Html\FormFacade::open(['method' => 'get', 'route' => [ViewsConstants::PPS . '.duplicate', $proposal->id],'id'=>'duplicate-form-'.$proposal->id]) !!}
                                                        <a href="#" class="{{ ViewClassNamesConstants::BT_SM_CT_PR }}" data-bs-toggle="tooltip" data-original-title="{{__('Duplicate')}}"  title="{{__('Duplicate Proposal')}}" data-confirm="You want to confirm duplicate this invoice. Press Yes to continue or Cancel to go back" data-confirm-yes="document.getElementById('duplicate-form-{{$proposal->id}}').submit();">
                                                            <i class="ti ti-copy text-white"></i>
                                                        </a>
                                                        {!! Collective\Html\FormFacade::close() !!}
                                                    </div>
                                                @endcan
                                                @can('show proposal')
                                                    <div class="action-btn bg-info ms-2">
                                                        <a href="{{ route(ViewsConstants::PPS . '.show',Crypt::encrypt($proposal->id)) }}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Show')}}" data-original-title="{{__('Detail')}}">
                                                            <i class="ti ti-eye text-white"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('edit proposal')
                                                    <div class="action-btn bg-primary ms-2">
                                                        <a href="{{ route(ViewsConstants::PPS . '.edit',Crypt::encrypt($proposal->id)) }}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                                                            <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                                        </a>
                                                    </div>
                                                @endcan

                                                @can('delete proposal')
                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'route' => [ViewsConstants::PPS . '.destroy', $proposal->id],'id'=>'delete-form-'.$proposal->id]) !!}
                                                        <a href="#" class="{{ ViewClassNamesConstants::BT_SM_CT_PR }}" data-bs-toggle="tooltip"  title="Delete" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$proposal->id}}').submit();">
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
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-border-style table-border-style">
                    <h5 class="d-inline-block mb-5">{{__('Invoice')}}</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>{{__('Invoice')}}</th>
                                <th>{{__('Issue Date')}}</th>
                                <th>{{__('Due Date')}}</th>
                                <th>{{__('Due Amount')}}</th>
                                <th>{{__('Status')}}</th>
                                @if(Gate::check('edit invoice') || Gate::check('delete invoice') || Gate::check('show invoice'))
                                    <th width="10%"> {{__('Action')}}</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($customer->customerInvoice($customer->id) as $invoice)
                                <tr>
                                    <td class="Id">
                                        <a href="{{ route(ViewsConstants::INV.'.show',Crypt::encrypt($invoice->id)) }}" class="btn btn-outline-primary">{{ $user?->invoiceNumberFormat($invoice->invoice_id) }}
                                        </a>
                                    </td>
                                    <td>{{ $user?->dateFormat($invoice->issue_date) }}</td>
                                    <td>
                                        @if(($invoice->due_date < date('Y-m-d')))
                                            <p class="text-danger"> {{ $user?->dateFormat($invoice->due_date) }}</p>
                                        @else
                                            {{ $user?->dateFormat($invoice->due_date) }}
                                        @endif
                                    </td>
                                    <td>{{$user?->priceFormat($invoice->getDue())  }}</td>
                                    @php
                                        $statusBadgeClasses = [
                                            0 => 'bg-primary',
                                            1 => 'bg-warning', 
                                            2 => 'bg-danger',
                                            3 => 'bg-info',
                                            4 => 'bg-primary'
                                        ];
                                    @endphp
                                    <td>
                                        <span class="badge {{ $statusBadgeClasses[$invoice->status] ?? 'bg-secondary' }} p-2 px-3 rounded status_badge">
                                            {{ __(\App\Models\Invoice::$statuses[$invoice->status]) }}
                                        </span>
                                    </td>
                                    @if(Gate::check('edit invoice') || Gate::check('delete invoice') || Gate::check('show invoice'))
                                        <td class="Action">
                                            <span>
                                                @can('copy invoice')
                                                    <div class="action-btn bg-warning ms-2">
                                                        <a class="mx-3 btn btn-sm align-items-center"  id="{{ route(ViewsConstants::INV.'.link.copy',$invoice->id) }}"  onclick="copyToClipboard(this)" data-bs-toggle="tooltip" data-original-title="{{__('Copy Invoice')}}"><i class="ti ti-link text-white"></i></a>
                                                    </div>
                                                @endcan
                                                @can('duplicate invoice')
                                                    <div class="action-btn bg-primary ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" data-original-title="{{__('Duplicate')}}" title="{{__('Duplicate Invoice')}}" data-confirm="You want to confirm this action. Press Yes to continue or Cancel to go back" data-confirm-yes="document.getElementById('duplicate-form-{{$invoice->id}}').submit();">
                                                            <i class="ti ti-copy text-white"></i>
                                                            {!! Collective\Html\FormFacade::open(['method' => 'get', 'route' => [ViewsConstants::INV.'.duplicate', $invoice->id],'id'=>'duplicate-form-'.$invoice->id]) !!}
                                                            {!! Collective\Html\FormFacade::close() !!}
                                                        </a>
                                                    </div>
                                                @endcan
                                                    @can('show invoice')
                                                        <div class="action-btn bg-info ms-2">
                                                        <a href="{{ route(ViewsConstants::INV.'.show',Crypt::encrypt($invoice->id)) }}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Show')}}" data-original-title="{{__('Detail')}}">
                                                            <i class="ti ti-eye text-white"></i>
                                                        </a>
                                                    </div>
                                                    @endcan
                                                    @can('edit invoice')
                                                        <div class="action-btn bg-primary ms-2">
                                                            <a href="{{ route(ViewsConstants::INV.'.edit',Crypt::encrypt($invoice->id)) }}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                                                                <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                                            </a>
                                                        </div>
                                                    @endcan
                                                    @can('delete invoice')
                                                        <div class="action-btn bg-danger ms-2">
                                                            {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'route' => [ViewsConstants::INV.'.destroy', $invoice->id],'id'=>'delete-form-'.$invoice->id]) !!}

                                                            <a href="#" class="{{ ViewClassNamesConstants::BT_SM_CT_PR }}" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$invoice->id}}').submit();">
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
