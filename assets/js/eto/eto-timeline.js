

ETO.Timeline = function() {
    var etoFn = {};

    etoFn.config = {
        initializingStatus: false,
        no_data_message: '',
        loadLength: 10,
        loadStart: 0,
        loadOrder: {
            column: 'created_at',
            dir: 'desc',
        },
        loadSearch: {
            value: '',
            regex: false,
        },
        selectedId: 0,
        date: '',
        container: {},
        uriParams: ETO.getUrlParams(window.location.search),
    };

    etoFn.init = function (config) {
        if (typeof config != 'undefined') {
            etoFn.config = $.extend(etoFn.config, config);
        }

        if(typeof etoFn.config.uriParams.search != "undefined") {
            etoFn.config.loadSearch.value =etoFn.config.uriParams.search;
            $('#search').val(etoFn.config.loadSearch.value);
        }

        if(typeof etoFn.config.uriParams.order != "undefined") {
            var order = etoFn.config.uriParams.order.split('__');

            if(order.length === 2) {
                etoFn.config.loadOrder.column = order[0];
                etoFn.config.loadOrder.dir = order[1];
            }
            $('#order').val(etoFn.config.uriParams.order);
        }

        if(typeof etoFn.config.uriParams.perload != "undefined") {
            etoFn.config.loadLength = etoFn.config.uriParams.perload;
            $('#length').val(etoFn.config.uriParams.perload);
        }

        if (etoFn.config.typeView.localeCompare('table') === 0) {
            etoFn.config.container = etoFn.config.containerTable.find('tbody');
        } else if (etoFn.config.typeView.localeCompare('timeline') === 0) {
            etoFn.config.container = etoFn.config.containerTimeline.find('.timeline');
        } else {
            return false;
        }

        etoFn.config.loadSearch.value = $('#search').val();

        etoFn.load(etoFn.config.loadLength, etoFn.config.loadStart, etoFn.config.loadOrder, etoFn.config.loadSearch);

        if (etoFn.config.initializingStatus === false) {
            $('.eto-load-more').on('click', function (e) {
                etoFn.config.loadStart = parseInt(etoFn.config.loadStart) + parseInt(etoFn.config.loadLength);
                etoFn.load(etoFn.config.loadLength, etoFn.config.loadStart, etoFn.config.loadOrder, etoFn.config.loadSearch);
            });

            $('#search').on('change', function (e) {
                etoFn.config.loadSearch.value = $(this).val();
                etoFn.reloadTimeline();
            });

            $('#order').on('change', function (e) {
                var order = $(this).val().split('__');

                if(order.length === 2) {
                    etoFn.config.loadOrder.column = order[0];
                    etoFn.config.loadOrder.dir = order[1];
                }
                etoFn.reloadTimeline();
            });

            $('#length').on('change', function (e) {
                etoFn.config.loadLength = $(this).val();
            });

            $('.eto-type-view').on('click', function (e) {
                etoFn.config.typeView = $(this).data('etoTypeView');
                $('.eto-type-view.btn-default').removeClass('btn-default').addClass('btn-success');
                $('.eto-type-view.btn-success:not([data-eto-type-view="'+etoFn.config.typeView+'"])').removeClass('btn-success').addClass('btn-default');

                $('.eto-table-view tbody, .eto-timeline-view .timeline').html('');
                etoFn.config.loadStart = 0;
                etoFn.init();
            });

            ETO.updateFormPlaceholderInit($('.eto-field-search'));
        }
        etoFn.config.initializingStatus = true;
    };

    etoFn.load = function (length, start, order, search) {
        $.LoadingOverlay('show');
        ETO.ajax(etoFn.config.uriSearch, {
            data: {
                length: length,
                start: start,
                order: order,
                search: search,
                subject: etoFn.config.uriParams.subject,
                subject_id: etoFn.config.uriParams.subject_id,
                causer: etoFn.config.uriParams.causer,
                causer_id: etoFn.config.uriParams.causer_id,
            },
            async: false,
            success: function(response) {
                if (response.news.length > 0) {
                    etoFn.addToTimeline(response.news);

                    if (etoFn.config.container.find('.eto-item').length === 0
                        || etoFn.config.container.find('.eto-item').length >= response.recordsFiltered
                    ) {
                        // $('.eto-load-more').hide();
                        $('.eto-load-more').addClass('hidden');
                    } else {
                        // $('.eto-load-more').show();
                        $('.eto-load-more').removeClass('hidden');
                    }
                } else if (etoFn.config.container.find('.eto-item').length === 0) {
                    if(etoFn.config.typeView.localeCompare('timeline') === 0 ) {
                        etoFn.config.container.append('<li class="eto-timeline-item eto-item"> \
                            <div class="timeline-item">\
                                <h3 class="timeline-header">'+etoFn.config.no_data_message+'</h3>\
                                </div>\
                                </li>');
                    } else if (etoFn.config.typeView.localeCompare('table') === 0) {
                        etoFn.config.container.append('<tr class="eto-item">\
                        <td style="width: max-content;">'+etoFn.config.no_data_message+'</td></tr>');
                    }
                }
                $.LoadingOverlay('hide');
            },
            error: function() {
                $.LoadingOverlay('hide');
            }
        });
    };

    etoFn.addToTimeline = function (data) {
        var container = etoFn.config.container;

        $.each(data, function(key, item) {
            if (typeof item.uuid != "undefined") {
                item.slug = item.uuid;
            }
            var url = etoFn.config.uriAnhor === false ? item.slug :ETO.config.appPath + etoFn.config.uriAnhor + item.slug,
                anchor = '<a class="pull-left" href="' + url + '">' + item.name + '</a>',
                dateFormated = moment(item.created_at, "YYYY-MM-DD h:mm:ss").fromNow();

            if (item.read_at === null) {
                anchor = '<b>'+anchor+'</b>';
            }

            if(etoFn.config.typeView.localeCompare('timeline') === 0 ) {
                if (item.date !== null && item.date != etoFn.config.date) {
                    container.append('<li class="time-label"><span class="bg-red">' + ETO.convertDate(ETO.config.format_date, item.created_at) + '</span></li>');
                    etoFn.config.date = item.date;
                }

                container.append('<li class="eto-timeline-item eto-item"> \
                <i class="fa fa-envelope bg-blue"></i> <div class="timeline-item">\
                <span class="time" title="' + ETO.convertDate(ETO.config.format_date, item.created_at) + ' ' + ETO.convertTime(ETO.config.format_time, item.created_at) + '"><i class="fa fa-clock-o"></i>\
                    ' + dateFormated + '\
                    </span>\
                    <h3 class="timeline-header">'+anchor+'</h3>\
                    <div class="timeline-body">\
                    ' + item.excerpt + '\
                    </div>\
                    </div>\
                    </li>');
            } else if (etoFn.config.typeView.localeCompare('table') === 0) {
                // if (typeof etoFn.config.uriParams.subject != "undefined") {
                    container.append('<tr class="eto-item">\
                        <td style="width: max-content;">' + anchor + '\
                        <br>\
                        <div class="pull-left">\
                        ' + item.excerpt + '\
                        </div></td>\
                        <td><span class="pull-right" title="' + ETO.convertDate(ETO.config.format_date, item.created_at) + ' ' + ETO.convertTime(ETO.config.format_time, item.created_at) + '">' +
                        dateFormated +
                        '</span></td>' +
                        '</tr>');
                // } else {
                //     container.append('<tr class="eto-item">\
                //         <td style="width:20%;">' + anchor + '</td>\
                //         <td><span class="pull-left">' + item.excerpt + '</span></td>\
                //         <td><span class="pull-right" title="' + ETO.convertDate(ETO.config.format_date, item.created_at) + ' ' + ETO.convertTime(ETO.config.format_time, item.created_at) + '">' +
                //         dateFormated +
                //         '</span></td>' +
                //         '</tr>');
                // }
            }
        })
    };

    etoFn.reloadTimeline = function () {
        $('.eto-item, .time-label').remove();
        etoFn.load(etoFn.config.loadLength, etoFn.config.loadStart, etoFn.config.loadOrder, etoFn.config.loadSearch);
    };

    return etoFn;
}();
