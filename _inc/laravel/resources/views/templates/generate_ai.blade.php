@php
use App\Config\Constants\{DatabaseConstants, StacksConstants, ViewClassNamesConstants as VC};
use App\Models\Utility;
use Collective\Html\FormFacade as Form;
use Illuminate\Support\{Facades\Log, Str};

$templateName ??= [];
$lang = Utility::fetchUserLang();
$formId = 'ai-template-form';
$descId = 'ai-description';
$genBtnId = 'ai-generate-btn';
$copyAllBtnId = 'ai-copy-all-btn';
$copySelBtnId = 'ai-copy-selected-btn';
$labelForWhat = __('For What') ?: __('No label available');
$labelLanguage = __('Language') ?: __('No label available');
$labelTone = __('Tone') ?: __('No label available');
$labelCreativity = __('AI Creativity') ?: __('No label available');
$labelNumResults = __('Number of Result') ?: __('No label available');
$labelMaxLen = __('Maximum Result Length') ?: __('No label available');
$labelDescription = __('Description') ?: __('No description available');
$labelGenerate = __('Generate') ?: __('Generate');
$labelCopy = __('Copy Text') ?: __('Copy Text');
$labelCopySel = __('Copy Selected Text') ?: __('Copy Selected Text');
$selectTemplateMsg = Utility::fetchLinkMessage($lang, 'ai_templates', 'select_template_first') ?? 'Please select a template first.';
$copyAllMsg = Utility::fetchLinkMessage($lang, 'ai_templates', 'copied_all_to_clipboard') ?? 'Text copied to clipboard.';
$copySelMsg = Utility::fetchLinkMessage($lang, 'ai_templates', 'copied_selected_to_clipboard') ?? 'Selected text copied to clipboard.';
$copyErrorMsg = Utility::fetchLinkMessage($lang, 'ai_templates', 'copy_failed') ?? 'Copy failed. Please try again.';

$flags = [];
try { $flags = Utility::flagOfCountry() ?? []; } catch (\Throwable $e) { Log::error('Blade aiTemplates/form: flagOfCountry error: ' . $e->getMessage()); $flags = []; }

$tone = [
'funny' => 'funny',
'casual' => 'casual',
'excited' => 'excited',
'professional'=> 'professional',
'witty' => 'witty',
'sarcastic' => 'sarcastic',
'feminine' => 'feminine',
'masculine' => 'masculine',
'bold' => 'bold',
'dramatic' => 'dramatic',
'gumpy' => 'gumpy',
'secretive' => 'secretive',
];
@endphp

{{ Form::open([
	'url'    => '#',
	'method' => 'post',
	'id'     => $formId,
]) }}
<div class="{{ VC::RW }}">
    <div class="{{ VC::C12 }}">
        <div class="{{ VC::FM_G }}">
            {{ Form::label('template', $labelForWhat, ['class' => VC::FM_LB]) }}<br>
            @foreach(is_iterable($templateName) ? $templateName : [] as $key => $value)
            @php
            $valId = data_get($value, 'id') ?? '';
            $valTm = data_get($value, 'template_name') ?? '';
            $human = (string) Str::of($valTm)->replace('_', ' ')->title() ?: __('No template name available');
            $inputId = 'product_name_' . $valId;
            @endphp
            <div class="{{ VC::FM_CHK_IL }}">
                <input class="form-check-input template_name" type="radio" name="template_name" value="{{ $valId }}" id="{{ $inputId }}" data-name="{{ $valTm }}">
                <label class="form-check-label" for="{{ $inputId }}">{{ $human }}</label>
            </div>
            @endforeach
        </div>
    </div>

    <div class="{{ VC::FM_GCB6 }}">
        <div class="{{ VC::FM_G }}">
            {{ Form::label('language', $labelLanguage, ['class' => VC::FM_LB]) }}
            <select name="language" class="{{ VC::FM_CT_SL }}" id="language">
                @foreach($flags as $key => $lng)
                <option value="{{ $key }}" {{ $lang === $key ? 'selected' : '' }}>{{ Str::upper($lng) }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="{{ VC::FM_GCB6 }} tone">
        <div class="{{ VC::FM_G }}">
            {{ Form::label('tone', $labelTone, ['class' => VC::FM_LB]) }}
            {{ Form::select('tone', $tone, null, ['class' => VC::FM_CT]) }}
        </div>
    </div>

    <div class="{{ VC::FM_GCB6 }}">
        <div class="{{ VC::FM_G }}">
            {{ Form::label('ai_creativity', $labelCreativity, ['class' => VC::FM_LB]) }}
            <select name="ai_creativity" id="ai_creativity" class="{{ VC::FM_CT_SL }}">
                <option value="1">{{ __('High') }}</option>
                <option value="0.5">{{ __('Medium') }}</option>
                <option value="0">{{ __('Low') }}</option>
            </select>
        </div>
    </div>

    <div class="{{ VC::FM_GCB6 }}">
        <div class="{{ VC::FM_G }}">
            {{ Form::label('num_of_result', $labelNumResults, ['class' => VC::FM_LB]) }}
            <select name="num_of_result" id="num_of_result" class="{{ VC::FM_CT_SL }}">
                @for($i = 1; $i <= 10; $i++)
                    <option value="{{ $i }}">{{ $i }}</option>
                    @endfor
            </select>
        </div>
    </div>

    <div class="{{ VC::FM_GCB6 }}">
        <div class="{{ VC::FM_G }}">
            {{ Form::label('result_length', $labelMaxLen, ['class' => VC::FM_LB]) }}
            {{ Form::number('result_length', 10, ['class' => VC::FM_CT]) }}
        </div>
    </div>

    <div class="{{ VC::C12 }}" id="getkeywords"></div>
</div>
{{ Form::close() }}

<div class="response">
    <a class="{{ VC::BT_SM_PM }} float-left" href="#!" id="{{ $genBtnId }}" data-msg-select-template="{{ $selectTemplateMsg }}" data-sv-localized="true">{{ $labelGenerate }}</a>
    <a href="#!" id="{{ $copyAllBtnId }}" class="{{ VC::BT_SM_PM }} {{ VC::FEND }}"><i class="{{ VC::TI_CC_PLS }}"></i> {{ $labelCopy }}</a>
    <a href="#!" id="{{ $copySelBtnId }}" class="{{ VC::BT_SM_PM }} {{ VC::FEND }} {{ VC::MS2 }}"><i class="{{ VC::TI_CC_PLS }}"></i> {{ $labelCopySel }}</a>
    <div class="{{ VC::FM_G }} {{ VC::MT3 }}">
        {{ Form::textarea('description', null, ['class' => VC::FM_CT, 'rows' => 5, 'placeholder' => $labelDescription, 'id' => $descId, 'data-copy-all-msg' => $copyAllMsg, 'data-copy-sel-msg' => $copySelMsg, 'data-copy-err-msg' => $copyErrorMsg, 'data-sv-localized' => 'true']) }}
    </div>
</div>
<script async src="{{ asset('assets/js/routes/ai/generate/lang/copy.js') }}"></script>
<script async src="{{ asset('assets/js/routes/ai/generate/copy.js') }}"></script>
<script defer src="{{ asset('assets/js/routes/ai/generate/index.js') }}"></script>
<script defer src="{{ asset('assets/js/routes/ai/generate/clipboard.js') }}"></script>