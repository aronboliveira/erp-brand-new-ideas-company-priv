@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use Illuminate\Support\Facades\Route;
@endphp
@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Promotion')}}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Promotion')}}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
    @can('create promotion')
            <a href="#" data-url="{{ route('promotion.create') }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Create New Promotion')}}" data-bs-toggle="tooltip" title="{{__('Create')}}"  class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-md-12">
            <div class="card">
            <div class="card-body table-border-style">
                    <div class="table-responsive">
                    <table class="table datatable">
                            <thead>
                            <tr>
                                @role('company')
                                <th>{{__('Employee Name')}}</th>
                                @endrole
                                <th>{{__('Designation')}}</th>
                                <th>{{__('Promotion Title')}}</th>
                                <th>{{__('Promotion Date')}}</th>
                                <th>{{__('Description')}}</th>
                                @if(Gate::check('edit promotion') || Gate::check('delete promotion'))
                                    <th width="200px">{{__('Action')}}</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody class="font-style">
                            @foreach ($promotions as $promotion)
                                <tr>
                                    @role('company')
                                    <td>{{ !empty($promotion->employee)?$promotion->employee->name:'' }}</td>
                                    @endrole
                                    <td>{{ !empty($promotion->designation)?$promotion->designation->name:'' }}</td>
                                    <td>{{ $promotion->promotion_title }}</td>
                                    <td>{{  Illuminate\Support\Facades\Auth::user()?->dateFormat($promotion->promotion_date) }}</td>
                                    <td>{{ $promotion->description }}</td>
                                    @if(Gate::check('edit promotion') || Gate::check('delete promotion'))
                                        <td>
                                           @can('edit promotion')
                                                <div class="action-btn bg-primary ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center" data-url="{{ URL::to('promotion/'.$promotion->id.'/edit') }}" data-ajax-popup="true" data-title="{{__('Edit Promotion')}}" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                                                    <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                                </a>
                                                </div>
                                           @endcan
                                           @can('delete promotion')
                                                <div class="action-btn bg-danger ms-2">
                                                {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'route' => ['promotion.destroy', $promotion->id],'id'=>'delete-form-'.$promotion->id]) !!}
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$promotion->id}}').submit();">
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
