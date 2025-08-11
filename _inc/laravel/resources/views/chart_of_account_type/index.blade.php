@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        PermissionsConstants,
        StacksConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Chart of Account Type')}}
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="all-button-box row d-flex justify-content-end">
        @can(PermissionsConstants::CR_COA_TYPE)
            <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6 col-6">
                <a href="#" data-url="{{ route('chart-of-account-type.create') }}" data-ajax-popup="true" data-title="{{__('Create New Type')}}" class="btn btn-xs btn-white btn-icon-only width-auto">
                    <i class="ti ti-plus"></i> {{__('Create')}}
                </a>
            </div>
        @endcan
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0 dataTable">
                            <thead>
                            <tr>
                                <th> {{__('Name')}}</th>
                                <th> {{__('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($types as $type)
                                <tr>
                                    <td>{{ $type->name }}</td>
                                    <td class="Action">
                                        <span>
                                            @can('edit constant chart of account type')
                                                <a href="#" class="edit-icon" data-url="{{ route('chart-of-account-type.edit',$type->id) }}" data-ajax-popup="true" data-title="{{__('Edit Unit')}}" data-bs-toggle="tooltip" data-original-title="{{__('Edit')}}">
                                                <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                            </a>
                                            @endcan
                                            @can('delete constant chart of account type')
                                                <a href="#" class="delete-icon" data-bs-toggle="tooltip" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$type->id}}').submit();">
                                                <i class="ti ti-trash"></i>
                                            </a>
                                                {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'route' => ['chart-of-account-type.destroy', $type->id],'id'=>'delete-form-'.$type->id]) !!}
                                                {!! Collective\Html\FormFacade::close() !!}
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
