@php
$user ??= null;
	$hasFetchUserLang ??= false;
	$hasFetchLinkMessage ??= false;
	$lang ??= 'en';
	$dashBase ??= 'dashboard';
	$dashKebab ??= '';
	$dashResolved ??= null;
	$dashUrl ??= '#';
	$dashLinkId ??= 'dashboard-breadcrumb-link';
	$dashGuardMsg ??= '';
	$fmtDate ??= null;
	$isEmployeeCol ??= false;
	$leavesIsList ??= false;
	try {
		$user = Auth::user();
		$hasFetchUserLang = is_callable([Utility::class, 'fetchUserLang']);
		$hasFetchLinkMessage = is_callable([Utility::class, 'fetchLinkMessage']);
		$lang = $hasFetchUserLang ? (Utility::fetchUserLang(user: $user) ?? 'en') : app()->getLocale();
		$dashKebab = Str::kebab($dashBase);
		$dashResolved = Route::has($dashBase) ? $dashBase : (Route::has($dashKebab) ? $dashKebab : null);
		$dashUrl = $dashResolved ? (route($dashResolved) ?? '#') : '#';
		$dashGuardMsg = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, 'generics', 'dashboard_unavailable') : null)
			?? __('Dashboard route is unavailable. Please contact technical support or your domain administrator.');
		$fmtDate = function ($value, $fallback) use ($user) {
			return ($value && $user && method_exists($user, 'dateFormat')) ? ($user->dateFormat($value) ?? $fallback) : $fallback;
		};
		$isEmployeeCol = strtolower($user?->{UsersConstants::COL_TP} ?? '') !== 'employee';
		$leavesIsList = (is_array($leaves ?? null) && count($leaves ?? []) > 0)
			|| (($leaves ?? null) instanceof Collection && $leaves->isNotEmpty());
	} catch (\Error $e) {
		Log::error('Error in leaves/index.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in leaves/index.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in leaves/index.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

@extends(EL::ADM)

@section(YD::ADM_PG_TTL)
    {{ __('Manage Leave') }}
@endsection

@section(YD::ADM_BDC)
    <li class="{{ VC::BCI }}">
        <a href="{{ $dashUrl }}"
           id="{{ $dashLinkId }}"
           data-url="{{ $dashUrl }}"
           data-sv-localized="true"
           data-guard-msg="{{ base64_encode($dashGuardMsg) }}">
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="{{ VC::BCI }}">{{ __('Manage Leave') }}</li>
@endsection

@section(YD::ADM_ACT_BTN)
    <div class="{{ VC::FEND }}">
        @can('create leave')
            @php
                try {
                    $createUrl = Route::has(VW::LV.'.create') ? route(VW::LV.'.create') : '#';
                    $createGuardMsg = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::LV, 'create_leave_unavailable') : null)
                        ?? __('Create leave route is unavailable. Please contact technical support or your domain administrator.');
                } catch (\Throwable $e) {
                    \Log::error('leaves/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                }
@endphp
            <a href="{{ $createUrl }}"
               id="leave-create-link"
               class="{{ VC::BT_SM_PM }}"
               data-url="{{ $createUrl }}"
               data-ajax-popup="true"
               data-size="lg"
               data-title="{{ __('Create Leave') }}"
               data-sv-localized="true"
               data-guard-msg="{{ base64_encode($createGuardMsg) }}"
               data-bs-toggle="tooltip"
               title="{{ __('Create') }}">
                <i class="{{ VC::TI_PLS }}"></i>
            </a>
        @endcan
    </div>
@endsection

@section(YD::ADM_CTT)
    @if(!$user)
        <div class="{{ VC::ALT_WRN_MB0 }}">
            {{ __('The current user session was not available. The procedure could not be completed.') }}
        </div>
    @else
        <div class="{{ VC::RW }}">
            <div class="{{ VC::C12 }}">
                <div class="{{ VC::CD }}">
                    <div class="{{ VC::CD_BD_TB_BD }}">
                        <div class="{{ VC::TB_RSP }}">
                            <table class="{{ VC::TB }} datatable">
                                <thead>
                                    <tr>
                                        @if($isEmployeeCol)
                                            <th>{{ __('Employee') }}</th>
                                        @endif
                                        <th>{{ __('Leave Type') }}</th>
                                        <th>{{ __('Applied On') }}</th>
                                        <th>{{ __('Start Date') }}</th>
                                        <th>{{ __('End Date') }}</th>
                                        <th>{{ __('Total Days') }}</th>
                                        <th>{{ __('Leave Reason') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        @can('edit leave')
                                            <th width="200px">{{ __('Action') }}</th>
                                        @endcan
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($leavesIsList)
                                        @foreach(($leaves ?? []) as $leave)
                                            @php
                                                try {
                                                    $lid = isset($leave->id) ? (string)$leave->id : '';

                                                    $empName = (isset($leave->employees) && isset($leave->employees->name) && $leave->employees->name !== '')
                                                        ? $leave->employees->name
                                                        : __('Employee name was not available.');

                                                    $typeTitle = (isset($leave->leaveType) && isset($leave->leaveType->title) && $leave->leaveType->title !== '')
                                                        ? $leave->leaveType->title
                                                        : __('Leave type was not available.');

                                                    $appliedOn = $fmtDate($leave->applied_on ?? null, __('Applied date was not available or failed to be formatted.'));
                                                    $startDate = $fmtDate($leave->start_date ?? null, __('Start date was not available or failed to be formatted.'));
                                                    $endDate   = $fmtDate($leave->end_date ?? null, __('End date was not available or failed to be formatted.'));

                                                    $totalDays = (isset($leave->total_leave_days) && $leave->total_leave_days !== '')
                                                        ? $leave->total_leave_days
                                                        : __('Total days data was not available.');

                                                    $reason = isset($leave->leave_reason) && $leave->leave_reason !== ''
                                                        ? $leave->leave_reason
                                                        : __('Leave reason was not available.');

                                                    $stRaw  = strtolower((string)($leave->status ?? ''));
                                                    $stText = isset($leave->status) && $leave->status !== ''
                                                        ? $leave->status
                                                        : __('Status was not available.');

                                                    $stClass = match ($stRaw) {
                                                        'pending'                  => 'bg-warning',
                                                        'approved'                 => 'bg-success',
                                                        'reject', 'rejected'       => 'bg-danger',
                                                        default                    => 'bg-secondary',
                                                    };
                                                } catch (\Throwable $e) {
                                                    \Log::error('leaves/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                }
@endphp
                                            <tr>
                                                @if($isEmployeeCol)
                                                    <td>{{ $empName }}</td>
                                                @endif
                                                <td>{{ $typeTitle }}</td>
                                                <td>{{ $appliedOn }}</td>
                                                <td>{{ $startDate }}</td>
                                                <td>{{ $endDate }}</td>
                                                <td>{{ $totalDays }}</td>
                                                <td>{{ $reason }}</td>
                                                <td>
                                                    <div class="badge {{ $stClass }} p-2 {{ VC::PX3 }} rounded">
                                                        {{ $stText }}
                                                    </div>
                                                </td>
                                                @can('edit leave')
                                                    <td>
                                                        @if($isEmployeeCol)
                                                            @if($stRaw === 'pending')
                                                                @php
                                                                    try {
                                                                        $editUrl   = ($lid !== '' && Route::has(VW::LV.'.edit')) ? route(VW::LV.'.edit', [$lid]) : '#';
                                                                        $editGuard = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::LV, 'edit_leave_unavailable') : null)
                                                                            ?? __('Edit leave route is unavailable. Please contact technical support or your domain administrator.');
                                                                    } catch (\Throwable $e) {
                                                                        \Log::error('leaves/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                    }
@endphp
                                                                <div class="{{ VC::ACT_BTN_PRIM }}">
                                                                    <a href="#"
                                                                       class="{{ VC::BT_SM_CT }}"
                                                                       data-url="{{ $editUrl }}"
                                                                       data-ajax-popup="true"
                                                                       data-size="lg"
                                                                       data-title="{{ __('Edit Leave') }}"
                                                                       data-sv-localized="true"
                                                                       data-guard-msg="{{ base64_encode($editGuard) }}"
                                                                       data-bs-toggle="tooltip"
                                                                       title="{{ __('Edit') }}">
                                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                                    </a>
                                                                </div>
                                                            @endif
                                                        @else
                                                            @php
                                                                try {
                                                                    $actionUrl = ($lid !== '' && Route::has(VW::LV.'.action')) ? route(VW::LV.'.action', [$lid]) : '#';
                                                                    $actionGuard = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::LV, 'leave_action_route_unavailable') : null)
                                                                        ?? __('Leave action route is unavailable. Please contact technical support or your domain administrator.');

                                                                    $editUrl   = ($lid !== '' && Route::has(VW::LV.'.edit')) ? route(VW::LV.'.edit', [$lid]) : '#';
                                                                    $editGuard = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::LV, 'edit_leave_unavailable') : null)
                                                                        ?? __('Edit leave route is unavailable. Please contact technical support or your domain administrator.');
                                                                } catch (\Throwable $e) {
                                                                    \Log::error('leaves/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                }
@endphp
                                                            <div class="{{ VC::BT_SM_MX3 }} {{ VC::DFL_IL_VC }}">
                                                                <div class="{{ VC::ACT_BTN_WRN }}">
                                                                    <a href="#"
                                                                       class="{{ VC::BT_SM_CT }}"
                                                                       data-url="{{ $actionUrl }}"
                                                                       data-ajax-popup="true"
                                                                       data-size="lg"
                                                                       data-title="{{ __('Leave Action') }}"
                                                                       data-sv-localized="true"
                                                                       data-guard-msg="{{ base64_encode($actionGuard) }}"
                                                                       data-bs-toggle="tooltip"
                                                                       title="{{ __('Leave Action') }}">
                                                                        <i class="{{ VC::TI_CRT_WT }}"></i>
                                                                    </a>
                                                                </div>
                                                                <div class="{{ VC::ACT_BTN_PRIM }}">
                                                                    <a href="#"
                                                                       class="{{ VC::BT_SM_CT }}"
                                                                       data-url="{{ $editUrl }}"
                                                                       data-ajax-popup="true"
                                                                       data-size="lg"
                                                                       data-title="{{ __('Edit Leave') }}"
                                                                       data-sv-localized="true"
                                                                       data-guard-msg="{{ base64_encode($editGuard) }}"
                                                                       data-bs-toggle="tooltip"
                                                                       title="{{ __('Edit') }}">
                                                                        <i class="{{ VC::TI_PC_WT }}"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        @endif

                                                        @can('delete leave')
                                                            @php
                                                                try {
                                                                    $delUrl   = ($lid !== '' && Route::has(VW::LV.'.destroy')) ? route(VW::LV.'.destroy', [$lid]) : '#';
                                                                    $formId   = 'delete-form-'.$lid;
                                                                    $delGuard = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, VW::LV, 'delete_leave_unavailable') : null)
                                                                        ?? __('Delete leave route is unavailable. Please contact technical support or your domain administrator.');
                                                                    $confirmA = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') : null) ?? __('Are You Sure?');
                                                                    $confirmB = ($hasFetchLinkMessage ? Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') : null) ?? __('This action can not be undone. Do you want to continue?');
                                                                } catch (\Throwable $e) {
                                                                    \Log::error('leaves/index — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
                                                                }
@endphp
                                                            <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                                {!! Form::open([
                                                                    'method'            => 'DELETE',
                                                                    'url'               => $delUrl,
                                                                    'id'                => $formId,
                                                                    'data-url'          => $delUrl,
                                                                    'data-sv-localized' => 'true',
                                                                    'data-guard-msg'    => $delGuard,
                                                                ]) !!}
                                                                    <a href="#"
                                                                       class="{{ VC::BT_SM_CT_PR }}"
                                                                       data-bs-toggle="tooltip"
                                                                       title="{{ __('Delete') }}"
                                                                       data-confirm="{{ __($confirmA) }}|{{ __($confirmB) }}"
                                                                       data-confirm-yes="document.getElementById('{{ $formId }}').submit();">
                                                                        <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                    </a>
                                                                {!! Form::close() !!}
                                                            </div>
                                                        @endcan
                                                    </td>
                                                @endcan
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="{{ $isEmployeeCol ? 9 : 8 }}" class="{{ VC::TXCT_MT }}">
                                                {{ __('Leave data was not available or failed to be fetched.') }}
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
@push(ST::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/routes/generics/dashboard.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/leaves/index.js') }}"></script>
    <script async src="{{ asset('assets/js/routes/leaves/lang/index.js') }}"></script>
    <script defer>
        (function () {
            const $ = window.jQuery;
            const errFb = "# ERROR";
            const dataClientLocalized = "data-client-localized";
            const dataGuardMsg = "data-guard-msg";
            const RG = window.RouteGuard || {};
            const showErrorNow = RG.showToast || (m => alert(m));
            const dataSvLocalized = "data-sv-localized";
            const dataInit = "data-leaves-bound";
            const dataErr = "data-leaves-error";
            const ns = "._npLeaves";
            const qs = (s, r = document) => r.querySelector(s);
            const schedulePointerupError = msg => {
                const host = document.body;
                if (!host || host.getAttribute(dataErr) === "true") return;
                host.setAttribute(dataErr, "true");
                const once = () => {
                try {
                    showErrorNow(msg);
                } finally {
                    host.removeAttribute(dataErr);
                }
                };
                document.addEventListener("pointerup", once, { once: true });
            };
            const getMsg = (el, key) => {
                let msg = errFb;
                if (
                el?.getAttribute?.(dataSvLocalized) === "true" ||
                el?.getAttribute?.(dataClientLocalized) === "true"
                ) {
                msg = el.getAttribute(dataGuardMsg) || errFb;
                } else {
                let lang = (
                    window.sessionStorage.getItem("erp-np-lang") ||
                    document.documentElement.lang ||
                    "en"
                )
                    .toLowerCase()
                    .replace(/_/g, "-");
                lang = lang === "pt-br" ? lang : lang.slice(0, 2);
                const msgKey = key;
                msg =
                    window.translations?.[lang]?.[msgKey] ||
                    el?.getAttribute?.(dataGuardMsg) ||
                    window.translations?.en?.[msgKey] ||
                    errFb;
                if (msg !== errFb && el) {
                    el.setAttribute(dataGuardMsg, msg);
                    el.setAttribute(dataClientLocalized, "true");
                }
                }
                return msg;
            };
            const ensureJq = () => {
                if (!$ || !$.fn) {
                try {
                    if (
                        window.location.hostname === "localhost" ||
                        window.location.hostname === "127.0.0.1"
                    ) console.error("jQuery unavailable");
                } catch (_) {}
                schedulePointerupError(getMsg(document.body, "plugin_unavailable"));
                return false;
                }
                return true;
            };
            const buildEndpoint = el => {
                const url = el?.getAttribute?.("data-url") ?? "";
                const href = el?.getAttribute?.("href") ?? "";
                if ((!url || url === "#") && (!href || href === "#"))
                return "{{route(VW::LV.'.jsoncount')}}";
                return url && url !== "#" ? url : href;
            };
            const bind = () => {
                if (!ensureJq()) return;
                const host = document.body;
                if (host.getAttribute(dataInit) === "true") return;
                host.setAttribute(dataInit, "true");
                $(document).on("change" + ns, "#employee_id", function () {
                const empEl = this;
                const employee_id = $(this).val() ?? "";
                const endpoint = buildEndpoint(empEl);
                if (!endpoint || endpoint === "#") {
                    schedulePointerupError(getMsg(empEl, "route_unavailable"));
                    return;
                }
                $.ajax({
                    url: endpoint,
                    type: "POST",
                    data: {
                    employee_id: employee_id,
                    _token:
                        document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute("content") ?? "",
                    },
                    success: function (data) {
                    const $select = $("#leave_type_id");
                    if (!$select.length) {
                        schedulePointerupError(getMsg(empEl, "leaves_unavailable"));
                        return;
                    }
                    $select.empty();
                    $select.append('<option value="">{{__('Select Leave Type')}}</option>');
                    try {
                        $.each(data ?? [], function (key, value) {
                        const id = value?.id ?? "";
                        const title = value?.title ?? "";
                        const total = Number(value?.total_leave ?? 0);
                        const days = Number(value?.days ?? 0);
                        const disabled = total >= days ? " disabled" : "";
                        const label = title + "&nbsp(" + total + "/" + days + ")";
                        $select.append(
                            '<option value="' +
                            id +
                            '"' +
                            disabled +
                            ">" +
                            label +
                            "</option>"
                        );
                        });
                    } catch (_) {
                        schedulePointerupError(getMsg(empEl, "leaves_unavailable"));
                    }
                    },
                    error: function () {
                    schedulePointerupError(getMsg(empEl, "leaves_unavailable"));
                    },
                });
                });
                const mo = new MutationObserver(function () {
                if (!$("#employee_id").length) {
                    $(document).off("change" + ns, "#employee_id");
                    host.removeAttribute(dataInit);
                }
                });
                mo.observe(document.documentElement, { childList: true, subtree: true });
            };
            if (document.readyState === "loading") {
                document.addEventListener("DOMContentLoaded", bind, { once: true });
            } else {
                bind();
            }
        })();
    </script>
@endpush
