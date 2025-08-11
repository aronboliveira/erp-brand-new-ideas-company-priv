@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        PermissionsConstants,
        StacksConstants,
        UsersConstants,
        ViewClassNamesConstants,
        YieldingConstants,
    };
    use Illuminate\Support\Facades\{Auth, Route};
    $user = Auth::user();
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{$lead->name}}
@endsection
@push(StacksConstants::ADM_CSS)
    <link rel="stylesheet" href="{{asset('css/summernote/summernote-bs4.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/plugins/dropzone.min.css')}}">
@endpush
@push(StacksConstants::ADM_SCR_PG)
    <script src="{{asset('css/summernote/summernote-bs4.js')}}"></script>
    <script src="{{asset('assets/js/plugins/dropzone-amd-module.min.js')}}"></script>
    <script>
        var scrollSpy = new bootstrap.ScrollSpy(document.body, {
            target: '#lead-sidenav',
            offset: 300
        })
        Dropzone.autoDiscover = false;
        Dropzone.autoDiscover = false;
        myDropzone = new Dropzone("#dropzonewidget", {
            maxFiles: 20,
            // maxFilesize: 2000,
            parallelUploads: 1,
            filename: false,
            // acceptedFiles: ".jpeg,.jpg,.png,.pdf,.doc,.txt",
            url: "{{route(ViewsConstants::LD.'.file.upload',$lead->id)}}",
            success: function (file, response) {
                if (response.is_success) {
                    if(response.status==1){
                        show_toastr('success', response.success_msg, 'success');
                    }
                    dropzoneBtn(file, response);
                } else {
                    myDropzone.removeFile(file);
                    show_toastr('error', response.error, 'error');
                }
            },
            error: function (file, response) {
                myDropzone.removeFile(file);
                if (response.error) {
                    show_toastr('error', response.error, 'error');
                } else {
                    show_toastr('error', response, 'error');
                }
            }
        });
        myDropzone.on("sending", function (file, xhr, formData) {
            formData.append("_token", $('meta[name="csrf-token"]').attr('content'));
            formData.append("lead_id", {{$lead->id}});
        });

        function dropzoneBtn(file, response) {
            var download = document.createElement('a');
            download.setAttribute('href', response.download);
            download.setAttribute('class', "badge bg-info mx-1");
            download.setAttribute('data-toggle', "tooltip");
            download.setAttribute('data-original-title', "{{__('Download')}}");
            download.innerHTML = "<i class='ti ti-download'></i>";

            var del = document.createElement('a');
            del.setAttribute('href', response.delete);
            del.setAttribute('class', "badge bg-danger mx-1");
            del.setAttribute('data-toggle', "tooltip");
            del.setAttribute('data-original-title', "{{__('Delete')}}");
            del.innerHTML = "<i class='ti ti-trash'></i>";

            del.addEventListener("click", function (e) {
                e.preventDefault();
                e.stopPropagation();
                if (confirm("Are you sure ?")) {
                    var btn = $(this);
                    $.ajax({
                        url: btn.attr('href'),
                        data: {_token: $('meta[name="csrf-token"]').attr('content')},
                        type: 'DELETE',
                        success: function (response) {
                            if (response.is_success) {
                                btn.closest('.dz-image-preview').remove();
                            } else {
                                show_toastr('error', response.error, 'error');
                            }
                        },
                        error: function (response) {
                            response = response.responseJSON;
                            if (response.is_success) {
                                show_toastr('error', response.error, 'error');
                            } else {
                                show_toastr('error', response, 'error');
                            }
                        }
                    })
                }
            });

            var html = document.createElement('div');
            html.appendChild(download);
            @if($user[UsersConstants::COL_TP] != PermissionsConstants::CL)
            @can('edit lead')
            html.appendChild(del);
            @endcan
            @endif

            file.previewTemplate.appendChild(html);
        }

        @foreach($lead->files as $file)
        @if (file_exists(storage_path('lead_files/'.$file->file_path)))
        // Create the mock file:
        var mockFile = {name: "{{$file->file_name}}", size: {{\File::size(storage_path('lead_files/'.$file->file_path))}}};
        // Call the default addedfile event handler
        myDropzone.emit("addedfile", mockFile);
        // And optionally show the thumbnail of the file:
        myDropzone.emit("thumbnail", mockFile, "{{asset(Storage::url('lead_files/'.$file->file_path))}}");
        myDropzone.emit("complete", mockFile);

        dropzoneBtn(mockFile, {download: "{{route(ViewsConstants::LD.'.file.download',[$lead->id,$file->id])}}", delete: "{{route(ViewsConstants::LD.'.file.delete',[$lead->id,$file->id])}}"});
        @endif
        @endforeach

        @can('edit lead')
        $('.summernote-simple').on('summernote.blur', function () {

            $.ajax({
                url: "{{route(ViewsConstants::LD.'.note.store',$lead->id)}}",
                data: {_token: $('meta[name="csrf-token"]').attr('content'), notes: $(this).val()},
                type: 'POST',
                success: function (response) {
                    if (response.is_success) {
                        // show_toastr('Success', response.success,'success');
                    } else {
                        show_toastr('error', response.error, 'error');
                    }
                },
                error: function (response) {
                    response = response.responseJSON;
                    if (response.is_success) {
                        show_toastr('error', response.error, 'error');
                    } else {
                        show_toastr('error', response, 'error');
                    }
                }
            })
        });
        @else
        $('.summernote-simple').summernote('disable');
        @endcan

    </script>
@endpush
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item"><a href="{{route(ViewsConstants::LD.'.index')}}">{{__('Lead')}}</a></li>
    <li class="breadcrumb-item"> {{$lead->name}}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
    <div class="float-end">
        @can('convert lead to deal')
            @if(!empty($deal))
                <a href="@can('View Deal') @if($deal->is_active) {{route('deals.show',$deal->id)}} @else # @endif @else # @endcan" data-size="lg" data-bs-toggle="tooltip" title=" {{__('Already Converted To Deal')}}" class="btn btn-sm btn-primary">
                    <i class="ti ti-exchange"></i>
                </a>
            @else
                <a href="#" data-size="lg" data-url="{{ URL::to('leads/'.$lead->id.'/show_convert') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Convert ['.$lead->subject.'] To Deal')}}" class="btn btn-sm btn-primary">
                    <i class="ti ti-exchange"></i>
                </a>
            @endif
        @endcan

        <a href="#" data-url="{{ URL::to('leads/'.$lead->id.'/labels') }}" data-ajax-popup="true" data-size="lg" data-bs-toggle="tooltip" title="{{__('Label')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-bookmark"></i>
        </a>
        <a href="#" data-size="lg" data-url="{{ route(ViewsConstants::LD.'.edit',$lead->id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Edit')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-pencil"></i>
        </a>
    </div>
@endsection
@section(YieldingConstants::ADM_CTT)
    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                <div class="col-xl-3">
                    @php
                        $userType = $user[UsersConstants::COL_TP] ?? null;
                        $navItems = [
                            ['id' => 'general',         'label' => __('General')],
                            ['id' => 'users_products',  'label' => __('Users').' | '.__('Products')],
                            ['id' => 'sources_emails',  'label' => __('Sources').' | '.__('Emails')],
                            ['id' => 'discussion_note', 'label' => __('Discussion').' | '.__('Notes')],
                            ['id' => 'files',           'label' => __('Files')],
                            ['id' => 'calls',           'label' => __('Calls')],
                            ['id' => 'activity',        'label' => __('Activity')],
                        ];
                    @endphp
                    @if($userType != PermissionsConstants::CL)
                        <div class="{{ ViewClassNamesConstants::CD_STK }}" style="top:30px">
                            <div class="{{ ViewClassNamesConstants::LG_FLSH }}" id="lead-sidenav">
                                @foreach($navItems as $item)
                                    <a href="#{{ $item['id'] }}"
                                    class="{{ ViewClassNamesConstants::LGI_ACT_NBD }}">
                                        {{ $item['label'] }}
                                        <div class="float-end">
                                            <i class="{{ ViewClassNamesConstants::TI_CHV_RT }}"></i>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
                <div class="col-xl-9">
                    <?php
                    $products = $lead->products();
                    $sources = $lead->sources();
                    $calls = $lead->calls;
                    $emails = $lead->emails;
                    ?>
                    <div id="general" class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 col-sm-4">
                                    <div class="d-flex align-items-start">
                                        <div class="theme-avatar bg-primary">
                                            <i class="ti ti-mail"></i>
                                        </div>
                                        <div class="ms-2">
                                            <p class="text-muted text-sm mb-0">{{__('Email')}}</p>
                                            <h5 class="mb-0 text-primary">{{!empty($lead->email)?$lead->email:''}}</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-4">
                                    <div class="d-flex align-items-start">
                                        <div class="theme-avatar bg-warning">
                                            <i class="ti ti-phone"></i>
                                        </div>
                                        <div class="ms-2">
                                            <p class="text-muted text-sm mb-0">{{__('Phone')}}</p>
                                            <h5 class="mb-0 text-warning">{{!empty($lead->phone)?$lead->phone:''}}</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-4">
                                    <div class="d-flex align-items-start">
                                        <div class="theme-avatar bg-info">
                                            <i class="ti ti-test-pipe"></i>
                                        </div>
                                        <div class="ms-2">
                                            <p class="text-muted text-sm mb-0">{{__('Pipeline')}}</p>
                                            <h5 class="mb-0 text-info">{{$lead->pipeline->name}}</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-4 mt-4">
                                    <div class="d-flex align-items-start">
                                        <div class="theme-avatar bg-primary">
                                            <i class="ti ti-server"></i>
                                        </div>
                                        <div class="ms-2">
                                            <p class="text-muted text-sm mb-0">{{__('Stage')}}</p>
                                            <h5 class="mb-0 text-primary">{{$lead->stage->name}}</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-4 mt-4">
                                    <div class="d-flex align-items-start">
                                        <div class="theme-avatar bg-warning">
                                            <i class="ti ti-calendar"></i>
                                        </div>
                                        <div class="ms-2">
                                            <p class="text-muted text-sm mb-0">{{__('Created')}}</p>
                                            <h5 class="mb-0 text-warning">{{$user?->dateFormat($lead->created_at)}}</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-4 mt-4">
                                    <div class="d-flex align-items-start">
                                        <div class="theme-avatar bg-info">
                                            <i class="ti ti-chart-bar"></i>
                                        </div>
                                        <div class="ms-2">
                                            <h3 class="mb-0 text-info">{{$precentage}}%</h3>
                                            <div class="progress mb-0">
                                                <div class="progress-bar bg-info" style="width: {{$precentage}}%;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row align-items-center justify-content-between">
                                        <div class="col-auto mb-3 mb-sm-0">
                                            <small class="text-muted">{{__('Product')}}</small>
                                            <h3 class="m-0">{{count($products)}}</h3>
                                        </div>
                                        <div class="col-auto">
                                            <div class="theme-avatar bg-info">
                                                <i class="ti ti-shopping-cart"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row align-items-center justify-content-between">
                                        <div class="col-auto mb-3 mb-sm-0">
                                            <small class="text-muted">{{__('Source')}}</small>
                                            <h3 class="m-0">{{count($sources)}}</h3>
                                        </div>
                                        <div class="col-auto">
                                            <div class="theme-avatar bg-primary">
                                                <i class="ti ti-social"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row align-items-center justify-content-between">
                                        <div class="col-auto mb-3 mb-sm-0">
                                            <small class="text-muted">{{__('Files')}}</small>
                                            <h3 class="m-0">{{count($lead->files)}}</h3>
                                        </div>
                                        <div class="col-auto">
                                            <div class="theme-avatar bg-warning">
                                                <i class="ti ti-file"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div id="users_products">
                        <div class="row">
                            <div class="col-6">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <h5>{{__('Users')}}</h5>
                                            <div class="float-end">
                                                <a  data-size="md" data-url="{{ route(ViewsConstants::LD.'.users.edit',$lead->id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Add User')}}" class="btn btn-sm btn-primary ">
                                                    <i class="ti ti-plus text-white"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead>
                                                <tr>
                                                    <th>{{__('Name')}}</th>
                                                    <th>{{__('Action')}}</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($lead->users as $user)
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div>
                                                                    <img @if($user->avatar) src="{{asset('/storage/uploads/avatar/'.$user->avatar)}}" @else src="{{asset('/storage/uploads/avatar/avatar.png')}}" @endif class="wid-30 rounded-circle me-3" alt="avatar image">
                                                                </div>
                                                                <p class="mb-0">{{$user->name}}</p>
                                                            </div>
                                                        </td>
                                                        @can('edit lead')
                                                            <td>
                                                                <div class="action-btn bg-danger ms-2">
                                                                    {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'route' => [ViewsConstants::LD.'.users.destroy', $lead->id,$user->id],'id'=>'delete-form-'.$lead->id]) !!}
                                                                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white"></i></a>

                                                                    {!! Collective\Html\FormFacade::close() !!}
                                                                </div>
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
                            <div class="col-6">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <h5>{{__('Products')}}</h5>
                                            <div class="float-end">
                                                <a data-size="md" data-url="{{ route(ViewsConstants::LD.'.products.edit',$lead->id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Add Product')}}" class="btn btn-sm btn-primary">
                                                    <i class="ti ti-plus text-white"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead>
                                                <tr>
                                                    <th>{{__('Name')}}</th>
                                                    <th>{{__('Price')}}</th>
                                                    <th>{{__('Action')}}</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($lead->products() as $product)
                                                    <tr>
                                                        <td>
                                                            {{$product->name}}
                                                        </td>
                                                        <td>
                                                            {{$user?->priceFormat($product->sale_price)}}
                                                        </td>
                                                        @can('edit lead')
                                                            <td>
                                                                <div class="action-btn bg-danger ms-2">
                                                                    {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'route' => [ViewsConstants::LD.'.products.destroy', $lead->id,$product->id]]) !!}
                                                                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white"></i></a>

                                                                    {!! Collective\Html\FormFacade::close() !!}
                                                                </div>
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
                        <div class="row">
                            <div class="col-6">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <h5>{{__('Sources')}}</h5>
                                            <div class="float-end">
                                            <a data-size="md" data-url="{{ route(ViewsConstants::LD.'.sources.edit',$lead->id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Add Source')}}" class="btn btn-sm btn-primary">
                                                <i class="ti ti-plus text-white"></i>
                                            </a>
                                        </div>
                                        </div>

                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover mb-0">
                                                <thead>
                                                <tr>
                                                    <th>{{__('Name')}}</th>
                                                    <th>{{__('Action')}}</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($sources as $source)
                                                    <tr>
                                                        <td>{{$source->name}} </td>
                                                        @can('edit lead')
                                                            <td>
                                                                <div class="action-btn bg-danger ms-2">
                                                                    {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'route' => [ViewsConstants::LD.'.sources.destroy', $lead->id,$source->id],'id'=>'delete-form-'.$lead->id]) !!}
                                                                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white"></i></a>

                                                                    {!! Collective\Html\FormFacade::close() !!}
                                                                </div>
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
                            <div class="col-6">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <h5>{{__('Emails')}}</h5>
                                            @can('create lead email')
                                                <div class="float-end">
                                                    <a data-size="md" data-url="{{ route(ViewsConstants::LD.'.emails.create',$lead->id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create Email')}}" class="btn btn-sm btn-primary">
                                                        <i class="ti ti-plus text-white"></i>
                                                    </a>
                                                </div>
                                            @endcan
                                        </div>

                                    </div>
                                    <div class="card-body">
                                        <div class="{{ ViewClassNamesConstants::LG_FLSH_MT2 }}">
                                            @if(!$emails->isEmpty())
                                                @foreach($emails as $email)
                                                    <li class="list-group-item px-0">
                                                        <div class="d-block d-sm-flex align-items-start">
                                                            <img src="{{asset('/storage/uploads/avatar/avatar.png')}}"
                                                                 class="img-fluid wid-40 me-3 mb-2 mb-sm-0" alt="image">
                                                            <div class="w-100">
                                                                <div class="d-flex align-items-center justify-content-between">
                                                                    <div class="mb-3 mb-sm-0">
                                                                        <h6 class="mb-0">{{$email->subject}}</h6>
                                                                        <span class="text-muted text-sm">{{$email->to}}</span>
                                                                    </div>
                                                                    <div class="form-check form-switch form-switch-right mb-2">
                                                                        {{$email->created_at->diffForHumans()}}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            @else
                                                <li class="text-center">
                                                    {{__(' No Emails Available.!')}}
                                                </li>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="discussion_note">
                        <div class="row">
                            <div class="col-6">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <h5>{{__('Discussion')}}</h5>
                                            <div class="float-end">
                                                <a data-size="lg" data-url="{{ route(ViewsConstants::LD.'.discussions.create',$lead->id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Add Message')}}" class="btn btn-sm btn-primary">
                                                    <i class="ti ti-plus text-white"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <ul class="{{ ViewClassNamesConstants::LG_FLSH_MT2 }}">
                                            @if(!$lead->discussions->isEmpty())
                                                @foreach($lead->discussions as $discussion)
                                                    <li class="list-group-item px-0">
                                                        <div class="d-block d-sm-flex align-items-start">
                                                            <img src="@if($discussion->user->avatar) {{asset('/storage/uploads/avatar/'.$discussion->user->avatar)}} @else {{asset('/storage/uploads/avatar/avatar.png')}} @endif"
                                                                 class="img-fluid wid-40 me-3 mb-2 mb-sm-0" alt="image">
                                                            <div class="w-100">
                                                                <div class="d-flex align-items-center justify-content-between">
                                                                    <div class="mb-3 mb-sm-0">
                                                                        <h6 class="mb-0"> {{$discussion->comment}}</h6>
                                                                        <span class="text-muted text-sm">{{$discussion->user->name}}</span>
                                                                    </div>
                                                                    <div class="form-check form-switch form-switch-right mb-2">
                                                                        {{$discussion->created_at->diffForHumans()}}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            @else
                                                <li class="text-center">
                                                    {{__(' No Data Available.!')}}
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <h5>{{__('Notes')}}</h5>
                                            @php
                                                $plan= \App\Models\Plan::getPlan($user->plan);
                                            @endphp
                                            @if($plan->chatgpt == 1)
                                                <div class="float-end">
                                                    <a href="#" data-size="md" class="btn btn-primary btn-icon btn-sm" data-ajax-popup-over="true" id="grammarCheck" data-url="{{ route('grammar',['grammar']) }}"
                                                       data-bs-placement="top" data-title="{{ __('Grammar check with AI') }}">
                                                        <i class="ti ti-rotate"></i> <span>{{__('Grammar check with AI')}}</span>
                                                    </a>
                                                    <a href="#" data-size="md" class="btn  btn-primary btn-icon btn-sm" data-ajax-popup-over="true" data-url="{{ route('generate',['lead']) }}"
                                                       data-bs-placement="top" data-title="{{ __('Generate content with AI') }}">
                                                        <i class="fas fa-robot"></i> <span>{{__('Generate with AI')}}</span>
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="card-body">
                                        <textarea class="summernote-simple " name="note">{!! $lead->notes !!}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="files" class="card">
                        <div class="card-header">
                            <h5>{{__('Files')}}</h5>
                        </div>
                        <div class="card-body">
                            <div class="col-md-12 dropzone top-5-scroll browse-file" id="dropzonewidget"></div>
                        </div>
                    </div>
                    <div id="calls" class="card">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between">
                                <h5>{{__('Calls')}}</h5>

                                <div class="float-end">
                                <a data-size="lg" data-url="{{ route(ViewsConstants::LD.'.calls.create',$lead->id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Add Call')}}" class="btn btn-sm btn-primary">
                                    <i class="ti ti-plus text-white"></i>
                                </a>
                            </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                    <tr>
                                        <th width="">{{__('Subject')}}</th>
                                        <th>{{__('Call Type')}}</th>
                                        <th>{{__('Duration')}}</th>
                                        <th>{{__('User')}}</th>
                                        <th>{{__('Action')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($calls as $call)
                                        <tr>
                                            <td>{{ $call->subject }}</td>
                                            <td>{{ ucfirst($call->call_type) }}</td>
                                            <td>{{ $call->duration }}</td>
                                            <td>{{ isset($call->getLeadCallUser) ? $call->getLeadCallUser->name : '-' }}</td>
                                            <td>
                                                @can('edit lead call')
                                                    <div class="action-btn bg-info ms-2">
                                                        <a href="#" class="mx-3 btn btn-sm d-inline-flex align-items-center" data-url="{{ URL::to('leads/'.$lead->id.'/call/'.$call->id.'/edit') }}" data-ajax-popup="true" data-size="xl" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-title="{{__('Role Edit')}}">
                                                            <i class="{{ ViewClassNamesConstants::TI_PC_WT }}"></i>
                                                        </a>
                                                    </div>
                                                @endcan
                                                @can('delete lead call')
                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'route' => [ViewsConstants::LD.'.calls.destroy', $lead->id,$call->id],'id'=>'delete-form-'.$lead->id]) !!}
                                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}"><i class="ti ti-trash text-white"></i></a>
                                                        {!! Collective\Html\FormFacade::close() !!}
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
                    <div id="activity" class="card">
                        <div class="card-header">
                            <h5>{{__('Activity')}}</h5>
                        </div>
                        <div class="card-body ">
                            <div class="row leads-scroll" >
                                <ul class="{{ ViewClassNamesConstants::LG_FLSH_W }}">
                                    @if(!$lead->activities->isEmpty())
                                        @foreach($lead->activities as $activity)
                                            <li class="list-group-item card mb-3">
                                                <div class="row align-items-center justify-content-between">
                                                    <div class="col-auto mb-3 mb-sm-0">
                                                        <div class="d-flex align-items-center">
                                                            <div class="theme-avatar bg-primary">
                                                                <i class="ti {{ $activity->logIcon() }}"></i>
                                                            </div>
                                                            <div class="ms-3">
                                                                <span class="text-dark text-sm">{{ __($activity->log_type) }}</span>
                                                                <h6 class="m-0">{!! $activity->getLeadRemark() !!}</h6>
                                                                <small class="text-muted">{{$activity->created_at->diffForHumans()}}</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-auto">
                                                    </div>
                                                </div>
                                            </li>
                                        @endforeach
                                    @else
                                        No activity found yet.
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
