@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants as EL,
        StacksConstants as ST,
        YieldingConstants as YW,
        ViewsConstants as VW
    };
    use App\Models\Utility;
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\{Collection, Facades\Route, Str};

    $user = Auth::user();
    $lang = Utility::fetchUserLang(user: $user);
    $indexBase  = VW::VND . '.payment';
    $indexKebab = Str::kebab($indexBase);
    $indexName  = Route::has($indexBase) ? $indexBase : (Route::has($indexKebab) ? $indexKebab : null);
    $indexUrl   = $indexName ? route($indexName) : '#';
    $indexGuard = Utility::fetchLinkMessage($lang, VW::VND, 'vendor_payment_route_unavailable')
        ?? 'Vendor payment route is unavailable. Please contact technical support or your domain administrator.';
    $formId     = 'vendor-payment-filter-form';
    $resetId    = 'vendor-payment-reset-link';

    $categoryOptions = (($category ?? null) instanceof Collection) ? $category->toArray() : (is_array($category ?? null) ? $category : []);
@endphp

@extends(EL::ADM)

@push(ST::ADM_SCR_PG)
@endpush

@section(YW::ADM_PG_TTL)
    {{ __('Payment') }}
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    {!! Form::open([
                        'url'                  => $indexUrl,
                        'method'               => 'GET',
                        'id'                   => $formId,
                        'data-resolved-action' => $indexUrl,
                        'data-guard-msg'       => $indexGuard,
                        'data-sv-localized'    => 'true',
                    ]) !!}
                        <div class="row d-flex justify-content-end mt-2">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                <div class="all-select-box">
                                    <div class="btn-box">
                                        {{ Form::label('date', __('Date'), ['class' => 'text-type']) }}
                                        {{ Form::text('date', request('date'), ['class' => 'form-control datepicker-range']) }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                <div class="all-select-box">
                                    <div class="btn-box">
                                        {{ Form::label('category', __('Category'), ['class' => 'text-type']) }}
                                        {{ Form::select('category', ['' => 'All'] + $categoryOptions, request('category'), ['class' => 'form-control select2']) }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto my-auto">
                                <button type="submit" class="apply-btn" data-bs-toggle="tooltip" title="{{ __('apply') }}">
                                    <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                </button>
                                <a id="{{ $resetId }}"
                                   href="{{ $indexUrl }}"
                                   data-url="{{ $indexUrl }}"
                                   data-guard-msg="{{ $indexGuard }}"
                                   data-sv-localized="true"
                                   class="reset-btn"
                                   data-bs-toggle="tooltip"
                                   title="{{ __('Reset') }}">
                                    <span class="btn-inner--icon"><i class="ti ti-trash-restore-alt"></i></span>
                                </a>
                            </div>
                        </div>
                    {!! Form::close() !!}

                    <div class="table-responsive mt-3">
                        <table class="table table-striped mb-0 dataTable">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Category') }}</th>
                                    <th>{{ __('Description') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $rows = (($payments ?? null) instanceof Collection || is_array($payments ?? null)) ? $payments : [];
                                @endphp
                                @forelse($rows as $pmt)
                                    @php
                                        $dateRaw = data_get($pmt, 'date');
                                        $dateStr = $dateRaw
                                            ? (method_exists($user, 'dateFormat') ? ($user->dateFormat($dateRaw) ?? (string)$dateRaw) : (string)$dateRaw)
                                            : __('No date available');

                                        $amtRaw  = data_get($pmt, 'amount');
                                        $amtStr  = (isset($amtRaw) && $amtRaw !== '' && is_numeric($amtRaw))
                                            ? (method_exists($user, 'priceFormat') ? ($user->priceFormat($amtRaw) ?? (string)$amtRaw) : (string)$amtRaw)
                                            : __('No amount available');

                                        $catRaw  = data_get($pmt, 'category');
                                        $catStr  = (isset($catRaw) && $catRaw !== '') ? (string)$catRaw : __('No category available');

                                        $descRaw = data_get($pmt, 'description');
                                        $descStr = (isset($descRaw) && $descRaw !== '') ? (string)$descRaw : __('No description available');
                                    @endphp
                                    <tr>
                                        <td>{{ $dateStr }}</td>
                                        <td>{{ $amtStr }}</td>
                                        <td>{{ $catStr }}</td>
                                        <td>{{ $descStr }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">
                                            {{ __('No payment information available') }}
                                        </td>
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
    <script defer src="{{ asset('assets/js/routes/vendors/payments/index.js') }}"></script>
@endpush
