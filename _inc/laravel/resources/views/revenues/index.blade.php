@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth, Route};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Revenues')}}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Revenue')}}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        {{--        <a class="btn btn-sm btn-primary" data-bs-toggle="collapse" href="#multiCollapseExample1" role="button" aria-expanded="false" aria-controls="multiCollapseExample1" data-bs-toggle="tooltip" title="{{__('Filter')}}">--}}
        {{--            <i class="ti ti-filter"></i>--}}
        {{--        </a>--}}
        @can('create revenue')
            <a href="#" data-url="{{ route(ViewsConstans::RVN.'.create') }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Create New Revenue')}}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{__('Create')}}">
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
                        {{ Collective\Html\FormFacade::open(array('route' => array(ViewsConstans::RVN.'.index'),'method' => 'GET','id'=>'revenue_form')) }}
                        <div class="row align-items-center justify-content-end">
                            <div class="col-xl-10">
                                <div class="row">
                                    <div class="col-3">
                                        {{Collective\Html\FormFacade::label('date',__('Date'),['class'=>'form-label'])}}
                                        {{ Collective\Html\FormFacade::text('date', isset($_GET['date'])?$_GET['date']:null, array('class' => 'form-control month-btn','id'=>'pc-daterangepicker-1','readonly')) }}

                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 month">
                                        <div class="btn-box">
                                            {{Collective\Html\FormFacade::label('account',__('Account'),['class'=>'form-label'])}}
                                            {{ Collective\Html\FormFacade::select('account',$account,isset($_GET['account'])?$_GET['account']:'', array('class' => 'form-control select')) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 date">
                                        <div class="btn-box">
                                            {{ Collective\Html\FormFacade::label('customer', __('Customer'),['class'=>'form-label'])}}
                                            {{ Collective\Html\FormFacade::select('customer',$customer,isset($_GET['customer'])?$_GET['customer']:'', array('class' => 'form-control select')) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Collective\Html\FormFacade::label('category', __('Category'),['class'=>'form-label'])}}
                                            {{ Collective\Html\FormFacade::select('category',$category,isset($_GET['category'])?$_GET['category']:'', array('class' => 'form-control select')) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto mt-4">
                                <div class="row">
                                    <div class="col-auto">
                                        <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('revenue_form').submit(); return false;" data-bs-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>
                                        <a href="{{route(ViewsConstans::RVN.'.index')}}" class="btn btn-sm btn-danger" data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
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
                <div class="card-body table-border-style mt-2">
                    <h5></h5>
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th> {{__('Date')}}</th>
                                <th> {{__('Amount')}}</th>
                                <th> {{__('Account')}}</th>
                                <th> {{__('Customer')}}</th>
                                <th> {{__('Category')}}</th>
                                <th> {{__('Reference')}}</th>
                                <th> {{__('Description')}}</th>
                                <th>{{__('Payment Receipt')}}</th>
                                @if(Gate::check('edit revenue') || Gate::check('delete revenue'))
                                    <th width="10%"> {{__('Action')}}</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>
                            @php
                                $revenuepath=\App\Models\Utility::getFile('uploads/revenue');
                            @endphp
                            @foreach ($revenues as $revenue)

                                <tr class="font-style">
                                    <td>{{  $user?->dateFormat($revenue->date)}}</td>
                                    <td>{{  $user?->priceFormat($revenue->amount)}}</td>
                                    <td>{{ !empty($revenue->bankAccount)?$revenue->bankAccount->bank_name.' '.$revenue->bankAccount->holder_name:''}}</td>
                                    <td>{{  (!empty($revenue->customer)?$revenue->customer->name:'-')}}</td>
                                    <td>{{  !empty($revenue->category)?$revenue->category->name:'-'}}</td>
                                    <td>{{  !empty($revenue->reference)?$revenue->reference:'-'}}</td>
                                    <td>{{  !empty($revenue->description)?$revenue->description:'-'}}</td>
                                    <td>
                                            {{--                                        @if(!empty($revenue->add_receipt))--}}
                                            {{--                                            <a href="{{asset(Storage::url('uploads/revenue')).'/'.$revenue->add_receipt}}" download="" class="action-btn bg-primary ms-2 mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Download')}}" target="_blank"><span class="btn-inner--icon"><i class="ti ti-download text-white" ></i></span></a>--}}

                                            {{--                                            <div class="action-btn bg-secondary">--}}
                                            {{--                                                <a class="mx-3 btn btn-sm align-items-center" href="{{asset(Storage::url('uploads/revenue')).'/'.$revenue->add_receipt}}" target="_blank"  >--}}
                                            {{--                                                    <i class="ti ti-crosshair text-white" data-bs-toggle="tooltip" data-bs-original-title="{{ __('Preview') }}"></i>--}}
                                            {{--                                                </a>--}}
                                            {{--                                            </div>--}}
                                            {{--                                        @else--}}
                                            {{--                                            ---}}
                                            {{--                                        @endif--}}

                                        @if(!empty($revenue->add_receipt))
                                            <a  class="action-btn bg-primary ms-2 btn btn-sm align-items-center" href="{{ $revenuepath . '/' . $revenue->add_receipt }}" download="">
                                                <i class="ti ti-download text-white"></i>
                                            </a>
                                            <a href="{{ $revenuepath . '/' . $revenue->add_receipt }}"  class="action-btn bg-secondary ms-2 mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Download')}}" target="_blank"><span class="btn-inner--icon"><i class="ti ti-crosshair text-white" ></i></span></a>
                                        @else
                                            -
                                        @endif

                                    </td>
                                    @if(Gate::check('edit revenue') || Gate::check('delete revenue'))
                                        <td class="Action">
                                            <span>
                                            @can('edit revenue')
                                                    <div class="action-btn bg-primary ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center" data-url="{{ route(ViewsConstans::RVN.'.edit',$revenue->id) }}" data-ajax-popup="true" data-size="lg" data-bs-toggle="tooltip" title="{{__('Edit')}}" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                                                            <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('delete revenue')
                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'route' => [ViewsConstans::RVN.'.destroy', $revenue->id],'class'=>'delete-form-btn','id'=>'delete-form-'.$revenue->id]) !!}

                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}" data-confirm-yes="document.getElementById('delete-form-{{$revenue->id}}').submit();">
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
