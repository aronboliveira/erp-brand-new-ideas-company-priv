@foreach ($project->users as $user)
    <li class="{{ VC::LGI }} px-0">
        <div class="{{ VC::R_ALC }} {{ VC::JCB }}">
            <div class="col-sm-auto {{ VC::MB3 }} mb-sm-0">
                <div class="{{ VC::DFL_AIC }}">
                    <div class="{{ VC::AV_CC_SM }} {{ VC::ME3 }}">
                        <img
                            @if ($user->avatar)
                                src="{{ asset('/storage/uploads/avatar/'.$user->avatar) }}"
                            @else
                                src="{{ asset('/storage/uploads/avatar/avatar.png') }}"
                            @endif
                            alt="avatar"
                            class="img-user">
                    </div>
                    <div>
                        <h5 class="m-0">{{ $user?->name }}</h5>
                        <small class="{{ VC::TXT_MT }}">{{ $user?->email }}</small>
                    </div>
                </div>
            </div>
            <div class="col-sm-auto text-sm-end {{ VC::DFL_AIC }}">
                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                    {!! Collective\Html\FormFacade::open([
                        'method' => 'DELETE',
                        'route'  => ['projects.user.destroy', [$project->id, $user?->id]],
                    ]) !!}
                        <a href="#"
                           class="{{ VC::BT_SM_CT_PR }}"
                           data-bs-toggle="tooltip"
                           title="{{ __('Delete') }}">
                            <i class="{{ VC::TI_TRS_WT }}"></i>
                        </a>
                    {!! Collective\Html\FormFacade::close() !!}
                </div>
            </div>
        </div>
    </li>
@endforeach

    
    {{--                        <img src="@if($user->avatar) src="{{asset('/storage/uploads/avatar/'.$user->avatar)}}" @else src="{{asset('/storage/uploads/avatar/avatar.png')}}" @endif " alt = "kal" class="img-user">--}}