@php
    use App\Config\Constants\{
        ActivitiesConstants as AC, 
        ProjectsConstants as PC, 
        ViewsConstants as VW,
        ViewClassNamesConstants as VC
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    $isUpdate      = isset($task);
    $routeKey      = VW::DL.'.tasks.' . ($isUpdate ? 'update' : 'store');
    $kebabKey      = Str::kebab($routeKey);
    $hasRoute      = Route::has($routeKey);
    $hasKebab      = Route::has($kebabKey);
    $routeName     = $hasRoute
        ? $routeKey
        : ($hasKebab ? $kebabKey : null);
@endphp
@if(!empty($deal) && isset($deal->id) && (!$isUpdate || $isUpdate && !empty($task) && isset($task->id)))
        @php
            $routeParams   = $routeName
                ? ($isUpdate
                    ? [$routeName, $deal->id, $task->id]
                    : [$routeName, $deal->id])
                : ['#'];
            $routeUrl      = $routeName
                ? ($isUpdate
                    ? route($routeName, [$deal->id, $task->id])
                    : route($routeName, $deal->id))
                : '#';
            $guardKey      = $isUpdate
                ? 'deal_tasks_update_route_unavailable'
                : 'deal_tasks_store_route_unavailable';
            $guardMsg      = Utility::fetchLinkMessage($lang, VW::DL, $guardKey)
                ?? ($isUpdate
                    ? 'Update deal task route is unavailable. Please contact technical support or your domain administrator.'
                    : 'Create deal task route is unavailable. Please contact technical support or your domain administrator.'
                );
        @endphp
        @if($isUpdate)
            {!! Form::model(
                $task,
                [
                    'route'          => $routeParams,
                    'method'         => 'PUT',
                    'id'             => 'form-tasks-'. $deal->id .'-'. $task->id,
                    'data-url'       => $routeUrl,
                    'data-guard-msg' => $guardMsg
                ]
            ) !!}
        @else
            {!! Form::open([
                'route'          => $routeParams,
                'id'             => 'form-tasks-'. $deal->id,
                'data-url'       => $routeUrl,
                'data-guard-msg' => $guardMsg
            ]) !!}
        @endif
        <div class="modal-body">
            <div class="row">
                <div class="col-12 form-group">
                    {{ Form::label(PC::COL_NM, __('Name'),['class'=>'form-label']) }}
                    {{ Form::text(PC::COL_NM, null, array('class' => 'form-control',
                        'required'=>'required')) }}
                </div>
                <div class="col-6 form-group">
                    {{ Form::label(AC::COL_TSK_DATE, __('Date'),['class'=>'form-label']) }}
                    {{ Form::date(AC::COL_TSK_DATE, null, array('class' => 'form-control',
                        'required'=>'required')) }}
                </div>
                <div class="col-6 form-group">
                    {{ Form::label(AC::COL_TSK_TIME, __('Time'),['class'=>'form-label']) }}
                    {{ Form::time(AC::COL_TSK_TIME, null, array('class' => 'form-control',
                        'required'=>'required')) }}
                </div>
                <div class="col-6 form-group">
                    {{ Form::label(PC::COL_PRT, __('Priority'),['class'=>'form-label']) }}
                    <select class="form-control select2" name="priority" required id="choices-multiple1">
                        @foreach($priorities as $key => $priority)
                            <option value="{{$key}}" @if(isset($task) && $task->priority == $key) selected @endif>{{__($priority)}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 form-group">
                    {{ Form::label(AC::COL_TSK_STT, __('Status'),['class'=>'form-label']) }}
                    <select class="form-control select2" name="status" id="choices-multiple2" required>
                        @foreach($status as $key => $st)
                            <option value="{{$key}}" @if(isset($task) && $task->status == $key) selected @endif>{{__($st)}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{__('Cancel')}}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            @if(isset($task))
                <input type="submit" value="{{__('Update')}}" class="{{ VC::BT_PRM }}">
            @else
                <input type="submit" value="{{__('Create')}}" class="{{ VC::BT_PRM }}">
            @endif
        </div>
        <script async src="{{ asset('assets/js/routes/deals/lang/tasks.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/deals/tasks.js') }}"></script>
    {{Form::close()}}
@else
    <div class="modal-body">
        <div class="row">
            <div class="col-12">
                <div class="alert alert-danger p-3">
                    {{__('Deal information is unavailable.')}}
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{__('Close')}}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
    </div>
@endif

    
