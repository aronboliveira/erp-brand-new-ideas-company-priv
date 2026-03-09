<div class="modal-body">
    <div class="{{ VC::C12 }}">
        <div class="{{ VC::RW }}">
            @if(isset($users) && (is_array($users) || is_countable($users)) && count($users) > 0)
                @foreach($users as $user)
                    @if(isset($user) && is_object($user))
                        <div class="col-6 {{ VC::MB4 }}">
                            <div class="{{ VC::LGI }} px-0">
                                <div class="{{ VC::RW }}">
                                    <div class="{{ VC::C_AT }}">
                                        @php
                                            try {
                                                $userAvatar = data_get($user, 'avatar');
                                                $avatarSrc = !empty($userAvatar) && is_string($userAvatar)
                                                    ? asset('/storage/uploads/avatar/' . $userAvatar)
                                                    : asset('/storage/uploads/avatar/avatar.png');
                                            } catch (\Throwable $e) {
                                                \Log::error('projects/invite — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                            }
@endphp
                                        <img src="{{ $avatarSrc }}"
                                             class="wid-40 rounded-circle ml-3"
                                             alt="avatar"
                                             onerror="this.src='{{ asset('/storage/uploads/avatar/avatar.png') }}'">
                                    </div>
                                    <div class="col">
                                        <h6 class="{{ VC::MB0 }} {{ VC::H6 }}">
                                            {{ data_get($user, 'name', __('Unknown User')) }}
                                        </h6>
                                        <p class="{{ VC::MB0 }}">
                                            @if(data_get($user, 'email') && filter_var(data_get($user, 'email'), FILTER_VALIDATE_EMAIL))
                                                <span class="text-success">{{ data_get($user, 'email') }}</span>
                                            @else
                                                <span class="{{ VC::TXT_MT }}">{{ __('No email available') }}</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="{{ VC::C_AT }}">
                                        @if(data_get($user, 'id') && (is_numeric(data_get($user, 'id')) || is_string(data_get($user, 'id'))))
                                            <div class="{{ VC::ACT_BTN_INF }} invite_usr"
                                                 data-id="{{ data_get($user, 'id') }}">
                                                <button type="button" class="{{ VC::BT_SM_CT }}">
                                                    <span class="btn-inner--visible">
                                                        <i class="{{ VC::TI_PLS }} {{ VC::TXT_WT }}"
                                                           id="usr_icon_{{ data_get($user, 'id') }}"></i>
                                                    </span>
                                                </button>
                                            </div>
                                        @else
                                            <div class="{{ VC::ACT_BTN_INF }}">
                                                <button type="button" class="{{ VC::BT_SM_CT }}" disabled>
                                                    <span class="btn-inner--visible">
                                                        <i class="{{ VC::TI_PLS }} {{ VC::TXT_WT }}"></i>
                                                    </span>
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            @else
                <div class="{{ VC::C12 }} text-center">
                    <div class="{{ VC::PY4 }}">
                        <i class="{{ VC::TI_USRS }} {{ VC::TXT_MT }} {{ VC::MB3 }}" style="font-size: 3rem;"></i>
                        <h5 class="{{ VC::TXT_MT }}">{{ __('No Users Available') }}</h5>
                        <p class="{{ VC::TXT_MT }}">{{ __('There are no users to invite at this time.') }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
    @if(isset($project_id) && !empty($project_id))
        @php
            $safeProjectId = is_numeric($project_id) || is_string($project_id) ? $project_id : '';
@endphp
        {{ Collective\Html\FormFacade::hidden('project_id', $safeProjectId, ['id' => 'project_id']) }}
    @else
        <div class="{{ VC::ALT_WRN }} {{ VC::MT3 }}">
            <i class="ti ti-alert-triangle"></i>
            {{ __('Warning: Project ID is missing. User invitation may not work properly.') }}
        </div>
        <input type="hidden" name="project_id" id="project_id" value="">
    @endif
</div>
