@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use App\Models\{Estimation, Utility};
    use Illuminate\Support\Facades\Auth, Crypt;
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)

@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Estimate')}}
@endsection

@section(YieldingConstants::ADM_ACT_BTN)
    <div class="all-button-box row d-flex justify-content-end">
        @can('create estimation')
            <div class="col-xl-2 col-lg-2 col-md-4 col-sm-6 col-6">
                <a href="#" data-url="{{ route(ViewsConstants::EST.'.create') }}" data-size="sm" data-ajax-popup="true" data-title="{{__('Create Estimate')}}" class="btn btn-xs btn-white btn-icon-only width-auto"><i class="ti ti-plus"></i> {{__('Create')}}</a>
            </div>
        @endcan
    </div>
@endsection

@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col">
            <div class="card p-4 mb-4">
                <h5 class="report-text gray-text mb-0">{{__('Total Estimate')}}</h5>
                <h5 class="report-text mb-0">{{ $cnt_estimation['total'] }}</h5>
            </div>
        </div>
        <div class="col">
            <div class="card p-4 mb-4">
                <h5 class="report-text gray-text mb-0">{{__('This Month Total Estimate')}}</h5>
                <h5 class="report-text mb-0">{{ $cnt_estimation['this_month'] }}</h5>
            </div>
        </div>
        <div class="col">
            <div class="card p-4 mb-4">
                <h5 class="report-text gray-text mb-0">{{__('This Week Total Estimate')}}</h5>
                <h5 class="report-text mb-0">{{ $cnt_estimation['this_week'] }}</h5>
            </div>
        </div>
        <div class="col">
            <div class="card p-4 mb-4">
                <h5 class="report-text gray-text mb-0">{{__('Last 30 Days Total Estimate')}}</h5>
                <h5 class="report-text mb-0">{{ $cnt_estimation['last_30days'] }}</h5>
            </div>
        </div>

        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped dataTable">
                            <thead>
                            <tr>
                                <th>{{__('Estimate')}}</th>
                                <th>{{__('Client')}}</th>
                                <th>{{__('Issue Date')}}</th>
                                <th>{{__('Value')}}</th>
                                <th>{{__('Status')}}</th>
                                @if($user?->type != 'client')
                                    <th width="250px">{{__('Action')}}</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($estimations as $estimate)
                                <tr>
                                    <td class="Id">
                                        @can('View Estimation')
                                            <a href="{{route(ViewsConstants::EST.'.show',$estimate->id)}}"> <i class="ti ti-file-estimate"></i> {{ $user?->estimateNumberFormat($estimate->estimation_id) }}</a>
                                        @else
                                            {{ $user?->estimateNumberFormat($estimate->estimation_id) }}
                                        @endcan
                                    </td>
                                    <td>{{ $estimate->client->name }}</td>
                                    <td>{{ $user?->dateFormat($estimate->issue_date) }}</td>
                                    <td>{{ $user?->priceFormat($estimate->getTotal()) }}</td>
                                    <td>
                                        @if($estimate->status == 0)
                                            <span class="badge badge-pill badge-primary">{{ __(Estimation::$statuses[$estimate->status]) }}</span>
                                        @elseif($estimate->status == 1)
                                            <span class="badge badge-pill badge-danger">{{ __(Estimation::$statuses[$estimate->status]) }}</span>
                                        @elseif($estimate->status == 2)
                                            <span class="badge badge-pill badge-warning">{{ __(Estimation::$statuses[$estimate->status]) }}</span>
                                        @elseif($estimate->status == 3)
                                            <span class="badge badge-pill badge-success">{{ __(Estimation::$statuses[$estimate->status]) }}</span>
                                        @elseif($estimate->status == 4)
                                            <span class="badge badge-pill badge-info">{{ __(Estimation::$statuses[$estimate->status]) }}</span>
                                        @endif
                                    </td>
                                    @if($user?->type != 'client')
                                        <td class="Action">
                                            <span>
                                            @can('view estimation')
                                                    <a href="{{route(ViewsConstants::EST.'.show',$estimate->id)}}" class="edit-icon bg-warning"> <i class="ti ti-eye"></i></a>
                                                @endcan
                                                @can('edit estimation')
                                                    <a href="#" data-url="{{ URL::to(ViewsConstants::EST.'/'.$estimate->id.'/edit') }}" data-ajax-popup="true" data-title="{{__('Edit Estimation')}}" class="edit-icon" data-toggle="tooltip" data-original-title="{{__('Edit')}}"><i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i></a>
                                                @endcan
                                                @can('delete estimation')
                                                    <a href="#" class="delete-icon" data-toggle="tooltip" data-original-title="{{__('Delete')}}" data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}" data-confirm-yes="document.getElementById('delete-form-{{$estimate->id}}').submit();"><i class="ti ti-trash"></i></a>
                                                    {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'route' => [ViewsConstants::EST.'.destroy', $estimate->id],'id'=>'delete-form-'.$estimate->id]) !!}
                                                    {!! Collective\Html\FormFacade::close() !!}
                                                @endif
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
