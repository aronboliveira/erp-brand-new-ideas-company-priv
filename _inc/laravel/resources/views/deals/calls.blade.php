@php
    use App\Config\Constants\{
        PlansConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
    };
    use App\Models\{User, UserDeal, Utility};
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\{Collection, Str};
    $lang = Utility::fetchUserLang();
@endphp
@push('css-page')
    <link rel="stylesheet" href="{{ asset('assets/libs/summernote/summernote-bs4.css') }}">
@endpush

@push('script-page')
    <script src="{{ asset('assets/libs/summernote/summernote-bs4.js') }}"></script>
@endpush

@if(isset($call))
    @php
        $updateCallRoute = Route::has(ViewsConstants::DL . '.calls.update')
            ? route(ViewsConstants::DL . '.calls.update', [$deal->id, $call->id])
            : (Route::has(Str::kebab(ViewsConstants::DL . '.calls.update'))
                ? route(Str::kebab(ViewsConstants::DL . '.calls.update'), [$deal->id, $call->id])
                : '#');
        $updateCallGuardMsg = Utility::fetchLinkMessage(
            $lang,
            ViewsConstants::DL,
            'calls_update_route_unavailable'
        ) ?? 'Update call route is unavailable. Please contact technical support or your domain administrator.';
    @endphp
    @push(StacksConstants::ADM_SCR_PG)
        <script defer>
            (() => {
                const form = document.getElementById('update-call-form-{{ $call->id }}');
                if (!form || form.getAttribute('data-listener-active') === 'true') return;
                form.setAttribute('data-listener-active', 'true');
                form.addEventListener('submit', e => {
                    try {
                        const url = form.getAttribute('data-url') || '#';
                        if (url !== '#') return;
                        e.preventDefault();
                        const msg = form.getAttribute('data-guard-msg') || '# ERROR';
                        const bs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                        let container = document.getElementById('toast-container');
                        if (!container) {
                            container = document.createElement('div');
                            container.id = 'toast-container';
                            document.body.appendChild(container);
                        }
                        if (bs) {
                            const toast = document.createElement('div');
                            toast.className = 'toast';
                            toast.setAttribute('role','alert');
                            toast.setAttribute('aria-live','assertive');
                            toast.setAttribute('aria-atomic','true');
                            const body = document.createElement('div');
                            body.className = 'toast-body';
                            body.textContent = msg;
                            toast.appendChild(body);
                            container.appendChild(toast);
                            bootstrap.Toast.getOrCreateInstance(toast).show();
                        } else {
                            alert(msg);
                        }
                        form.setAttribute('data-failed-route', 'true');
                    } catch (error) {}
                });
            })();
        </script>
    @endpush
    {!! Form::model($call, [
        'route'         => $updateCallRoute,
        'method'        => 'PUT',
        'id'            => 'update-call-form-' . $call->id,
        'data-url'      => $updateCallRoute,
        'data-guard-msg'=> $updateCallGuardMsg
    ]) !!}
@else
    @php
        $storeRoute = Route::has(ViewsConstants::DL . '.calls.store')
            ? route(ViewsConstants::DL . '.calls.store', $deal->id)
            : '#';
        $storeGuardMsg = Utility::fetchLinkMessage(
            $lang,
            ViewsConstants::DL,
            'calls_store_route_unavailable'
        ) ?? 'Call store route is unavailable. Please contact technical support or your domain administrator.';
    @endphp
    @push(StacksConstants::ADM_SCR_PG)
        <script defer src="{{ asset('assets/js/routes/deals/store.js') }}"></script>
    @endpush
    {{ Form::open([
        'route'            => $storeRoute,
        'method'         => 'POST',
        'id'             => 'deal-call-store-form',
        'data-guard-msg' => $storeGuardMsg
    ]) }}
@endif
    <div class="modal-body">
        @php $plan = Utility::getChatGPTSettings(); @endphp
        @if($plan?->{PlansConstants::COL_GPT} == 1)
            <div class="text-end">
                @php
                    $generateRoute = Route::has('generate')
                        ? route('generate', ['deal' => $deal->id])
                        : '#';
                    $generateGuardMsg = Utility::fetchLinkMessage(
                        $lang,
                        ViewsConstants::DL,
                        'generate_route_unavailable'
                    ) ?? 'Generate content for deals with AI route is unavailable. Please contact technical support or your domain administrator.';
                @endphp
                <a
                    id="generate-ai-btn-{{ $deal->id }}"
                    href="{{ $generateRoute }}"
                    data-url="{{ $generateRoute }}"
                    data-guard-msg="{{ $generateGuardMsg }}"
                    data-size="md"
                    class="{{ VC::BT_PRM }} btn-icon btn-sm"
                    data-ajax-popup-over="true"
                    data-bs-placement="top"
                    data-title="{{ __('Generate content with AI') }}"
                >
                    <i class="{{ VC::FAS_RB }}"></i> <span>{{ __('Generate with AI') }}</span>
                </a>
                @push(StacksConstants::ADM_SCR_PG)
                    <script defer>
                        (() => {
                            const btn = document.getElementById('generate-ai-btn-{{ $deal->id }}');
                            if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                            btn.setAttribute('data-listener-active','true');
                            btn.addEventListener('click', e => {
                                try {
                                    const url = btn.getAttribute('data-url') ?? '#';
                                    if (url !== '#') return;
                                    e.preventDefault();
                                    const msg = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                                    const bs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                    let container = document.getElementById('toast-container');
                                    if (!container) {
                                        container = document.createElement('div');
                                        container.id = 'toast-container';
                                        document.body.appendChild(container);
                                    }
                                    if (bs) {
                                        const toast = document.createElement('div');
                                        toast.className = 'toast';
                                        toast.setAttribute('role','alert');
                                        toast.setAttribute('aria-live','assertive');
                                        toast.setAttribute('aria-atomic','true');
                                        const body = document.createElement('div');
                                        body.className = 'toast-body';
                                        body.textContent = msg;
                                        toast.appendChild(body);
                                        container.appendChild(toast);
                                        bootstrap.Toast.getOrCreateInstance(toast).show();
                                    } else {
                                        alert(msg);
                                    }
                                    btn.setAttribute('data-failed-route','true');
                                } catch {}
                            });
                        })();
                    </script>
                @endpush
            </div>
        @endif
        <div class="row">
            <div class="col-6 {{ VC::FM_G }}">
                {{ Form::label('subject', __('Subject'), ['class' => VC::FM_LB]) }}
                {{ Form::text('subject', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>
            <div class="col-6 {{ VC::FM_G }}">
                {{ Form::label('call_type', __('Call Type'), ['class' => VC::FM_LB]) }}
                <select name="call_type" id="choices-multiple1" class="{{ VC::FM_CT }} select2" required>
                    <option value="outbound" @if(isset($call->call_type) && $call->call_type == 'outbound') selected @endif>
                        {{ __('Outbound') }}
                    </option>
                    <option value="inbound" @if(isset($call->call_type) && $call->call_type == 'inbound') selected @endif>
                        {{ __('Inbound') }}
                    </option>
                </select>
            </div>
            <div class="col-6 {{ VC::FM_G }}">
                {{ Form::label('duration', __('Duration'), ['class' => VC::FM_LB]) }}
                <small class="font-weight-bold">
                    {{ __('(Format h:m:s i.e 00:35:20 means 35 Minutes and 20 Sec)') }}
                </small>
                {{ Form::time('duration', null, ['class' => VC::FM_CT, 'placeholder' => '00:35:20', 'step' => '2']) }}
            </div>
            <div class="col-6 {{ VC::FM_G }}">
                {{ Form::label('user_id', __('Assignee'), ['class' => VC::FM_LB]) }}
                <select name="user_id" id="choices-multiple2" class="{{ VC::FM_CT }} select2" required>
                    @if(Utility::isFilled($users))
                        @foreach($users as $usr)
                            @php
                                $isUsrDeal = $usr instanceof UserDeal && method_exists($usr, 'getDealUser');
                                if ($isUsrDeal) $dealUser = $usr->getDealUser();
                                else if ($user instanceof User) $dealUser = $usr;
                                else $dealUser = null;
                                if (!$dealUser || !isset($dealUser->id)) continue;
                            @endphp
                            <option value="{{ $dealUser->id }}"
                                @if(isset($call->user_id) && $call->user_id == $dealUser->id) selected @endif>
                                {{ !empty($dealUser->name) ? $dealUser->name : __('User name not found') }}
                            </option>
                        @endforeach
                    @else
                        <option value="">{{ __('No Users Found') }}</option>
                    @endif
                </select>
            </div>
            <div class="col-12 {{ VC::FM_G }}">
                {{ Form::label('description', __('Description'), ['class' => VC::FM_LB]) }}
                {{ Form::textarea('description', null, ['class' => VC::FM_CT]) }}
            </div>
            <div class="col-12 {{ VC::FM_G }}">
                {{ Form::label('call_result', __('Call Result'), ['class' => VC::FM_LB]) }}
                {{ Form::textarea('call_result', null, ['class' => 'summernote-simple']) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        @if(isset($call))
            <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
        @else
            <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
        @endif
    </div>
{{ Form::close() }}
