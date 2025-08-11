@php
    use App\Config\Constants\{
        ViewsConstants,
        ViewClassNamesConstants as VC,
        StacksConstants
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $lang = Utility::fetchUserLang();
    $plan = Utility::getChatGPTSettings();
    $awardStoreRoute = Route::has(ViewsConstants::AWD . '.store')
        ? route(ViewsConstants::AWD . '.store')
        : '#';
    $generateRoute = Route::has('generate')
        ? route('generate', [ViewsConstants::AWD])
        : '#';
    $formId    = 'award-store-form';
    $linkId    = 'award-generate-link';
    $storeMsg  = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::AWD,
        'award_store_route_unavailable'
    ) ?? 'Award store route is unavailable. Please contact technical support or your domain administrator.';
    $generateMsg = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::AWD,
        'award_generate_route_unavailable'
    ) ?? 'Award generate route is unavailable. Please contact technical support or your domain administrator.';
    $fields = [
        [
            'name'     => 'employee_id',
            'type'     => 'select',
            'label'    => __('Employee'),
            'options'  => $employees,
            'colClass' => 'col-md-6 col-lg-6',
            'attrs'    => ['required' => 'required'],
        ],
        [
            'name'     => 'award_type',
            'type'     => 'select',
            'label'    => __('Award Type'),
            'options'  => $awardtypes,
            'colClass' => 'col-md-6 col-lg-6',
            'attrs'    => ['required' => 'required'],
        ],
        [
            'name'     => 'date',
            'type'     => 'date',
            'label'    => __('Date'),
            'colClass' => 'col-md-6 col-lg-6',
            'attrs'    => [],
        ],
        [
            'name'     => 'gift',
            'type'     => 'text',
            'label'    => __('Gift'),
            'colClass' => 'col-md-6 col-lg-6',
            'attrs'    => ['placeholder' => __('Enter Gift')],
        ],
        [
            'name'     => 'description',
            'type'     => 'textarea',
            'label'    => __('Description'),
            'colClass' => 'col-md-12',
            'attrs'    => ['placeholder' => __('Enter Description')],
        ],
    ];
@endphp

{{ Form::open([
    'url'            => $awardStoreRoute,
    'method'         => 'post',
    'id'             => $formId,
    'data-url'       => $awardStoreRoute,
    'data-guard-msg' => $storeMsg,
]) }}
    <div class="{{ VC::RW }}">
        @if($plan->chatgpt == 1)
            <div class="{{ VC::FEND }} {{ VC::MB3 }}">
                <a id="{{ $linkId }}"
                   href="{{ $generateRoute }}"
                   class="{{ VC::BT_SM_PM }} {{ VC::DFL_IL }} btn-icon"
                   data-ajax-popup-over="true"
                   data-size="md"
                   data-url="{{ $generateRoute }}"
                   data-guard-msg="{{ $generateMsg }}"
                   title="{{ __('Generate content with AI') }}">
                    <i class="fas fa-robot"></i> {{ __('Generate with AI') }}
                </a>
            </div>
        @endif

        @foreach($fields as $f)
            <div class="{{ VC::FM_G }} {{ $f['colClass'] }}">
                {{ Form::label($f['name'], $f['label'], ['class' => VC::FM_LB]) }}<span class="text-danger">*</span>
                @php
                    $attrs = ['class' => VC::FM_CT, 'required' => 'required'];
                    if (!empty($f['attrs'])) {
                        $attrs = array_merge($attrs, $f['attrs']);
                    }
                @endphp
                @if($f['type'] === 'select')
                    {{ Form::select($f['name'], $f['options'], null, $attrs + ['placeholder' => '']) }}
                @elseif($f['type'] === 'textarea')
                    {{ Form::textarea($f['name'], null, $attrs) }}
                @else
                    {{ Form::{$f['type']}($f['name'], null, $attrs) }}
                @endif
            </div>
        @endforeach
    </div>
    <div class="{{ VC::CD_POS }}">
        <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Create') }}</button>
    </div>
{{ Form::close() }}
@push(StacksConstants::ADM_SCR_PG)
    <script defer>
        (() => {
            const form = document.getElementById('{{ $formId }}');
            if (form && form.getAttribute('data-listener-active') !== 'true') {
                form.setAttribute('data-listener-active', 'true');
                form.addEventListener('submit', event => {
                    try {
                        const action = form.getAttribute('action');
                        const url    = form.getAttribute('data-url');
                        if ((action && action !== '#') || (url && url !== '#')) return;
                        event.preventDefault();
                        const msg = form.getAttribute('data-guard-msg') ?? '# ERROR';
                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                        let container = document.getElementById('toast-container');
                        if (!container) {
                            container = document.createElement('div');
                            container.id = 'toast-container';
                            document.body.appendChild(container);
                        }
                        if (bootstrapLink && window.bootstrap) {
                            const toastEl = document.createElement('div');
                            toastEl.className = 'toast';
                            toastEl.setAttribute('role', 'alert');
                            toastEl.setAttribute('aria-live', 'assertive');
                            toastEl.setAttribute('aria-atomic', 'true');
                            const body = document.createElement('div');
                            body.className = 'toast-body';
                            body.textContent = msg;
                            toastEl.appendChild(body);
                            container.appendChild(toastEl);
                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                        } else {
                            alert(msg);
                        }
                        form.setAttribute('data-failed-route', 'true');
                    } catch (e) {}
                });
            }
            const link = document.getElementById('{{ $linkId }}');
            if (link && link.getAttribute('data-listener-active') !== 'true') {
                link.setAttribute('data-listener-active', 'true');
                link.addEventListener('click', event => {
                    try {
                        const href = link.getAttribute('href');
                        const url  = link.getAttribute('data-url');
                        if ((href && href !== '#') || (url && url !== '#')) return;
                        event.preventDefault();
                        const msg = link.getAttribute('data-guard-msg') ?? '# ERROR';
                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                        let container = document.getElementById('toast-container');
                        if (!container) {
                            container = document.createElement('div');
                            container.id = 'toast-container';
                            document.body.appendChild(container);
                        }
                        if (bootstrapLink && window.bootstrap) {
                            const toastEl = document.createElement('div');
                            toastEl.className = 'toast';
                            toastEl.setAttribute('role', 'alert');
                            toastEl.setAttribute('aria-live', 'assertive');
                            toastEl.setAttribute('aria-atomic', 'true');
                            const body = document.createElement('div');
                            body.className = 'toast-body';
                            body.textContent = msg;
                            toastEl.appendChild(body);
                            container.appendChild(toastEl);
                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                        } else {
                            alert(msg);
                        }
                        link.setAttribute('data-failed-route', 'true');
                    } catch (e) {}
                });
            }
        })();
    </script>
@endpush
