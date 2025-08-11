@php
    use Collective\Html\FormFacade as Form;
    use App\Config\Constants\{ViewClassNamesConstants, ViewsConstants};
    use Module\LandingPage\Config\Constants\SettingsConstants as LandingPageSettingsConstants;
    $radioOptions = [
        ['id'=>'page_content', 'value'=>'page_content', 'label'=>__('Page Content')],
        ['id'=>'page_url',     'value'=>'page_url',     'label'=>__('Page URL')],
    ];
    $toggles = [
        ['id'=>'header', 'label'=>__('Header')],
        ['id'=>'footer', 'label'=>__('Footer')],
        ['id'=>'login',  'label'=>__('Login')],
    ];
@endphp

{{ Form::model(null, [
    'route'   => [ViewsConstants::CST_PG . '.update', $key],
    'method'  => 'PUT',
    'enctype' => 'multipart/form-data',
]) }}

<div class="modal-body">
    @csrf
    <div class="row">
        {{-- Page Name --}}
        <div class="form-group col-md-12">
            {{ Form::label(LandingPageSettingsConstants::MB_PG_NM, __('Page Name'), ['class'=>'form-label']) }}
            {{ Form::text(
                LandingPageSettingsConstants::MB_PG_NM,
                $page[LandingPageSettingsConstants::MB_PG_NM] ?? '',
                ['class'=>'form-control font-style','placeholder'=>__('Enter Plan Name'),'required'=>'required']
            ) }}
        </div>
        {{-- Template radios --}}
        <div class="form-group col-md-12">
            @foreach($radioOptions as $opt)
                <div class="{{ ViewClassNamesConstants::FM_CHK_IL }}">
                    <input
                        class="form-check-input"
                        type="radio"
                        name="template_name"
                        id="{{ $opt['id'] }}"
                        value="{{ $opt['value'] }}"
                        {{ (isset($page['template_name']) && $page['template_name'] === $opt['value']) ? 'checked' : '' }}
                    >
                    <label class="form-check-label" for="{{ $opt['id'] }}">
                        {{ $opt['label'] }}
                    </label>
                </div>
            @endforeach
        </div>

        {{-- Page Content --}}
        <div class="form-group col-md-12 page_content {{ (isset($page['template_name']) && $page['template_name'] !== 'page_content') ? 'd-none' : '' }}">
            {{ Form::label(LandingPageSettingsConstants::MB_PG_CT, __('Page Content'), ['class'=>'form-label']) }}
            {{ Form::textarea(
                LandingPageSettingsConstants::MB_PG_CT,
                $page[LandingPageSettingsConstants::MB_PG_CT] ?? '',
                ['class'=>'form-control summernote-simple','rows'=>5]
            ) }}
        </div>

        {{-- Page URL --}}
        <div class="form-group col-md-12 page_url {{ (isset($page['template_name']) && $page['template_name'] !== 'page_url') ? 'd-none' : '' }}">
            {{ Form::label('page_url', __('Page URL'), ['class'=>'form-label']) }}
            {{ Form::text(
                'page_url',
                $page['page_url'] ?? '',
                ['class'=>'form-control font-style','placeholder'=>__('Enter Page URL')]
            ) }}
        </div>

        {{-- Toggles --}}
        @foreach($toggles as $t)
            <div class="col-lg-2 col-xl-2 col-md-2">
                <div class="form-check form-switch ml-1">
                    <input
                        type="checkbox"
                        class="form-check-input"
                        id="{{ $t['id'] }}"
                        name="{{ $t['id'] }}"
                        {{ (isset($page[$t['id']]) && $page[$t['id']] === 'on') ? 'checked' : '' }}
                    />
                    <label class="form-check-label f-w-600 pl-1" for="{{ $t['id'] }}">
                        {{ $t['label'] }}
                    </label>
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
    <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
</div>

{{ Form::close() }}

<script>
    $(function() {
        $('input[name="template_name"]').on('change', function() {
            if (this.value === 'page_content') {
                $('.page_content').removeClass('d-none');
                $('.page_url').addClass('d-none');
            } else {
                $('.page_content').addClass('d-none');
                $('.page_url').removeClass('d-none');
            }
        }).filter(':checked').trigger('change');
    });
</script>
