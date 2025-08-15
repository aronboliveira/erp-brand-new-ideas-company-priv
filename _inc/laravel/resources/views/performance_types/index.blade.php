@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        ViewClassNamesConstants,
        StacksConstants,
        YieldingConstants,
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\Route;
    $lang = Utility::fetchUserLang();
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@push(StacksConstants::ADM_SCR_PG)
@endpush
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Performance type')}}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0 ">{{__('Performance Type')}}</h5>
    </div>
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{__('Performance Type')}}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        <a href="#" data-url="{{ route(ViewsConstans::PFM_TP.'.create') }}" data-ajax-popup="true" data-title="{{__('Create New Performance Type')}}" data-bs-toggle="tooltip" title="{{__('Create')}}"  class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </a>
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-3">
            @include('layouts.hrm_setup')
        </div>
        <div class="col-9">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                <th scope="col">{{__('Name')}}</th>
                                <th scope="col" class="">{{__('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody class="list">
                            @foreach ($types as $type)
                                <tr class="font-style">
                                    <td>{{ $type->name }}</td>
                                    <td class="">
                                        <div class="action-btn bg-primary ms-2">
                                            <a href="#" data-url="{{ route(ViewsConstans::PFM_TP.'.edit',$type->id) }}" data-ajax-popup="true" title="{{__('Edit')}}" data-title="{{__('Edit Performance Type')}}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" data-original-title="{{__('Edit')}}">
                                                <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                            </a>
                                        </div>
                                        <div class="action-btn bg-danger ms-2">
                                            {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'route' => [ViewsConstans::PFM_TP.'.destroy', $type->id],'id'=>'delete-form-'.$type->id]) !!}
                                            <a href="#!" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}" data-confirm-yes="document.getElementById('delete-form-{{$type->id}}').submit();">
                                                <i class="ti ti-trash text-white"></i>
                                            </a>
                                            {!! Collective\Html\FormFacade::close() !!}
                                        </div>
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

