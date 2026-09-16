
<div class="row">
    <div class="col-sm-6">
        {!! Form::button('<i class="fa fa-check"></i> '. ($mode == 'edit' ? 'Update' : 'Add'), ['type' => 'submit', 'class' => 'btn btn-block btn-primary']) !!}
    </div>
    <div class="col-sm-6">
        <a href="{{ route('driver.jobs.index') }}" class="btn btn-block btn-default">{{ trans('driver/jobs.button.cancel') }}</a>
    </div>
</div>


@section('subfooter')
    <script src="{{ asset_url('plugins','autosize/autosize.min.js') }}"></script>

    <script type="text/javascript">
    $(document).ready(function() {
        // Textarea auto height
        autosize($('textarea'));

        $('.form-master').find('input:not([type="submit"]), textarea, select').each(function() {
            var $container = $(this).closest('.form-group:not(.placeholder-disabled)');

            if( $(this).val() != '' || $container.hasClass('placeholder-visible') ) {
                $container.find('label').show();
            }
            else {
                $container.find('label').hide();
            }
        })
        .bind('change keyup', function(e) {
            var $container = $(this).closest('.form-group:not(.placeholder-disabled)');

            if( $(this).val() != '' || $container.hasClass('placeholder-visible') ) {
                $container.find('label').show();
            }
            else {
                $container.find('label').hide();
            }
        });
    });
    </script>
@stop
