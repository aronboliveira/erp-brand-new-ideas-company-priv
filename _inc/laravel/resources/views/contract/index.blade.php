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
    {{__('Manage Contract')}}
@endsection
@push(StacksConstants::ADM_SCR_PG)
@endpush
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Contract')}}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        <a href="{{ route('contract.grid') }}"  data-bs-toggle="tooltip" title="{{__('Grid View')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-layout-grid"></i>
        </a>
        @if($user?->type == 'company')
            <a href="#" data-size="md" data-url="{{ route('contract.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create New Contract')}}" class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endif
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
                                <th scope="col">{{__('#')}}</th>
                                <th scope="col">{{__('Subject')}}</th>
                                @if($user?->type!='client')
                                    <th scope="col">{{__('Client')}}</th>
                                @endif
                                <th scope="col">{{__('Project')}}</th>
                                <th scope="col">{{__('Contract Type')}}</th>
                                <th scope="col">{{__('Contract Value')}}</th>
                                <th scope="col">{{__('Start Date')}}</th>
                                <th scope="col">{{__('End Date')}}</th>
                                <th scope="col" >{{__('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($contracts as $contract)
                                <tr class="font-style">
                                    <td>
                                        <a href="{{route('contract.show',$contract->id)}}" class="btn btn-outline-primary">{{\Auth::user()->contractNumberFormat($contract->id)}}</a>
                                    </td>
                                    <td>{{ $contract->subject}}</td>
                                    @if($user?->type!='client')
                                        <td>{{ !empty($contract->clients)?$contract->clients->name:'-' }}</td>
                                    @endif
                                    <td>{{ !empty($contract->projects)?$contract->projects->project_name:'-' }}</td>
                                    <td>{{ !empty($contract->types)?$contract->types->name:'' }}</td>
                                    <td>{{ $user?->priceFormat($contract->value) }}</td>
                                    <td>{{ $user?->dateFormat($contract->start_date )}}</td>
                                    <td>{{ $user?->dateFormat($contract->end_date )}}</td>
                                    {{--                                    <td>--}}
                                    {{--                                        <a href="#" class="action-item" data-url="{{ route('contract.description',$contract->id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Desciption')}}" data-title="{{__('Desciption')}}"><i class="fa fa-comment"></i></a>--}}
                                    {{--                                    </td>--}}
                                    <td class="action ">
                                        @if($user?->type=='company')
                                            @if($contract->status=='accept')
                                                <div class="action-btn bg-primary ms-2">
                                                    <a href="#" data-size="lg"
                                                       data-url="{{ route('contract.copy', $contract->id) }}"
                                                       data-ajax-popup="true"
                                                       data-title="{{ __('Copy Contract') }}"
                                                       class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                       data-bs-toggle="tooltip" data-bs-placement="top"
                                                       title="{{ __('Duplicate') }}"><i
                                                            class="ti ti-copy text-white"></i>
                                                    </a>
                                                </div>
                                            @endif
                                        @endif
                                        @can('show contract')
                                            <div class="action-btn bg-warning ms-2">
                                                <a href="{{ route('contract.show',$contract->id) }}"
                                                   class="mx-3 btn btn-sm d-inline-flex align-items-center"
                                                   data-bs-whatever="{{__('View Budget Planner')}}" data-bs-toggle="tooltip"
                                                   data-bs-original-title="{{__('View')}}"> <span class="text-white"> <i class="ti ti-eye"></i></span></a>
                                            </div>
                                        @endcan
                                        @can('edit contract')
                                            <div class="action-btn bg-info ms-2">
                                                <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-url="{{ route('contract.edit',$contract->id) }}" data-ajax-popup="true" data-size="md" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-title="{{__('Edit Contract')}}">
                                                    <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                                </a></div>
                                        @endcan
                                        @can('delete contract')
                                            <div class="action-btn bg-danger ms-2">
                                                {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'route' => ['contract.destroy', $contract->id]]) !!}
                                                <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white"></i></a>
                                                {!! Collective\Html\FormFacade::close() !!}
                                            </div>
                                        @endcan
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
