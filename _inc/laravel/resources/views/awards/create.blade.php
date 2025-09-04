@php
    use App\Config\Constants\{
        PlansConstants,
        StacksConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
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
        @if($plan?->{PlansConstants::COL_GPT} == 1)
            <div class="{{ VC::FEND }} {{ VC::MB3 }}">
                <a id="{{ $linkId }}"
                   href="{{ $generateRoute }}"
                   class="{{ VC::BT_SM_PM }} {{ VC::DFL_IL }} btn-icon"
                   data-ajax-popup-over="true"
                   data-size="md"
                   data-url="{{ $generateRoute }}"
                   data-guard-msg="{{ $generateMsg }}"
                   title="{{ __('Generate content with AI') }}">
                    <i class="{{ VC::FAS_RB }}"></i> {{ __('Generate with AI') }}
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
    <script defer src="{{ asset('assets/js/routes/awards/store.js') }}"></script>
{{ Form::close() }}
