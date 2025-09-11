@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants as EL,
        StacksConstants as ST,
        ViewClassNamesConstants as VC,
        YieldingConstants as YW,
        ViewsConstants as VW
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\{Auth, Route};
    use Illuminate\Support\{Collection, Str};
    use Collective\Html\FormFacade as Form;

    $user    = Auth::user();
    $lang    = Utility::fetchUserLang(user: $user);
    $hasForm = !empty($form ?? null) && data_get($form, 'id');
    $formName = $hasForm ? (data_get($form, 'name') ?: __('Unnamed form')) : __('Form not found');
@endphp

@extends(EL::ADM)

@section(YW::ADM_PG_TTL)
    {{ $hasForm ? ($formName . ' ' . __("Response")) : __('Form not found') }}
@endsection

@push(ST::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/routes/formBuilders/responses.js') }}"></script>
@endpush

@section(YW::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}" {{ Route::has('dashboard') ? '' : 'aria-disabled=true' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">
        @php
            $indexBase     = VW::FM_BD . '.index';
            $indexKebab    = Str::kebab($indexBase);
            $indexResolved = Route::has($indexBase) ? $indexBase : (Route::has($indexKebab) ? $indexKebab : null);
            $indexUrl      = $indexResolved ? route($indexResolved) : '#';
            $indexGuardMsg = Utility::fetchLinkMessage($lang, VW::FM_BD, 'index_route_unavailable') ?? __('Form builder index route is unavailable. Please contact technical support or your domain administrator.');
        @endphp
        <a href="{{ $indexUrl }}" data-url="{{ $indexUrl }}" data-guard-msg="{{ $indexGuardMsg }}" data-sv-localized="true">{{ __('Form Builder') }}</a>
    </li>
    <li class="breadcrumb-item">{{ __('Response') }}</li>
@endsection

@section(YW::ADM_CTT)
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body table-border-style">
                    @if(!$hasForm)
                        <div class="alert alert-warning mb-0" role="alert">{{ __('The requested form was not found or is unavailable.') }}</div>
                    @else
                        @php
                            $responses = data_get($form, 'response');
                            $hasResponses = (is_array($responses) && count($responses) > 0) || ($responses instanceof Collection && $responses->isNotEmpty());
                        @endphp
                        <div class="table-responsive">
                            <table class="table datatable">
                                @if($hasResponses)
                                    <tbody>
                                        @php
                                            $first = null; $second = null; $third = null;
                                        @endphp
                                        @foreach ($responses as $response)
                                            @php
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
                                                $first  = $th0;
                                                $second = $th1;
                                                $third  = $th2;
                                                $vals   = array_values($firstThree);
                                            @endphp

                                            <tr>
                                                <td>{{ $vals[0] ?? '-' }}</td>
                                                <td>{{ $vals[1] ?? '-' }}</td>
                                                <td>{{ $vals[2] ?? '-' }}</td>
                                                @can('view form response')
                                                    @php
                                                        $detailBase     = VW::FM . '.response.detail';
                                                        $detailKebab    = Str::kebab($detailBase);
                                                        $detailResolved = Route::has($detailBase) ? $detailBase : (Route::has($detailKebab) ? $detailKebab : null);
                                                        $respId         = data_get($response, 'id');
                                                        $detailUrl      = ($detailResolved && $respId) ? route($detailResolved, $respId) : '#';
                                                        $detailGuardMsg = Utility::fetchLinkMessage($lang, VW::FM, 'response_detail_route_unavailable') ?? __('Response detail route is unavailable. Please contact technical support or your domain administrator.');
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
                                                               data-guard-msg="{{ $detailGuardMsg }}"
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
                                            <td class="text-center">{{ __('No data available in table') }}</td>
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
