@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewClassNamesConstants as VC,
        YieldingConstants,
    };
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth,Route};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Deals')}} @if($pipeline) - {{$pipeline->name}} @endif
@endsection
@push(StacksConstants::ADM_CSS)
    <link rel="stylesheet" href="{{asset('css/summernote/summernote-bs4.css')}}">
@endpush
@push(StacksConstants::ADM_SCR_PG)
    <script src="{{asset('css/summernote/summernote-bs4.js')}}"></script>
    <script async src="{{ asset('assets/js/routes/deals/lang/list.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/deals/pipelines/index.js') }}"></script>
@endpush
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Lead')}}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
  @php
      $indexRoute = Route::has(ViewsConstants::DL . '.index')
          ? route(ViewsConstants::DL . '.index')
          : '#';
      $indexGuardMsg = Utility::fetchLinkMessage(
          $lang,
          ViewsConstants::DL,
          'deals_index_route_unavailable'
      ) ?? 'Deal index route is unavailable. Please contact technical support or your domain administrator.';
      $createRoute = Route::has(ViewsConstants::DL . '.create')
          ? route(ViewsConstants::DL . '.create')
          : '#';
      $createGuardMsg = Utility::fetchLinkMessage(
          $lang,
          ViewsConstants::DL,
          'deals_create_route_unavailable'
      ) ?? 'Deal create route is unavailable. Please contact technical support or your domain administrator.';
  @endphp
  <div class="{{ VC::FEND }}">
      <a
          id="deal-kanban-btn"
          href="{{ $indexRoute }}"
          data-url="{{ $indexRoute }}"
          data-guard-msg="{{ $indexGuardMsg }}"
          data-bs-toggle="tooltip"
          title="{{ __('Kanban View') }}"
          class="{{ VC::BT_SM_PM }}"
      >
          <i class="ti ti-layout-grid"></i>
      </a>
      <a
          id="deal-create-btn"
          href="{{ $createRoute }}"
          data-url="{{ $createRoute }}"
          data-guard-msg="{{ $createGuardMsg }}"
          data-size="lg"
          data-ajax-popup="true"
          data-bs-toggle="tooltip"
          title="{{ __('Create New Deal') }}"
          class="{{ VC::BT_SM_PM }}"
      >
          <i class="{{ VC::TI_PLS }}"></i>
      </a>
  </div>
  @push(StacksConstants::ADM_SCR_PG)
      <script defer src="{{ asset('assets/js/routes/deals/kanban.js') }}"></script>
      <script defer src="{{ asset('assets/js/routes/deals/createList.js') }}"></script>
  @endpush
@endsection

