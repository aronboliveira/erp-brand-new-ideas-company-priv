@php
    try {

        $radioOptions = [
            ['id'=>'page_content', 'value'=>'page_content', 'label'=>__('Page Content')],
            ['id'=>'page_url',     'value'=>'page_url',     'label'=>__('Page URL')],
        ];
        $toggles = [
            ['id'=>'header', 'label'=>__('Header')],
            ['id'=>'footer', 'label'=>__('Footer')],
            ['id'=>'login',  'label'=>__('Login')],
        ];
    } catch (\Throwable $e) {
        \Log::error('Modules/LandingPage/Resources/views/landingpage/menubar/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
{{ Form::open([
    'route'     => ViewsConstants::CST_PG . '.store',
    'method'    => 'post',
    'enctype'   => 'multipart/form-data'
]) }}
<div class="modal-body">
    @csrf
    <div class="row">
        <div class="form-group col-md-12">
            {{ Form::label(LPSC::MB_PG_NM, __('Page Name'), ['class'=>'form-label']) }}
            {{ Form::text(LPSC::MB_PG_NM, null, ['class'=>'form-control font-style','placeholder'=>__('Enter Plan Name'),'required'=>'required']) }}
        </div>
        <div class="form-group col-md-12">
            @foreach($radioOptions as $opt)
                <div class="{{ ViewClassNamesConstants::FM_CHK_IL }}">
                    <input
                        class="form-check-input"
                        type="radio"
                        name="template_name"
                        id="{{ $opt['id'] }}"
                        value="{{ $opt['value'] }}"
                        {{ $opt['value']==='page_content' ? 'checked' : '' }}
                    >
                    <label class="form-check-label" for="{{ $opt['id'] }}">
                        {{ $opt['label'] }}
                    </label>
                </div>
            @endforeach
        </div>
        <div class="form-group col-md-12 page_url d-none">
            {{ Form::label('page_url', __('Page URL'), ['class'=>'form-label']) }}
            {{ Form::text('page_url', null, ['class'=>'form-control font-style','placeholder'=>__('Enter Page URL')]) }}
        </div>
        <div class="form-group col-md-12 page_content">
            {{ Form::label(LPSC::MB_PG_CT, __('Page Content'), ['class'=>'form-label']) }}
            {{ Form::textarea(LPSC::MB_PG_CT, null, ['class'=>'form-control summernote-simple','rows'=>5]) }}
        </div>
        @foreach($toggles as $t)
            <div class="col-lg-2 col-xl-2 col-md-2">
                <div class="form-check form-switch ml-1">
                    <input type="checkbox" class="form-check-input" id="{{ $t['id'] }}" name="{{ $t['id'] }}" />
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
    <button type="submit" class="btn btn-primary">{{ __('Create') }}</button>
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
        }).filter('[value="page_content"]').prop('checked', true).trigger('change');
    });
</script>
