@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use Illuminate\Support\Facades\{Auth, Route};
    $user = Auth::user();
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Goal Tracking')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Goal Tracking')}}</li>
@endsection
@push(StacksConstants::ADM_CSS)
    <style>
        @import url({{ asset('css/font-awesome.css') }});
    </style>
@endpush
@push(StacksConstants::ADM_SCR_PG)
    <script defer src="{{ asset('js/bootstrap-toggle.js') }}"></script>
    <script defer>
        (() => {
          const errFb = '# ERROR';
          const dataClientLoc = 'data-client-localized';
          const dataGuardMsg = 'data-guard-msg';
        
          function getLocalizedMessage(el, key) {
            let msg = errFb;
            if (el.getAttribute(dataClientLoc) === 'true') {
              msg = el.getAttribute(dataGuardMsg) || errFb;
            } else {
              let lang = (window.sessionStorage.getItem('erp-np-lang')
                || document.documentElement.lang
                || 'en')
                .toLowerCase()
                .replace(/_/g, '-');
              lang = lang === 'pt-br' ? lang : lang.slice(0, 2);
              msg = window.translations?.[lang]?.[key]
                 || el.getAttribute(dataGuardMsg)
                 || window.translations?.['en']?.[key]
                 || errFb;
              if (msg !== errFb) {
                el.setAttribute(dataGuardMsg, msg);
                el.setAttribute(dataClientLoc, 'true');
              }
            }
            return msg;
          }
        
          function showError(msg) {
            const bsLink = document.querySelector("link[href*='bootstrap']");
            if (bsLink && window.bootstrap?.Toast) {
              const container = document.getElementById('toast-container') || (() => {
                const c = document.createElement('div'); c.id = 'toast-container'; document.body.appendChild(c); return c;
              })();
              const toastEl = document.createElement('div');
              toastEl.className = 'toast';
              toastEl.setAttribute('role', 'alert');
              toastEl.setAttribute('aria-live', 'assertive');
              toastEl.setAttribute('aria-atomic', 'true');
              const body = document.createElement('div');
              body.className = 'toast-body';
              body.textContent = msg;
              toastEl.appendChild(body);
              container.appendChild(toastEl);
              window.bootstrap.Toast.getOrCreateInstance(toastEl).show();
            } else {
              alert(msg);
            }
          }
        
          document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.toggleswitch').forEach(el => {
              try {
                if (typeof $(el).bootstrapToggle !== 'function') {
                  throw new Error('bootstrapToggle missing');
                }
                $(el).bootstrapToggle();
              } catch {
                const msg = getLocalizedMessage(el, 'toggle_init_failed');
                el.addEventListener('click', () => showError(msg), { once: true });
              }
            });
        
            const starSelector = "fieldset[id^='demo'] .stars";
            const handleStarClick = e => {
              const tgt = e.target;
              if (!tgt.matches(starSelector)) return;
              try {
                alert(tgt.value);
                tgt.checked = true;
              } catch {
                const msg = getLocalizedMessage(tgt, 'star_click_failed');
                tgt.addEventListener('pointerup', () => showError(msg), { once: true });
              }
            };
        
            if (!document.body.hasAttribute('data-star-listener')) {
              document.body.addEventListener('click', handleStarClick);
              document.body.setAttribute('data-star-listener', 'true');
              const mo = new MutationObserver(() => {
                if (!document.querySelector(starSelector)) {
                  mo.disconnect();
                  document.body.removeEventListener('click', handleStarClick);
                }
              });
              mo.observe(document.body, { childList: true, subtree: true });
            }
          });
        })();
    </script>
@endpush
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
    @can('create goal tracking')
       <a href="#" data-size="lg" data-url="{{ route('goal_trackings.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create New Goal Tracking')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </a>
        @endcan
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-md-12">
            <div class="card">
            <div class="card-body table-border-style">
                    <div class="table-responsive">
                    <table class="table datatable">
                            <thead>
                            <tr>
                                <th>{{__('Goal Type')}}</th>
                                <th>{{__('Subject')}}</th>
                                <th>{{__('Branch')}}</th>
                                <th>{{__('Target Achievement')}}</th>
                                <th>{{__('Start Date')}}</th>
                                <th>{{__('End Date')}}</th>
                                <th>{{__('Rating')}}</th>
                                <th width="20%">{{__('Progress')}}</th>
                                    <th width="200px">{{__('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody class="font-style">
                            @foreach ($goalTrackings as $goalTracking)
                                <tr>
                                    <td>{{ !empty($goalTracking->goalType)?$goalTracking->goalType->name:'' }}</td>
                                    <td>{{$goalTracking->subject}}</td>
                                    <td>{{ !empty($goalTracking->branches)?$goalTracking->branches->name:'' }}</td>
                                    <td>{{$goalTracking->target_achievement}}</td>
                                    <td>{{$user?->dateFormat($goalTracking->start_date)}}</td>
                                    <td>{{$user?->dateFormat($goalTracking->end_date)}}</td>
                                    <td>
                                        @for($i=1; $i<=5; $i++)
                                            @if($goalTracking->rating < $i)
                                                <i class="fas fa-star"></i>
                                            @else
                                                <i class="text-warning fas fa-star"></i>
                                            @endif
                                        @endfor
                                    </td>
                                    <td>
                                        <div class="progress-wrapper">
                                            <span class="progress-percentage"><small class="font-weight-bold"></small>{{$goalTracking->progress}}%</span>
                                            <div class="progress progress-xs mt-2 w-100">
                                                <div class="progress-bar bg-{{Utility::getProgressColor($goalTracking->progress)}}" role="progressbar" aria-valuenow="{{$goalTracking->progress}}" aria-valuemin="0" aria-valuemax="100" style="width: {{$goalTracking->progress}}%;"></div>
                                            </div>
                                        </div>
                                    </td>
                                    @if( Gate::check('edit goal tracking') ||Gate::check('delete goal tracking'))
                                        <td>
                                            @can('edit goal tracking')
                                            <div class="action-btn bg-primary ms-2">
                                                <a href="#" data-url="{{ route('goal_trackings.edit',$goalTracking->id) }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Edit Goal Tracking')}}" class="mx-3 btn btn-sm align-items-center " data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                                                <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i></a>
                                            </div>
                                                @endcan
                                            @can('delete goal tracking')
                                            <div class="action-btn bg-danger ms-2">
                                            {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'route' => ['goal_trackings.destroy', $goalTracking->id],'id'=>'delete-form-'.$goalTracking->id]) !!}
                                                   <a href="#" class="{{ ViewClassNamesConstants::BT_SM_CT_PR }}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm-yes="document.getElementById('delete-form-{{$goalTracking->id}}').submit();">
                                                   <i class="ti ti-trash text-white"></i>
                                                    </a>
                                                {!! Collective\Html\FormFacade::close() !!}
                                            </div>
                                            @endcan
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

