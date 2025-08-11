@php
    use App\Config\Constants\ViewsConstants;
@endphp
{{ Collective\Html\FormFacade::open(array('url' => ViewsConstants::PRD_SV_CAT)) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            {{ Collective\Html\FormFacade::label('name', __('Category Name'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::text('name', '', array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="form-group col-md-12 d-block">
            {{ Collective\Html\FormFacade::label('type', __('Category Type'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::select('type',$types,null, array('class' => 'form-control select cattype ','required'=>'required')) }}
        </div>
        <div class="form-group col-md-12 account d-none">
            {{Collective\Html\FormFacade::label('chart_account_id',__('Account'),['class'=>'form-label'])}}
            <select class="form-control select" name="chart_account" id="chart_account">
            </select>
        </div>

        <div class="form-group col-md-12">
            {{ Collective\Html\FormFacade::label('color', __('Category Color'),['class'=>'form-label']) }}
            {{ Collective\Html\FormFacade::text('color', '', array('class' => 'form-control jscolor','required'=>'required')) }}
            <small>{{__('For chart representation')}}</small>
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn btn-primary">
</div>
{{ Collective\Html\FormFacade::close() }}


<script>

    //hide & show chartofaccount

    $(document).on('click', '.cattype', function ()
    {
        var type = $(this).val();
        if (type != 'product & service') {
            $('.account').removeClass('d-none')
            $('.account').addClass('d-block');
        } else {
            $('.account').addClass('d-none')
            $('.account').removeClass('d-block');
        }
    });


    $(document).on('change', '#type', function () {
        var type = $(this).val();

        $.ajax({
            url: '{{route(ViewsConstants::PRD_SV_CAT.".get_account")}}',
            type: 'POST',
            data: {
                "type": type,
                "_token": "{{ csrf_token() }}",
            },

            success: function (data) {
                $('#chart_account').empty();
                $.each(data, function (key, value) {
                    $('#chart_account').append('<option value="' + key + '">' + value + '</option>');
                });
            }

        });
    });
</script>

