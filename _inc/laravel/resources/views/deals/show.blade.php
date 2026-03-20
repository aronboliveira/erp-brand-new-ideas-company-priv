@php
    try {
$authUser = Auth::user();
        $user = $authUser ? User::find($authUser->creatorId()) : null;
        $lang = Utility::fetchUserLang(user:$authUser);
    } catch (\Throwable $e) {
        \Log::error('deals/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{!empty($deal) && isset($deal->name) ? $deal->name : __('No deal name available')}}
@endsection
@push(StacksConstants::ADM_CSS)
    <link rel="stylesheet" href="{{asset('css/summernote/summernote-bs4.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/plugins/dropzone.min.css')}}">
@endpush
@push(StacksConstants::ADM_SCR_PG)
    <script src="{{asset('css/summernote/summernote-bs4.js')}}"></script>
    <script src="{{asset('assets/js/plugins/dropzone-amd-module.min.js')}}"></script>
    <script async src="{{ asset('assets/js/routes/deals/lang/show.js') }}"></script>
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

          const showError = message => (window.RouteGuard?.showToast || (m => alert(m)))(message);

          const onUp = () => {
            if (errorMessage) {
              showError(errorMessage);
              errorMessage = '';
            }
          };
          document.addEventListener('pointerup', onUp);
          new MutationObserver((m, obs) => {
            m.forEach(mut =>
              Array.from(mut.removedNodes).forEach(n => {
                if (n === document.documentElement) {
                  document.removeEventListener('pointerup', onUp);
                  obs.disconnect();
                }
              })
            );
          }).observe(document.body,{ childList:true, subtree:true });

          document.addEventListener('DOMContentLoaded', () => {
            try {
              new bootstrap.ScrollSpy(document.body, {
                target: '#deal-sidenav',
                offset: 300
              });
            } catch {}

            Dropzone.autoDiscover = false;
            const dzEl = document.querySelector('#dropzonewidget');
            if (dzEl) {
              const myDropzone = new Dropzone(dzEl, {
                maxFiles: 20,
                parallelUploads: 1,
                url: "{{ route(ViewsConstants::DL . '.file.upload', $deal->id) }}",
                success(file, res) {
                  if (res.is_success) {
                    show_toastr('success', res.success_msg, 'success');
                    attachButtons(file, res);
                  } else {
                    myDropzone.removeFile(file);
                    show_toastr('error', res.error, 'error');
                  }
                },
                error(file, res) {
                  myDropzone.removeFile(file);
                  show_toastr('error', res.error || '{{ __("Upload error") }}', 'error');
                }
              });
              myDropzone.on('sending', (file, xhr, formData) => {
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                formData.append('deal_id', {{ $deal->id }});
              });

              const attachButtons = (file, res) => {
                const download = document.createElement('a');
                download.href = res.download;
                download.className = 'badge bg-info mx-1';
                download.title = translations.en?.download || 'Download';
                download.innerHTML = "<i class='{{ VC::TI_DWN }}'></i>";

                const del = document.createElement('a');
                del.href = res.delete;
                del.className = 'badge bg-danger mx-1';
                del.title = translations.en?.delete || 'Delete';
                del.innerHTML = "<i class='{{ VC::TI_TRS }}'></i>";
                del.addEventListener('click', e => {
                  e.preventDefault(); e.stopPropagation();
                  if (!confirm('{{ __("Are you sure?") }}')) return;
                  $.ajax({
                    url: del.href,
                    type: 'DELETE',
                    data: {_token: $('meta[name="csrf-token"]').attr('content')},
                  })
                  .done(r => {
                    if (r.is_success) file.previewElement.remove();
                    else show_toastr('error', r.error, 'error');
                  })
                  .fail(() => {
                    show_toastr('error', getMsg('file_delete_failed', del), 'error');
                  });
                });

                file.previewTemplate.appendChild(download);
                @can('edit deal')
                file.previewTemplate.appendChild(del);
                @endcan
              };
            }
            const sn = $('.summernote-simple');
            if (sn.length) {
              @can('edit deal')
              sn.on('summernote.blur', function() {
                const el = this;
                $.post("{{ route(ViewsConstants::DL . '.note.store', $deal->id) }}", {
                  _token: $('meta[name="csrf-token"]').attr('content'),
                  notes: $(el).val()
                })
                .fail(() => {
                  show_toastr('error', getMsg('notes_save_failed', el), 'error');
                });
              });
              @else
              sn.summernote('disable');
              @endcan
            }
            $(document).on('click', '.task-checkbox', function() {
              const cb = this;
              const lbl = $(cb).closest('label');
              $.ajax({
                url: $(cb).data('url'),
                type: 'PUT',
                data: {
                  _token: $('meta[name="csrf-token"]').attr('content'),
                  status: cb.value
                }
              })
              .done(res => {
                if (res.is_success) {
                  cb.value = res.status;
                  lbl.toggleClass('strike', !!res.status);
                  lbl.find('.badge')
                    .toggleClass('badge-success', res.status)
                    .toggleClass('badge-warning', !res.status)
                    .text(res.status_label);
                  show_toastr('success', res.success, 'success');
                } else {
                  show_toastr('error', res.error, 'error');
                }
              })
              .fail(() => {
                show_toastr('error', getMsg('task_toggle_failed', cb), 'error');
              });
            });
          });
        })();
    </script>
@endpush
@section(YieldingConstants::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    @php
        try {
            $namespace      = ViewsConstants::DL;
            $indexRoute     = Route::has("{$namespace}.index")
                ? route("{$namespace}.index")
                : '#';
            $indexGuardMsg  = Utility::fetchLinkMessage(
                $lang,
                $namespace,
                'deals_index_route_unavailable'
            ) ?? 'Deal index route is unavailable. Please contact technical support or your domain administrator.';
        } catch (\Throwable $e) {
            \Log::error('deals/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
@endphp
    <li class="{{ VC::BCI }}">
        <a
            id="deal-index-breadcrumb"
            href="{{ $indexRoute }}"
            data-url="{{ $indexRoute }}"
            data-guard-msg="{{ base64_encode($indexGuardMsg) }}"
        >
            {{ __('Deal') }}
        </a>
    </li>
    @push(StacksConstants::ADM_SCR_PG)
        <script defer src="{{ asset('assets/js/routes/deals/lang/indexBreadcrumb.js') }}"></script>
    @endpush
    <li class="{{ VC::BCI }}"> {{ !empty($deal) && isset($deal->name) ? $deal->name : __('No deal name available') }}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('view deal')
            @if(!empty($deal))
              @php
                try {
                    $namespace     = ViewsConstants::DL;
                    $routeName     = "{$namespace}.show";
                    $showRoute     = (Route::has($routeName) && $deal->is_active)
                        ? route($routeName, $deal->id)
                        : '#';
                    $showGuardMsg  = Utility::fetchLinkMessage(
                        $lang,
                        $namespace,
                        'deal_show_route_unavailable'
                    ) ?? 'Deal show route is unavailable. Please contact technical support or your domain administrator.';
                } catch (\Throwable $e) {
                    \Log::error('deals/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
              <a
                  id="deal-convert-btn-{{ $deal->id }}"
                  href="{{ $showRoute }}"
                  data-url="{{ $showRoute }}"
                  data-guard-msg="{{ base64_encode($showGuardMsg) }}"
                  data-size="lg"
                  data-bs-toggle="tooltip"
                  title="{{ __('Already Converted To Deal') }}"
                  class="{{ VC::BT_SM_PM }}"
              >
                  <i class="ti ti-exchange"></i>
              </a>
              @push(StacksConstants::ADM_SCR_PG)
                  <script defer>
                      (() => {
                          const btn = document.getElementById('deal-convert-btn-{{ $deal->id }}');
                          if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                          btn.setAttribute('data-listener-active', 'true');
                          btn.addEventListener('click', e => {
                              try {
                                  const url = btn.getAttribute('data-url') ?? '#';
                                  if (url !== '#') return;
                                  e.preventDefault();
                                  const msg = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                                  (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                  btn.setAttribute('data-failed-route', 'true');
                              } catch {}
                          });
                      })();
                  </script>
              @endpush
            @else
              @php
                  try {
                      $namespace         = ViewsConstants::DL;
                      $routeName         = "{$namespace}.show_convert";
                      $showConvertRoute  = Route::has($routeName)
                          ? route($routeName, $deal->id)
                          : (Route::has(Str::kebab($routeName))
                              ? route(Str::kebab($routeName), $deal->id)
                              : '#');
                      $showConvertGuardMsg = Utility::fetchLinkMessage(
                          $lang,
                          $namespace,
                          'deal_show_convert_route_unavailable'
                      ) ?? 'Convert deal route is unavailable. Please contact technical support or your domain administrator.';
                  } catch (\Throwable $e) {
                      \Log::error('deals/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                  }
@endphp
              <a
                  id="deal-show-convert-btn-{{ $deal->id }}"
                  href="{{ $showConvertRoute }}"
                  data-url="{{ $showConvertRoute }}"
                  data-guard-msg="{{ base64_encode($showConvertGuardMsg) }}"
                  data-size="lg"
                  data-bs-toggle="tooltip"
                  title="{{ __('Convert [' . $deal->subject . '] To Deal') }}"
                  class="{{ VC::BT_SM_PM }}"
              >
                  <i class="ti ti-exchange"></i>
              </a>
              @push(StacksConstants::ADM_SCR_PG)
                  <script defer>
                      (() => {
                          const btn = document.getElementById('deal-show-convert-btn-{{ $deal->id }}');
                          if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                          btn.setAttribute('data-listener-active', 'true');
                          btn.addEventListener('click', e => {
                              try {
                                  const url = btn.getAttribute('data-url') ?? '#';
                                  if (url !== '#') return;
                                  e.preventDefault();
                                  const msg = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                                  (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                  btn.setAttribute('data-failed-route', 'true');
                              } catch {}
                          });
                      })();
                  </script>
              @endpush
            @endif
        @endcan
        @php
            try {
                $ns                   = ViewsConstants::DL;
                $labelsRouteName      = "{$ns}.labels";
                $labelsRoute          = Route::has($labelsRouteName)
                    ? route($labelsRouteName, $deal->id)
                    : URL::to("deals/{$deal->id}/labels");
                $labelsGuardMsg       = Utility::fetchLinkMessage(
                    $lang,
                    $ns,
                    'deals_labels_route_unavailable'
                ) ?? 'Deal labels route is unavailable. Please contact technical support or your domain administrator.';
                $editRouteName        = "{$ns}.edit";
                $editRoute            = Route::has($editRouteName)
                    ? route($editRouteName, $deal->id)
                    : '#';
                $editGuardMsg         = Utility::fetchLinkMessage(
                    $lang,
                    $ns,
                    'deals_edit_route_unavailable'
                ) ?? 'Deal edit route is unavailable. Please contact technical support or your domain administrator.';
            } catch (\Throwable $e) {
                \Log::error('deals/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
            }
@endphp
        <div class="{{ VC::FEND }}">
            <a
                id="deal-labels-btn-{{ $deal->id }}"
                href="{{ $labelsRoute }}"
                data-url="{{ $labelsRoute }}"
                data-guard-msg="{{ base64_encode($labelsGuardMsg) }}"
                data-ajax-popup="true"
                data-size="lg"
                data-bs-toggle="tooltip"
                title="{{ __('Label') }}"
                class="{{ VC::BT_SM_PM }}"
            >
                <i class="ti ti-bookmark"></i>
            </a>
            <a
                id="deal-edit-btn-{{ $deal->id }}"
                href="{{ $editRoute }}"
                data-url="{{ $editRoute }}"
                data-ajax-popup="true"
                data-size="lg"
                data-bs-toggle="tooltip"
                title="{{ __('Edit') }}"
                class="{{ VC::BT_SM_PM }}"
            >
                <i class="{{ VC::TI_PC }}"></i>
            </a>
        </div>
        @push(StacksConstants::ADM_SCR_PG)
            <script defer>
                (() => {
                    const btn = document.getElementById('deal-labels-btn-{{ $deal->id }}');
                    if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                    btn.setAttribute('data-listener-active', 'true');
                    btn.addEventListener('click', e => {
                        try {
                            const url = btn.getAttribute('data-url') ?? '#';
                            if (url !== '#') return;
                            e.preventDefault();
                            const msg = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                            (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                            btn.setAttribute('data-failed-route', 'true');
                        } catch {}
                    });
                })();
            </script>
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
                            (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                            btn.setAttribute('data-failed-route', 'true');
                        } catch {}
                    });
                })();
            </script>
        @endpush
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="{{ VC::CS12 }}">
            <div class="row">
                <div class="{{ VC::CXL3 }}">
                    @php
                        try {
                            $navItems = [
                                ['id' => 'general',           'label' => __('General')],
                                ['id' => 'tasks',             'label' => __('Task')],
                                ['id' => 'users_products',    'label' => __('Users').' | '.__('Products')],
                                ['id' => 'sources_emails',    'label' => __('Sources').' | '.__('Emails')],
                                ['id' => 'discussion_note',   'label' => __('Discussion').' | '.__('Notes')],
                                ['id' => 'files',             'label' => __('Files')],
                                ['id' => 'calls',             'label' => __('Calls')],
                                ['id' => 'activity',          'label' => __('Activity')],
                            ];
                        } catch (\Throwable $e) {
                            \Log::error('deals/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                        }
@endphp
                    <div class="{{ VC::CD_STK }}" style="top:30px">
                      @php
                          try {
                              $sidenavGuardMsg = Utility::fetchLinkMessage(
                                  $lang,
                                  ViewsConstants::DL,
                                  'section_anchor_deal_unavailable'
                              ) ?? 'Deal section anchor is unavailable. Please contact technical support or your domain administrator.';
                          } catch (\Throwable $e) {
                              \Log::error('deals/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                          }
@endphp
                      <div class="{{ VC::LG_FLSH }}" id="deal-sidenav">
                          @foreach($navItems as $item)
                              <a href="#{{ $item['id'] }}" data-target-id="{{ $item['id'] }}" class="{{ VC::LGI_ACT_NBD }}">
                                  {{ $item['label'] }}
                                  <div class="{{ VC::FEND }}"><i class="{{ VC::TI_CHV_RT }}"></i></div>
                              </a>
                          @endforeach
                      </div>
                      @push(StacksConstants::ADM_SCR_PG)
                          <script defer>
                              (() => {
                                  const sidenav = document.getElementById('deal-sidenav');
                                  if (!sidenav || sidenav.getAttribute('data-listener-active') === 'true') return;
                                  sidenav.setAttribute('data-listener-active', 'true');
                                  sidenav.querySelectorAll('a').forEach(l => {
                                      if (!l || l.getAttribute('data-click-listener') === 'true') return;
                                      l.setAttribute('data-click-listener', 'true');
                                      l.addEventListener('click', e => {
                                          try {
                                              const href = l.getAttribute('href') ?? '';
                                              if (!href || href.charAt(0) !== '#') return;
                                              const targetId = l.getAttribute('data-target-id') ?? href.slice(1);
                                              const target = document.getElementById(targetId);
                                              if (target) return;
                                              e.preventDefault();
                                              const msg = {{ json_encode($sidenavGuardMsg) }};
                                              (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                              l.setAttribute('data-failed-route', 'true');
                                          } catch {}
                                      });
                                  });
                              })();
                          </script>
                      @endpush
                    </div>
                </div>
                <div class="col-xl-9">
                    <?php
                        $tasks = $deal->tasks ?? [];
                        $products = $deal->products() ?? [];
                        $sources = $deal->sources() ?? [];
                        $calls = $deal->calls ?? [];
                        $emails = $deal->emails ?? [];
                    ?>
                    <div id="general" class="{{ VC::CD }}">
                        <div class="{{ VC::CD }}-body">
                            @php
                                try {
                                    $pipelineName             = $deal->pipeline?->name ?? __('-');
                                    $stageName                = $deal->stage?->name ?? __('-');
                                    $isDateFormatAvailable    = isset($authUser) && method_exists($authUser, 'dateFormat');
                                    $isPriceFormatAvailable   = isset($authUser) && method_exists($authUser, 'priceFormat');
                                    $createdAt                = $deal->created_at ?? null;
                                    $priceVal                 = $deal->price ?? null;

                                    $tasksCount               = is_countable($tasks ?? []) ? count($tasks ?? []) : 0;
                                    $productsCount            = is_countable($products ?? []) ? count($products ?? []) : 0;
                                    $sourcesCount             = is_countable($sources ?? []) ? count($sources ?? []) : 0;
                                    $filesCount               = is_countable($deal->files ?? []) ? count($deal->files ?? []) : 0;

                                    $stats = [
                                        ['label' => __('Task'),    'count' => $tasksCount,    'icon' => 'ti-subtask',        'bg' => 'bg-danger'],
                                        ['label' => __('Product'), 'count' => $productsCount, 'icon' => 'ti-shopping-cart', 'bg' => 'bg-info'],
                                        ['label' => __('Source'),  'count' => $sourcesCount,  'icon' => 'ti-social',        'bg' => 'bg-primary'],
                                        ['label' => __('Files'),   'count' => $filesCount,    'icon' => 'ti-file',          'bg' => 'bg-warning'],
                                    ];
                                } catch (\Throwable $e) {
                                    \Log::error('deals/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                }
@endphp
                            <div class="{{ VC::RW }}">
                                <div class="{{ VC::CM3 }} col-sm-6">
                                    <div class="{{ VC::DFL }} align-items-start">
                                        <div class="theme-avatar {{ VC::BG_P }}">
                                            <i class="ti ti-test-pipe"></i>
                                        </div>
                                        <div class="{{ VC::MS2 }}">
                                            <p class="{{ VC::TXT_MT }} {{ VC::TXSM }} {{ VC::MB0 }}">{{ __('Pipeline') }}</p>
                                            <h5 class="{{ VC::MB0 }} text-success">{{ $pipelineName }}</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="{{ VC::CM3 }} col-sm-6 {{ VC::MY3 }} my-sm-0">
                                    <div class="{{ VC::DFL }} align-items-start">
                                        <div class="theme-avatar {{ VC::BG_P }}">
                                            <i class="ti ti-server"></i>
                                        </div>
                                        <div class="{{ VC::MS2 }}">
                                            <p class="{{ VC::TXT_MT }} {{ VC::TXSM }} {{ VC::MB0 }}">{{ __('Stage') }}</p>
                                            <h5 class="{{ VC::MB0 }} {{ VC::TX_PM }}">{{ $stageName }}</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="{{ VC::CM3 }} col-sm-6">
                                    <div class="{{ VC::DFL }} align-items-start">
                                        <div class="theme-avatar bg-warning">
                                            <i class="{{ VC::TI_CLD }}"></i>
                                        </div>
                                        <div class="{{ VC::MS2 }}">
                                            <p class="{{ VC::TXT_MT }} {{ VC::TXSM }} {{ VC::MB0 }}">{{ __('Created') }}</p>
                                            <h5 class="{{ VC::MB0 }} text-warning">
                                                {{ $createdAt ? ($isDateFormatAvailable ? $authUser?->dateFormat($createdAt) : $createdAt) : '-' }}
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="{{ VC::CM3 }} col-sm-6">
                                    <div class="{{ VC::DFL }} align-items-start">
                                        <div class="theme-avatar bg-info">
                                            <i class="{{ VC::TI_RPT_MN }}"></i>
                                        </div>
                                        <div class="{{ VC::MS2 }}">
                                            <p class="{{ VC::TXT_MT }} {{ VC::TXSM }} {{ VC::MB0 }}">{{ __('Price') }}</p>
                                            <h5 class="{{ VC::MB0 }} text-info">
                                                {{ is_numeric($priceVal) ? ($isPriceFormatAvailable ? $authUser?->priceFormat($priceVal) : $priceVal) : '-' }}
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="{{ VC::RW }}">
                        @if(Utility::isFilled($stats) ?? [])
                          @foreach($stats as $stat)
                              <div class="{{ VC::CM3 }} col-sm-3">
                                  <div class="{{ VC::CD }}">
                                      <div class="{{ VC::CD }}-body">
                                          <div class="{{ VC::RW }} align-items-center justify-content-between">
                                              <div class="{{ VC::C_AT }} {{ VC::MB3 }} mb-sm-0">
                                                  <small class="{{ VC::TXT_MT }}">{{ !empty($stat['label']) ? $stat['label'] : __('No label available for statistic') }}</small>
                                                  <h3 class="m-0">{{ !empty($stat['count']) ? $stat['count'] : 'null' }}</h3>
                                              </div>
                                              <div class="{{ VC::C_AT }}">
                                                  <div class="theme-avatar {{ !empty($stat['bg']) ? $stat['bg'] : '' }}">
                                                      <i class="{{ !empty($stat['icon']) ? $stat['icon'] : '' }}"></i>
                                                  </div>
                                              </div>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                          @endforeach
                        @else
                          <div class="{{ VC::C12 }}">
                              <div class="{{ VC::ALERT }} {{ VC::ALERT_INFO }} {{ VC::MB0 }}">
                                  {{ __('No statistics available') }}
                              </div>
                          </div>
                        @endif
                    </div>
                    <div id="users_products">
                        <div class="{{ VC::RW }}">
                          <div class="{{ VC::C6 }}">
                            <div class="{{ VC::CD }}">
                              <div class="{{ VC::CD }}-header">
                                <div class="{{ VC::DFL_AIC_JCB }}">
                                  <h5>{{ __('Users') }}</h5>
                                  <div class="{{ VC::FEND }}">
                                    @php
                                        try {
                                            $routeKey = ViewsConstants::DL . '.users.edit';
                                            $kebabRouteKey = Str::kebab($routeKey);
                                            $editRouteName = Route::has($routeKey)
                                                ? $routeKey
                                                : (Route::has($kebabRouteKey) ? $kebabRouteKey : null);
                                            $editRouteUrl = $editRouteName ? route($editRouteName, $deal->id) : '#';
                                            $editGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::DL, 'deal_users_edit_route_unavailable')
                                                ?? 'Add user in deal route is unavailable. Please contact technical support or your domain administrator.';
                                        } catch (\Throwable $e) {
                                            \Log::error('deals/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                        }
@endphp
                                    <a
                                      id="deal-users-edit-btn-{{ $deal->id }}"
                                      href="{{ $editRouteUrl }}"
                                      data-url="{{ $editRouteUrl }}"
                                      data-guard-msg="{{ base64_encode($editGuardMsg) }}"
                                      data-size="md"
                                      data-ajax-popup="true"
                                      data-bs-toggle="tooltip"
                                      title="{{ __('Add User') }}"
                                      class="{{ VC::BT_SM_PM }}"
                                    >
                                      <i class="{{ VC::TI_PLS }}"></i>
                                    </a>
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer>
                                            (() => {
                                                const btn = document.getElementById('deal-users-edit-btn-{{ $deal->id }}');
                                                if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                btn.setAttribute('data-listener-active', 'true');
                                                btn.addEventListener('click', e => {
                                                    try {
                                                        const url = btn.getAttribute('data-url') || '#';
                                                        if (url !== '#') return;
                                                        e.preventDefault();
                                                        const msg = btn.getAttribute('data-guard-msg') || '# ERROR';
                                                        (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                        btn.setAttribute('data-failed-route', 'true');
                                                    } catch (e) {}
                                                });
                                            })();
                                        </script>
                                    @endpush
                                  </div>
                                </div>
                              </div>
                              <div class="{{ VC::CD }}-body">
                                <div class="{{ VC::TB_RSP }}">
                                  <table class="{{ VC::TB }} table-hover {{ VC::MB0 }}">
                                    <thead>
                                      <tr>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Action') }}</th>
                                      </tr>
                                    </thead>
                                    <tbody>
                                      @foreach($deal->users as $user)
                                        <tr>
                                          <td>
                                            <div class="{{ VC::DFL }} align-items-center">
                                              <div>
                                                <img
                                                  @if($user->avatar)
                                                    src="{{ asset('storage/uploads/avatar/'.$user->avatar) }}"
                                                  @else
                                                    src="{{ asset('storage/uploads/avatar/avatar.png') }}"
                                                  @endif
                                                  class="wid-30 rounded-circle {{ VC::ME3 }}"
                                                >
                                              </div>
                                              <p class="{{ VC::MB0 }}">{{ $user->name }}</p>
                                            </div>
                                          </td>
                                          @can('edit deal')
                                            <td>
                                              @php
                                                  try {
                                                      $ns               = ViewsConstants::DL;
                                                      $destroyName      = "{$ns}.users.destroy";
                                                      $resolvedDestroy  = Route::has($destroyName)
                                                          ? $destroyName
                                                          : (Route::has(Str::kebab($destroyName))
                                                              ? Str::kebab($destroyName)
                                                              : null);
                                                      $destroyActionUrl = $resolvedDestroy
                                                          ? route($resolvedDestroy, [$deal->id, $user->id])
                                                          : '#';
                                                      $destroyGuardMsg  = Utility::fetchLinkMessage(
                                                          $lang,
                                                          $ns,
                                                          'users_destroy_route_unavailable'
                                                      ) ?? 'Deal user delete route is unavailable. Please contact technical support or your domain administrator.';
                                                      $formId           = 'deal-user-delete-form-' . $deal->id . '-' . $user->id;
                                                      $btnId            = 'deal-user-delete-btn-' . $deal->id . '-' . $user->id;
                                                  } catch (\Throwable $e) {
                                                      \Log::error('deals/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                  }
@endphp
                                              <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                  @if($resolvedDestroy)
                                                      {!! Form::open([
                                                          'method'        => 'DELETE',
                                                          'route'         => [$resolvedDestroy, $deal->id, $user->id],
                                                          'id'            => $formId,
                                                          'data-url'      => $destroyActionUrl,
                                                          'data-guard-msg'=> $destroyGuardMsg
                                                      ]) !!}
                                                  @else
                                                      {!! Form::open([
                                                          'method'        => 'DELETE',
                                                          'url'           => '#',
                                                          'id'            => $formId,
                                                          'data-url'      => $destroyActionUrl,
                                                          'data-guard-msg'=> $destroyGuardMsg
                                                      ]) !!}
                                                  @endif
                                                      <a
                                                          id="{{ $btnId }}"
                                                          href="#"
                                                          class="{{ VC::BT_SM_CT_PR }}"
                                                          data-form-id="{{ $formId }}"
                                                          data-bs-toggle="tooltip"
                                                          title="{{ __('Delete') }}"
                                                      >
                                                          <i class="{{ VC::TI_TRS_WT }}"></i>
                                                      </a>
                                                  {!! Form::close() !!}
                                              </div>
                                              @push(StacksConstants::ADM_SCR_PG)
                                                  <script defer>
                                                      (() => {
                                                          const btn = document.getElementById('{{ $btnId }}');
                                                          if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                          btn.setAttribute('data-listener-active', 'true');
                                                          btn.addEventListener('click', e => {
                                                              try {
                                                                  const formId = btn.getAttribute('data-form-id') ?? '';
                                                                  const form = formId ? document.getElementById(formId) : null;
                                                                  if (!form) return;
                                                                  const action = form.getAttribute('action') ?? form.getAttribute('data-url') ?? '#';
                                                                  if (action !== '#') { e.preventDefault(); form.submit(); return; }
                                                                  e.preventDefault();
                                                                  const msg = form.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                  (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                  btn.setAttribute('data-failed-route', 'true');
                                                              } catch {}
                                                          });
                                                      })();
                                                  </script>
                                              @endpush
                                            </td>
                                          @endcan
                                        </tr>
                                      @endforeach
                                    </tbody>
                                  </table>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="{{ VC::C6 }}">
                            <div class="{{ VC::CD }}">
                              <div class="{{ VC::CD }}-header">
                                <div class="{{ VC::DFL_AIC_JCB }}">
                                  <h5>{{ __('Products') }}</h5>
                                  <div class="{{ VC::FEND }}">
                                    @php
                                        try {
                                            $routeKey = ViewsConstants::DL . '.products.edit';
                                            $kebabRouteKey = Str::kebab($routeKey);
                                            $editRouteName = Route::has($routeKey)
                                                ? $routeKey
                                                : (Route::has($kebabRouteKey) ? $kebabRouteKey : null);
                                            $editRouteUrl = $editRouteName ? route($editRouteName, $deal->id) : '#';
                                            $editGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::DL, 'deal_products_edit_route_unavailable')
                                                ?? 'Add product route is unavailable. Please contact technical support or your domain administrator.';
                                        } catch (\Throwable $e) {
                                            \Log::error('deals/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                        }
@endphp
                                    <a
                                      id="deal-products-edit-btn-{{ $deal->id }}"
                                      href="{{ $editRouteUrl }}"
                                      data-url="{{ $editRouteUrl }}"
                                      data-guard-msg="{{ base64_encode($editGuardMsg) }}"
                                      data-size="md"
                                      data-ajax-popup="true"
                                      data-bs-toggle="tooltip"
                                      title="{{ __('Add Product') }}"
                                      class="{{ VC::BT_SM_PM }}"
                                    >
                                      <i class="{{ VC::TI_PLS }}"></i>
                                    </a>
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer>
                                            (() => {
                                                const btn = document.getElementById('deal-products-edit-btn-{{ $deal->id }}');
                                                if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                btn.setAttribute('data-listener-active', 'true');
                                                btn.addEventListener('click', e => {
                                                    try {
                                                        const url = btn.getAttribute('data-url') || '#';
                                                        if (url !== '#') return;
                                                        e.preventDefault();
                                                        const msg = btn.getAttribute('data-guard-msg') || '# ERROR';
                                                        (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                        btn.setAttribute('data-failed-route', 'true');
                                                    } catch (e) {}
                                                });
                                            })();
                                        </script>
                                    @endpush
                                  </div>
                                </div>
                              </div>
                              <div class="{{ VC::CD }}-body">
                                <div class="{{ VC::TB_RSP }}">
                                  <table class="{{ VC::TB }} table-hover {{ VC::MB0 }}">
                                    <thead>
                                      <tr>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Price') }}</th>
                                        <th>{{ __('Action') }}</th>
                                      </tr>
                                    </thead>
                                    <tbody>
                                      @foreach($deal->products() as $product)
                                        <tr>
                                          <td>{{ $product->name }}</td>
                                          <td>{{ $authUser?->priceFormat($product->sale_price) }}</td>
                                          @can('edit deal')
                                            <td>
                                              @php
                                                try {
                                                    $ns                = ViewsConstants::DL;
                                                    $destroyName       = "{$ns}.products.destroy";
                                                    $resolvedDestroy   = Route::has($destroyName)
                                                        ? $destroyName
                                                        : (Route::has(Str::kebab($destroyName)) ? Str::kebab($destroyName) : null);
                                                    $destroyActionUrl  = $resolvedDestroy ? route($resolvedDestroy, [$deal->id, $product->id]) : '#';
                                                    $destroyGuardMsg   = Utility::fetchLinkMessage($lang, $ns, 'products_destroy_route_unavailable') ?? 'Deal product delete route is unavailable. Please contact technical support or your domain administrator.';
                                                    $formId            = 'deal-product-delete-form-' . $deal->id . '-' . $product->id;
                                                    $btnId             = 'deal-product-delete-btn-' . $deal->id . '-' . $product->id;
                                                } catch (\Throwable $e) {
                                                    \Log::error('deals/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                }
@endphp
                                              <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                  @if($resolvedDestroy)
                                                      {!! Form::open([
                                                          'method'         => 'DELETE',
                                                          'route'          => [$resolvedDestroy, $deal->id, $product->id],
                                                          'id'             => $formId,
                                                          'data-url'       => $destroyActionUrl,
                                                          'data-guard-msg' => $destroyGuardMsg
                                                      ]) !!}
                                                  @else
                                                      {!! Form::open([
                                                          'method'         => 'DELETE',
                                                          'url'            => '#',
                                                          'id'             => $formId,
                                                          'data-url'       => $destroyActionUrl,
                                                          'data-guard-msg' => $destroyGuardMsg
                                                      ]) !!}
                                                  @endif
                                                      <a
                                                          id="{{ $btnId }}"
                                                          href="#"
                                                          class="{{ VC::BT_SM_CT_PR }}"
                                                          data-form-id="{{ $formId }}"
                                                          data-bs-toggle="tooltip"
                                                          title="{{ __('Delete') }}"
                                                      >
                                                          <i class="{{ VC::TI_TRS_WT }}"></i>
                                                      </a>
                                                  {!! Form::close() !!}
                                              </div>
                                              @push(StacksConstants::ADM_SCR_PG)
                                                  <script defer>
                                                      (() => {
                                                          const btn = document.getElementById('{{ $btnId }}');
                                                          if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                          btn.setAttribute('data-listener-active', 'true');
                                                          btn.addEventListener('click', e => {
                                                              try {
                                                                  const formId = btn.getAttribute('data-form-id') ?? '';
                                                                  const form = formId ? document.getElementById(formId) : null;
                                                                  if (!form) return;
                                                                  const action = form.getAttribute('action') ?? form.getAttribute('data-url') ?? '#';
                                                                  if (action !== '#') { e.preventDefault(); form.submit(); return; }
                                                                  e.preventDefault();
                                                                  const msg = form.getAttribute('data-guard-msg') ?? '# ERROR';
                                                                  (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                  btn.setAttribute('data-failed-route', 'true');
                                                              } catch {}
                                                          });
                                                      })();
                                                  </script>
                                              @endpush
                                            </td>
                                          @endcan
                                        </tr>
                                      @endforeach
                                    </tbody>
                                  </table>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                    </div>
                    <div id="sources_emails">
                        <div class="{{ VC::RW }}">
                          <div class="{{ VC::C6 }}">
                            <div class="{{ VC::CD }}">
                              <div class="{{ VC::CD }}-header">
                                <div class="{{ VC::DFL_AIC_JCB }}">
                                  <h5>{{ __('Sources') }}</h5>
                                  <div class="{{ VC::FEND }}">
                                    @php
                                        try {
                                            $routeKey = ViewsConstants::DL . '.sources.edit';
                                            $kebabRouteKey = Str::kebab($routeKey);
                                            $editRouteName = Route::has($routeKey)
                                                ? $routeKey
                                                : (Route::has($kebabRouteKey) ? $kebabRouteKey : null);
                                            $editRouteUrl = $editRouteName ? route($editRouteName, $deal->id) : '#';
                                            $editGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::DL, 'deal_sources_edit_route_unavailable')
                                                ?? 'Add source route is unavailable. Please contact technical support or your domain administrator.';
                                        } catch (\Throwable $e) {
                                            \Log::error('deals/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                        }
@endphp
                                    <a
                                      id="deal-sources-edit-btn-{{ $deal->id }}"
                                      href="{{ $editRouteUrl }}"
                                      data-url="{{ $editRouteUrl }}"
                                      data-guard-msg="{{ base64_encode($editGuardMsg) }}"
                                      data-size="md"
                                      data-ajax-popup="true"
                                      data-bs-toggle="tooltip"
                                      title="{{ __('Add Source') }}"
                                      class="{{ VC::BT_SM_PM }}"
                                    >
                                      <i class="{{ VC::TI_PLS }}"></i>
                                    </a>
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer>
                                            (() => {
                                                const btn = document.getElementById('deal-sources-edit-btn-{{ $deal->id }}');
                                                if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                btn.setAttribute('data-listener-active', 'true');
                                                btn.addEventListener('click', e => {
                                                    try {
                                                        const url = btn.getAttribute('data-url') || '#';
                                                        if (url !== '#') return;
                                                        e.preventDefault();
                                                        const msg = btn.getAttribute('data-guard-msg') || '# ERROR';
                                                        (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                        btn.setAttribute('data-failed-route', 'true');
                                                    } catch (e) {}
                                                });
                                            })();
                                        </script>
                                    @endpush
                                  </div>
                                </div>
                              </div>
                              <div class="{{ VC::CD }}-body">
                                <div class="{{ VC::TB_RSP }}">
                                  <table class="{{ VC::TB }} table-hover {{ VC::MB0 }}">
                                    <thead>
                                      <tr>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Action') }}</th>
                                      </tr>
                                    </thead>
                                    <tbody>
                                      @foreach($sources as $source)
                                        <tr>
                                          <td>{{ $source->name }}</td>
                                          @can('edit deal')
                                            <td>
                                              @php
                                                  try {
                                                      $routeKey = ViewsConstants::DL . '.sources.destroy';
                                                      $kebabRouteKey = Str::kebab($routeKey);
                                                      $destroyRouteName = Route::has($routeKey)
                                                          ? $routeKey
                                                          : (Route::has($kebabRouteKey) ? $kebabRouteKey : null);
                                                      $destroyRouteArr = $destroyRouteName
                                                          ? [$destroyRouteName, $deal->id, $source->id]
                                                          : ['#'];
                                                      $destroyRouteUrl = $destroyRouteName
                                                          ? route($destroyRouteName, [$deal->id, $source->id])
                                                          : '#';
                                                      $destroyGuardMsg = Utility::fetchLinkMessage(
                                                          $lang,
                                                          ViewsConstants::DL,
                                                          'deal_sources_destroy_route_unavailable'
                                                      ) ?? 'Delete deal source route is unavailable. Please contact technical support or your domain administrator.';
                                                  } catch (\Throwable $e) {
                                                      \Log::error('deals/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                  }
@endphp
                                              <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                  {!! Collective\Html\FormFacade::open([
                                                      'method' => 'DELETE',
                                                      'route'  => $destroyRouteArr,
                                                      'id'     => 'delete-source-form-' . $deal->id . '-' . $source->id
                                                  ]) !!}
                                                      <a
                                                          id="delete-source-btn-{{ $deal->id }}-{{ $source->id }}"
                                                          href="{{ $destroyRouteUrl }}"
                                                          data-url="{{ $destroyRouteUrl }}"
                                                          data-guard-msg="{{ base64_encode($destroyGuardMsg) }}"
                                                          class="{{ VC::BT_SM_CT_PR }}"
                                                          data-bs-toggle="tooltip"
                                                          title="{{ __('Delete') }}"
                                                      >
                                                          <i class="{{ VC::TI_TRS_WT }}"></i>
                                                      </a>
                                                  {!! Collective\Html\FormFacade::close() !!}
                                              </div>
                                              @push(StacksConstants::ADM_SCR_PG)
                                                  <script defer>
                                                      (() => {
                                                          const btn = document.getElementById('delete-source-btn-{{ $deal->id }}-{{ $source->id }}');
                                                          if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                          btn.setAttribute('data-listener-active', 'true');
                                                          btn.addEventListener('click', e => {
                                                              try {
                                                                  const url = btn.getAttribute('data-url') || '#';
                                                                  if (url !== '#') return;
                                                                  e.preventDefault();
                                                                  const msg = btn.getAttribute('data-guard-msg') || '# ERROR';
                                                                  (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                                  btn.setAttribute('data-failed-route', 'true');
                                                              } catch (error) {
                                                              }
                                                          });
                                                      })();
                                                  </script>
                                              @endpush
                                            </td>
                                          @endcan
                                        </tr>
                                      @endforeach
                                    </tbody>
                                  </table>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="{{ VC::C6 }}">
                            <div class="{{ VC::CD }}">
                              <div class="{{ VC::CD }}-header">
                                <div class="{{ VC::DFL_AIC_JCB }}">
                                  <h5>{{ __('Emails') }}</h5>
                                  @can('create deal email')
                                    <div class="{{ VC::FEND }}">
                                      @php
                                          try {
                                              $ns               = ViewsConstants::DL;
                                              $routeName        = "{$ns}.emails.create";
                                              $emailCreateRoute = Route::has($routeName)
                                                  ? route($routeName, $deal->id)
                                                  : (Route::has(Str::kebab($routeName))
                                                      ? route(Str::kebab($routeName), $deal->id)
                                                      : '#');
                                              $emailCreateGuard = Utility::fetchLinkMessage(
                                                  $lang,
                                                  $ns,
                                                  'emails_create_route_unavailable'
                                              ) ?? 'Deal email create route is unavailable. Please contact technical support or your domain administrator.';
                                          } catch (\Throwable $e) {
                                              \Log::error('deals/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                          }
@endphp
                                      <a
                                          id="deal-email-create-btn-{{ $deal->id }}"
                                          href="{{ $emailCreateRoute }}"
                                          data-url="{{ $emailCreateRoute }}"
                                          data-guard-msg="{{ base64_encode($emailCreateGuard) }}"
                                          data-size="lg"
                                          data-ajax-popup="true"
                                          data-bs-toggle="tooltip"
                                          title="{{ __('Create Email') }}"
                                          class="{{ VC::BT_SM_PM }}"
                                      >
                                          <i class="{{ VC::TI_PLS }}"></i>
                                      </a>
                                      @push(StacksConstants::ADM_SCR_PG)
                                          <script defer>
                                              (() => {
                                                  const btn = document.getElementById('deal-email-create-btn-{{ $deal->id }}');
                                                  if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                  btn.setAttribute('data-listener-active', 'true');
                                                  btn.addEventListener('click', e => {
                                                      try {
                                                          const url = btn.getAttribute('data-url') ?? '#';
                                                          if (url !== '#') return;
                                                          e.preventDefault();
                                                          const msg = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                                                          (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                          btn.setAttribute('data-failed-route', 'true');
                                                      } catch {}
                                                  });
                                              })();
                                          </script>
                                      @endpush
                                    </div>
                                  @endcan
                                </div>
                              </div>
                              <div class="{{ VC::CD }}-body">
                                <ul class="{{ VC::LG_FLSH }} mt-2">
                                  @if(!$emails->isEmpty())
                                    @foreach($emails as $email)
                                      <li class="{{ VC::LGI }} px-0">
                                        <div class="{{ VC::DBL }} d-sm-flex align-items-start">
                                          <img
                                            src="{{ asset('storage/uploads/avatar/avatar.png') }}"
                                            class="{{ VC::IMG_FL }} wid-40 {{ VC::ME3 }} {{ VC::MB2 }} mb-sm-0"
                                            alt="image"
                                          >
                                          <div class="{{ VC::W100 }}">
                                            <div class="{{ VC::DFL_AIC_JCB }}">
                                              <div class="{{ VC::MB3 }} mb-sm-0">
                                                <h5 class="{{ VC::MB0 }}">{{ $email->subject }}</h5>
                                                <span class="{{ VC::TXT_MT }} {{ VC::TXSM }}">{{ $email->to }}</span>
                                              </div>
                                              <div class="{{ VC::FM_CHK }} form-switch form-switch-right {{ VC::MB2 }}">
                                                {{ $email->created_at->diffForHumans() }}
                                              </div>
                                            </div>
                                          </div>
                                        </div>
                                      </li>
                                    @endforeach
                                  @else
                                    <li class="{{ VC::TXCT }}">{{ __('No Emails Available.!') }}</li>
                                  @endif
                                </ul>
                              </div>
                            </div>
                          </div>
                        </div>
                    </div>
                    <div id="discussion_note">
                        <div class="{{ VC::RW }}">
                          <div class="{{ VC::C6 }}">
                            <div class="{{ VC::CD }}">
                              <div class="{{ VC::CD }}-header">
                                <div class="{{ VC::DFL_AIC_JCB }}">
                                  <h5>{{ __('Discussion') }}</h5>
                                  <div class="{{ VC::FEND }}">
                                    @php
                                        try {
                                            $ns = ViewsConstants::DL;
                                            $routeName = "{$ns}.discussions.create";
                                            $createRoute = Route::has($routeName)
                                                ? route($routeName, $deal->id)
                                                : (Route::has(Str::kebab($routeName))
                                                    ? route(Str::kebab($routeName), $deal->id)
                                                    : '#');
                                            $createGuardMsg = Utility::fetchLinkMessage(
                                                $lang,
                                                $ns,
                                                'discussions_create_route_unavailable'
                                            ) ?? 'Deal discussions create route is unavailable. Please contact technical support or your domain administrator.';
                                        } catch (\Throwable $e) {
                                            \Log::error('deals/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                        }
@endphp
                                    <a
                                        id="deal-discussions-create-btn-{{ $deal->id }}"
                                        href="{{ $createRoute }}"
                                        data-url="{{ $createRoute }}"
                                        data-guard-msg="{{ base64_encode($createGuardMsg) }}"
                                        data-size="lg"
                                        data-ajax-popup="true"
                                        data-bs-toggle="tooltip"
                                        title="{{ __('Add Message') }}"
                                        class="{{ VC::BT_SM_PM }}"
                                    >
                                        <i class="{{ VC::TI_PLS }}"></i>
                                    </a>
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer>
                                            (() => {
                                                const btn = document.getElementById('deal-discussions-create-btn-{{ $deal->id }}');
                                                if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                btn.setAttribute('data-listener-active','true');
                                                btn.addEventListener('click', e => {
                                                    try {
                                                        const url = btn.getAttribute('data-url') ?? '#';
                                                        if (url !== '#') return;
                                                        e.preventDefault();
                                                        const msg = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                                                        (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                        btn.setAttribute('data-failed-route','true');
                                                    } catch {}
                                                });
                                            })();
                                        </script>
                                    @endpush
                                  </div>
                                </div>
                              </div>
                              <div class="{{ VC::CD }}-body">
                                <ul class="{{ VC::LG_FLSH }} mt-2">
                                  @if(!$deal->discussions->isEmpty())
                                    @foreach($deal->discussions as $discussion)
                                      <li class="{{ VC::LGI }} px-0">
                                        <div class="{{ VC::DBL }} d-sm-flex align-items-start">
                                          <img
                                            src="{{ $discussion->user->avatar
                                              ? asset('storage/uploads/avatar/'.$discussion->user->avatar)
                                              : asset('storage/uploads/avatar/avatar.png') }}"
                                            class="{{ VC::IMG_FL }} wid-40 {{ VC::ME3 }} {{ VC::MB2 }} mb-sm-0"
                                            alt="image"
                                          >
                                          <div class="{{ VC::W100 }}">
                                            <div class="{{ VC::DFL_AIC_JCB }}">
                                              <div class="{{ VC::MB3 }} mb-sm-0">
                                                <h5 class="{{ VC::MB0 }}">{{ $discussion->comment }}</h5>
                                                <span class="{{ VC::TXT_MT }} {{ VC::TXSM }}">{{ $discussion->user->name }}</span>
                                              </div>
                                              <div class="form-switch form-switch-right {{ VC::MB4 }}">
                                                {{ $discussion->created_at->diffForHumans() }}
                                              </div>
                                            </div>
                                          </div>
                                        </div>
                                      </li>
                                    @endforeach
                                  @else
                                    <li class="{{ VC::TXCT }}">{{ __('No Data Available.!') }}</li>
                                  @endif
                                </ul>
                              </div>
                            </div>
                          </div>
                          <div class="{{ VC::C6 }}">
                            <div class="{{ VC::CD }}">
                              <div class="{{ VC::CD }}-header">
                                <div class="{{ VC::DFL_AIC_JCB }}">
                                  <h5>{{ __('Notes') }}</h5>
                                  @php
 $plan = \App\Models\Plan::getPlan($user->plan);
@endphp
                                  @if($plan?->{PlansConstants::COL_GPT} == 1)
                                    @php
                                        try {
                                            $grammarRoute = Route::has('grammar')
                                                ? route('grammar', ['grammar'])
                                                : '#';
                                            $grammarGuardMsg = Utility::fetchLinkMessage(
                                                $lang,
                                                ViewsConstants::DL,
                                                'grammar_route_unavailable'
                                            ) ?? 'Grammar check with AI route is unavailable. Please contact technical support or your domain administrator.';
                                            $generateParams = isset($deal) && isset($deal->id) ? ['deal' => $deal->id] : ['deal'];
                                            $generateRoute = Route::has('generate')
                                                ? route('generate', $generateParams)
                                                : '#';
                                            $generateGuardMsg = Utility::fetchLinkMessage(
                                                $lang,
                                                ViewsConstants::DL,
                                                'generate_route_unavailable'
                                            ) ?? 'Generate content with AI route is unavailable. Please contact technical support or your domain administrator.';
                                        } catch (\Throwable $e) {
                                            \Log::error('deals/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                        }
@endphp
                                    <div class="{{ VC::FEND }}">
                                        <a
                                            id="grammar-check-btn"
                                            href="{{ $grammarRoute }}"
                                            data-url="{{ $grammarRoute }}"
                                            data-guard-msg="{{ base64_encode($grammarGuardMsg) }}"
                                            data-size="md"
                                            class="{{ VC::BT_LG }}"
                                            data-ajax-popup-over="true"
                                            data-bs-placement="top"
                                            title="{{ __('Grammar check with AI') }}"
                                        >
                                            <i class="ti ti-rotate"></i> {{ __('Grammar check with AI') }}
                                        </a>
                                        <a
                                            id="generate-ai-btn{{ isset($deal) && isset($deal->id) ? '-'.$deal->id : '' }}"
                                            href="{{ $generateRoute }}"
                                            data-url="{{ $generateRoute }}"
                                            data-guard-msg="{{ base64_encode($generateGuardMsg) }}"
                                            data-size="md"
                                            class="{{ VC::BT_LG }}"
                                            data-ajax-popup-over="true"
                                            data-bs-placement="top"
                                            title="{{ __('Generate content with AI') }}"
                                        >
                                            <i class="{{ VC::FAS_RB }}"></i> {{ __('Generate with AI') }}
                                        </a>
                                    </div>
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer>
                                            (() => {
                                                const btn = document.getElementById('grammar-check-btn');
                                                if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                btn.setAttribute('data-listener-active','true');
                                                btn.addEventListener('click', e => {
                                                    try {
                                                        const url = btn.getAttribute('data-url') ?? '#';
                                                        if (url !== '#') return;
                                                        e.preventDefault();
                                                        const msg = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                                                        (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                        btn.setAttribute('data-failed-route','true');
                                                    } catch {}
                                                });
                                            })();
                                        </script>
                                        <script defer>
                                            (() => {
                                                const btn = document.querySelector('[id^="generate-ai-btn"]');
                                                if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                btn.setAttribute('data-listener-active','true');
                                                btn.addEventListener('click', e => {
                                                    try {
                                                        const url = btn.getAttribute('data-url') ?? '#';
                                                        if (url !== '#') return;
                                                        e.preventDefault();
                                                        const msg = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                                                        (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                        btn.setAttribute('data-failed-route','true');
                                                    } catch {}
                                                });
                                            })();
                                        </script>
                                    @endpush
                                  @endif
                                </div>
                              </div>
                              <div class="{{ VC::CD }}-body">
                                <textarea class="summernote-simple grammar_textarea" name="note">{{ $deal->notes }}</textarea>
                              </div>
                            </div>
                          </div>
                        </div>
                    </div>
                    <div id="files" class="{{ VC::CD }}">
                        <div class="{{ VC::CD }}-header">
                          <h5>{{ __('Files') }}</h5>
                        </div>
                        <div class="{{ VC::CD }}-body">
                          <div class="{{ VC::CM12 }} dropzone top-5-scroll browse-file" id="dropzonewidget"></div>
                        </div>
                    </div>
                    <div id="calls" class="{{ VC::CD }}">
                        <div class="{{ VC::CD }}-header">
                          <div class="{{ VC::DFL_AIC_JCB }}">
                            <h5>{{ __('Calls') }}</h5>
                            @can('create deal call')
                              <div class="{{ VC::FEND }}">
                                @php
                                    try {
                                        $routeKey = ViewsConstants::DL . '.calls.create';
                                        $kebabRouteKey = Str::kebab($routeKey);
                                        $createRouteName = Route::has($routeKey)
                                            ? $routeKey
                                            : (Route::has($kebabRouteKey) ? $kebabRouteKey : null);
                                        $createRouteUrl = $createRouteName ? route($createRouteName, $deal->id) : '#';
                                        $createGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::DL, 'deal_calls_create_route_unavailable')
                                            ?? 'Add call route for deal is unavailable. Please contact technical support or your domain administrator.';
                                    } catch (\Throwable $e) {
                                        \Log::error('deals/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                    }
@endphp
                                <a
                                  id="deal-calls-create-btn-{{ $deal->id }}"
                                  href="{{ $createRouteUrl }}"
                                  data-url="{{ $createRouteUrl }}"
                                  data-guard-msg="{{ base64_encode($createGuardMsg) }}"
                                  data-size="lg"
                                  data-ajax-popup="true"
                                  data-bs-toggle="tooltip"
                                  title="{{ __('Add Call') }}"
                                  class="{{ VC::BT_SM_PM }}"
                                >
                                  <i class="{{ VC::TI_PLS }}"></i>
                                </a>
                                @push(StacksConstants::ADM_SCR_PG)
                                    <script defer>
                                        (() => {
                                            const btn = document.getElementById('deal-calls-create-btn-{{ $deal->id }}');
                                            if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                            btn.setAttribute('data-listener-active', 'true');
                                            btn.addEventListener('click', e => {
                                                try {
                                                    const url = btn.getAttribute('data-url') || '#';
                                                    if (url !== '#') return;
                                                    e.preventDefault();
                                                    const msg = btn.getAttribute('data-guard-msg') || '# ERROR';
                                                    (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                    btn.setAttribute('data-failed-route', 'true');
                                                } catch (e) {}
                                            });
                                        })();
                                    </script>
                                @endpush
                              </div>
                            @endcan
                          </div>
                        </div>
                        <div class="{{ VC::CD }}-body">
                          <div class="{{ VC::TB_RSP }}">
                            <table class="{{ VC::TB }} table-hover {{ VC::MB0 }}">
                              <thead>
                                <tr>
                                  <th>{{ __('Subject') }}</th>
                                  <th>{{ __('Call Type') }}</th>
                                  <th>{{ __('Duration') }}</th>
                                  <th>{{ __('User') }}</th>
                                  <th>{{ __('Action') }}</th>
                                </tr>
                              </thead>
                              <tbody>
                                @foreach($calls as $call)
                                  <tr>
                                    <td>{{ $call->subject }}</td>
                                    <td>{{ ucfirst($call->call_type) }}</td>
                                    <td>{{ $call->duration }}</td>
                                    <td>{{ $call->getLeadCallUser->name ?? '-' }}</td>
                                    <td>
                                      @can('edit deal call')
                                        <div class="{{ VC::ACT_BTN_INF }}">
                                          @php
                                              try {
                                                  $ns            = ViewsConstants::DL;
                                                  $routeName     = "{$ns}.calls.edit";
                                                  $editRoute     = Route::has($routeName)
                                                      ? route($routeName, [$deal->id, $call->id])
                                                      : (Route::has(Str::kebab($routeName))
                                                          ? route(Str::kebab($routeName), [$deal->id, $call->id])
                                                          : '#');
                                                  $editGuardMsg  = Utility::fetchLinkMessage(
                                                      $lang,
                                                      $ns,
                                                      'calls_edit_route_unavailable'
                                                  ) ?? 'Deal call edit route is unavailable. Please contact technical support or your domain administrator.';
                                              } catch (\Throwable $e) {
                                                  \Log::error('deals/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                              }
@endphp
                                          <a
                                              id="deal-call-edit-btn-{{ $deal->id }}-{{ $call->id }}"
                                              href="{{ $editRoute }}"
                                              class="{{ VC::BT_SM_FL_CT }}"
                                              data-url="{{ $editRoute }}"
                                              data-guard-msg="{{ base64_encode($editGuardMsg) }}"
                                              data-ajax-popup="true"
                                              data-size="xl"
                                              data-bs-toggle="tooltip"
                                              title="{{ __('Edit') }}"
                                              data-title="{{ __('Edit Call') }}"
                                          >
                                              <i class="{{ VC::TI_PC_WT }}"></i>
                                          </a>
                                          @push(StacksConstants::ADM_SCR_PG)
                                              <script defer>
                                                  (() => {
                                                      const btn = document.getElementById('deal-call-edit-btn-{{ $deal->id }}-{{ $call->id }}');
                                                      if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                      btn.setAttribute('data-listener-active', 'true');
                                                      btn.addEventListener('click', e => {
                                                          try {
                                                              const url = btn.getAttribute('data-url') ?? '#';
                                                              if (url !== '#') return;
                                                              e.preventDefault();
                                                              const msg = btn.getAttribute('data-guard-msg') ?? '# ERROR';
                                                              (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                              btn.setAttribute('data-failed-route', 'true');
                                                          } catch {}
                                                      });
                                                  })();
                                              </script>
                                          @endpush
                                        </div>
                                      @endcan
                                      @can('delete deal call')
                                        <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                          @php
                                              try {
                                                  $routeKey = ViewsConstants::DL . '.calls.destroy';
                                                  $kebabRouteKey = Str::kebab($routeKey);
                                                  $destroyRouteName = Route::has($routeKey)
                                                      ? $routeKey
                                                      : (Route::has($kebabRouteKey) ? $kebabRouteKey : null);
                                                  $destroyRouteArr = $destroyRouteName
                                                      ? [$destroyRouteName, $deal->id, $call->id]
                                                      : ['#'];
                                                  $destroyRouteUrl = $destroyRouteName
                                                      ? route($destroyRouteName, [$deal->id, $call->id])
                                                      : '#';
                                                  $destroyGuardMsg = Utility::fetchLinkMessage(
                                                      isset($lang) && $lang ? $lang : Utility::fetchUserLang(),
                                                      ViewsConstants::DL,
                                                      'deal_calls_destroy_route_unavailable'
                                                  ) ?? 'Delete call route is unavailable. Please contact technical support or your domain administrator.';
                                              } catch (\Throwable $e) {
                                                  \Log::error('deals/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                              }
@endphp
                                          {!! Form::open([
                                              'method' => 'DELETE',
                                              'route'  => $destroyRouteArr,
                                              'id'     => 'delete-form-' . $call->id
                                          ]) !!}
                                              <a
                                                  id="delete-call-btn-{{ $deal->id }}-{{ $call->id }}"
                                                  href="{{ $destroyRouteUrl }}"
                                                  data-url="{{ $destroyRouteUrl }}"
                                                  data-guard-msg="{{ base64_encode($destroyGuardMsg) }}"
                                                  class="{{ VC::BT_SM_CT_PR }}"
                                                  data-bs-toggle="tooltip"
                                                  title="{{ __('Delete') }}"
                                              >
                                                  <i class="{{ VC::TI_TRS_WT }}"></i>
                                              </a>
                                          {!! Form::close() !!}
                                          @push(StacksConstants::ADM_SCR_PG)
                                              <script defer>
                                                  (() => {
                                                      const btn = document.getElementById('delete-call-btn-{{ $deal->id }}-{{ $call->id }}');
                                                      if (!btn || btn.getAttribute('data-listener-active') === 'true') return;
                                                      btn.setAttribute('data-listener-active', 'true');
                                                      btn.addEventListener('click', (e) => {
                                                          try {
                                                              const url = btn.getAttribute('data-url') || '#';
                                                              if (url !== '#') return;
                                                              e.preventDefault();
                                                              const msg = btn.getAttribute('data-guard-msg') || '# ERROR';
                                                              (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                                                              btn.setAttribute('data-failed-route', 'true');
                                                          } catch (err) {}
                                                      });
                                                  })();
                                              </script>
                                          @endpush
                                        </div>
                                      @endcan
                                    </td>
                                  </tr>
                                @endforeach
                              </tbody>
                            </table>
                          </div>
                        </div>
                    </div>
                    <div id="activity" class="{{ VC::CD }}">
                        <div class="{{ VC::CD }}-header">
                            <h5>{{ __('Activity') }}</h5>
                        </div>
                        <div class="{{ VC::CD }}-body">
                            <div class="row leads-scroll">
                                @php
                                    try {
                                        $rawActivities = $deal->activities ?? [];
                                        $activities    = ($rawActivities instanceof Collection)
                                                            ? $rawActivities
                                                            : (is_array($rawActivities) ? collect($rawActivities) : collect());
                                    } catch (\Throwable $e) {
                                        \Log::error('deals/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                    }
@endphp
                                <ul class="event-cards {{ VC::LG_FLSH }} mt-3 w-100">
                                    @forelse($activities as $activity)
                                        @php
                                            try {
                                                $icon     = method_exists($activity, 'logIcon') ? $activity->logIcon() : 'circle-dashed';
                                                $remark   = method_exists($activity, 'getRemark') ? $activity->getRemark() : e($activity->remark ?? '');
                                                $logType  = $activity->log_type ?? '-';
                                                $when     = $activity->created_at ? $activity->created_at->diffForHumans() : '-';
                                            } catch (\Throwable $e) {
                                                \Log::error('deals/show — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                            }
@endphp
                                        <li class="{{ VC::LGI }} {{ VC::MB3 }}">
                                            <div class="{{ VC::R_ALC_JCE }}">
                                                <div class="{{ VC::C_AT }}">
                                                    <div class="{{ VC::DFL_AIC }}">
                                                        <div class="theme-avatar {{ VC::BG_P }}">
                                                            <i class="ti ti-{{ $icon }}"></i>
                                                        </div>
                                                        <div class="ms-3">
                                                            <span class="{{ VC::TX_DK }} {{ VC::TXSM }}">{{ __($logType) }}</span>
                                                            <h6 class="m-0">{!! $remark !!}</h6>
                                                            <small class="{{ VC::TXT_MT }}">{{ $when }}</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="{{ VC::C_AT }}"></div>
                                            </div>
                                        </li>
                                    @empty
                                        <li class="{{ VC::LGI }} text-center py-4">
                                            <div class="{{ VC::DFL_IL_VC }} gap-2">
                                                <i class="{{ VC::TI_INB }} {{ VC::FS_2X }} {{ VC::TX_MUTED }}"></i>
                                                <span class="{{ VC::TX_MUTED }}">{{ __('No activity found for this query.') }}</span>
                                            </div>
                                        </li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
