@php
    use App\Config\Constants\{
        PlansConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\{Collection, Str};
    $lang = Utility::fetchUserLang();
    $namespace      = ViewsConstants::DL;
    $routeName      = "{$namespace}.labels.store";
@endphp
@if(!empty($deal) && isset($deal->id))
    @php
        $storeRoute     = route($routeName, $deal->id);
        $storeGuardMsg  = Utility::fetchLinkMessage(
            $lang,
            $namespace,
            'labels_store_route_unavailable'
        ) ?? 'Labels store route is unavailable. Please contact technical support or your domain administrator.';
    @endphp
    {{ Form::open([
        'route'           => [$routeName, $deal->id],
        'method'          => 'POST',
        'id'              => 'labels-store-form',
        'data-url'        => $storeRoute,
        'data-guard-msg'  => $storeGuardMsg
    ]) }}
        <div class="modal-body">
            @php
                $labelsRaw    = $labels ?? [];
                $labelsList   = Utility::isFilled($labelsRaw)
                                ? $labelsRaw
                                : [];
                $selectedRaw  = $selected ?? [];
                $selectedArr  = ($selectedRaw instanceof Collection) ? $selectedRaw->toArray()
                            : (is_array($selectedRaw) ? $selectedRaw : []);
                $isAssoc      = array_keys($selectedArr) !== range(0, max(count($selectedArr) - 1, 0));
                $selectedKeys = $isAssoc ? array_keys($selectedArr) : $selectedArr;
                $selectedKeys = array_map('strval', $selectedKeys);
            @endphp

            <div class="{{ VC::RW }}">
                <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                    <div class="{{ VC::RW }} gutters-xs">
                        @forelse($labelsList as $label)
                            @php
                                $labelId    = isset($label->id) ? (string)$label->id : '';
                                $labelName  = isset($label->name) && $label->name !== '' ? ucfirst($label->name) : __('(Unnamed label)');
                                $labelColor = isset($label->color) && $label->color !== '' ? $label->color : 'secondary';
                                $inputId    = $labelId !== '' ? ('labels_'.$labelId) : ('labels_'.uniqid());
                                $isChecked  = $labelId !== '' && in_array($labelId, $selectedKeys, true);
                            @endphp

                            <div class="{{ VC::C12 }} {{ VC::CST_CTL }} {{ VC::CST_CB }} mt-2 mb-2">
                                {{ Form::checkbox(
                                    'labels[' . $labelId . ']',
                                    $labelId,
                                    $isChecked,
                                    [
                                        'class'    => 'form-check-input',
                                        'id'       => $inputId,
                                        'disabled' => $labelId === '' ? true : null,
                                    ]
                                ) }}
                                {{ Form::label(
                                    $inputId,
                                    $labelName,
                                    [
                                        'class' => VC::CST_LB.' ml-4 '.VC::TXT_WT.' px-3 '.VC::PY2.' rounded '.VC::BDG.' bg-'.$labelColor
                                    ]
                                ) }}
                            </div>
                        @empty
                            <div class="{{ VC::C12 }} text-center text-muted py-3">
                                {{ __('No labels found') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Save') }}" class="{{ VC::BT_PRM }}">
        </div>
        <script defer src="{{ asset('assets/js/routes/deals/label.js') }}"></script>
    {{ Form::close() }}
@else
    <div>{{ __('No deal could be found') }}</div>
@endif
