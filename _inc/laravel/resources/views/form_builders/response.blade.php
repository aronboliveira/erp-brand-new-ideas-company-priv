@php
    try {
$user    = Auth::user();
        $lang    = Utility::fetchUserLang(user: $user);
        $hasForm = !empty($form ?? null) && data_get($form, 'id');
        $formName = $hasForm ? (data_get($form, 'name') ?: __('Unnamed form')) : __('Form not found');
    } catch (\Throwable $e) {
        \Log::error('form_builders/response — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@extends(EL::ADM)

@section(YW::ADM_PG_TTL)
    {{ $hasForm ? ($formName . ' ' . __("Response")) : __('Form not found') }}
@endsection

@push(ST::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/routes/formBuilders/responses.js') }}"></script>
@endpush

@section(YW::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}" {{ Route::has('dashboard') ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">
        @php
            try {
                $indexBase     = VW::FM_BD . '.index';
                $indexKebab    = Str::kebab($indexBase);
                $indexResolved = Route::has($indexBase) ? $indexBase : (Route::has($indexKebab) ? $indexKebab : null);
                $indexUrl      = $indexResolved ? route($indexResolved) : '#';
                $indexGuardMsg = Utility::fetchLinkMessage($lang, VW::FM_BD, 'form_builder_index_route_unavailable') ?? __('Form builder index route is unavailable. Please contact technical support or your domain administrator.');
            } catch (\Throwable $e) {
                \Log::error('form_builders/response — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            }
@endphp
        <a href="{{ $indexUrl }}" data-url="{{ $indexUrl }}" data-guard-msg="{{ base64_encode($indexGuardMsg) }}" data-sv-localized="true">{{ __('Form Builder') }}</a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Response') }}</li>
@endsection

@section(YW::ADM_CTT)
    <div class="row">
        <div class="{{ VC::CXL12 }}">
            <div class="card">
                <div class="{{ VC::CD_BD_TB_BD }}">
                    @if(!$hasForm)
                        <div class="{{ VC::ALT_WRN_MB0 }}" role="alert">{{ __('The requested form was not found or is unavailable.') }}</div>
                    @else
                        @php
                            $responses = data_get($form, 'response');
                            $hasResponses = Utility::isFilled($responses ?? []);
@endphp
                        <div class="{{ VC::TB_RSP }}">
                            <table class="table datatable">
                                @if($hasResponses)
                                    <tbody>
                                        @php
                                            $first = null; $second = null; $third = null;
@endphp
                                        @foreach ($responses as $response)
                                            @php
                                                try {
                                                    $raw = data_get($response, 'response', '{}');
                                                    $respArr = is_string($raw) ? (json_decode($raw, true) ?: []) : (is_array($raw) ? $raw : []);
                                                    if (count($respArr) === 1) { $respArr[''] = ''; $respArr[' '] = ''; }
                                                    elseif (count($respArr) === 2) { $respArr[''] = ''; }
                                                    $firstThree = array_slice($respArr, 0, 3, true);
                                                    $thead = array_values(array_keys($firstThree));
                                                    $th0 = $thead[0] ?? '';
                                                    $th1 = $thead[1] ?? '';
                                                    $th2 = $thead[2] ?? '';
                                                    $head1 = ($first !== $th0) ? $th0 : '';
                                                    $head2 = (!empty($th1) && $second !== $th1) ? $th1 : '';
                                                    $head3 = (!empty($th2) && $third !== $th2) ? $th2 : '';
                                                } catch (\Throwable $e) {
                                                    \Log::error('form_builders/response — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                }
@endphp

                                            @if(!empty($head1) || !empty($head2) || (!empty($head3) && $head3 !== ' '))
                                                <tr>
                                                    <th>{{ $head1 }}</th>
                                                    <th>{{ $head2 }}</th>
                                                    <th>{{ $head3 }}</th>
                                                    @can('view form response')
                                                        <th>#</th>
                                                    @endcan
                                                </tr>
                                            @endif

                                            @php
                                                try {
                                                    $first  = $th0;
                                                    $second = $th1;
                                                    $third  = $th2;
                                                    $vals   = array_values($firstThree);
                                                } catch (\Throwable $e) {
                                                    \Log::error('form_builders/response — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                }
@endphp

                                            <tr>
                                                <td>{{ $vals[0] ?? '-' }}</td>
                                                <td>{{ $vals[1] ?? '-' }}</td>
                                                <td>{{ $vals[2] ?? '-' }}</td>
                                                @can('view form response')
                                                    @php
                                                        try {
                                                            $detailBase     = VW::FM . '.response.detail';
                                                            $detailKebab    = Str::kebab($detailBase);
                                                            $detailResolved = Route::has($detailBase) ? $detailBase : (Route::has($detailKebab) ? $detailKebab : null);
                                                            $respId         = data_get($response, 'id');
                                                            $detailUrl      = ($detailResolved && $respId) ? route($detailResolved, $respId) : '#';
                                                            $detailGuardMsg = Utility::fetchLinkMessage($lang, VW::FM, 'response_detail_route_unavailable') ?? __('Response detail route is unavailable. Please contact technical support or your domain administrator.');
                                                        } catch (\Throwable $e) {
                                                            \Log::error('form_builders/response — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                        }
@endphp
                                                    <td class="Action">
                                                        <div class="{{ VC::ACT_BTN_WRN }}">
                                                            <a href="#"
                                                               class="{{ VC::BT_SM_FL_CT }}"
                                                               data-url="{{ $detailUrl }}"
                                                               data-ajax-popup="true"
                                                               data-size="md"
                                                               data-bs-toggle="tooltip"
                                                               title="{{ __('View') }}"
                                                               data-title="{{ __('Response Detail') }}"
                                                               data-guard-msg="{{ base64_encode($detailGuardMsg) }}"
                                                               data-sv-localized="true">
                                                                <i class="{{ VC::TI_EYE_WT }}"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                @endcan
                                            </tr>
                                        @endforeach
                                    </tbody>
                                @else
                                    <tbody>
                                        <tr>
                                            <td class="{{ VC::TXCT }}">{{ __('No data available in table') }}</td>
                                        </tr>
                                    </tbody>
                                @endif
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
