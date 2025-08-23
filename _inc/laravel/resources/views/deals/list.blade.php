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
        <script async>
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
          ar: {
            pipeline_change_failed: 'فشل تغيير مسار العملية.'
          },
          da: {
            pipeline_change_failed: 'Kunne ikke ændre pipeline.'
          },
          de: {
            pipeline_change_failed: 'Pipeline konnte nicht gewechselt werden.'
          },
          en: {
            pipeline_change_failed: 'Failed to change pipeline.'
          },
          es: {
            pipeline_change_failed: 'Error al cambiar el pipeline.'
          },
          fr: {
            pipeline_change_failed: 'Échec du changement de pipeline.'
          },
          he: {
            pipeline_change_failed: 'שינוי הצנרת נכשל.'
          },
          it: {
            pipeline_change_failed: 'Impossibile cambiare pipeline.'
          },
          ja: {
            pipeline_change_failed: 'パイプラインの変更に失敗しました。'
          },
          nl: {
            pipeline_change_failed: 'Kon pipeline niet wijzigen.'
          },
          pl: {
            pipeline_change_failed: 'Nie udało się zmienić pipeline.'
          },
          pt: {
            pipeline_change_failed: 'Falha ao alterar pipeline.'
          },
          'pt-br': {
            pipeline_change_failed: 'Falha ao alterar pipeline.'
          },
          ru: {
            pipeline_change_failed: 'Не удалось сменить воронку.'
          },
          tr: {
            pipeline_change_failed: 'Pipeline değiştirilemedi.'
          },
          zh: {
            pipeline_change_failed: '更改管道失败。'
          }
        };
Object.keys(t).forEach(
  k =>
    (window.translations[k] = {
      ...(window.translations[k] || {}),
      ...t[k],
    })
);
     
          })();
    </script>
    <script defer>
        (() => {
          const ERR_FB     = '# ERROR';
          const FL_CLIENT  = 'data-client-localized';
          const FL_GUARD   = 'data-guard-msg';
          const LANG_KEY   = 'erp-np-lang';
          let errorMessage = '';
        
          const getMsg = (key, el) => {
            let msg = ERR_FB;
            if (el.getAttribute(FL_CLIENT) === 'true') {
              msg = el.getAttribute(FL_GUARD) || msg;
            } else {
              let lang = (sessionStorage.getItem(LANG_KEY) || document.documentElement.lang || 'en')
                .toLowerCase().replace(/_/g,'-');
              lang = lang === 'pt-br' ? lang : lang.slice(0,2);
              msg = translations?.[lang]?.[key]
                 ?? el.getAttribute(FL_GUARD)
                 ?? translations?.['en']?.[key]
                 ?? msg;
              if (msg !== ERR_FB) {
                el.setAttribute(FL_GUARD, msg);
                el.setAttribute(FL_CLIENT, 'true');
              }
            }
            return msg;
          };
        
          const showError = message => {
            try {
              let c = document.getElementById('toast-container');
              if (!c) {
                c = document.createElement('div');
                c.id = 'toast-container';
                document.body.appendChild(c);
              }
              const bs = !!document.querySelector('link[href*="bootstrap"]') && window.bootstrap?.Toast;
              if (bs) {
                const t = document.createElement('div');
                t.className = 'toast';
                t.setAttribute('role','alert');
                t.setAttribute('aria-live','assertive');
                t.setAttribute('aria-atomic','true');
                const b = document.createElement('div');
                b.className = 'toast-body';
                b.textContent = message;
                t.appendChild(b);
                c.appendChild(t);
                bootstrap.Toast.getOrCreateInstance(t).show();
              } else {
                alert(message);
              }
            } catch {
              alert(message);
            }
          };
        
          const onUp = () => {
            if (errorMessage) {
              showError(errorMessage);
              errorMessage = '';
            }
          };
          document.addEventListener('pointerup', onUp);
          new MutationObserver((m, obs) => {
            m.forEach(mut => Array.from(mut.removedNodes).forEach(n => {
              if (n === document.documentElement) {
                document.removeEventListener('pointerup', onUp);
                obs.disconnect();
              }
            }));
          }).observe(document.body,{ childList:true, subtree:true });
        
          document.addEventListener('DOMContentLoaded', () => {
            const sel = document.querySelector('.change-pipeline select[name=default_pipeline_id]');
            if (!sel) return;
            if (sel.dataset.listenerAttached === 'true') return;
            sel.dataset.listenerAttached = 'true';
        
            const handler = () => {
              try {
                const form = document.getElementById('change-pipeline');
                if (!form) throw new Error('pipeline_change_failed');
                form.submit();
              } catch (e) {
                errorMessage = getMsg('pipeline_change_failed', sel);
              }
            };
        
            sel.addEventListener('change', handler);
            new MutationObserver((m, obs) => {
              m.forEach(mut => Array.from(mut.removedNodes).forEach(n => {
                if (n === sel) {
                  sel.removeEventListener('change', handler);
                  obs.disconnect();
                }
              }));
            }).observe(document.body,{ childList:true, subtree:true });
          });
        })();
    </script>
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
  @push(StacksConstants::ADM_SCRP_PG)
      <script defer>
          (() => {
              const btn = document.getElementById('deal-kanban-btn');
              if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
              btn.setAttribute('data-listener-active', 'true');
              btn.addEventListener('click', e => {
                  try {
                      const url = btn.getAttribute('data-url') ?? '#';
                      if (url !== '#') return;
                      e.preventDefault();
                      const msg = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                      const bs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                      let container = document.getElementById('toast-container');
                      if (!container) {
                          container = document.createElement('div');
                          container.id = 'toast-container';
                          document.body.appendChild(container);
                      }
                      if (bs) {
                          const toast = document.createElement('div');
                          toast.className = 'toast';
                          toast.setAttribute('role','alert');
                          toast.setAttribute('aria-live','assertive');
                          toast.setAttribute('aria-atomic','true');
                          const body = document.createElement('div');
                          body.className = 'toast-body';
                          body.textContent = msg;
                          toast.appendChild(body);
                          container.appendChild(toast);
                          bootstrap.Toast.getOrCreateInstance(toast).show();
                      } else {
                          alert(msg);
                      }
                      btn.setAttribute('data-failed-route','true');
                  } catch {}
              });
          })();
      </script>
      <script defer>
          (() => {
              const btn = document.getElementById('deal-create-btn');
              if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
              btn.setAttribute('data-listener-active', 'true');
              btn.addEventListener('click', e => {
                  try {
                      const url = btn.getAttribute('data-url') ?? '#';
                      if (url !== '#') return;
                      e.preventDefault();
                      const msg = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                      const bs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                      let container = document.getElementById('toast-container');
                      if (!container) {
                          container = document.createElement('div');
                          container.id = 'toast-container';
                          document.body.appendChild(container);
                      }
                      if (bs) {
                          const toast = document.createElement('div');
                          toast.className = 'toast';
                          toast.setAttribute('role','alert');
                          toast.setAttribute('aria-live','assertive');
                          toast.setAttribute('aria-atomic','true');
                          const body = document.createElement('div');
                          body.className = 'toast-body';
                          body.textContent = msg;
                          toast.appendChild(body);
                          container.appendChild(toast);
                          bootstrap.Toast.getOrCreateInstance(toast).show();
                      } else {
                          alert(msg);
                      }
                      btn.setAttribute('data-failed-route','true');
                  } catch {}
              });
          })();
      </script>
  @endpush
@endsection
@section(YieldingConstants::ADM_CTT)
    @if($pipeline)
        <div class="{{ VC::RW }}">
            <div class="{{ VC::CS3 }}">
                <div class="{{ VC::CD }}">
                    <div class="card-body">
                        <div class="{{ VC::DFL_AIC_JCB }}">
                            <div class="{{ VC::C_AT }} {{ VC::MB3 }} mb-sm-0">
                                <small class="{{ VC::TXT_MT }}">{{ __('Total Deals') }}</small>
                                <h3 class="m-0">{{ $cnt_deal['total'] }}</h3>
                            </div>
                            <div class="{{ VC::C_AT }}">
                                <div class="theme-avatar bg-info">
                                    <i class="ti ti-layers-difference"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="{{ VC::CS3 }}">
                <div class="{{ VC::CD }}">
                    <div class="card-body">
                        <div class="{{ VC::DFL_AIC_JCB }}">
                            <div class="{{ VC::C_AT }} {{ VC::MB3 }} mb-sm-0">
                                <small class="{{ VC::TXT_MT }}">{{ __('This Month Total Deals') }}</small>
                                <h3 class="m-0">{{ $cnt_deal['this_month'] }}</h3>
                            </div>
                            <div class="{{ VC::C_AT }}">
                                <div class="theme-avatar bg-primary">
                                    <i class="ti ti-layers-difference"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="{{ VC::CS3 }}">
                <div class="{{ VC::CD }}">
                    <div class="card-body">
                        <div class="{{ VC::DFL_AIC_JCB }}">
                            <div class="{{ VC::C_AT }} {{ VC::MB3 }} mb-sm-0">
                                <small class="{{ VC::TXT_MT }}">{{ __('This Week Total Deals') }}</small>
                                <h3 class="m-0">{{ $cnt_deal['this_week'] }}</h3>
                            </div>
                            <div class="{{ VC::C_AT }}">
                                <div class="theme-avatar bg-warning">
                                    <i class="ti ti-layers-difference"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="{{ VC::CS3 }}">
                <div class="{{ VC::CD }}">
                    <div class="card-body">
                        <div class="{{ VC::DFL_AIC_JCB }}">
                            <div class="{{ VC::C_AT }} {{ VC::MB3 }} mb-sm-0">
                                <small class="{{ VC::TXT_MT }}">{{ __('Last 30 Days Total Deals') }}</small>
                                <h3 class="m-0">{{ $cnt_deal['last_30days'] }}</h3>
                            </div>
                            <div class="{{ VC::C_AT }}">
                                <div class="theme-avatar bg-danger">
                                    <i class="ti ti-layers-difference"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="{{ VC::RW }}">
            <div class="{{ VC::C12 }}">
                <div class="{{ VC::CD }}">
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="{{ VC::TB }} datatable">
                                <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Price') }}</th>
                                    <th>{{ __('Stage') }}</th>
                                    <th>{{ __('Tasks') }}</th>
                                    <th>{{ __('Users') }}</th>
                                    <th width="300px">{{ __('Action') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse ($deals as $deal)
                                    <tr>
                                        <td>{{ $deal->name }}</td>
                                        <td>{{ $user?->priceFormat($deal->price) }}</td>
                                        <td>{{ $deal->stage->name }}</td>
                                        <td>{{ count($deal->tasks) }}/{{ count($deal->completeTasks) }}</td>
                                        <td>
                                            @foreach($deal->users as $u)
                                                <a href="#" class="{{ VC::BT_SM_MX3 }} p-0 rounded-circle">
                                                    <img
                                                        src="{{ $u->avatar
                                                            ? asset('storage/uploads/avatar/'.$u->avatar)
                                                            : asset('storage/uploads/avatar/avatar.png') }}"
                                                        width="25" height="25"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ $u->name }}"
                                                        class="rounded-circle">
                                                </a>
                                            @endforeach
                                        </td>
                                        @if($user?->type !== 'Client')
                                            <td class="Action">
                                                @can('view deal')
                                                    @if($deal->is_active)
                                                        @php
                                                          $namespace      = ViewsConstants::DL;
                                                          $routeName      = "{$namespace}.show";
                                                          $showRoute      = Route::has($routeName)
                                                              ? route($routeName, $deal->id)
                                                              : '#';
                                                          $showGuardMsg   = Utility::fetchLinkMessage(
                                                              $lang,
                                                              $namespace,
                                                              'deal_show_route_unavailable'
                                                          ) ?? 'Deal show route is unavailable. Please contact technical support or your domain administrator.';
                                                        @endphp
                                                        <div class="{{ VC::ACT_BTN_WRN }}">
                                                            <a
                                                                id="deal-view-btn-{{ $deal->id }}"
                                                                href="{{ $showRoute }}"
                                                                data-url="{{ $showRoute }}"
                                                                data-guard-msg="{{ $showGuardMsg }}"
                                                                class="{{ VC::BT_SM_FL_CT }}"
                                                                data-size="xl"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('View') }}"
                                                                data-title="{{ __('Lead Detail') }}"
                                                            >
                                                                <i class="{{ VC::TI_EYE_WT }}"></i>
                                                            </a>
                                                        </div>
                                                        @push(StacksConstants::ADM_SCRP_PG)
                                                            <script defer>
                                                                (() => {
                                                                    const btn = document.getElementById('deal-view-btn-{{ $deal->id }}');
                                                                    if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                                    btn.setAttribute('data-listener-active', 'true');
                                                                    btn.addEventListener('click', e => {
                                                                        try {
                                                                            const url = btn.getAttribute('data-url') ?? '#';
                                                                            if (url !== '#') return;
                                                                            e.preventDefault();
                                                                            const msg = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                            const bs  = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                                            let container = document.getElementById('toast-container');
                                                                            if (!container) {
                                                                                container = document.createElement('div');
                                                                                container.id = 'toast-container';
                                                                                document.body.appendChild(container);
                                                                            }
                                                                            if (bs) {
                                                                                const toast = document.createElement('div');
                                                                                toast.className = 'toast';
                                                                                toast.setAttribute('role','alert');
                                                                                toast.setAttribute('aria-live','assertive');
                                                                                toast.setAttribute('aria-atomic','true');
                                                                                const body = document.createElement('div');
                                                                                body.className = 'toast-body';
                                                                                body.textContent = msg;
                                                                                toast.appendChild(body);
                                                                                container.appendChild(toast);
                                                                                bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                            } else {
                                                                                alert(msg);
                                                                            }
                                                                            btn.setAttribute('data-failed-route', 'true');
                                                                        } catch {}
                                                                    });
                                                                })();
                                                            </script>
                                                        @endpush
                                                    @endif
                                                @endcan
                                                @can('edit deal')
                                                    <div class="{{ VC::ACT_BTN_INF }}">
                                                      @php
                                                          $ns             = ViewsConstants::DL;
                                                          $routeName      = "{$ns}.edit";
                                                          $editRoute      = Route::has($routeName)
                                                              ? route($routeName, $deal->id)
                                                              : '#';
                                                          $editGuardMsg   = Utility::fetchLinkMessage(
                                                              $lang,
                                                              $ns,
                                                              'deals_edit_route_unavailable'
                                                          ) ?? 'Deal edit route is unavailable. Please contact technical support or your domain administrator.';
                                                      @endphp
                                                      <a
                                                          id="deal-edit-btn-{{ $deal->id }}"
                                                          href="{{ $editRoute }}"
                                                          data-url="{{ $editRoute }}"
                                                          data-ajax-popup="true"
                                                          data-size="xl"
                                                          data-bs-toggle="tooltip"
                                                          title="{{ __('Edit') }}"
                                                          data-title="{{ __('Lead Edit') }}"
                                                          class="{{ VC::BT_SM_FL_CT }}"
                                                      >
                                                          <i class="{{ VC::TI_PC_WT }}"></i>
                                                      </a>
                                                      @push(StacksConstants::ADM_SCRP_PG)
                                                          <script defer>
                                                              (() => {
                                                                  const btn = document.getElementById('deal-edit-btn-{{ $deal->id }}');
                                                                  if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                                  btn.setAttribute('data-listener-active', 'true');
                                                                  btn.addEventListener('click', e => {
                                                                      try {
                                                                          const url = btn.getAttribute('data-url') ?? '#';
                                                                          if (url !== '#') return;
                                                                          e.preventDefault();
                                                                          const msg = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                          const bs  = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                                          let container = document.getElementById('toast-container');
                                                                          if (!container) {
                                                                              container = document.createElement('div');
                                                                              container.id = 'toast-container';
                                                                              document.body.appendChild(container);
                                                                          }
                                                                          if (bs) {
                                                                              const toast = document.createElement('div');
                                                                              toast.className = 'toast';
                                                                              toast.setAttribute('role', 'alert');
                                                                              toast.setAttribute('aria-live', 'assertive');
                                                                              toast.setAttribute('aria-atomic', 'true');
                                                                              const body = document.createElement('div');
                                                                              body.className = 'toast-body';
                                                                              body.textContent = msg;
                                                                              toast.appendChild(body);
                                                                              container.appendChild(toast);
                                                                              bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                          } else {
                                                                              alert(msg);
                                                                          }
                                                                          btn.setAttribute('data-failed-route', 'true');
                                                                      } catch {}
                                                                  });
                                                              })();
                                                          </script>
                                                      @endpush
                                                    </div>
                                                @endcan
                                                @can('delete deal')
                                                    <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                      @php
                                                          $routeKey           = ViewsConstants::DL . '.destroy';
                                                          $kebabRouteKey      = Str::kebab($routeKey);
                                                          $destroyRouteName   = Route::has($routeKey)
                                                              ? $routeKey
                                                              : (Route::has($kebabRouteKey) ? $kebabRouteKey : null);
                                                          $destroyRouteArray  = $destroyRouteName
                                                              ? [$destroyRouteName, $deal->id]
                                                              : ['#'];
                                                          $destroyRouteUrl    = $destroyRouteName
                                                              ? route($destroyRouteName, $deal->id)
                                                              : '#';
                                                          $destroyGuardMsg    = Utility::fetchLinkMessage(
                                                              $lang,
                                                              ViewsConstants::DL,
                                                              'deal_destroy_route_unavailable'
                                                          ) ?? 'Delete deal route is unavailable. Please contact technical support or your domain administrator.';
                                                      @endphp
                                                      {!! Form::open([
                                                          'route'  => $destroyRouteArray,
                                                          'method' => 'DELETE',
                                                          'id'     => 'delete-form-' . $deal->id
                                                      ]) !!}
                                                          <a
                                                              id="delete-deal-btn-{{ $deal->id }}"
                                                              href="{{ $destroyRouteUrl }}"
                                                              data-url="{{ $destroyRouteUrl }}"
                                                              data-guard-msg="{{ $destroyGuardMsg }}"
                                                              class="{{ VC::BT_SM_CT_PR }}"
                                                              data-bs-toggle="tooltip"
                                                              title="{{ __('Delete') }}"
                                                          >
                                                              <i class="ti ti-trash text-white"></i>
                                                          </a>
                                                      {!! Form::close() !!}
                                                      @push(StacksConstants::ADM_SCR_PG)
                                                          <script defer>
                                                              (() => {
                                                                  const btn = document.getElementById('delete-deal-btn-{{ $deal->id }}');
                                                                  if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                                  btn.setAttribute('data-listener-active', 'true');
                                                                  btn.addEventListener('click', e => {
                                                                      try {
                                                                          const url = btn.getAttribute('data-url') || '#';
                                                                          if (url !== '#') return;
                                                                          e.preventDefault();
                                                                          const msg = btn.getAttribute('data-guard-msg') || '# ERROR';
                                                                          const bs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
                                                                          let container = document.getElementById('toast-container');
                                                                          if (!container) {
                                                                              container = document.createElement('div');
                                                                              container.id = 'toast-container';
                                                                              document.body.appendChild(container);
                                                                          }
                                                                          if (bs) {
                                                                              const toast = document.createElement('div');
                                                                              toast.className = 'toast';
                                                                              toast.setAttribute('role','alert');
                                                                              toast.setAttribute('aria-live','assertive');
                                                                              toast.setAttribute('aria-atomic','true');
                                                                              const body = document.createElement('div');
                                                                              body.className = 'toast-body';
                                                                              body.textContent = msg;
                                                                              toast.appendChild(body);
                                                                              container.appendChild(toast);
                                                                              bootstrap.Toast.getOrCreateInstance(toast).show();
                                                                          } else {
                                                                              alert(msg);
                                                                          }
                                                                          btn.setAttribute('data-failed-route', 'true');
                                                                      } catch (error) {}
                                                                  });
                                                              })();
                                                          </script>
                                                      @endpush
                                                    </div>
                                                @endcan
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">{{ __('No data available in table') }}</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
