/*
Copyright (c) 2020 by EasyTaxiOffice - All Rights Reserved
Website: https://easytaxioffice.com
Email: support@easytaxioffice.com
*/

function trans(key, replace = {}) {
    var translation = key.split('.').reduce(function (t, i) {
        if(null === t) {
            return null;
        }
        return t[i] || null;
    }, window.reminder.lang);

    if (null !== translation) {
        for (var placeholder in replace) {
            translation = translation.replace(`:${placeholder}`, replace[placeholder]);
        }
    }

    return null !== translation ? translation: key;
}

function checkReminds(modal, row, no) {
    window.reminder.data.splice(no, 1);
    row.find('button').attr('disabled', true);
    if(window.reminder.data.length === 0) {
        modal.modal('hide');
    }
}

$(document).ready(function(){
    var modalRemind =  $('.eto-modal-reminder'),
        bodyModalRemind = modalRemind.find('.modal-body');

    if (typeof window.reminder.data != 'undefined' && window.reminder.data.length > 0) {
        $.each(window.reminder.data, function(no, remind){
            var transData = {
                days: function() {
                    var date2 = new Date();
                    var date1 = new Date(remind.expire_at);
                    var timeDiff = Math.abs(date2.getTime() - date1.getTime());
                    return Math.ceil(timeDiff / (1000 * 3600 * 24));
                }()
            },
            content = remind.type == 'requirements' ? '<div>' + remind.description + '</div>' : '<h4>' + trans('type.'+remind.type, transData) +'</h4>';

            bodyModalRemind.append('<div class="row clearfix eto-remind-'+remind.type+'" data-eto-id-remind="'+remind.id+'" data-eto-no-remind="'+no+'">' +
                '<div class="col-lg-12">'+ content +
                '</div></div>');

            if (remind.type == 'requirements') {
                $('eto-remind-'+remind.type).append('<button type="button" class="btn btn-xs btn-info pull-right eto-button-not-remind" style="text-decoration:none; margin-right:5px;">'+
                    '<span>Mark as read</span>' +
                    '</button>');
            } else {
                $('eto-remind-'+remind.type).append('<button type="button" class="btn btn-xs btn-danger pull-right eto-button-not-remind" style="text-decoration:none; margin-right:5px;">' +
                    '<span>Do not remind</span>' +
                    '</button>' +
                    '<button type="button" class="btn btn-xs btn-success pull-left eto-button-remind-tomorow" style="text-decoration:none; margin-right:5px;">' +
                    '<span>Remind me tomorrow</span>' +
                    '</button>' +
                    '<button type="button" class="btn btn-xs btn-success pull-left eto-button-remind-week" style="text-decoration:none; margin-right:5px;">' +
                    '<span>Remind me in seven days</span>' +
                    '</button>');
            }
        });

        modalRemind.modal('show');
    }

    modalRemind.on('hide.bs.modal', function () {
        if(typeof window.reminder.data != 'undefined' && window.reminder.data.length > 0) {
            return false;
        }
    });

    $('body').on('click', '.eto-button-not-remind', function(e) {
        var row = $(this).closest('.row'),
            id = row.data('etoIdRemind'),
            no = row.data('etoNoRemind');
        $.ajax({
            headers : {
                'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
            },
            url: EasyTaxiOffice.appPath +'/remind/not-remind',
            type: 'POST',
            dataType: 'json',
            cache: false,
            async: false,
            data: {id: id},
            success: function(response) {
                if(response.read_at !== null) {
                    checkReminds(modalRemind, row, no);
                }
            },
            error: function(response) {
                alert('An error occurred while processing your request');
            }
        });
    })
    .on('click', '.eto-button-remind-tomorow', function(e) {
        var row = $(this).closest('.row'),
            id = row.data('etoIdRemind'),
            no = row.data('etoNoRemind');
        $.ajax({
            headers : {
                'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
            },
            url: EasyTaxiOffice.appPath +'/remind/tomorow',
            type: 'POST',
            dataType: 'json',
            cache: false,
            async: false,
            data: {id: id},
            success: function(response) {
                if(response.read_at !== null && response.remind_at !== null) {
                    checkReminds(modalRemind, row, no);
                }
            },
            error: function(response) {
                alert('An error occurred while processing your request');
            }
        });
    })
    .on('click', '.eto-button-remind-week', function(e) {
        var row = $(this).closest('.row'),
            id = row.data('etoIdRemind'),
            no = row.data('etoNoRemind');
        $.ajax({
            headers : {
                'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
            },
            url: EasyTaxiOffice.appPath +'/remind/week',
            type: 'POST',
            dataType: 'json',
            cache: false,
            async: false,
            data: {id: id},
            success: function(response) {
                if(response.read_at !== null && response.remind_at !== null) {
                    checkReminds(modalRemind, row, no);
                }
            },
            error: function(response) {
                alert('An error occurred while processing your request');
            }
        });
    });
});
