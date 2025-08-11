@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use Illuminate\Support\Facades\{Auth, Crypt, Route};
    $user = Auth::user();
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Employee')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Employee')}}</li>
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        <a href="#" data-size="md"  data-bs-toggle="tooltip" title="{{__('Import')}}" data-url="{{ route('employee.file.import') }}" data-ajax-popup="true" data-title="{{__('Import employee CSV file')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-file-import"></i>
        </a>
        <a href="{{route('employee.export')}}" data-bs-toggle="tooltip" title="{{__('Export')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-file-export"></i>
        </a>
        <a href="{{ route('employees.create') }}"
            data-title="{{ __('Create New Employee') }}" data-bs-toggle="tooltip" title="" class="btn btn-sm btn-primary"
            data-bs-original-title="{{ __('Create') }}">
            <i class="ti ti-plus"></i>
        </a>
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
            <div class="card-body table-border-style">
                        <div class="table-responsive">
                        <table class="table datatable">
                                <thead>
                                <tr>
                                    <th>{{__('Employee ID')}}</th>
                                    <th>{{__('Name')}}</th>
                                    <th>{{__('Email')}}</th>
                                    <th>{{__('Branch') }}</th>
                                    <th>{{__('Department') }}</th>
                                    <th>{{__('Designation') }}</th>
                                    <th>{{__('Date Of Joining') }}</th>
                                    <th> {{__('Last Login')}}</th>
                                    <th width="200px">{{__('Action')}}</th>

                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($employees as $employee)
                                    <tr>
                                        <td class="Id">
                                            @can('show employee profile')
                                                <a href="{{route('employee.show',Crypt::encrypt($employee->id))}}" class="btn btn-outline-primary">{{ $user?->employeeIdFormat($employee->employee_id) }}</a>
                                            @else
                                                <a href="#"  class="btn btn-outline-primary">{{ $user?->employeeIdFormat($employee->employee_id) }}</a>
                                            @endcan
                                        </td>
                                        <td class="font-style">{{ $employee->name }}</td>
                                        <td>{{ $employee->email }}</td>
                                        @if($employee->branch_id)
                                            <td class="font-style">{{!empty($user?->getBranch($employee->branch_id ))?$user?->getBranch($employee->branch_id )->name:''}}</td>
                                        @else
                                            <td>-</td>
                                        @endif
                                        @if($employee->department_id)
                                            <td class="font-style">{{!empty($user?->getDepartment($employee->department_id ))?$user?->getDepartment($employee->department_id )->name:''}}</td>
                                        @else
                                            <td>-</td>
                                        @endif
                                        @if($employee->designation_id)
                                            <td class="font-style">{{!empty($user?->getDesignation($employee->designation_id ))?$user?->getDesignation($employee->designation_id )->name:''}}</td>
                                        @else
                                            <td>-</td>
                                        @endif
                                        @if($employee->company_doj)
                                            <td class="font-style">{{ $user?->dateFormat($employee->company_doj )}}</td>
                                        @else
                                            <td>-</td>
                                        @endif
                                        <td>
                                            {{ (!empty($employee->user->last_login_at)) ? $employee->user->last_login_at : '-' }}
                                        </td>
                                        @if(Gate::check('edit employee') || Gate::check('delete employee'))
                                            <td>
                                                @if($employee->is_active==1)
                                                    @can('edit employee')
                                                        <div class="action-btn bg-primary ms-2">
                                                            <a href="{{route('employee.edit',Crypt::encrypt($employee->id))}}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}"
                                                            data-original-title="{{__('Edit')}}"><i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i></a>
                                                        </div>
                                                    @endcan
                                                    @can('delete employee')
                                                        <div class="action-btn bg-danger ms-2">
                                                        {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'route' => ['employee.destroy', $employee->id],'id'=>'delete-form-'.$employee->id]) !!}
                                                            <a href="#" class="{{ ViewClassNamesConstants::BT_SM_CT_PR }}" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$employee->id}}').submit();"><i class="ti ti-trash text-white"></i></a>
                                                            {!! Collective\Html\FormFacade::close() !!}
                                                    </div>
                                                    @endcan
                                                @else
                                                    <i class="ti ti-lock"></i>
                                                @endif
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
    </div>
@endsection
