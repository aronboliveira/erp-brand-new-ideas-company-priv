@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants
        ViewsConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use Illuminate\Support\Facades\Route;
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Project Stages')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Project Stage')}}</li>
@endsection
@push(StacksConstants::ADM_SCR_PG)
    <script src="{{ asset('assets/js/jscolor.js') }}"></script>
    <script src="{{ asset('assets/libs/jquery-ui/jquery-ui.js') }}"></script>
    <script>
        $(function () {
            $(".sortable").sortable();
            $(".sortable").disableSelection();
            $(".sortable").sortable({
                stop: function () {
                    var order = [];
                    $(this).find('li').each(function (index, data) {
                        order[index] = $(data).attr('data-id');
                    });
                    $.ajax({
                        url: "{{route(ViewsConstants::PRJ_STG.'.order')}}",
                        data: {order: order, _token: $('meta[name="csrf-token"]').attr('content')},
                        type: 'POST',
                        success: function (data) {
                        },
                        error: function (data) {
                            data = data.responseJSON;
                            show_toastr('{{__("Error")}}', data.error, 'error')
                        }
                    })
                }
            });
        });
    </script>
@endpush
@section(YieldingConstants::ADM_ACT_BTN)
    @can('create project stage')
        <div class="float-end">
            <a href="#" data-url="{{ route(ViewsConstants::PRJ_STG.'.create') }}" data-ajax-popup="true" data-title="{{__('Create Project Stage')}}" class="btn btn-xs btn-white btn-icon-only width-auto"><i class="ti ti-plus"></i> {{__('Create')}} </a>
        </div>
    @endcan
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info note-constant text-xs">
                <p class="mt-4"><strong>{{__('Note')}} : </strong><b>{{__('System will consider last stage as a completed / done task for get progress on project.')}}</b></p>

            </div>
        </div>
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <ul class="list-group sortable">
                        @foreach ($projectstages as $projectstage)
                            <li class="list-group-item" data-id="{{$projectstage->id}}">
                                <div class="row">
                                    <div class="col-6 text-xs text-dark">{{$projectstage->name}}</div>
                                    <div class="col-4 text-xs text-dark">{{$projectstage->created_at}}</div>
                                    <div class="col-2">
                                        @can('edit project stage')
                                            <a href="#" data-url="{{ URL::to(App\Config\Constants\ViewsConstants::PRJ_STG.'/'.$projectstage->id.'/edit') }}" data-ajax-popup="true" data-title="{{__('Edit Project Stages')}}" class="edit-icon">
                                                <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                            </a>
                                        @endcan
                                        @can('delete project stage')
                                            <a href="#" class="delete-icon" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$projectstage->id}}').submit();"><i class="ti ti-trash"></i></a>
                                            {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'route' => [ViewsConstants::PRJ_STG.'.destroy', $projectstage->id],'id'=>'delete-form-'.$projectstage->id]) !!}
                                            {!! Collective\Html\FormFacade::close() !!}
                                        @endcan
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
