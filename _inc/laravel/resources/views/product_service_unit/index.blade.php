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
    {{__('Manage Product & Service Unit')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Unit')}}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @can('create constant unit')
            <a href="#" data-url="{{ route(ViewsConstants::PRD_SV_UNT.'.create') }}" data-ajax-popup="true" data-title="{{__('Create New Unit')}}" data-bs-toggle="tooltip" title="{{__('Create')}}"  class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endcan
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-3">
            @include('layouts.account_setup')
        </div>
        <div class="col-9">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th> {{__('Unit')}}</th>
                                <th width="10%"> {{__('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($units as $unit)
                                <tr>
                                    <td>{{ $unit->name }}</td>
                                    <td class="Action">
                                        <span>
                                        @can('edit constant category')
                                                <div class="action-btn bg-primary ms-2">
                                                    <a href="#" class="mx-3 btn btn-sm align-items-center" data-url="{{ route(ViewsConstants::PRD_SV_UNT.'.edit',$unit->id) }}" data-ajax-popup="true" data-title="{{__('Edit Unit')}}" data-toggle="tooltip" data-original-title="{{__('Edit')}}">
                                                <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                            </a>
                                                </div>
                                            @endcan
                                            @can('delete constant category')
                                                <div class="action-btn bg-danger ms-2">
                                                {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'route' => [ViewsConstants::PRD_SV_UNT.'.destroy', $unit->id],'id'=>'delete-form-'.$unit->id]) !!}
                                                    <a href="#" class="{{ ViewClassNamesConstants::BT_SM_CT_PR }}" data-toggle="tooltip" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$unit->id}}').submit();">
                                                        <i class="ti ti-trash text-white"></i>
                                                    </a>
                                                {!! Collective\Html\FormFacade::close() !!}
                                                </div>
                                            @endcan
                                        </span>
                                    </td>
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
