<script src="{{ asset_url('plugins','moment/moment-with-locales.min.js') }}"></script>
@include('layouts.eto-js')

<script>
function numbersAndLettersOnly() {
    var ek = event.keyCode;
    if (48 <= ek && ek <= 57) {
        return true;
    }
    if (65 <= ek && ek <= 90) {
        return true;
    }
    if (97 <= ek && ek <= 122) {
        return true;
    }
    return false;
}

// Placeholder
function updateFormPlaceholder(that) {
    var $container = $(that).closest('.form-group:not(.placeholder-disabled)');

    if( $(that).val() != '' || $container.hasClass('placeholder-visible') ) {
        $container.find('label').show();
    }
    else {
        $container.find('label').hide();
    }
}

$(function() {
    if (ETO.model === false) {
        ETO.init({ config: [], lang: ['user'] }, 'roles');
    }

    var is_touch_device = 'ontouchstart' in document.documentElement;
    if (!is_touch_device) {
        $('[data-toggle="tooltip"]').tooltip();
    }

    $('body').on('click', '.eto-anchor-action', function() {
        var form = $(this).closest('.eto-action-form').find('form');
        var message = $(this).attr('data-message');
        var title = $(this).attr('data-title');
        var type = $(this).attr('data-type');

        ETO.swalWithBootstrapButtons({
            title: title,
            text: message,
            type: type,
            showCancelButton: true,
        })
        .then(function(result){
            if (result.value) {
                form.submit()
            }
        });
    });

    $('.form-master').find('input:not([type="submit"]), textarea, select').each(function() {
        updateFormPlaceholder(this);
    })
    .bind('change keyup', function(e) {
        updateFormPlaceholder(this);
    });
});
</script>
