@php
    use App\Config\Constants\{PlansConstants, ViewsConstants, ViewClassNamesConstants as VC, StacksConstants};
    use Collective\Html\FormFacade as Form;
    use App\Models\Utility;
    use Illuminate\Support\{Facades\Route, Str};
    $lang = Utility::fetchUserLang();
    $chatEnabled = Utility::getChatGPTSettings()?->{PlansConstants::COL_GPT} ?? 0;
    $row = VC::RW;
    $colMd6 = VC::CM6;
    $col12 = VC::C12;
    $formGroup = VC::FM_G;
    $formControl = VC::FM_CT;
    $formLabel = VC::FM_LB;
    $formId = 'edit-account-asset-form';
    $genLinkId = 'account-asset-generate-link';
    $updateBase = ViewsConstants::ACC_AST . '.update';
    $updateKebab = Str::kebab($updateBase);
    $updateResolved = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
    $updateUrl = ($updateResolved && isset($asset) && !empty($asset->id)) ? route($updateResolved, [$asset->id]) : '#';
    $updateGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::ACC_AST, 'update_account_asset_unavailable') ?? 'Update account asset route is unavailable. Please contact technical support or your domain administrator.';
    $genBase = 'generate';
    $genKebab = Str::kebab($genBase);
    $genResolved = Route::has($genBase) ? $genBase : (Route::has($genKebab) ? $genKebab : null);
    $genUrl = $genResolved ? route($genResolved, ['account asset']) : '#';
    $genGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::ACC_AST, 'generate_account_asset_unavailable') ?? 'Generate account asset route is unavailable. Please contact technical support or your domain administrator.';
@endphp

{{ Form::model($asset, [
    'url'  => $updateUrl,
    'method' => 'PUT',
    'id'     => $formId,
    'data-resolved-action' => $updateUrl,
    'data-guard-msg' => $updateGuardMsg,
    'data-sv-localized' => 'true',
]) }}
    <div class="modal-body">
        @if($chatEnabled)
            <div class="text-end">
                <a href="{{ $genUrl }}"
                   id="{{ $genLinkId }}"
                   class="{{ VC::BT_SM_PM }} btn-icon"
                   data-ajax-popup-over="true"
                   data-size="md"
                   data-url="{{ $genUrl }}"
                   data-bs-placement="top"
                   title="{{ __('Generate with AI') }}"
                   data-guard-msg="{{ $genGuardMsg }}"
                   data-sv-localized="true">
                    <i class="{{ VC::FAS_RB }}"></i> <span>{{ __('Generate with AI') }}</span>
                </a>
            </div>
        @endif

        <div class="{{ $row }}">
            <div class="{{ $col12 }} {{ $formGroup }}">
                {{ Form::label('employee_id', __('Employee'), ['class' => $formLabel]) }}
                {{ Form::select('employee_id[]', $employee, $asset->employee_id, [
                    'class' => "$formControl select2",
                    'id' => 'choices-multiple',
                ]) }}
            </div>
            <div class="{{ $colMd6 }} {{ $formGroup }}">
                {{ Form::label('name', __('Name'), ['class' => $formLabel]) }}
                {{ Form::text('name', null, ['class' => $formControl, 'required' => true]) }}
            </div>
            <div class="{{ $colMd6 }} {{ $formGroup }}">
                {{ Form::label('amount', __('Amount'), ['class' => $formLabel]) }}
                {{ Form::number('amount', null, ['class' => $formControl, 'required' => true, 'step' => '0.01']) }}
            </div>
            <div class="{{ $colMd6 }} {{ $formGroup }}">
                {{ Form::label('purchase_date', __('Purchase Date'), ['class' => $formLabel]) }}
                {{ Form::date('purchase_date', null, ['class' => $formControl]) }}
            </div>
            <div class="{{ $colMd6 }} {{ $formGroup }}">
                {{ Form::label('supported_date', __('Supported Date'), ['class' => $formLabel]) }}
                {{ Form::date('supported_date', null, ['class' => $formControl]) }}
            </div>
            <div class="{{ $col12 }} {{ $formGroup }}">
                {{ Form::label('description', __('Description'), ['class' => $formLabel]) }}
                {{ Form::textarea('description', null, ['class' => $formControl, 'rows' => 3]) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Update') }}</button>
    </div>
{{ Form::close() }}
<script defer src="{{ asset('assets/js/routes/accountAssets/generate.js') }}"></script>
<script defer src="{{ asset('assets/js/routes/accountAssets/edit.js') }}"></script>
