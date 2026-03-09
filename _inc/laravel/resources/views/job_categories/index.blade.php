@php
    try {
$user               = Auth::user();
        $hasFetchUserLang   = is_callable([Utility::class, 'fetchUserLang']);
        $hasFetchLinkMsg    = is_callable([Utility::class, 'fetchLinkMessage']);
        $lang               = $hasFetchUserLang ? Utility::fetchUserLang(user: $user) : app()->getLocale();
    } catch (\Throwable $e) {
        \Log::error('job_categories/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@extends(EL::ADM)

@section(YW::ADM_PG_TTL)
    {{ __('Manage Job Category') }}
@endsection

@section(YW::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}" {{ Route::has('dashboard') ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Job Category') }}</li>
@endsection

@section(YW::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create job category')
            @php
                try {
                    $createBase   = VW::JB_CAT . '.create';
                    $createUrl    = Route::has($createBase) ? route($createBase) : '#';
                    $createMsg    = ($hasFetchLinkMsg ? Utility::fetchLinkMessage($lang, VW::JB_CAT, 'create_job_category_route_unavailable') : null)
                                    ?? __('Create job category route is unavailable. Please contact technical support or your domain administrator.');
                } catch (\Throwable $e) {
                    \Log::error('job_categories/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            <a  href="{{ $createUrl }}"
                data-url="{{ $createUrl }}"
                data-ajax-popup="true"
                data-title="{{ __('Create New Job Category') }}"
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
                class="{{ VC::BT_SM_PM }}"
                data-guard-msg="{{ base64_encode($createMsg) }}"
                data-sv-localized="true">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection

@section(YW::ADM_CTT)
    <div class="{{ VC::RW }}">
        <div class="{{ VC::CL_XL3 }}">
            @include('layouts.hrm_setup')
        </div>
        <div class="{{ VC::CLMS9 }}">
            <div class="{{ VC::CD }}">
                <div class="{{ VC::CD_BD_TB_BD }}">
                    <div class="{{ VC::TB_RSP }}">
                        <table class="{{ VC::TB }} datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Category') }}</th>
                                    <th width="200px">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="font-style">
                                @forelse ($categories as $category)
                                    @php
                                        $cid   = isset($category->id) ? (string)$category->id : '';
                                        $title = isset($category->title) && $category->title !== '' ? $category->title : __('Category title was not available.');
@endphp
                                    <tr>
                                        <td>{{ $title }}</td>
                                        <td>
                                            @can('edit job category')
                                                @php
                                                    try {
                                                        $editBase = VW::JB_CAT . '.edit';
                                                        $editUrl  = (Route::has($editBase) && $cid !== '') ? route($editBase, $cid) : '#';
                                                        $editMsg  = ($hasFetchLinkMsg ? Utility::fetchLinkMessage($lang, VW::JB_CAT, 'edit_job_category_route_unavailable') : null)
                                                                    ?? __('Edit job category route is unavailable. Please contact technical support or your domain administrator.');
                                                    } catch (\Throwable $e) {
                                                        \Log::error('job_categories/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                    }
@endphp
                                                <div class="{{ VC::ACT_BTN_PRIM }}">
                                                    <a  href="{{ $editUrl }}"
                                                        class="{{ VC::BT_SM_FL_CT }}"
                                                        data-url="{{ $editUrl }}"
                                                        data-ajax-popup="true"
                                                        data-title="{{ __('Edit Job Category') }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Edit') }}"
                                                        data-guard-msg="{{ base64_encode($editMsg) }}"
                                                        data-sv-localized="true">
                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                    </a>
                                                </div>
                                            @endcan

                                            @can('delete job category')
                                                @php
                                                    try {
                                                        $destroyBase = VW::JB_CAT . '.destroy';
                                                        $destroyUrl  = (Route::has($destroyBase) && $cid !== '') ? route($destroyBase, $cid) : '#';
                                                        $destroyMsg  = ($hasFetchLinkMsg ? Utility::fetchLinkMessage($lang, VW::JB_CAT, 'destroy_job_category_route_unavailable') : null)
                                                                        ?? __('Delete job category route is unavailable. Please contact technical support or your domain administrator.');
                                                        $delFormId   = 'delete-form-' . ($cid === '' ? 'x' : $cid);
                                                        $cTitle      = ($hasFetchLinkMsg ? Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') : null) ?? 'Are You Sure?';
                                                        $cBody       = ($hasFetchLinkMsg ? Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') : null) ?? 'This action can not be undone. Do you want to continue?';
                                                    } catch (\Throwable $e) {
                                                        \Log::error('job_categories/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                    }
@endphp
                                                <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                    {!! Form::open([
                                                        'method'            => 'DELETE',
                                                        'url'               => $destroyUrl,
                                                        'id'                => $delFormId,
                                                        'data-url'          => $destroyUrl,
                                                        'data-guard-msg'    => $destroyMsg,
                                                        'data-sv-localized' => 'true',
                                                    ]) !!}
                                                        <a  href="#"
                                                            class="{{ VC::BT_SM_CT_PR }}"
                                                            data-bs-toggle="tooltip"
                                                            title="{{ __('Delete') }}"
                                                            data-confirm="{{ __($cTitle) }}|{{ __($cBody) }}"
                                                            data-confirm-yes="document.getElementById('{{ $delFormId }}').submit();">
                                                            <i class="{{ VC::TI_TRS_WT }}"></i>
                                                        </a>
                                                    {!! Form::close() !!}
                                                </div>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="{{ VC::TXCT }}">{{ __('No job categories were available to display.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push(ST::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/routes/jobs/categories/index.js') }}"></script>
@endpush
