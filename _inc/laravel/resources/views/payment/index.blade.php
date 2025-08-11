@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use Illuminate\Support\Facades\{Auth, Route};
    $user = Auth::user();
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Payments')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Payment')}}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @can('create payment')
            <a href="#" data-url="{{ route('payment.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip"  data-size="lg" data-title="{{__('Create New Payment')}}"  title="{{__('Create')}}" class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Collective\Html\FormFacade::open(array('route' => array('payment.index'),'method' => 'GET','id'=>'payment_form')) }}
                        <div class="row align-items-center justify-content-end">
                            <div class="col-xl-10">
                                <div class="row">
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Collective\Html\FormFacade::label('date', __('Date'),['class'=>'form-label']) }}
                                            {{ Collective\Html\FormFacade::date('date', isset($_GET['date'])?$_GET['date']:'', array('class' => 'form-control month-btn ','id'=>'pc-daterangepicker-1')) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Collective\Html\FormFacade::label('account', __('Account'),['class'=>'form-label']) }}
                                            {{ Collective\Html\FormFacade::select('account',$account,isset($_GET['account'])?$_GET['account']:'', array('class' => 'form-control select' ,'id'=>'choices-multiple')) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Collective\Html\FormFacade::label('vendor', __('Vendor'),['class'=>'form-label']) }}
                                            {{ Collective\Html\FormFacade::select('vendor',$vendor,isset($_GET['vendor'])?$_GET['vendor']:'', array('class' => 'form-control select','id'=>'choices-multiple1')) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Collective\Html\FormFacade::label('category', __('Category'),['class'=>'form-label']) }}
                                            {{ Collective\Html\FormFacade::select('category',$category,isset($_GET['category'])?$_GET['category']:'', array('class' => 'form-control select','id'=>'choices-multiple2')) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto mt-4">
                                <div class="row">
                                    <div clas="col-auto">
                                        <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('payment_form').submit(); return false;" data-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>
                                        <a href="{{ route('product_services.index') }}" class="btn btn-sm btn-danger" data-bs-toggle="tooltip"
                                           title="{{ __('Reset') }}">
                                            <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off"></i></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{ Collective\Html\FormFacade::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th>{{__('Date')}}</th>
                                <th>{{__('Amount')}}</th>
                                <th>{{__('Account')}}</th>
                                {{--                                <th> {{__('Chart Of Account')}}</th>--}}
                                <th>{{__('Vendor')}}</th>
                                <th>{{__('Category')}}</th>
                                <th>{{__('Reference')}}</th>
                                <th>{{__('Description')}}</th>
                                <th>{{__('Payment Receipt')}}</th>
                                @if(Gate::check('edit payment') || Gate::check('delete payment'))
                                    <th>{{__('Action')}}</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>
                            @php
                                $paymentpath =\App\Models\Utility::getFile('uploads/payment');
                            @endphp

                            @foreach ($payments as $payment)
                                <tr class="font-style">
                                    <td>{{  $user?->dateFormat($payment->date)}}</td>
                                    <td>{{  $user?->priceFormat($payment->amount)}}</td>
                                    <td>{{ !empty($payment->bankAccount)?$payment->bankAccount->bank_name.' '.$payment->bankAccount->holder_name:''}}</td>
{{--                                    <td>{{ !empty($payment->chartAccount)?$payment->chartAccount->name :'-' }}</td>--}}
                                    <td>{{  !empty($payment->vendor)?$payment->vendor->name:'-'}}</td>
                                    <td>{{  !empty($payment->category)?$payment->category->name:'-'}}</td>
                                    <td>{{  !empty($payment->reference)?$payment->reference:'-'}}</td>
                                    <td>{{  !empty($payment->description)?$payment->description:'-'}}</td>
                                    <td>
                                        @if(!empty($payment->add_receipt))
                                            <a class="action-btn bg-primary ms-2 btn btn-sm align-items-center" href="{{ $paymentpath . '/' . $payment->add_receipt }}" download="">
                                                <i class="ti ti-download text-white"></i>
                                            </a>
                                            <a href="{{ $paymentpath . '/' . $payment->add_receipt }}"  class="action-btn bg-secondary ms-2 mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Download')}}" target="_blank"><span class="btn-inner--icon"><i class="ti ti-crosshair text-white" ></i></span></a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    @if(Gate::check('edit revenue') || Gate::check('delete revenue'))
                                        <td class="action">
                                            @can('edit payment')
                                                <div class="action-btn bg-primary ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center" data-url="{{ route('payment.edit',$payment->id) }}" data-ajax-popup="true" data-title="{{__('Edit Payment')}}" data-size="lg" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                                                        <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                                    </a>
                                                </div>
                                            @endcan
                                            @can('delete payment')
                                                <div class="action-btn bg-danger ms-2">
                                                    {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'route' => ['payment.destroy', $payment->id],'id'=>'delete-form-'.$payment->id]) !!}
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" data-original-title="{{__('Delete')}}" title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$payment->id}}').submit();">
                                                        <i class="ti ti-trash text-white"></i>
                                                    </a>
                                                    {!! Collective\Html\FormFacade::close() !!}
                                                </div>
                                            @endcan
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
