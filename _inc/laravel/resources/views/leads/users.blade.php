@php
    try {
$lang = is_callable([Utility::class,'fetchUserLang']) ? Utility::fetchUserLang() : app()->getLocale();

        $leadOk   = isset($lead) && !empty($lead);
        $formId   = 'leads-users-form';

        $updateBase  = VW::LD . '.users.update';
        $updateKebab = Str::kebab($updateBase);
        $updateName  = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);

        $formUrl   = ($leadOk && $updateName) ? route($updateName, [$lead->id]) : '#';
        $formGuard = Utility::fetchLinkMessage($lang, VW::LD, 'users_update_route_unavailable') ?? 'Leads users update route is unavailable. Please contact technical support or your domain administrator.';

        $usersIsList = (isset($users) && ((is_array($users) && count($users) > 0) || (is_object($users) && method_exists($users,'isNotEmpty') && $users->isNotEmpty())));
        $userOptions = $usersIsList ? (is_array($users) ? $users : (method_exists($users,'toArray') ? $users->toArray() : [])) : ['' => __('No users available')];
        $usersDisabled = !$usersIsList;
    } catch (\Throwable $e) {
        \Log::error('leads/users — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@if($leadOk)
    {{ Form::model($lead, [
        'url'               => $formUrl,
        'method'            => 'PUT',
        'id'                => $formId,
        'data-url'          => $formUrl,
        'data-guard-msg'    => $formGuard,
        'data-sv-localized' => 'true',
    ]) }}
        {{ Form::token() }}
        <div class="modal-body">
            <div class="row">
                <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                    {{ Form::label('users', __('User'), ['class' => 'form-label']) }}
                    {{ Form::select(
                        'users[]',
                        $userOptions,
                        null,
                        array_merge(
                            [
                                'class'            => 'form-control select2',
                                'id'               => 'choices-multiple3',
                                'multiple'         => 'multiple',
                                'data-placeholder' => __('Select Users'),
                                'placeholder'      => __('Select Users'),
                            ],
                            $usersDisabled ? ['disabled' => 'disabled'] : []
                        )
                    ) }}
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
        </div>

        <script async src="{{ asset('assets/js/routes/leads/lang/users.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/leads/users.js') }}"></script>
    {{ Form::close() }}
@else
    <div>{{ __('No lead could be found') }}</div>
@endif
