@php
    use App\Config\Constants\{
        PlansConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;

    $lang = Utility::fetchUserLang();
@endphp

{!! Form::open([
    'url'  => ViewsConstants::PRM,
    'method' => 'post',
    'id'     => 'create_promotion',
]) !!}
    <div class="modal-body">
        @php($plan = Utility::getChatGPTSettings())
        @if($plan?->{PlansConstants::COL_GPT} == 1)
            <div class="text-end">
                <a href="#"
                   data-size="md"
                   class="btn btn-primary btn-icon btn-sm"
                   data-ajax-popup-over="true"
                   data-url="{{ route('generate', ['promotion']) }}"
                   data-bs-placement="top"
                   data-title="{{ __('Generate content with AI') }}">
                    <i class="{{ VC::FAS_RB }}"></i>
                    <span>{{ __('Generate with AI') }}</span>
                </a>
            </div>
        @endif

        <div class="row">
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('employee_id', __('Employee'), ['class' => 'form-label']) }}
                {{ Form::select('employee_id', $employees, null, ['class' => 'form-control select', 'required' => 'required']) }}
            </div>
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('designation_id', __('Designation'), ['class' => 'form-label']) }}
                {{ Form::select('designation_id', $designations, null, ['class' => 'form-control select']) }}
            </div>
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('promotion_title', __('Promotion Title'), ['class' => 'form-label']) }}
                {{ Form::text('promotion_title', null, ['class' => 'form-control']) }}
            </div>
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('promotion_date', __('Promotion Date'), ['class' => 'form-label']) }}
                {{ Form::date('promotion_date', null, ['class' => 'form-control']) }}
            </div>
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
                {{ Form::textarea('description', null, ['class' => 'form-control', 'placeholder' => __('Enter Description')]) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
    </div>
{!! Form::close() !!}
