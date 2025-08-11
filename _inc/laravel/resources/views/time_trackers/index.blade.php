@php
    use App\Config\Constants\{
        ExtendingLayoutsConstants,
        StacksConstants,
        ViewClassNamesConstants
        YieldingConstants,
    };
    use Illuminate\Support\Facades\Route;
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
    {{__('Manage Tracker')}}
@endsection
@section(YieldingConstants::ADM_BDC)
    <li class="breadcrumb-item">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
        {{ Route::has('dashboard') ? '' : 'aria-disabled="true"' }}>
            {{ __('Dashboard') }}
        </a>
    </li>
    <li class="breadcrumb-item">{{__('Tracker')}}</li>
@endsection

@push(StacksConstants::ADM_CSS)
    <link rel="stylesheet" href="{{url('css/swiper.min.css')}}">
    <link rel="stylesheet" href="{{url('css/swiper.min.css')}}">
    <style>
        .product-thumbs .swiper-slide img {
        border:2px solid transparent;
        object-fit: cover;
        cursor: pointer;
        }
        .product-thumbs .swiper-slide-active img {
        border-color: #bc4f38;
        }

        .product-slider .swiper-button-next:after,
        .product-slider .swiper-button-prev:after {
            font-size: 20px;
            color: #000;
            font-weight: bold;
        }
        .modal-dialog.modal-md {
            background-color: #fff !important;
        }

        .no-image{
            min-height: 300px;
            align-items: center;
            display: flex;
            justify-content: center;
        }
    </style>
@endpush


@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-border-style mt-2">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>

                                <th> {{__('Title')}}</th>
                                <th> {{__('Task')}}</th>
                                 <th> {{__('Project')}}</th>
                                <th> {{__('Start Time')}}</th>
                                <th> {{__('End Time')}}</th>
                                <th>{{__('Total Time')}}</th>
                                <th>{{__('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody>

                            @foreach ($treckers as $trecker)

                                @php
                                    $total_name = Utility::secondToTime($trecker->total_time);
                                @endphp
                                <tr>
                                    <td>{{$trecker->name}}</td>
                                    <td>{{$trecker->project_task}}</td>
                                    <td>{{$trecker->project_name}}</td>
                                    <td>{{date("H:i:s",strtotime($trecker->start_time))}}</td>
                                    <td>{{date("H:i:s",strtotime($trecker->end_time))}}</td>
                                    <td>{{$total_name}}</td>
                                    <td>
                                        <img alt="Image placeholder" src="{{ asset('assets/images/gallery.png')}}" class="avatar view-images rounded-circle avatar-sm"
                                             data-bs-toggle="tooltip" title="{{__('View Screenshot images')}}" data-original-title="{{__('View Screenshot images')}}" style="height: 25px;width:24px;margin-right:10px;cursor: pointer;" data-id="{{$trecker->id}}" id="track-images-{{$trecker->id}}">
                                        <div class="action-btn bg-danger ms-2">
                                            {!! Collective\Html\FormFacade::open(['method' => 'DELETE', 'route' => ['tracker.destroy', $trecker->id],'id'=>'delete-form-'.$trecker->id]) !!}

                                            <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').' | '.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$trecker->id}}').submit();">
                                                <i class="ti ti-trash text-white"></i>
                                            </a>
                                            {!! Collective\Html\FormFacade::close() !!}
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="{{ ViewClassNamesConstants::MD_FD }}" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg ss_modale" role="document">
          <div class="modal-content image_sider_div">

          </div>
        </div>
    </div>

@endsection

@push(StacksConstants::ADM_SCR_PG)

<script src="{{url('js/swiper.min.js')}}"></script>


<script type="text/javascript">

    function init_slider()
    {
            if($(".product-left").length){
                    var productSlider = new Swiper('.product-slider', {
                        spaceBetween: 0,
                        centeredSlides: false,
                        loop:false,
                        direction: 'horizontal',
                        loopedSlides: 5,
                        navigation: {
                            nextEl: ".swiper-button-next",
                            prevEl: ".swiper-button-prev",
                        },
                        resizeObserver:true,
                    });
                var productThumbs = new Swiper('.product-thumbs', {
                    spaceBetween: 0,
                    centeredSlides: true,
                    loop: false,
                    slideToClickedSlide: true,
                    direction: 'horizontal',
                    slidesPerView: 7,
                    loopedSlides: 5,
                });
                productSlider.controller.control = productThumbs;
                productThumbs.controller.control = productSlider;
            }
        }

    $(document).on('click', '.view-images', function () {

            var p_url = "{{route('tracker.image.view')}}";
            var data = {
                'id': $(this).attr('data-id')
            };
            postAjax(p_url, data, function (res) {
                $('.image_sider_div').html(res);
                $('#exampleModalCenter').modal('show');
                setTimeout(function(){
                    var total = $('.product-left').find('.product-slider').length
                    if(total > 0){
                        init_slider();
                    }

                },200);

            });
            });

    // ============================ Remove Track Image ===============================//
    $(document).on("click", '.track-image-remove', function () {
            var rid = $(this).attr('data-pid');
            $('.confirm_yes').addClass('image_remove');
            $('.confirm_yes').attr('image_id', rid);
            $('#cModal').modal('show');
            var total = $('.product-left').find('.swiper-slide').length
            });

    function removeImage(id){
        var p_url = "{{route('tracker.image.remove')}}";
        var data = {id: id};
        deleteAjax(p_url, data, function (res) {
            if(res.flag){
                $('#slide-thum-'+id).remove();
                $('#slide-'+id).remove();
                setTimeout(function(){
                    var total = $('.product-left').find('.swiper-slide').length
                    if(total > 0){
                        init_slider();
                    }else{
                        $('.product-left').html('<div class="no-image"><h5 class="text-muted">Images Not Available .</h5></div>');
                    }
                },200);
            }
            $('#cModal').modal('hide');
            show_toastr('error',res.msg,'error');
        });
    }
</script>
@endpush
