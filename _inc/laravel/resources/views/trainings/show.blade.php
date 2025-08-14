@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        YieldingConstants
    };
    use Illuminate\Support\Facades\Route;
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Trainig Details')}}
@endsection

@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item"><a href="{{route('training.index')}}">{{__('Training')}}</a></li>
    <li class="breadcrumb-item">{{__('Training Details')}}</li>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table">
                            <tbody>
                            <tr>
                                <td>{{__('Training Type')}}</td>
                                <td class="text-end">{{ !empty($training->types)?$training->types->name:'' }}</td>
                            </tr>
                            <tr>
                                <td>{{__('Trainer')}}</td>
                                <td class="text-end">{{ !empty($training->trainers)?$training->trainers->firstname:'--' }}</td>
                            </tr>
                            <tr>
                                <td>{{__('Training Cost')}}</td>
                                <td class="text-end">{{\Auth::user()->priceFormat($training->training_cost)}}</td>
                            </tr>
                            <tr>
                                <td>{{__('Start Date')}}</td>
                                <td class="text-end">{{\Auth::user()->dateFormat($training->start_date)}}</td>
                            </tr>
                            <tr>
                                <td>{{__('End Date')}}</td>
                                <td class="text-end">{{\Auth::user()->dateFormat($training->end_date)}}</td>
                            </tr>
                            <tr>
                                <td>{{__('Date')}}</td>
                                <td class="text-end">{{\Auth::user()->dateFormat($training->created_at)}}</td>
                            </tr>
                            </tbody>
                        </table>
                        <div class="text-sm mt-4 p-2"> {{$training->description}}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="row">
                        <div class="col-md-12">
                            <h6>{{__('Training Employee')}}</h6>
                            <hr>
                            <div class="media-list" id="all_employees_list">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item" style="border:0px;">
                                        <div class="media align-items-center">
                                            <img src="{{!empty($training->employees)? !empty($training->employees->user->avatar)?asset(Storage::url('uploads/avatar')).'/'.$training->employees->user->avatar:asset(Storage::url('uploads/avatar')).'/avatar.png':asset(Storage::url('uploads/avatar')).'/avatar.png'}}" class="user-image-hr-prj ui-w-30 rounded-circle" width="50px" height="50px">
                                            <div class="media-body px-2 text-sm">
                                                <a href="{{route('employee.show',!empty($training->employees)?\Illuminate\Support\Facades\Crypt::encrypt($training->employees->id):0)}}" class="text-dark">
                                                    {{ !empty($training->employees)?$training->employees->name:'' }}
                                                </a>
                                                <br>
                                                {{ !empty($training->employees)?!empty($training->employees->designation)?$training->employees->designation->name:'':'' }}
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>

                            {{Collective\Html\FormFacade::model($training,array('route' => array('training.status', $training->id), 'method' => 'post')) }}
                            <h6>{{__('Update Status')}}</h6>
                            <hr>
                            <div class="row col-md-12">
                                <div class="col-md-6">
                                    <input type="hidden" value="{{$training->id}}" name="id">
                                    <div class="form-group">
                                        {{Collective\Html\FormFacade::label('performance',__('Performance'),['class'=>'form-label text-dark'])}}
                                        {{Collective\Html\FormFacade::select('performance',$performance,null,array('class'=>'form-control select'))}}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{Collective\Html\FormFacade::label('status',__('Status'),['class'=>'form-label text-dark'])}}
                                        {{Collective\Html\FormFacade::select('status',$status,null,array('class'=>'form-control select'))}}
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    {{Collective\Html\FormFacade::label('remarks',__('Remarks'),['class'=>'form-label text-dark'])}}
                                    {{Collective\Html\FormFacade::textarea('remarks',null,array('class'=>'form-control','placeholder'=>__('Remarks'),'rows'=>3))}}
                                </div>
                            </div>
                            <div class="form-group col-lg-12 text-end">
                                <input type="submit" value="{{__('Save')}}" class="btn btn-primary">
                            </div>
                            {{Collective\Html\FormFacade::close()}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection



