@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewClassNamesConstants,
        YieldingConstants
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth,Crypt, Route};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@push(StacksConstants::ADM_SCR_PG)
@endpush
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Support')}}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0 ">{{__('Support')}}</h5>
    </div>
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">{{__('Support')}}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        <a href="{{ route(ViewsConstants::SPT.'.index') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{__('List View')}}">
            <i class="ti ti-list"></i>
        </a>
        <a href="#" data-size="lg" data-url="{{ route(ViewsConstants::SPT.'.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create Support')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </a>
    </div>
@endsection
@section('filter')
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="row">
        @foreach($supports as $support)
            <div class="col-md-3">
                <div class="card card-fluid">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <a href="#" class="avatar rounded-circle">
                                    <img alt="" class="" @if(!empty($support->createdBy) && !empty($support->createdBy->avatar))
                                    src="{{asset(Storage::url('uploads/avatar')).'/'.$support->createdBy->avatar}}" @else  src="{{asset(Storage::url('uploads/avatar')).'/avatar.png'}}" @endif>
                                    @if($support->replyUnread()>0)
                                        <span class="avatar-child avatar-badge bg-success"></span>
                                    @endif
                                </a>
                            </div>
                            <div class="col">
                                <a href="#!" class="d-block h6 mb-0">{{!empty($support->createdBy)?$support->createdBy->name:''}}</a>
                                <small class="d-block text-muted">{{$support->subject}}</small>
                            </div>

                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col text-center">
                                <span class="h6 mb-0">{{$support->ticket_code}}</span>
                                <span class="d-block text-sm">{{__('Code')}}</span>
                            </div>
                            <div class="col text-center">
                                @php
                                    $priorityBadgeClasses = [
                                        0 => 'bg-primary',
                                        1 => 'badge-info',
                                        2 => 'badge-warning',
                                        3 => 'badge-danger'
                                    ];
                                @endphp
                                <span class="h6 mb-0">
                                    <span class="text-capitalize badge {{ $priorityBadgeClasses[$support->priority] ?? 'bg-secondary' }} rounded-pill badge-sm">
                                        {{ __(\App\Models\Support::$priority[$support->priority]) }}
                                    </span>
                                </span>
                                <span class="d-block text-sm">{{__('Priority')}}</span>
                            </div>
                            <div class="col text-center">
                                <span class="h6 mb-0">
                                    @if(!empty($support->attachment))
                                        <a href="{{asset(Storage::url('uploads/supports')).'/'.$support->attachment}}" download="" class="btn btn-sm btn-secondary btn-icon rounded-pill" target="_blank">
                                            <span class="btn-inner--icon"><i class="ti ti-download"></i></span>
                                        </a>
                                    @else
                                        -
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row align-items-center">
                            <div class="col-6 text-start">
                                <span data-toggle="tooltip" data-title="{{__('Created Date')}}">{{$user?->dateFormat($support->created_at)}}</span>
                            </div>
                            <div class="col-6 d-flex float-end">
                                <div class="action-btn bg-warning me-2">
                                    <a href="{{ route(ViewsConstants::SPT.'.reply',Crypt::encrypt($support->id)) }}" data-title="{{__('Support Reply')}}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Reply')}}" data-original-title="{{__('Reply')}}">
                                        <i class="ti ti-corner-up-left text-white"></i>
                                    </a>
                                </div>
                                @if(\Auth::user()->id==$support->ticket_created)
                                    <div class="action-btn bg-primary me-2">
                                        <a href="#" data-size="lg" data-url="{{ route(ViewsConstants::SPT.'.edit',$support->id) }}" data-ajax-popup="true" data-title="{{__('Edit Support')}}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                                            <i class="ti ti-edit text-white"></i>
                                        </a>
                                    </div>
                                    <div class="action-btn bg-danger me-2">
                                            {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'route' => [ViewsConstants::SPT.'.destroy', $support->id],'id'=>'delete-form-'.$support->id]) !!}
                                            <a href="#!" class="{{ ViewClassNamesConstants::BT_SM_CT_PR }}" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{ __(Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?') }}|{{ __(Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?') }}" data-confirm-yes="document.getElementById('delete-form-{{$support->id}}').submit();">
                                                <i class="ti ti-trash text-white"></i>
                                            </a>
                                            {!! Collective\Html\FormFacade::close() !!}
                                        </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection

