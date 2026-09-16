

ETO.Notifications = function() {
    var etoFn = {};

    etoFn.config = {
        init: [],
        lang: ['user', 'booking'],
        bodyModal: '<div class="modal fade" id="notifications_modal" role="dialog" aria-hidden="true">\
            <div class="modal-dialog">\
            <div class="modal-content">\
            <div class="modal-header">\
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>\
            <h4 class="modal-title"></h4>\
            </div>\
            <div class="modal-body"></div>\
            </div>\
            </div>\
            </div>'
    };

    etoFn.init = function (config) {
        ETO.extendConfig(this, config, 'notifications');

        etoFn.config.template = function() {
            var lang = ETO.lang.notifications,
                html = '<tr class="eto-notify-item" id="'+ETO.uuidHTML()+'">' +
                    '<td class="form-group clearfix">' +
                    '<div class="eto-notify-status-box">' +
                    '<select class="eto-notify-status">' +
                    '<option value="0">-- Select --</option>';

            for (var i in ETO.config.notifications){
                html += '<option value="'+i+'" >'+lang.types[i].title.replace('Status - ','')+'</option>';
            }

            return html + '</select>' +
                    '</div>' +
                    '</td>' +
                    '<td class="form-group clearfix">' +
                        '<div class="eto-notify-role-box"></div>' +
                    '</td>' +
                    '<td class="form-group clearfix">' +
                        '<div class="eto-notify-actions-box"></div>' +
                    '</td>' +
                    '</tr>';
        };

        $('body').append(etoFn.config.bodyModal);
        $('#notifications_modal').find('.modal-title').html(ETO.trans('notifications.notifications'));

        $('body').on('click', '.eto-add-notify', function() {
            $('.eto-notify-items tbody').append(etoFn.config.template());

            setTimeout(function() {
                etoFn.setEvent($('.eto-notify-items tbody tr').last().find('.eto-notify-status'), true);
            }, 0);
        })
        .on('click', '.eto-notifications', function() {
            var modal = $('#notifications_modal');
            modal.find('.modal-body').html('<div class="eto-notify-items">' +
                '<table class="table table-responsive" data-eto-id="'+$(this).data('etoId')+'">' +
                '<thead>' +
                '<th>'+ETO.trans('notifications.status')+'</th>' +
                '<th>'+ETO.trans('notifications.role')+'</th>' +
                '<th>'+ETO.trans('notifications.notifications')+'</th>' +
                '</thead>' +
                '<tbody>' +
                etoFn.config.template() +
                '</tbody>' +
                '</table>' +
                '<button class="btn btn-sm btn-default eto-add-notify">'+ETO.trans('notifications.add_new')+'</button>' +
                '<button class="btn btn-sm btn-success eto-send-notify pull-right">'+ETO.trans('notifications.send')+'</button>' +
                '</div>');

            setTimeout(function () {
                etoFn.setEvent($('.eto-notify-items tbody tr').last().find('.eto-notify-status'), '', true);
            }, 0);

            modal.modal('show');
        })
        .on('click', '.eto-send-notify', function () {
            $('body').LoadingOverlay('show');


            var notifications = {},
                data = {},
                bookingId = $('.eto-notify-items table').data('etoId'),
                i = 0;

            $('.eto-notify-item').each(function(){
                var role = $(this).find('.eto-notify-role').val(),
                    status = $(this).find('.eto-notify-status').val();

                if (typeof notifications[status] == 'undefined') {
                    notifications[status] = {};
                }
                if (typeof notifications[status][role] == 'undefined') {
                    notifications[status][role] = [];
                }

                $(this).find('.eto-notify-actions').find('option:selected').each(function(){
                    notifications[status][role].push($(this).val());
                    i++;
                });
            });

            if (i > 0) {
                data.notifications = notifications;
                data.bookingId = bookingId;

                ETO.ajax('booking2/send-notifications', {
                    data: data,
                    async: true,
                    success: function (data) {
                        $('#notifications_modal').modal('hide')
                    },
                    complete: function () {
                        $('body').LoadingOverlay('hide');
                    },
                    error: function () {
                        $('body').LoadingOverlay('hide');
                    }
                });
            } else {
                ETO.swalWithBootstrapButtons({
                    type: 'warning',
                    title: ETO.trans('notifications.select_notifications'),
                    timer: 2000,
                });
            }
        })
        .on('change', '.eto-notify-status', function () {
            var status = $(this).select2('val'),
                tr = $(this).closest('tr');

            tr.find('.eto-notify-role-box, .eto-notify-actions-box').html('');

            if(typeof ETO.config.notifications[status] != 'undefined') {
                var template = '<select class="eto-notify-role">' +
                        '<option value="0">-- Select --</option>';

                for (var i in ETO.config.notifications[status]) {
                    template += '<option value="' + i + '" >' + ETO.trans('notifications.roles.'+i) + '</option>';
                }

                template += '</select>';
                tr.find('.eto-notify-role-box').html(template);
                setTimeout(function () {
                    etoFn.setEvent(tr.find('.eto-notify-role'), ETO.trans('notifications.role'));
                }, 0);
            }
        })
        .on('change', '.eto-notify-role', function () {
            var tr = $(this).closest('tr'),
                status = tr.find('.eto-notify-status').select2('val'),
                role = $(this).select2('val');

            if (typeof ETO.config.notifications[status][role] != 'undefined') {
                var template = '<select class="eto-notify-actions" multiple="multiple">';

                for (var i in ETO.config.notifications[status][role]) {
                    template += '<option value="' + ETO.config.notifications[status][role][i] + '" selected>' + ETO.trans('notifications.options.'+ETO.config.notifications[status][role][i]) + '</option>';
                }

                template += '</select>';

                tr.find('.eto-notify-actions-box').html(template);

                setTimeout(function () {
                    etoFn.setEvent(tr.find('.eto-notify-actions'), ETO.trans('notifications.notifications'));
                }, 0);
            } else {
                tr.find('.eto-notify-actions-box').html('');
            }
        });
    };

    etoFn.setEvent = function(select, placecholder, search) {
        search = search === true ? 0 : -1;

        var config = {
            minimumResultsForSearch: search,
            dropdownAutoWidth: true,
            width: '100%'
        };

        if (typeof placecholder != "undefined") {
            config.placecholder = {
                id: "0",
                text: placecholder,
            };
        }

        select.select2(config);
    };

    return etoFn;
}();
