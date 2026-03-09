@php
$lang ??= 'en';
	$generateRouteName ??= null;
	$generateUrl ??= '#';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
				$resolveProjectRoute = function($baseName) {
			$kebab = Str::kebab($baseName);
			return Route::has($baseName) ? $baseName : (Route::has($kebab) ? $kebab : null);
		};
				$safeProjectRoute = function($routeName, $params = []) {
			return $routeName ? (route($routeName, $params) ?? '#') : '#';
		};
		$generateRouteName = $resolveProjectRoute('generate');
		$generateUrl = $safeProjectRoute($generateRouteName, ['projects']);
	} catch (\Error $e) {
		Log::error('Error in projects/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in projects/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in projects/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

{!! Form::model($project, [
    'route'   => [ViewsConstants::PRJ . '.update', $project->id],
    'method'  => 'PUT',
    'enctype' => 'multipart/form-data',
    'id'      => 'edit_project',
]) !!}
    <div class="modal-body">
        @php($plan = Utility::getChatGPTSettings())
        @if($plan?->{PlansConstants::COL_GPT} == 1)
            <div class="{{ VC::TX_END }}">
                <a href="{{ $generateUrl }}"
                   data-size="md"
                   class="{{ VC::BT_PRM }} btn-icon btn-sm"
                   data-ajax-popup-over="true"
                   data-url="{{ $generateUrl }}"
                   data-bs-placement="top"
                   data-title="{{ __('Generate content with AI') }}">
                    <i class="{{ VC::FAS_RB }}"></i>
                    <span>{{ __('Generate with AI') }}</span>
                </a>
            </div>
        @endif

        <div class="row">
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('project_name', __('Project Name'), ['class' => 'form-label']) }}<span class="{{ VC::TX_DNG }}">*</span>
                {{ Form::text('project_name', null, ['class' => 'form-control']) }}
            </div>
        </div>

        <div class="row">
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                {{ Form::date('start_date', null, ['class' => 'form-control']) }}
            </div>
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                {{ Form::date('end_date', null, ['class' => 'form-control']) }}
            </div>
        </div>

        <div class="row">
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('client', __('Client'), ['class' => 'form-label']) }}<span class="{{ VC::TX_DNG }}">*</span>
                {{ Form::select('client', $clients, $project->client_id, ['class' => 'form-control select', 'id' => 'choices-multiple1', 'required' => 'required']) }}
            </div>
        </div>

        <div class="row">
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('budget', __('Budget'), ['class' => 'form-label']) }}
                {{ Form::number('budget', null, ['class' => 'form-control']) }}
            </div>
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('estimated_hrs', __('Estimated Hours'), ['class' => 'form-label']) }}
                {{ Form::number('estimated_hrs', null, ['class' => 'form-control', 'min' => '0', 'maxlength' => '8']) }}
            </div>
        </div>

        <div class="row">
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
                {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => 4, 'cols' => 50]) }}
            </div>
        </div>

        <div class="row">
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('tag', __('Tag'), ['class' => 'form-label']) }}
                {{ Form::text('tag', isset($project->tags) ? $project->tags : '', ['class' => 'form-control', 'data-toggle' => 'tags']) }}
            </div>
        </div>

        <div class="row">
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                <select name="status" id="status" class="{{ VC::FM_CT }} main-element select">
                    @foreach(\App\Models\Project::$project_status as $k => $v)
                        <option value="{{ $k }}" {{ ($project->status == $k) ? 'selected' : '' }}>{{ __($v) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row">
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('project_image', __('Project Image'), ['class' => 'form-label']) }}<span class="{{ VC::TX_DNG }}">*</span>
                <div class="form-file {{ VC::MB3 }}">
                    <input type="file" class="{{ VC::FM_CT }}" name="project_image">
                </div>
                <img src="{{ $project->img_image }}" class="avatar avatar-xl" alt="">
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
    </div>
{!! Form::close() !!}
