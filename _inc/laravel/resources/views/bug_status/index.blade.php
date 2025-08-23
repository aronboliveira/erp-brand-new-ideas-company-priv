@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;
    use App\Models\Utility;
    use App\Config\Constants\{
        PermissionsConstants,
        UsersConstants,
        ViewsConstants,
        ViewClassNamesConstants as VC,
        StacksConstants
    };
	$user = Auth::user();
    $lang                     = Utility::fetchUserLang(user:$user);
    $createRoute              = Route::has(ViewsConstants::BUG_STT . '.create')
        ? route(ViewsConstants::BUG_STT . '.create')
        : (Route::has(Str::kebab(ViewsConstants::BUG_STT . '.create'))
            ? route(Str::kebab(ViewsConstants::BUG_STT . '.create'))
            : '#');
    $createBtnId              = 'bugstatus-create-btn';
    $createGuardMsg           = Utility::fetchLinkMessage(
        $lang,
        ViewsConstants::BUG_STT,
        'bug_status_create_route_unavailable'
    ) ?? 'Create Bug Status route is unavailable. Please contact technical support or your domain administrator.';
@endphp
@extends('layouts.admin')
@section('page-title')
    {{__('Manage Project Bug Status')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Project Bug Status')}}</li>
@endsection
@push('script-page')
    <script src="{{asset('js/jquery-ui.min.js')}}"></script>
    @if($user?->{UsersConstants::COL_TP} === PermissionsConstants::CPN)
			    <script>
          (() => { 
              if (!window.translations) {
  window.translations = {};
}
const t = {
					ar: { bugstatus_order_failed: 'فشل تحديث ترتيب الحالة.' },
					da: { bugstatus_order_failed: 'Opdatering af statusrækkefølge mislykkedes.' },
					de: { bugstatus_order_failed: 'Aktualisierung der Statusreihenfolge fehlgeschlagen.' },
					en: { bugstatus_order_failed: 'Failed to update status order.' },
					es: { bugstatus_order_failed: 'Error al actualizar el orden de estado.' },
					fr: { bugstatus_order_failed: 'Échec de la mise à jour de l’ordre des statuts.' },
					he: { bugstatus_order_failed: 'נכשל עדכון סידור הסטטוסים.' },
					it: { bugstatus_order_failed: 'Aggiornamento dell’ordine di stato non riuscito.' },
					ja: { bugstatus_order_failed: 'ステータス順序の更新に失敗しました。' },
					nl: { bugstatus_order_failed: 'Bijwerken van statusvolgorde mislukt.' },
					pl: { bugstatus_order_failed: 'Aktualizacja kolejności statusu nie powiodła się.' },
					pt: { bugstatus_order_failed: 'Falha ao atualizar a ordem de status.' },
					'pt-br': { bugstatus_order_failed: 'Falha ao atualizar a ordem de status.' },
					ru: { bugstatus_order_failed: 'Не удалось обновить порядок статусов.' },
					tr: { bugstatus_order_failed: 'Durum sırası güncellenemedi.' },
					zh: { bugstatus_order_failed: '更新状态顺序失败。' }
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
					const errFb = '# ERROR';
					const guardMsgKey = 'data-guard-msg';
					const clientFlag = 'data-client-localized';
					const langKey = 'erp-np-lang';
					let errorMessage = '';
				
					function getLocalizedMessage(key, el) {
						let msg = errFb;
						if (el.getAttribute(clientFlag) === 'true') {
							msg = el.getAttribute(guardMsgKey) ?? msg;
						} else {
							let lang = (sessionStorage.getItem(langKey) ?? document.documentElement.lang ?? 'en')
								.toLowerCase().replace(/_/g, '-');
							lang = lang === 'pt-br' ? lang : lang.slice(0,2);
							msg = translations?.[lang]?.[key] ??
										el.getAttribute(guardMsgKey) ??
										translations?.['en']?.[key] ??
										msg;
							if (msg !== errFb) {
								el.setAttribute(guardMsgKey, msg);
								el.setAttribute(clientFlag, 'true');
							}
						}
						return msg;
					}
				
					function showError(message) {
						try {
							let container = document.getElementById('toast-container');
							if (!container) {
								container = document.createElement('div');
								container.id = 'toast-container';
								document.body.appendChild(container);
							}
							const bs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap;
							if (bs) {
								const toast = document.createElement('div');
								toast.className = 'toast';
								toast.setAttribute('role','alert');
								toast.setAttribute('aria-live','assertive');
								toast.setAttribute('aria-atomic','true');
								const body = document.createElement('div');
								body.className = 'toast-body';
								body.textContent = message;
								toast.appendChild(body);
								container.appendChild(toast);
								bootstrap.Toast.getOrCreateInstance(toast).show();
							} else {
								alert(message);
							}
						} catch {
							alert(message);
						}
					}
				
					const onErrorPointerUp = () => {
						if (errorMessage) {
							showError(errorMessage);
							errorMessage = '';
						}
					};
					document.addEventListener('pointerup', onErrorPointerUp);
					new MutationObserver((muts, obs) => {
						muts.forEach(m => m.removedNodes.forEach(n => {
							if (n === document.documentElement) {
								document.removeEventListener('pointerup', onErrorPointerUp);
								obs.disconnect();
							}
						}));
					}).observe(document.body, { childList: true, subtree: true });
				
					document.addEventListener('DOMContentLoaded', () => {
						document.querySelectorAll('.sortable').forEach(el => {
							if (el.dataset.listenerAttached === 'true') return;
							el.dataset.listenerAttached = 'true';
							try {
								$(el).sortable().disableSelection().on('sortstop', function() {
									try {
										const order = [];
										this.querySelectorAll('li').forEach((li, idx) => {
											order[idx] = li.getAttribute('data-id');
										});
										const url = "{{route(ViewsConstants::BUG_STT.'.order')}}";
										if (!url) throw new Error('bugstatus_order_failed');
										$.ajax({
											url,
											type: 'POST',
											data: { order, _token: $('meta[name="csrf-token"]').attr('content') }
										}).fail(() => {
											throw new Error('bugstatus_order_failed');
										});
									} catch (e) {
										errorMessage = getLocalizedMessage(e.message, el);
									}
								});
							} catch {
								errorMessage = getLocalizedMessage('bugstatus_order_failed', el);
							}
							const obsEl = new MutationObserver((m,o) => {
								m.forEach(mut => mut.removedNodes.forEach(node => {
									if (node === el) {
										$(el).sortable('destroy');
										obsEl.disconnect();
									}
								}));
							});
							obsEl.observe(document.body, { childList: true, subtree: true });
						});
					});
				})();
			</script>
    @endif
@endpush

@section('action-btn')
    @can('create bug status')
        <div class="float-end">
            <a
                id="{{ $createBtnId }}"
                href="#"
                data-url="{{ $createRoute }}"
                data-guard-msg="{{ $createGuardMsg }}"
                data-ajax-popup="true"
                data-title="{{ __('Create Bug Stage') }}"
                class="{{ VC::BT_SM_PM }}"
                data-bs-toggle="tooltip"
                title="{{ __('Create') }}"
            >
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        </div>
    @endcan
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="CS12 CM10 col-xxl-8">
            <div class="{{ VC::CD }} mt-5">
                <div class="card-body">
                    <div class="tab-content" id="pills-tabContent">
                        @php($i = 0)
                        @foreach($bugStatus as $stage)
                            <div class="tab-pane fade show @if($i == 0) active @endif" role="tabpanel">
                                <ul class="list-unstyled {{ VC::LGRP }} sortable stage">
                                    @foreach($bugStatus as $bug)
                                        @php
                                            $editRoute    = Route::has(ViewsConstants::BUG_STT . '.edit')
                                                ? route(ViewsConstants::BUG_STT . '.edit', $bug->id)
                                                : (Route::has(Str::kebab(ViewsConstants::BUG_STT . '.edit'))
																									? route(Str::kebab(ViewsConstants::BUG_STT . '.edit'))
																									: '#');
                                            $editBtnId    = 'bugstatus-edit-btn-' . $bug->id;
                                            $editMsg      = Utility::fetchLinkMessage(
                                                $lang,
                                                ViewsConstants::BUG_STT,
                                                'bug_status_edit_route_unavailable'
                                            ) ?? 'Edit Bug Status route is unavailable. Please contact technical support or your domain administrator.';
                                            $destroyRoute = Route::has(ViewsConstants::BUG_STT . '.destroy')
                                                ? route(ViewsConstants::BUG_STT . '.destroy', $bug->id)
                                                : (Route::has(Str::kebab(ViewsConstants::BUG_STT . '.destroy'))
																									? route(Str::kebab(ViewsConstants::BUG_STT . '.destroy'))
																									: '#');
                                            $destroyFormId = 'bugstatus-delete-form-' . $bug->id;
                                            $destroyBtnId  = 'bugstatus-delete-btn-' . $bug->id;
                                            $destroyMsg    = Utility::fetchLinkMessage(
                                                $lang,
                                                ViewsConstants::BUG_STT,
                                                'bug_status_destroy_route_unavailable'
                                            ) ?? 'Delete Bug Status route is unavailable. Please contact technical support or your domain administrator.';
                                        @endphp
                                        <li class="{{ VC::DFL_AIC_JCB_IT }}" data-id="{{ $bug->id }}">
                                            <h6 class="{{ VC::MB0 }}">
                                                <i class="{{ VC::ME3 }} {{ VC::TI_AR }}" data-feather="move"></i>
                                                <span>{{ $bug->title }}</span>
                                            </h6>
                                            <span class="{{ VC::FEND }}">
                                                @can('edit bug status')
                                                    <a
                                                        id="{{ $editBtnId }}"
                                                        href="#"
                                                        class="{{ VC::BT_SM_FL_CT }}"
                                                        data-url="{{ $editRoute }}"
                                                        data-guard-msg="{{ $editMsg }}"
                                                        data-ajax-popup="true"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Edit') }}"
                                                        data-title="{{ __('Edit Bug Status') }}"
                                                    >
                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                    </a>
                                                @endcan

                                                @can('delete bug status')
                                                    {!! Collective\Html\FormFacade::open([
                                                        'method'         => 'DELETE',
                                                        'route'          => [ViewsConstants::BUG_STT . '.destroy', $bug->id],
                                                        'id'             => $destroyFormId,
                                                        'data-url'       => $destroyRoute,
                                                        'data-guard-msg' => $destroyMsg,
                                                    ]) !!}
                                                    <a
                                                        id="{{ $destroyBtnId }}"
                                                        href="#"
                                                        class="{{ VC::BT_SM_CT_PR }}"
                                                        data-bs-toggle="tooltip"
                                                        title="{{ __('Delete') }}"
                                                    >
                                                        <i class="{{ VC::TI_TRS_WT }}"></i>
                                                    </a>
                                                    {!! Collective\Html\FormFacade::close() !!}
                                                @endcan
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            @php($i++)
                        @endforeach
                    </div>
                    <p class="{{ VC::MT4 }}">
                        <strong>{{ __('Note') }}:</strong>
                        <b>{{ __('You can easily change order of project Bug status using drag & drop.') }}</b>
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push(StacksConstants::ADM_SCR_PG)
    <script defer>
        (() => {
            const guardClick = id => {
                const el = document.getElementById(id);
                if (!el || el.getAttribute('data-listener-active') === 'true') return;
                el.setAttribute('data-listener-active', 'true');
                el.addEventListener('click', event => {
                    try {
                        const href = el.getAttribute('href');
                        const url  = el.getAttribute('data-url');
                        if ((href && href !== '#') || (url && url !== '#')) return;
                        event.preventDefault();
                        const msg           = el.getAttribute('data-guard-msg') ?? '# ERROR';
                        const bootstrapLink = document.querySelector('link[href*="bootstrap"]');
                        let container       = document.getElementById('toast-container');
                        if (!container) {
                            container       = document.createElement('div');
                            container.id    = 'toast-container';
                            document.body.appendChild(container);
                        }
                        if (bootstrapLink && window.bootstrap) {
                            const toastEl      = document.createElement('div');
                            toastEl.className  = 'toast';
                            toastEl.setAttribute('role', 'alert');
                            toastEl.setAttribute('aria-live', 'assertive');
                            toastEl.setAttribute('aria-atomic', 'true');
                            const body         = document.createElement('div');
                            body.className     = 'toast-body';
                            body.textContent   = msg;
                            toastEl.appendChild(body);
                            container.appendChild(toastEl);
                            bootstrap.Toast.getOrCreateInstance(toastEl).show();
                        } else {
                            alert(msg);
                        }
                        el.setAttribute('data-failed-route', 'true');
                    } catch (e) {}
                });
            };
            guardClick('{{ $createBtnId }}');
            @foreach($bugStatus as $bug)
                guardClick('bugstatus-edit-btn-{{ $bug->id }}');
                guardClick('bugstatus-delete-btn-{{ $bug->id }}');
            @endforeach
        })();
    </script>
@endpush
