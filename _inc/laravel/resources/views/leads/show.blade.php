@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants as EL,
        PermissionsConstants as PC,
        PlansConstants as PL,
        StacksConstants as ST,
        UsersConstants as UC,
        ViewClassNamesConstants as VC,
        YieldingConstants as YW,
        ViewsConstants as VW
    };
    use App\Models\{Plan, Utility};
    use Collective\Html\FormFacade as Form;
    use Illuminate\Support\Facades\{Auth, Route, URL};
    use Illuminate\Support\{Collection, Str};

    $user = Auth::user();
    $lang = Utility::fetchUserLang(user:$user);
    $canFetchMsg = is_callable([Utility::class,'fetchLinkMessage']);
    $canDateFormat = is_object($user) && method_exists($user,'dateFormat');
    $canPriceFormat = is_object($user) && method_exists($user,'priceFormat');

    function resolveRoute(string $base): ?string {
        $k = Str::kebab($base);
        return Route::has($base) ? $base : (Route::has($k) ? $k : null);
    }
    function urlFor(string $base, array $params = []): array {
        $resolved = resolveRoute($base);
        return [$resolved, $resolved ? route($resolved, $params) : '#'];
    }

    [$dashResolved, $dashUrl] = urlFor('dashboard');
    $dashGuard = __(($canFetchMsg ? Utility::fetchLinkMessage($lang,'generics','dashboard_unavailable') : 'Dashboard route is unavailable. Please contact technical support or your domain administrator.') ?? 'Dashboard route is unavailable. Please contact technical support or your domain administrator.');

    [$indexResolved, $indexUrl] = urlFor(VW::LD.'.index');
    $indexGuard = __(($canFetchMsg ? Utility::fetchLinkMessage($lang,VW::LD,'lead_index_route_unavailable') : 'Lead index route is unavailable. Please contact technical support or your domain administrator.') ?? 'Lead index route is unavailable. Please contact technical support or your domain administrator.');

    $leadOk = $user && isset($lead) && !empty($lead);
@endphp

@extends(EL::ADM)

@if($leadOk)
    @php
        $leadName = !empty($lead->name) ? $lead->name : __('No given name.');
        $leadId = (string) ($lead->id ?? '');
        $dealId = (string) ($deal->id ?? '');
        $isDealActive = (bool) ($deal->is_active ?? false);

        [$convertShowResolved, $convertShowUrl] = urlFor(VW::LD.'.show_convert', [$leadId]);
        [$labelsResolved, $labelsUrl] = urlFor(VW::LD.'.labels', [$leadId]);
        [$editResolved, $editUrl] = urlFor(VW::LD.'.edit', [$leadId]);

        $dealShowUrl = (function() use($dealId,$isDealActive){
            $base = VW::DL.'.show';
            $resolved = resolveRoute($base);
            return ($resolved && $isDealActive && $dealId !== '') ? route($resolved,$dealId) : '#';
        })();

        $convertGuard = __(($canFetchMsg ? Utility::fetchLinkMessage($lang,VW::LD,'leads_convert_route_unavailable') : 'Convert lead route is unavailable. Please contact technical support or your domain administrator.') ?? 'Convert lead route is unavailable. Please contact technical support or your domain administrator.');
        $labelsGuard  = __(($canFetchMsg ? Utility::fetchLinkMessage($lang,VW::LD,'leads_labels_route_unavailable') : 'Lead labels route is unavailable. Please contact technical support or your domain administrator.') ?? 'Lead labels route is unavailable. Please contact technical support or your domain administrator.');
        $editGuard    = __(($canFetchMsg ? Utility::fetchLinkMessage($lang,VW::LD,'leads_edit_route_unavailable') : 'Edit lead route is unavailable. Please contact technical support or your domain administrator.') ?? 'Edit lead route is unavailable. Please contact technical support or your domain administrator.');

        $usersList   = $lead->users ?? [];
        $products    = $lead->products() ?? [];
        $sources     = $lead->sources() ?? [];
        $calls       = $lead->calls ?? collect();
        $emails      = $lead->emails ?? collect();
        $discussions = $lead->discussions ?? collect();
        $activities  = $lead->activities ?? collect();

        [$usersEditResolved, $usersEditUrl]       = urlFor(VW::LD.'.users.edit',    [$leadId]);
        [$productsEditResolved, $productsEditUrl] = urlFor(VW::LD.'.products.edit', [$leadId]);
        [$sourcesEditResolved, $sourcesEditUrl]   = urlFor(VW::LD.'.sources.edit',  [$leadId]);
        [$emailsCreateResolved, $emailsCreateUrl] = urlFor(VW::LD.'.emails.create', [$leadId]);
        [$discussCreateResolved, $discussCreateUrl] = urlFor(VW::LD.'.discussions.create', [$leadId]);
        [$callsCreateResolved, $callsCreateUrl]   = urlFor(VW::LD.'.calls.create',  [$leadId]);

        $usersEditGuard     = __(($canFetchMsg ? Utility::fetchLinkMessage($lang,VW::LD,'users_edit_route_unavailable') : 'Add user route is unavailable. Please contact technical support or your domain administrator.') ?? 'Add user route is unavailable. Please contact technical support or your domain administrator.');
        $productsEditGuard  = __(($canFetchMsg ? Utility::fetchLinkMessage($lang,VW::LD,'products_edit_route_unavailable') : 'Add product route is unavailable. Please contact technical support or your domain administrator.') ?? 'Add product route is unavailable. Please contact technical support or your domain administrator.');
        $sourcesEditGuard   = __(($canFetchMsg ? Utility::fetchLinkMessage($lang,VW::LD,'sources_edit_route_unavailable') : 'Add source route is unavailable. Please contact technical support or your domain administrator.') ?? 'Add source route is unavailable. Please contact technical support or your domain administrator.');
        $emailsCreateGuard  = __(($canFetchMsg ? Utility::fetchLinkMessage($lang,VW::LD,'emails_create_route_unavailable') : 'Create email route is unavailable. Please contact technical support or your domain administrator.') ?? 'Create email route is unavailable. Please contact technical support or your domain administrator.');
        $discussCreateGuard = __(($canFetchMsg ? Utility::fetchLinkMessage($lang,VW::LD,'discussions_create_route_unavailable') : 'Add discussion route is unavailable. Please contact technical support or your domain administrator.') ?? 'Add discussion route is unavailable. Please contact technical support or your domain administrator.');
        $callsCreateGuard   = __(($canFetchMsg ? Utility::fetchLinkMessage($lang,VW::LD,'calls_create_route_unavailable') : 'Add call route is unavailable. Please contact technical support or your domain administrator.') ?? 'Add call route is unavailable. Please contact technical support or your domain administrator.');

        $plan = Plan::getPlan($user->plan ?? null);
        $canAI = (int) ($plan?->{PL::COL_GPT} ?? 0) === 1;

        if($canAI){
            [$grammarResolved, $grammarUrl] = urlFor('grammar', ['grammar']);
            [$generateResolved, $generateUrl] = urlFor('generate', ['lead']);
            $grammarGuard = __(($canFetchMsg ? Utility::fetchLinkMessage($lang,'generics','grammar_check_route_unavailable') : 'Grammar check route is unavailable. Please contact technical support or your domain administrator.') ?? 'Grammar check route is unavailable. Please contact technical support or your domain administrator.');
            $generateGuard = __(($canFetchMsg ? Utility::fetchLinkMessage($lang,VW::LD,'ai_generate_unavailable') : 'Generate route is unavailable. Please contact technical support or your domain administrator.') ?? 'Generate route is unavailable. Please contact technical support or your domain administrator.');
        }
    @endphp

    @section(YW::ADM_PG_TTL){{ $leadName }}@endsection

    @push(ST::ADM_CSS)
        <link rel="stylesheet" href="{{ asset('css/summernote/summernote-bs4.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/css/plugins/dropzone.min.css') }}">
    @endpush

    @section(YW::ADM_BDC)
        <li class="breadcrumb-item">
            <a href="{{ $dashUrl }}" data-url="{{ $dashUrl }}" data-guard-msg="{{ $dashGuard }}" data-sv-localized="true" class="lead-route-guard" {{ $dashUrl !== '#' ? '' : 'aria-disabled=true' }}>
                {{ __('Dashboard') }}
            </a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ $indexUrl }}" data-url="{{ $indexUrl }}" data-guard-msg="{{ $indexGuard }}" data-sv-localized="true" class="lead-route-guard">
                {{ __('Lead') }}
            </a>
        </li>
        <li class="breadcrumb-item">{{ $leadName }}</li>
    @endsection

    @section(YW::ADM_ACT_BTN)
        @if(!empty($leadId))
            <div class="{{ VC::FEND }}">
                @can('convert lead to deal')
                    @if(!empty($deal))
                        <a href="{{ $dealShowUrl }}" data-size="lg" data-bs-toggle="tooltip" title="{{ __('Already Converted To Deal') }}" class="{{ VC::BT_SM_PM }} lead-route-guard" data-url="{{ $dealShowUrl }}" data-guard-msg="{{ __('Deal route unavailable.') }}" data-sv-localized="true">
                            <i class="ti ti-exchange"></i>
                        </a>
                    @else
                        <a href="#" data-size="lg" data-url="{{ $convertShowUrl }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{ __('Convert ['.($lead->subject ?? __('N/A')).'] To Deal') }}" class="{{ VC::BT_SM_PM }} lead-route-guard" data-guard-msg="{{ $convertGuard }}" data-sv-localized="true">
                            <i class="ti ti-exchange"></i>
                        </a>
                    @endif
                @endcan
                <a href="#" data-url="{{ $labelsUrl }}" data-ajax-popup="true" data-size="lg" data-bs-toggle="tooltip" title="{{ __('Label') }}" class="{{ VC::BT_SM_PM }} lead-route-guard" data-guard-msg="{{ $labelsGuard }}" data-sv-localized="true">
                    <i class="ti ti-bookmark"></i>
                </a>
                <a href="#" data-size="lg" data-url="{{ $editUrl }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{ __('Edit') }}" class="{{ VC::BT_SM_PM }} lead-route-guard" data-guard-msg="{{ $editGuard }}" data-sv-localized="true">
                    <i class="ti ti-pencil"></i>
                </a>
            </div>
        @else
            <p>{{ __('No lead id found.') }}</p>
        @endif
    @endsection

    @section(YW::ADM_CTT)
        <div class="{{ VC::RW }}">
            <div class="col-sm-12">
                <div class="{{ VC::RW }}">
                    <div class="col-xl-3">
                        @php
                            $userType = $user[UC::COL_TP] ?? null;
                            $navItems = [
                                ['id'=>'general','label'=>__('General')],
                                ['id'=>'users_products','label'=>__('Users').' | '.__('Products')],
                                ['id'=>'sources_emails','label'=>__('Sources').' | '.__('Emails')],
                                ['id'=>'discussion_note','label'=>__('Discussion').' | '.__('Notes')],
                                ['id'=>'files','label'=>__('Files')],
                                ['id'=>'calls','label'=>__('Calls')],
                                ['id'=>'activity','label'=>__('Activity')],
                            ];
                        @endphp
                        @if($userType != PC::CL)
                            <div class="{{ VC::CD_STK }}" style="top:30px">
                                <div class="{{ VC::LG_FLSH }}" id="lead-sidenav">
                                    @foreach($navItems as $item)
                                        <a href="#{{ $item['id'] }}" class="{{ VC::LGI_ACT_NBD }}">
                                            {{ $item['label'] }}
                                            <div class="{{ VC::FEND }}"><i class="{{ VC::TI_CHV_RT }}"></i></div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="col-xl-9">
                        <div id="general" class="{{ VC::CD }}">
                            <div class="card-body">
                                <div class="{{ VC::RW }}">
                                    <div class="col-md-4 col-sm-4">
                                        <div class="{{ VC::DFL_AIC }}">
                                            <div class="theme-avatar {{ VC::BG_P }}"><i class="ti ti-mail"></i></div>
                                            <div class="{{ VC::MS2 }}">
                                                <p class="{{ VC::TXT_MT }} text-sm {{ VC::MB0 }}">{{ __('Email') }}</p>
                                                <h5 class="{{ VC::MB0 }} text-primary">{{ $lead->email ?? '' }}</h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-sm-4">
                                        <div class="{{ VC::DFL_AIC }}">
                                            <div class="theme-avatar bg-warning"><i class="ti ti-phone"></i></div>
                                            <div class="{{ VC::MS2 }}">
                                                <p class="{{ VC::TXT_MT }} text-sm {{ VC::MB0 }}">{{ __('Phone') }}</p>
                                                <h5 class="{{ VC::MB0 }} text-warning">{{ $lead->phone ?? '' }}</h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-sm-4">
                                        <div class="{{ VC::DFL_AIC }}">
                                            <div class="theme-avatar bg-info"><i class="ti ti-test-pipe"></i></div>
                                            <div class="{{ VC::MS2 }}">
                                                <p class="{{ VC::TXT_MT }} text-sm {{ VC::MB0 }}">{{ __('Pipeline') }}</p>
                                                <h5 class="{{ VC::MB0 }} text-info">{{ data_get($lead,'pipeline.name',__('N/A')) }}</h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-sm-4 mt-4">
                                        <div class="{{ VC::DFL_AIC }}">
                                            <div class="theme-avatar {{ VC::BG_P }}"><i class="ti ti-server"></i></div>
                                            <div class="{{ VC::MS2 }}">
                                                <p class="{{ VC::TXT_MT }} text-sm {{ VC::MB0 }}">{{ __('Stage') }}</p>
                                                <h5 class="{{ VC::MB0 }} text-primary">{{ data_get($lead,'stage.name',__('N/A')) }}</h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-sm-4 mt-4">
                                        <div class="{{ VC::DFL_AIC }}">
                                            <div class="theme-avatar bg-warning"><i class="ti ti-calendar"></i></div>
                                            <div class="{{ VC::MS2 }}">
                                                <p class="{{ VC::TXT_MT }} text-sm {{ VC::MB0 }}">{{ __('Created') }}</p>
                                                <h5 class="{{ VC::MB0 }} text-warning">{{ $canDateFormat ? ($user?->dateFormat($lead->created_at) ?? __('N/A')) : __('Failed to format date') }}</h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-sm-4 mt-4">
                                        <div class="{{ VC::DFL_AIC }}">
                                            <div class="theme-avatar bg-info"><i class="ti ti-chart-bar"></i></div>
                                            <div class="{{ VC::MS2 }}">
                                                <h3 class="{{ VC::MB0 }} text-info">{{ ($precentage ?? 0) }}%</h3>
                                                <div class="progress {{ VC::MB0 }}"><div class="progress-bar bg-info" style="width: {{ ($precentage ?? 0) }}%;"></div></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="{{ VC::RW }}">
                            <div class="col-sm-4">
                                <div class="{{ VC::CD }}">
                                    <div class="card-body">
                                        <div class="{{ VC::DFL_AIC_JCB }}">
                                            <div class="col-auto {{ VC::MB3 }}"><small class="{{ VC::TXT_MT }}">{{ __('Product') }}</small><h3 class="{{ VC::MB0 }}">{{ Utility::isFilled($products) ? (is_array($products) ? count($products) : ($products instanceof Collection ? $products->count() : 0) ?? []) : 0 }}</h3></div>
                                            <div class="col-auto"><div class="theme-avatar bg-info"><i class="ti ti-shopping-cart"></i></div></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="{{ VC::CD }}">
                                    <div class="card-body">
                                        <div class="{{ VC::DFL_AIC_JCB }}">
                                            <div class="col-auto {{ VC::MB3 }}"><small class="{{ VC::TXT_MT }}">{{ __('Source') }}</small><h3 class="{{ VC::MB0 }}">{{ Utility::isFilled($sources) ? (is_array($sources) ? count($sources) : ($sources instanceof Collection ? $sources->count() : 0) ?? []) : 0 }}</h3></div>
                                            <div class="col-auto"><div class="theme-avatar {{ VC::BG_P }}"><i class="ti ti-social"></i></div></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="{{ VC::CD }}">
                                    <div class="card-body">
                                        <div class="{{ VC::DFL_AIC_JCB }}">
                                            <div class="col-auto {{ VC::MB3 }}"><small class="{{ VC::TXT_MT }}">{{ __('Files') }}</small><h3 class="{{ VC::MB0 }}">{{ Utility::isFilled($lead->files) ? (is_array($lead->files) ? count($lead->files) : ($lead->files instanceof Collection ? $lead->files->count() : 0) ?? []) : 0 }}</h3></div>
                                            <div class="col-auto"><div class="theme-avatar bg-warning"><i class="ti ti-file"></i></div></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="users_products">
                            <div class="{{ VC::RW }}">
                                <div class="col-6">
                                    <div class="{{ VC::CD }}">
                                        <div class="card-header">
                                            <div class="{{ VC::DFL_AIC_JCB }}">
                                                <h5>{{ __('Users') }}</h5>
                                                <div class="{{ VC::FEND }}">
                                                    <a data-size="md" data-url="{{ $usersEditUrl }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{ __('Add User') }}" class="{{ VC::BT_SM_PM }} lead-route-guard" data-guard-msg="{{ $usersEditGuard }}" data-sv-localized="true">
                                                        <i class="ti ti-plus {{ VC::TXT_WT }}"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-hover mb-0">
                                                    <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Action') }}</th></tr></thead>
                                                    <tbody>
                                                    @if(Utility::isFilled($usersList) ?? [])
                                                        @foreach($usersList as $u)
                                                            @php
                                                                $uid = (string) ($u->id ?? '');
                                                                [$userDestroyResolved, $userDestroyUrl] = urlFor(VW::LD.'.users.destroy', [$leadId,$uid]);
                                                            @endphp
                                                            <tr>
                                                                <td>
                                                                    <div class="{{ VC::DFL_AIC }}">
                                                                        <div><img src="{{ $u->avatar ? asset('/storage/uploads/avatar/'.$u->avatar) : asset('/storage/uploads/avatar/avatar.png') }}" class="wid-30 rounded-circle me-3" alt="{{ __('avatar image') }}"></div>
                                                                        <p class="{{ VC::MB0 }}">{{ $u->name ?? '' }}</p>
                                                                    </div>
                                                                </td>
                                                                @can('edit lead')
                                                                    <td>
                                                                        <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                                            {!! Form::open(['method'=>'DELETE','route'=>[$userDestroyResolved ?? VW::LD.'.users.destroy', $leadId, $uid],'id'=>'delete-user-'.$uid]) !!}
                                                                            <a href="#"
                                                                               class="{{ VC::BT_SM_CT_PR }} lead-delete-guard"
                                                                               data-url="{{ $userDestroyUrl }}"
                                                                               data-guard-msg="{{ __('Delete route unavailable.') }}"
                                                                               data-sv-localized="true"
                                                                               data-bs-toggle="tooltip"
                                                                               title="{{ __('Delete') }}"
                                                                               data-confirm="{{ __('Are You Sure?') }}|{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                               data-confirm-yes="document.getElementById('delete-user-{{ $uid }}').submit();">
                                                                                <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                            </a>
                                                                            {!! Form::close() !!}
                                                                        </div>
                                                                    </td>
                                                                @endcan
                                                            </tr>
                                                        @endforeach
                                                    @else
                                                        <tr><td colspan="2" class="text-center text-muted py-3">{{ __('No users available.') }}</td></tr>
                                                    @endif
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-6">
                                    <div class="{{ VC::CD }}">
                                        <div class="card-header">
                                            <div class="{{ VC::DFL_AIC_JCB }}">
                                                <h5>{{ __('Products') }}</h5>
                                                <div class="{{ VC::FEND }}">
                                                    <a data-size="md" data-url="{{ $productsEditUrl }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{ __('Add Product') }}" class="{{ VC::BT_SM_PM }} lead-route-guard" data-guard-msg="{{ $productsEditGuard }}" data-sv-localized="true">
                                                        <i class="ti ti-plus {{ VC::TXT_WT }}"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-hover mb-0">
                                                    <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Price') }}</th><th>{{ __('Action') }}</th></tr></thead>
                                                    <tbody>
                                                    @if(Utility::isFilled($products) ?? [])
                                                        @foreach($products as $product)
                                                            @php
                                                                $pid = (string) ($product->id ?? '');
                                                                [$prodDestroyResolved, $prodDestroyUrl] = urlFor(VW::LD.'.products.destroy', [$leadId,$pid]);
                                                                $price = $canPriceFormat ? ($user?->priceFormat($product->sale_price) ?? __('N/A')) : __('Failed to format price');
                                                            @endphp
                                                            <tr>
                                                                <td>{{ $product->name ?? '' }}</td>
                                                                <td>{{ $price }}</td>
                                                                @can('edit lead')
                                                                    <td>
                                                                        <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                                            {!! Form::open(['method'=>'DELETE','route'=>[$prodDestroyResolved ?? VW::LD.'.products.destroy', $leadId,$pid],'id'=>'delete-product-'.$pid]) !!}
                                                                            <a href="#"
                                                                               class="{{ VC::BT_SM_CT_PR }} lead-delete-guard"
                                                                               data-url="{{ $prodDestroyUrl }}"
                                                                               data-guard-msg="{{ __('Delete route unavailable.') }}"
                                                                               data-sv-localized="true"
                                                                               data-bs-toggle="tooltip"
                                                                               title="{{ __('Delete') }}"
                                                                               data-confirm="{{ __('Are You Sure?') }}|{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                               data-confirm-yes="document.getElementById('delete-product-{{ $pid }}').submit();">
                                                                                <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                            </a>
                                                                            {!! Form::close() !!}
                                                                        </div>
                                                                    </td>
                                                                @endcan
                                                            </tr>
                                                        @endforeach
                                                    @else
                                                        <tr><td colspan="3" class="text-center text-muted py-3">{{ __('No products available.') }}</td></tr>
                                                    @endif
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
                                <div class="col-6">
                                    <div class="{{ VC::CD }}">
                                        <div class="card-header">
                                            <div class="{{ VC::DFL_AIC_JCB }}">
                                                <h5>{{ __('Sources') }}</h5>
                                                <div class="{{ VC::FEND }}">
                                                    <a data-size="md" data-url="{{ $sourcesEditUrl }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{ __('Add Source') }}" class="{{ VC::BT_SM_PM }} lead-route-guard" data-guard-msg="{{ $sourcesEditGuard }}" data-sv-localized="true">
                                                        <i class="ti ti-plus {{ VC::TXT_WT }}"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-hover mb-0">
                                                    <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Action') }}</th></tr></thead>
                                                    <tbody>
                                                    @if(Utility::isFilled($sources) ?? [])
                                                        @foreach($sources as $source)
                                                            @php
                                                                $sid = (string) ($source->id ?? '');
                                                                [$srcDestroyResolved, $srcDestroyUrl] = urlFor(VW::LD.'.sources.destroy', [$leadId,$sid]);
                                                            @endphp
                                                            <tr>
                                                                <td>{{ $source->name ?? '' }}</td>
                                                                @can('edit lead')
                                                                    <td>
                                                                        <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                                            {!! Form::open(['method'=>'DELETE','route'=>[$srcDestroyResolved ?? VW::LD.'.sources.destroy',$leadId,$sid],'id'=>'delete-source-'.$sid]) !!}
                                                                            <a href="#"
                                                                               class="{{ VC::BT_SM_CT_PR }} lead-delete-guard"
                                                                               data-url="{{ $srcDestroyUrl }}"
                                                                               data-guard-msg="{{ __('Delete route unavailable.') }}"
                                                                               data-sv-localized="true"
                                                                               data-bs-toggle="tooltip"
                                                                               title="{{ __('Delete') }}"
                                                                               data-confirm="{{ __('Are You Sure?') }}|{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                               data-confirm-yes="document.getElementById('delete-source-{{ $sid }}').submit();">
                                                                                <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                            </a>
                                                                            {!! Form::close() !!}
                                                                        </div>
                                                                    </td>
                                                                @endcan
                                                            </tr>
                                                        @endforeach
                                                    @else
                                                        <tr><td colspan="2" class="text-center text-muted py-3">{{ __('No sources available.') }}</td></tr>
                                                    @endif
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-6">
                                    <div class="{{ VC::CD }}">
                                        <div class="card-header">
                                            <div class="{{ VC::DFL_AIC_JCB }}">
                                                <h5>{{ __('Emails') }}</h5>
                                                @can('create lead email')
                                                    <div class="{{ VC::FEND }}">
                                                        <a data-size="md" data-url="{{ $emailsCreateUrl }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{ __('Create Email') }}" class="{{ VC::BT_SM_PM }} lead-route-guard" data-guard-msg="{{ $emailsCreateGuard }}" data-sv-localized="true">
                                                            <i class="ti ti-plus {{ VC::TXT_WT }}"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="{{ VC::LG_FLSH_MT2 }}">
                                                @if(Utility::isFilled($emails) ?? [])
                                                    @foreach($emails as $email)
                                                        <li class="list-group-item px-0">
                                                            <div class="d-block d-sm-flex align-items-start">
                                                                <img src="{{ asset('/storage/uploads/avatar/avatar.png') }}" class="img-fluid wid-40 me-3 mb-2 mb-sm-0" alt="{{ __('image') }}">
                                                                <div class="w-100">
                                                                    <div class="{{ VC::DFL_AIC_JCB }}">
                                                                        <div class="{{ VC::MB3 }}">
                                                                            <h6 class="{{ VC::MB0 }}">{{ $email->subject }}</h6>
                                                                            <span class="{{ VC::TXT_MT }} text-sm">{{ $email->to }}</span>
                                                                        </div>
                                                                        <div class="form-check form-switch form-switch-right mb-2">{{ $email->created_at?->diffForHumans() }}</div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                @else
                                                    <li class="text-center">{{ __('No Emails Available.!') }}</li>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="discussion_note">
                            <div class="{{ VC::RW }}">
                                <div class="col-6">
                                    <div class="{{ VC::CD }}">
                                        <div class="card-header">
                                            <div class="{{ VC::DFL_AIC_JCB }}">
                                                <h5>{{ __('Discussion') }}</h5>
                                                <div class="{{ VC::FEND }}">
                                                    <a data-size="lg" data-url="{{ $discussCreateUrl }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{ __('Add Message') }}" class="{{ VC::BT_SM_PM }} lead-route-guard" data-guard-msg="{{ $discussCreateGuard }}" data-sv-localized="true">
                                                        <i class="ti ti-plus {{ VC::TXT_WT }}"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <ul class="{{ VC::LG_FLSH_MT2 }}">
                                                @if(Utility::isFilled($discussions) ?? [])
                                                    @foreach($discussions as $discussion)
                                                        <li class="list-group-item px-0">
                                                            <div class="d-block d-sm-flex align-items-start">
                                                                <img src="{{ $discussion->user?->avatar ? asset('/storage/uploads/avatar/'.$discussion->user->avatar) : asset('/storage/uploads/avatar/avatar.png') }}" class="img-fluid wid-40 me-3 mb-2 mb-sm-0" alt="{{ __('image') }}">
                                                                <div class="w-100">
                                                                    <div class="{{ VC::DFL_AIC_JCB }}">
                                                                        <div class="{{ VC::MB3 }}">
                                                                            <h6 class="{{ VC::MB0 }}">{{ $discussion->comment }}</h6>
                                                                            <span class="{{ VC::TXT_MT }} text-sm">{{ $discussion->user?->name }}</span>
                                                                        </div>
                                                                        <div class="form-check form-switch form-switch-right mb-2">{{ $discussion->created_at?->diffForHumans() }}</div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                @else
                                                    <li class="text-center">{{ __('No Data Available.!') }}</li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-6">
                                    <div class="{{ VC::CD }}">
                                        <div class="card-header">
                                            <div class="{{ VC::DFL_AIC_JCB }}">
                                                <h5>{{ __('Notes') }}</h5>
                                                @if($canAI)
                                                    <div class="{{ VC::FEND }}">
                                                        <a href="#" data-size="md" class="{{ VC::BT_PRM }} btn-icon {{ VC::BT_SM }}" data-ajax-popup-over="true" id="grammarCheck" data-url="{{ $grammarUrl }}" data-guard-msg="{{ $grammarGuard }}" data-sv-localized="true" data-bs-placement="top" data-title="{{ __('Grammar check with AI') }}">
                                                            <i class="ti ti-rotate"></i> <span>{{ __('Grammar check with AI') }}</span>
                                                        </a>
                                                        <a href="#" data-size="md" class="{{ VC::BT_PRM }} btn-icon {{ VC::BT_SM }}" data-ajax-popup-over="true" data-url="{{ $generateUrl }}" data-guard-msg="{{ $generateGuard }}" data-sv-localized="true" data-bs-placement="top" data-title="{{ __('Generate content with AI') }}">
                                                            <i class="{{ VC::FAS_RB }}"></i> <span>{{ __('Generate with AI') }}</span>
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <textarea class="summernote-simple" name="note">{!! $lead->notes !!}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="files" class="{{ VC::CD }}">
                            <div class="card-header"><h5>{{ __('Files') }}</h5></div>
                            <div class="card-body"><div class="col-md-12 dropzone top-5-scroll browse-file" id="dropzonewidget"></div></div>
                        </div>

                        <div id="calls" class="{{ VC::CD }}">
                            <div class="card-header">
                                <div class="{{ VC::DFL_AIC_JCB }}">
                                    <h5>{{ __('Calls') }}</h5>
                                    <div class="{{ VC::FEND }}">
                                        <a data-size="lg" data-url="{{ $callsCreateUrl }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{ __('Add Call') }}" class="{{ VC::BT_SM_PM }} lead-route-guard" data-guard-msg="{{ $callsCreateGuard }}" data-sv-localized="true">
                                            <i class="ti ti-plus {{ VC::TXT_WT }}"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead><tr><th>{{ __('Subject') }}</th><th>{{ __('Call Type') }}</th><th>{{ __('Duration') }}</th><th>{{ __('User') }}</th><th>{{ __('Action') }}</th></tr></thead>
                                        <tbody>
                                        @if(Utility::isFilled($calls) ?? [])
                                            @foreach($calls as $call)
                                                @php
                                                    $cid = (string) ($call->id ?? '');
                                                    $callEditUrl = URL::to(VW::LD.'/'.$leadId.'/call/'.$cid.'/edit');
                                                    [$callDestroyResolved, $callDestroyUrl] = urlFor(VW::LD.'.calls.destroy', [$leadId,$cid]);
                                                @endphp
                                                <tr>
                                                    <td>{{ $call->subject }}</td>
                                                    <td>{{ ucfirst($call->call_type ?? '') }}</td>
                                                    <td>{{ $call->duration }}</td>
                                                    <td>{{ optional($call->getLeadCallUser)->name ?? '-' }}</td>
                                                    <td>
                                                        @can('edit lead call')
                                                            <div class="{{ VC::ACT_BTN_INF }}">
                                                                <a href="#"
                                                                   class="{{ VC::BT_SM_FL_CT }} lead-route-guard"
                                                                   data-url="{{ $callEditUrl }}"
                                                                   data-ajax-popup="true"
                                                                   data-size="xl"
                                                                   data-bs-toggle="tooltip"
                                                                   title="{{ __('Edit') }}"
                                                                   data-title="{{ __('Role Edit') }}"
                                                                   data-guard-msg="{{ __('Edit route unavailable.') }}"
                                                                   data-sv-localized="true">
                                                                    <i class="{{ VC::TI_PC_WT }}"></i>
                                                                </a>
                                                            </div>
                                                        @endcan
                                                        @can('delete lead call')
                                                            <div class="{{ VC::ACT_BTN_DNG_2 }}">
                                                                {!! Form::open(['method'=>'DELETE','route'=>[$callDestroyResolved ?? VW::LD.'.calls.destroy', $leadId,$cid],'id'=>'delete-call-'.$cid]) !!}
                                                                <a href="#"
                                                                   class="{{ VC::BT_SM_CT_PR }} lead-delete-guard"
                                                                   data-bs-toggle="tooltip"
                                                                   title="{{ __('Delete') }}"
                                                                   data-url="{{ $callDestroyUrl }}"
                                                                   data-guard-msg="{{ __('Delete route unavailable.') }}"
                                                                   data-sv-localized="true"
                                                                   data-confirm="{{ __('Are You Sure?') }}|{{ __('This action can not be undone. Do you want to continue?') }}"
                                                                   data-confirm-yes="document.getElementById('delete-call-{{ $cid }}').submit();">
                                                                    <i class="{{ VC::TI_TRS_WT }}"></i>
                                                                </a>
                                                                {!! Form::close() !!}
                                                            </div>
                                                        @endcan
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr><td colspan="5" class="text-center text-muted py-3">{{ __('No calls available.') }}</td></tr>
                                        @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div id="activity" class="{{ VC::CD }}">
                            <div class="card-header"><h5>{{ __('Activity') }}</h5></div>
                            <div class="card-body">
                                <div class="{{ VC::RW }} leads-scroll">
                                    <ul class="{{ VC::LG_FLSH_W }}">
                                        @if(Utility::isFilled($activities) ?? [])
                                            @foreach($activities as $activity)
                                                <li class="list-group-item {{ VC::CD }} {{ VC::MB3 }}">
                                                    <div class="{{ VC::DFL_AIC_JCB }}">
                                                        <div class="col-auto {{ VC::MB3 }}">
                                                            <div class="{{ VC::DFL_AIC }}">
                                                                <div class="theme-avatar {{ VC::BG_P }}"><i class="ti {{ $activity->logIcon() }}"></i></div>
                                                                <div class="{{ VC::MS3 ?? 'ms-3' }}">
                                                                    <span class="text-dark text-sm">{{ __($activity->log_type) }}</span>
                                                                    <h6 class="m-0">{!! $activity->getLeadRemark() !!}</h6>
                                                                    <small class="{{ VC::TXT_MT }}">{{ $activity->created_at?->diffForHumans() }}</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-auto"></div>
                                                    </div>
                                                </li>
                                            @endforeach
                                        @else
                                            <li class="text-center">{{ __('No activity found yet.') }}</li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    @endsection

    @push(ST::ADM_SCR_PG)
        <script src="{{asset('css/summernote/summernote-bs4.js')}}"></script>
        <script src="{{asset('assets/js/plugins/dropzone-amd-module.min.js')}}"></script>
        <script async src="{{ asset('assets/js/routes/leads/lang/show.js') }}"></script>
        <script defer src="{{ asset('assets/js/routes/leads/show.js') }}"></script>
        <script defer>
            (()=>{
                const errFb = "# ERROR";
                const dataClientLocalized = "data-client-localized";
                const dataGuardMsg = "data-guard-msg";
                const DATA_LISTENER_ADDED = "data-listener-added";

                const getMsg = (el, msgKey) => {
                let msg = errFb;
                if (el?.getAttribute("data-sv-localized") === "true" || el?.getAttribute(dataClientLocalized) === "true") {
                    msg = el.getAttribute(dataGuardMsg) || errFb;
                } else {
                    let lang = (window.sessionStorage.getItem("erp-np-lang") || document.documentElement.lang || "en")
                    .toLowerCase()
                    .replace(/_/g, "-");
                    lang = lang === "pt-br" ? lang : lang.slice(0, 2);
                    msg =
                    window.translations?.[lang]?.[msgKey] ||
                    el?.getAttribute(dataGuardMsg) ||
                    window.translations?.en?.[msgKey] ||
                    errFb;
                    if (msg !== errFb) {
                    el?.setAttribute(dataGuardMsg, msg);
                    el?.setAttribute(dataClientLocalized, "true");
                    }
                }
                return msg;
                };

                const showFeedback = (el, key, ev = "pointerup") => {
                const text = getMsg(el || document.body, key);
                const hasBs = document.querySelector('link[href*="bootstrap"]') && window.bootstrap?.Toast;
                if (hasBs) {
                    let toast = document.querySelector("#np-error-toast");
                    if (!toast) {
                    toast = document.createElement("div");
                    toast.id = "np-error-toast";
                    toast.className = "toast align-items-center text-bg-danger border-0";
                    toast.setAttribute("role", "alert");
                    toast.setAttribute("aria-live", "assertive");
                    toast.setAttribute("aria-atomic", "true");
                    toast.innerHTML = `
                        <div class="d-flex">
                        <div class="toast-body">${text}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="{{ __('Close') }}"></button>
                        </div>`;
                    document.body.appendChild(toast);
                    }
                    const handler = () => new bootstrap.Toast(toast).show();
                    document.addEventListener(ev, handler, { once: true });
                    const mo = new MutationObserver((_, o) => {
                    if (!document.body.contains(toast)) {
                        document.removeEventListener(ev, handler);
                        o.disconnect();
                    }
                    });
                    mo.observe(document.body, { childList: true, subtree: true });
                } else {
                    const handler = () => alert(text);
                    document.addEventListener(ev, handler, { once: true });
                }
                };

                const guardOnce = (el, key, ev = "pointerup") => {
                if (!el || el.getAttribute(DATA_LISTENER_ADDED) === "true") return;
                const handler = () => showFeedback(el, key, ev);
                el.addEventListener(ev, handler, { once: true });
                el.setAttribute(DATA_LISTENER_ADDED, "true");
                const mo = new MutationObserver((_, o) => {
                    if (!document.body.contains(el)) {
                    el.removeEventListener(ev, handler);
                    o.disconnect();
                    }
                });
                mo.observe(document.body, { childList: true, subtree: true });
                };

                const routeGuard = (element, alt) => {
                const url  = element?.getAttribute?.("data-url");
                const href = element?.action ?? element?.href;
                return (!url || url === "#") && (!href || href === "#") && (!alt || alt === "#");
                };

                try {
                if (typeof $ === "undefined") { 
                    if (
                        window.location.hostname === "localhost" ||
                        window.location.hostname === "127.0.0.1"
                    ) console.error("jQuery unavailable");
                    return; 
                }

                const leadId = {{$lead->id ?? 'null'}};
                const uploadUrl = "{{ route(ViewsConstants::LD.'.file.upload', $lead->id) }}";
                const saveNotesUrl = "{{ route(ViewsConstants::LD.'.note.store', $lead->id) }}";

                if (window.bootstrap?.ScrollSpy && document.querySelector("#lead-sidenav")) {
                    if (!document.body.getAttribute("data-np-scrollspy-lead")) {
                    new bootstrap.ScrollSpy(document.body, { target: "#lead-sidenav", offset: 300 });
                    document.body.setAttribute("data-np-scrollspy-lead", "true");
                    }
                }

                if (!window.Dropzone) {
                    if (
                        window.location.hostname === "localhost" ||
                        window.location.hostname === "127.0.0.1"
                    ) console.error("Dropzone unavailable");
                    guardOnce(document.body, "dropzone_unavailable");
                } else {
                    try { window.Dropzone.autoDiscover = false; } catch {}

                    const dzSelector = "#dropzonewidget";
                    const dzEl = document.querySelector(dzSelector);

                    if (dzEl && !routeGuard(null, uploadUrl)) {
                    const dz = new Dropzone(dzSelector, {
                        maxFiles: 20,
                        parallelUploads: 1,
                        filename: false,
                        url: uploadUrl,
                        success(file, response) {
                        const ok = response && (response.is_success ?? false);
                        if (ok) {
                            if (String(response.status ?? "") === "1" && typeof window.show_toastr === "function") {
                            window.show_toastr("success", response.success_msg ?? "", "success");
                            }
                            dropzoneBtn(file, response);
                        } else {
                            this.removeFile(file);
                            guardOnce(dzEl, "lead_upload_unavailable");
                        }
                        },
                        error(file, response) {
                        this.removeFile(file);
                        const hasErr = response && response.error;
                        if (!hasErr) {
                            guardOnce(dzEl, "lead_upload_unavailable");
                        } else {
                            guardOnce(dzEl, "lead_upload_unavailable");
                        }
                        }
                    });

                    dz.on("sending", (file, xhr, formData) => {
                        formData.append("_token", $('meta[name="csrf-token"]').attr('content') ?? "");
                        formData.append("lead_id", leadId ?? "");
                    });

                    const dropzoneBtn = (file, response) => {
                        const ensureOnce = (tpl, selector, node) => {
                        if (!tpl.querySelector(selector)) tpl.appendChild(node);
                        };

                        const download = document.createElement("a");
                        download.href = response?.download ?? "#";
                        download.className = "badge bg-info mx-1";
                        download.setAttribute("data-toggle", "tooltip");
                        download.setAttribute("data-original-title", "{{ __('Download') }}");
                        download.innerHTML = "<i class='ti ti-download'></i>";

                        const del = document.createElement("a");
                        del.href = response?.delete ?? "#";
                        del.className = "badge bg-danger mx-1";
                        del.setAttribute("data-toggle", "tooltip");
                        del.setAttribute("data-original-title", "{{ __('Delete') }}");
                        del.innerHTML = "<i class='ti ti-trash'></i>";

                        const onDelete = (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        if (!confirm("Are you sure ?")) return;
                        if (routeGuard(del, del.href)) { guardOnce(del, "lead_delete_unavailable"); return; }
                        $.ajax({
                            url: del.href,
                            type: "DELETE",
                            data: { _token: $('meta[name="csrf-token"]').attr('content') },
                            success: (res) => {
                            if (res && res.is_success) {
                                $(del).closest(".dz-image-preview").remove();
                            } else {
                                guardOnce(del, "lead_delete_unavailable");
                            }
                            },
                            error: (res) => {
                            const r = res?.responseJSON;
                            if (!(r && r.is_success)) guardOnce(del, "lead_delete_unavailable");
                            }
                        });
                        };

                        del.addEventListener("click", onDelete, { once: true });
                        const mo = new MutationObserver((_, o) => {
                        if (!document.body.contains(del)) { del.removeEventListener("click", onDelete); o.disconnect(); }
                        });
                        mo.observe(document.body, { childList: true, subtree: true });

                        const container = document.createElement("div");
                        ensureOnce(file.previewTemplate, "a.badge.bg-info", download);
                        @if($user[UsersConstants::COL_TP] != PermissionsConstants::CL)
                        @can('edit lead')
                        ensureOnce(file.previewTemplate, "a.badge.bg-danger", del);
                        @endcan
                        @endif
                        file.previewTemplate.appendChild(container);
                    };

                    @foreach($lead->files as $file)
                    @if (file_exists(storage_path('lead_files/'.$file->file_path)))
                    (function(){
                        const mock = { name: "{{$file->file_name}}", size: {{ \File::size(storage_path('lead_files/'.$file->file_path)) }} };
                        dz.emit("addedfile", mock);
                        dz.emit("thumbnail", mock, "{{ asset(Storage::url('lead_files/'.$file->file_path)) }}");
                        dz.emit("complete", mock);
                        const resp = {
                        download: "{{ route(ViewsConstants::LD.'.file.download',[$lead->id,$file->id]) }}",
                        delete:   "{{ route(ViewsConstants::LD.'.file.delete',[$lead->id,$file->id]) }}"
                        };
                        // reuse helper inside closure
                        const addBtns = (file, response) => {
                        const download = document.createElement("a");
                        download.href = response?.download ?? "#";
                        download.className = "badge bg-info mx-1";
                        download.innerHTML = "<i class='ti ti-download'></i>";
                        const del = document.createElement("a");
                        del.href = response?.delete ?? "#";
                        del.className = "badge bg-danger mx-1";
                        del.innerHTML = "<i class='ti ti-trash'></i>";
                        const onDelete = (e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            if (!confirm("Are you sure ?")) return;
                            if (routeGuard(del, del.href)) { guardOnce(del, "lead_delete_unavailable"); return; }
                            $.ajax({
                            url: del.href,
                            type: "DELETE",
                            data: { _token: $('meta[name="csrf-token"]').attr('content') },
                            success: (res) => { if (res?.is_success) $(del).closest(".dz-image-preview").remove(); else guardOnce(del, "lead_delete_unavailable"); },
                            error:   () => guardOnce(del, "lead_delete_unavailable")
                            });
                        };
                        del.addEventListener("click", onDelete, { once: true });
                        file.previewTemplate.appendChild(download);
                        @if($user[UsersConstants::COL_TP] != PermissionsConstants::CL)
                        @can('edit lead')
                        file.previewTemplate.appendChild(del);
                        @endcan
                        @endif
                        };
                        addBtns(mock, resp);
                    })();
                    @endif
                    @endforeach
                    } else {
                    guardOnce(document.body, "dropzone_unavailable");
                    }
                }

                @can('edit lead')
                if ($(".summernote-simple").length) {
                    $(".summernote-simple").on("summernote.blur", function () {
                    const notesVal = $(this).val() ?? "";
                    if (routeGuard(null, saveNotesUrl)) { guardOnce(this, "lead_notes_unavailable"); return; }
                    $.ajax({
                        url: saveNotesUrl,
                        type: "POST",
                        data: { _token: $('meta[name="csrf-token"]').attr('content'), notes: notesVal },
                        success: (res) => { if (!(res && res.is_success)) guardOnce(this, "lead_notes_unavailable"); },
                        error:   ()  => guardOnce(this, "lead_notes_unavailable")
                    });
                    });
                }
                @else
                if ($(".summernote-simple").length && $.fn.summernote) {
                    $(".summernote-simple").summernote("disable");
                }
                @endcan
                } catch (e) {
                    if (
                        window.location.hostname === "localhost" ||
                        window.location.hostname === "127.0.0.1"
                    ) {
                        console.error("Initialization failed", e);
                    }
                }
            })();
        </script>
    @endpush
@else
    <div class="alert alert-danger">{{ __('Lead not found') }}</div>
@endif