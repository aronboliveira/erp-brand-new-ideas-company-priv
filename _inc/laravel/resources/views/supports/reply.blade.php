@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewsConstants as VW,
        ViewClassNamesConstants as VC,
        YieldingConstants
    };
    use App\Models\{Support, Utility};
    use Illuminate\Support\Facades\{Route, Storage};
    use Illuminate\Support\{Collection, Str};
    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@push(StacksConstants::ADM_SCR_PG)
@endpush
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Support Reply')}}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0 ">{{__('Support Reply')}}</h5>
    </div>
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    @php
        $sptIndexBase = VW::SPT.'.index';
        $sptIndexKebab = Str::kebab($sptIndexBase);
        $sptIndexResolved = Route::has($sptIndexBase) ? $sptIndexBase : (Route::has($sptIndexKebab) ? $sptIndexKebab : null);
        $sptIndexUrl = $sptIndexResolved ? route($sptIndexResolved) : '#';
        $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
        $listGuardMsg = Utility::fetchLinkMessage($langValue, VW::SPT, 'index_support_route_unavailable') ?? 'Index support route is unavailable. Please contact technical support or your domain administrator.';
    @endphp
    <li class="breadcrumb-item">
        <a href="{{ $sptIndexUrl }}"
        class="support-list"
        data-url="{{ $sptIndexUrl }}"
        data-guard-msg="{{ $listGuardMsg }}"
        data-sv-localized="true">
            {{ __('Support') }}
        </a>
    </li>
    @push(StacksConstants::ADM_SCR_PG)
        <script src="{{ asset('assets/js/routes/supports/index.js') }}" defer></script>
    @endpush
    <li class="breadcrumb-item active" aria-current="page">{{__('Support Reply')}}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
    @php
        $sptEditBase = VW::SPT.'.edit';
        $sptEditKebab = Str::kebab($sptEditBase);
        $sptEditResolved = Route::has($sptEditBase) ? $sptEditBase : (Route::has($sptEditKebab) ? $sptEditKebab : null);
        $supportIdRaw = data_get($support,'id');
        $supportId = (is_string($supportIdRaw) && Str::isUuid($supportIdRaw)) ? $supportIdRaw : null;
        $sptEncryptedId = $supportId ? Crypt::encrypt($supportId) : null;
        $sptEditUrl = ($sptEditResolved && $sptEncryptedId) ? route($sptEditResolved, $sptEncryptedId) : '#';
        $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
        $sptEditGuardMsg = Utility::fetchLinkMessage($langValue, VW::SPT, 'edit_support_route_unavailable') ?? 'Edit support route is unavailable. Please contact technical support or your domain administrator.';
    @endphp
    <div class="{{ VC::FEND }}">
        <a href="{{ $sptEditUrl }}"
           data-size="lg"
           data-url="{{ $sptEditUrl }}"
           data-ajax-popup="true"
           data-bs-toggle="tooltip"
           title="{{ __('Edit') }}"
           data-title="{{ __('Edit Support') }}"
           class="{{ VC::BT_SM_PM }} support-edit"
           data-guard-msg="{{ $sptEditGuardMsg }}"
           data-sv-localized="true">
            <i class="{{ VC::TI_PC }}"></i>
        </a>
    </div>
    @push(StacksConstants::ADM_SCR_PG)
        <script src="{{ asset('assets/js/routes/supports/editReply.js') }}" defer></script>
    @endpush
@endsection
@section(YieldingConstants::ADM_CTT)
	<div class="{{ VC::RW }}">
		<div class="{{ VC::C12 }}">
			<div class="row gy-4">
				<div class="{{ VC::CL6 }}">
					<div class="{{ VC::RW }}">
						<h5 class="{{ VC::MB3 }}">{{ __('Reply Ticket') }} - <span class="text-primary">{{ data_get($support,'ticket_code') ?: __('No ticket code available') }}</span></h5>
						<div class="{{ VC::CD }} {{ VC::BD }}">
							<div class="card-body p-0">
								<div class="{{ VC::P4 }} border-bottom">
									@php
										$prio = data_get($support,'priority');
										$priorityMap = Support::$priority ?? [];
										$prioLabel = isset($priorityMap[$prio]) ? __($priorityMap[$prio]) : __('No priority available');
										$prioClass = $prio === 0 ? VC::BG_P : ($prio === 1 ? 'bg-info' : ($prio === 2 ? 'bg-warning' : ($prio === 3 ? 'bg-danger' : 'bg-secondary')));
									@endphp
									<span class="{{ VC::BDG }} {{ $prioClass }} mb-2">{{ $prioLabel }}</span>
									@php
										$status = (string) data_get($support,'status',__('No status available'));
										$statusMap = Support::$status ?? [];
										$statusLabel = isset($statusMap[$status]) ? __($statusMap[$status]) : __('No status available');
										$statusClass = $status === 'Open' ? 'bg-light-primary text-primary' : ($status === 'Close' ? 'bg-light-danger text-danger' : ($status === 'On Hold' ? 'bg-light-warning text-warning' : 'bg-secondary'));
									@endphp
									<div class="{{ VC::DFL_AIC_JCB }}">
										<h5>{{ data_get($support,'subject') ?: __('No subject available') }}</h5>
										<span class="badge {{ $statusClass }} p-2 {{ VC::FW600 }} rounded">{{ $statusLabel }}</span>
									</div>
									<p class="{{ VC::MB0 }}">
										<b>{{ data_get($support,'createdBy.name') ?: __('No creator name available') }}</b> . <span>{{ data_get($support,'createdBy.email') ?: __('No creator email available') }}</span> . <span class="{{ VC::TXT_MT }}">{{ $user?->dateFormat(data_get($support,'created_at')) ?? __('Failed to get created date') }}</span>
									</p>
								</div>
								@if(!empty(data_get($support,'description')))
									<div class="{{ VC::P4 }}">
										<p>{{ data_get($support,'description') }}</p>
										@php
											$attachment = data_get($support,'attachment');
											$attachUrl = !empty($attachment) ? asset(Storage::url('uploads/supports')).'/'.$attachment : '';
										@endphp
										@if(!empty($attachUrl))
											<h6>{{ __('Attachments') }} :</h6>
											<a href="{{ $attachUrl }}" download class="bg-secondary {{ VC::DFL_IL }} p-2 rounded {{ VC::TXT_WT }}" target="_blank">
												<i class="{{ VC::TI_DWN }} {{ VC::TXT_WT }} me-2 {{ VC::MT1 }}" data-bs-toggle="tooltip"></i>{{ $attachment }}
											</a>
										@endif
									</div>
								@endif
							</div>
						</div>
					</div>
					@if((string) data_get($support,'status','') === 'Open')
						<div class="{{ VC::RW }}">
							<div class="{{ VC::CD }}">
								<div class="card-body">
									<div class="{{ VC::RW }}">
										<div class="{{ VC::C6 }}"><h5 class="{{ VC::MB3 }}">{{ __('Comments') }}</h5></div>
										@php
											$plan = Utility::getChatGPTSettings();
										@endphp
										@if($plan?->{PlansConstants::COL_GPT} == 1)
											<div class="{{ VC::C6 }} text-end">
												@php
                                                    $grammarBase = 'grammar';
                                                    $grammarKebab = Str::kebab($grammarBase);
                                                    $grammarResolved = Route::has($grammarBase) ? $grammarBase : (Route::has($grammarKebab) ? $grammarKebab : null);
                                                    $grammarParam = 'grammar';
                                                    $grammarArgs = $grammarResolved ? [$grammarParam] : ['#'];
                                                    $grammarUrl = $grammarResolved ? route($grammarResolved, $grammarArgs) : '#';
                                                    $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                                    $grammarGuardMsg = Utility::fetchLinkMessage($langValue, 'generics', 'grammar_check_route_unavailable') ?? 'Grammar check route is unavailable. Please contact technical support or your domain administrator.';
                                                @endphp
                                                <a href="{{ $grammarUrl }}"
                                                data-size="md"
                                                class="{{ VC::BT_PRM }} btn-icon {{ VC::BT_SM }} {{ VC::MB3 }} me-2 grammar-check"
                                                data-ajax-popup-over="true"
                                                id="grammarCheck"
                                                data-url="{{ $grammarUrl }}"
                                                data-bs-placement="top"
                                                data-title="{{ __('Grammar check with AI') }}"
                                                data-guard-msg="{{ $grammarGuardMsg }}"
                                                data-sv-localized="true">
                                                    <i class="ti ti-rotate"></i> <span>{{ __('Grammar check with AI') }}</span>
                                                </a>
                                                @push(StacksConstants::ADM_SCR_PG)
                                                    <script src="{{ asset('assets/js/routes/generics/grammar.js') }}" defer></script>
                                                @endpush
											</div>
										@endif
									</div>
                                    @php
                                        $sptReplyAnswerBase = VW::SPT.'.reply.answer';
                                        $sptReplyAnswerKebab = Str::kebab($sptReplyAnswerBase);
                                        $sptReplyAnswerResolved = Route::has($sptReplyAnswerBase) ? $sptReplyAnswerBase : (Route::has($sptReplyAnswerKebab) ? $sptReplyAnswerKebab : null);
                                        $supportIdRaw = data_get($support,'id');
                                        $supportId = (is_string($supportIdRaw) && Str::isUuid($supportIdRaw)) ? $supportIdRaw : null;
                                        $sptEncryptedId = $supportId ? Crypt::encrypt($supportId) : null;
                                        $sptReplyAnswerUrl = ($sptReplyAnswerResolved && $sptEncryptedId) ? route($sptReplyAnswerResolved, $sptEncryptedId) : '#';
                                        $langValue = isset($lang) ? $lang : Utility::fetchUserLang();
                                        $replyAnswerGuardMsg = Utility::fetchLinkMessage($langValue, VW::SPT, 'reply_answer_support_route_unavailable') ?? 'Reply answer support route is unavailable. Please contact technical support or your domain administrator.';
                                        $formIdSuffix = $supportId ? substr(md5($supportId),0,8) : 'x';
                                        $formId = 'support-reply-answer-form-'.$formIdSuffix;
                                    @endphp
                                    {{ Form::open([
                                        'method' => 'POST',
                                        'url' => $sptReplyAnswerUrl,
                                        'id' => $formId,
                                        'data-url' => $sptReplyAnswerUrl,
                                        'data-guard-msg' => $replyAnswerGuardMsg,
                                        'data-sv-localized' => 'true',
                                    ]) }}
                                        <textarea class="{{ VC::FM_CT }} form-control-light mb-2 grammar_textarea" name="description" placeholder="{{ __('Your comment') }}" id="example-textarea" rows="3" required></textarea>
                                        <div class="text-end">
                                            <div class="{{ VC::C12 }}">
                                                <button type="submit" class="{{ VC::BT_PRM }} w-100"><i class="{{ VC::TI_CC_PLS }} me-1 {{ VC::MB0 }}"></i> {{ __('Send') }}</button>
                                            </div>
                                        </div>
                                    {{ Form::close() }}
                                    @push(StacksConstants::ADM_SCR_PG)
                                        <script defer>
                                            (() => {
                                                try {
                                                    const f = document.getElementById('{{ $formId }}');
                                                    if (!f) { return; }
                                                    if (f.getAttribute('data-listener-active') === 'true') { return; }
                                                    f.setAttribute('data-listener-active', 'true');
                                                    f.addEventListener('submit', (e) => {
                                                        try {
                                                            const action = f.getAttribute('action') ?? '#';
                                                            const url = f.getAttribute('data-url') ?? action ?? '#';
                                                            if (url !== '#' && action !== '#') { return; }
                                                            e.preventDefault();
                                                            const msg = f.getAttribute('data-guard-msg') ?? 'Reply answer support route is unavailable. Please contact technical support or your domain administrator.';
                                                            const hasBootstrap = !!(document.querySelector('link[href*="bootstrap"]') && window.bootstrap);
                                                            let container = document.getElementById('toast-container');
                                                            if (!container) {
                                                                container = document.createElement('div');
                                                                container.id = 'toast-container';
                                                                document.body.appendChild(container);
                                                            }
                                                            if (hasBootstrap) {
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
                                                            f.setAttribute('data-failed-route', 'true');
                                                        } catch (err) {}
                                                    });
                                                } catch (err) {}
                                            })();
                                        </script>
                                    @endpush
								</div>
							</div>
						</div>
					@endif
				</div>
				<div class="{{ VC::CL6 }}">
					<h5 class="{{ VC::MB3 }}">{{ __('Replies') }}</h5>
					@forelse((($replyes ?? null) instanceof Collection || is_array($replyes ?? null)) ? $replyes : [] as $reply)
						<div class="{{ VC::CD }} {{ VC::BD }}">
							<div class="card-header {{ VC::RW }} {{ VC::DFL_AIC_JCB }}">
								<div class="header-right col d-flex align-items-start">
									@php
										$rAvatar = data_get($reply,'users.avatar');
										$rAvatarSrc = !empty($rAvatar) ? asset(Storage::url('uploads/avatar/')).'/'.$rAvatar : asset(Storage::url('uploads/avatar/')).'/avatar.png';
										$rCreated = data_get($reply,'created_at');
										$rHuman = (is_object($rCreated) && method_exists($rCreated,'diffForHumans')) ? $rCreated->diffForHumans() : __('No time available');
									@endphp
									<a href="#" class="{{ VC::AV_CC_SM }} me-3"><img alt="" src="{{ $rAvatarSrc }}"></a>
									<h6 class="{{ VC::MB0 }}">{{ data_get($reply,'users.name') ?: __('No user name available') }}<div class="{{ VC::DBL }} {{ VC::TXT_MT }}">{{ data_get($reply,'users.email') ?: __('No user email available') }}</div></h6>
								</div>
								<p class="col-auto ms-1 {{ VC::MB0 }}"><span class="{{ VC::TXT_MT }}">{{ $rHuman }}</span></p>
							</div>
							<div class="card-body"><p class="{{ VC::MB0 }}">{{ data_get($reply,'description') ?: __('No description available') }}</p></div>
						</div>
					@empty
						<p class="text-muted">{{ __('No replies available') }}</p>
					@endforelse
				</div>
			</div>
		</div>
	</div>
@endsection
