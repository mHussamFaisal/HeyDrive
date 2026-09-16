
ETO.Report = function() {
    var etoFn = {};

    etoFn.config = {
        config: [],
        lang: [
            'user',
            'reports'
        ],
        reportData: {},
        driverTransactionStatuses: {},
        fleetTransactionStatuses: {},
        paymentTotal: {},
        isTrash: 0
    };

    etoFn.init = function (config)
    {
        ETO.extendConfig(this, config, 'reports');
        ETO.loadExtensionsETO(['chart']); // Add ETO.Chart library

        $('.pageFilters .datepicker').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            timePicker: true,
            timePicker24Hour: ETO.config.time_format == 'H:i',
            autoUpdateInput: false,
            timePickerIncrement: 5,
            locale: {
                format: 'YYYY-MM-DD HH:mm',
                firstDay: ETO.config.date_start_of_week
            },
        })
        .on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD HH:mm')).change();
        });

        // Reset form
        $('.eto-btn-reset').on('click', function(e) {
            $('.pageFilters form input[type="text"]').val('').trigger('change');
            $('.pageFilters form select.select2').each(function( index ) {
                $(this).val(null).trigger('change');
            });
            $('.eto-show-report, .eto-report-invalid-bookings, .eto-charts').html('');
            $('.eto-btn-report-befor-save,/* .eto-btn-report-after-save,*/ .eto-report-invalid-bookings, .eto-btn-export-actions').addClass('hidden');
            $('.eto-btn-report-befor-save').data('reportId', false);
            etoFn.config.reportData = {};
            etoFn.config.driverTransactionStatuses = {};
            etoFn.config.paymentTotal = {};
            e.preventDefault();
        });

        $('.eto-report-form').on('submit', function(e) {
            e.preventDefault();

            $('.eto-show-report, .eto-report-invalid-bookings, .eto-charts').html('');
            $('/*.eto-btn-report-befor-save,*//* .eto-btn-report-after-save,*/ .eto-report-invalid-bookings, .eto-btn-export-actions').addClass('hidden');
            $('.eto-btn-report-befor-save').data('reportId', false);

            etoFn.config.reportData = {};
            etoFn.config.driverTransactionStatuses = {};
            etoFn.config.paymentTotal = {};

            var action = $(this).attr('action'),
                data = $(this).serialize();

            $('section.content').LoadingOverlay('show');
            setTimeout(function() {
                ETO.ajax(action, {
                    data: data,
                    success: function(results) {
                        etoFn.renderReport(results);
                    },
                    error: function(results) {
                        $('section.content').LoadingOverlay('hide');
                    },
                    complete: function() {
                        $('section.content').LoadingOverlay('hide');
                    }
                });
            });
        });

        $('body').on('click', 'a.eto-refresh-page', function (e) {
            e.preventDefault();
            var table = new $.fn.dataTable.Api( ".dataTable" );

            table.state.clear();
            window.location = $(this).attr('href');
        })
        .on('click', '.eto-btn-save-report', function (e) {
            e.preventDefault();

            var data = $('.eto-report-form').formObject(),
                button = $(this);

            ETO.swalWithBootstrapButtons({
                title: ETO.trans('reports.name_message'),
                input: 'text',
                inputPlaceholder: ETO.trans('reports.enter_report_name'),
                showCancelButton: true,
            })
            .then(function (result) {
                if (typeof result.dismiss != 'undefined') {
                    return false;
                }

                $('section.content').LoadingOverlay('show');

                $.each(data, function (key, value) {
                    delete data[key];

                    if(key != '_token' || key != 'type') {
                        key = key.replace(/(filters\[|\[|\])/mg, '');

                        if (typeof data['filters'] == 'undefined') {
                            data['filters'] = {};
                        }

                        data['filters'][key] = value;
                    }
                });

                $.each(etoFn.config.reportData, function (key, value) {
                    data[key] = value;
                });

                data.type = etoFn.config.typeReport;
                data.name = result.value;

                setTimeout(function () {
                    ETO.ajax('reports/store', {
                        async:false,
                        data: {data: JSON.stringify(data)},
                        success: function(results) {
                            etoFn.config.reportData.id = results.id;
                            $('.eto-btn-delete-report').data('reportId', results.id);
                            // $('.eto-btn-report-befor-save').addClass('hidden');
                            // $('.eto-btn-report-after-save').removeClass('hidden');

                            if (!button.hasClass('eto-btn-send-after-save')) {
                                ETO.swalWithBootstrapButtons({
                                    title: ETO.trans('reports.saved_message'),
                                    type: "success",
                                    timer: 3000,
                                    showCancelButton: false,
                                });
                            } else {
                                    if (typeof etoFn.config.reportData.id != "undefined") {
                                        etoFn.sendSavedReportToAll(etoFn.config.reportData.id, $('.eto-btn-send-report-all'), false);
                                    } else {
                                        etoFn.sendReportToAll(false);
                                    }
                            }

                            $('.eto-btn-report-befor-save a, .eto-btn-export-actions a').each(function (k,v) {
                                var href = $(this).attr('href');
                                var replace = "reports/export_all/format/";
                                var re = new RegExp(replace,"g");
                                var replace2 = "reports/export/driver/";
                                var re2 = new RegExp(replace2,"g");

                                $(this).attr('href', href.replace(re, "reports/export_all/"+results.id+"/format/"));
                                href = $(this).attr('href');
                                $(this).attr('href', href.replace(re2, "reports/export/"+results.id+"/driver/"));
                            });
                        },
                        error: function() {
                            $('section.content').LoadingOverlay('hide');
                        },
                        complete: function() {
                            $('section.content').LoadingOverlay('hide');
                        }
                    });
                }, 0);
            });
        })
        .on('click', '.eto-btn-restore-report', function (e) {
            var report_id = $(this).data('reportId');

            ETO.swalWithBootstrapButtons({
                title: ETO.trans('reports.remove_confirm_message'),
                type: 'warning',
                showCancelButton: true,
            })
            .then(function (result) {
                if (result.value) {
                    $('.eto-show-report').LoadingOverlay('show');
                    setTimeout(function () {
                        ETO.ajax('reports/restore/' + report_id, {
                            data: {},
                            success: function (results) {
                                var datatable = new $.fn.dataTable.Api('.dataTable');

                                if (typeof datatable != 'undefined') {
                                    datatable.ajax.reload(null, false);
                                }

                                ETO.swalWithBootstrapButtons({
                                    title: ETO.trans('reports.restored_message'),
                                    type: 'success',
                                    timer: 3000,
                                    showCancelButton: false,
                                });
                            },
                            error: function (results) {
                                $('.eto-show-report').LoadingOverlay('hide');
                            },
                            complete: function () {
                                $('.eto-show-report').LoadingOverlay('hide');
                            }
                        });
                    }, 0);
                }
            });
        })
        .on('click', '.eto-btn-trash-report', function (e) {
            var report_id = $(this).data('reportId');

            ETO.swalWithBootstrapButtons({
                title: ETO.trans('reports.remove_confirm_message'),
                type: 'warning',
                showCancelButton: true,
            })
            .then(function (result) {
                if (result.value) {
                    $('section.content, .eto-show-report').LoadingOverlay('show');
                    setTimeout(function () {
                        ETO.ajax('reports/trash/' + report_id, {
                            type: 'DELETE',
                            data: {},
                            success: function (results) {
                                var datatable = new $.fn.dataTable.Api('.dataTable');

                                if (typeof datatable != 'undefined') {
                                    datatable.ajax.reload(null, false);
                                }

                                ETO.swalWithBootstrapButtons({
                                    title: ETO.trans('reports.deleted_message'),
                                    type: 'success',
                                    timer: 3000,
                                    showCancelButton: false,
                                });
                            },
                            error: function (results) {
                                $('section.content, .eto-show-report').LoadingOverlay('hide');
                            },
                            complete: function () {
                                $('section.content, .eto-show-report').LoadingOverlay('hide');
                            }
                        });
                    }, 0);
                }
            });
        })
        .on('click', '.eto-btn-delete-report, .eto-btn-destroy-report', function (e) {
            var report_id = $(this).data('reportId');

            ETO.swalWithBootstrapButtons({
                title: ETO.trans('reports.remove_confirm_message'),
                type: 'warning',
                showCancelButton: true,
            })
            .then(function (result) {
                $('section.content, .eto-show-report').LoadingOverlay('show');

                if (result.value) {
                    setTimeout(function () {
                        ETO.ajax('reports/destroy/'+report_id, {
                            type: 'DELETE',
                            data: {},
                            success: function(results) {
                                if($('.dataTable').length > 0) {
                                    var datatable = new $.fn.dataTable.Api('.dataTable');

                                    if (typeof datatable != 'undefined') {
                                        datatable.ajax.reload(null, false);
                                    }
                                }

                                if ($('.eto-btn-reset').length > 0) {
                                    $('.eto-btn-reset').click();
                                }

                                ETO.swalWithBootstrapButtons({
                                    title: ETO.trans('reports.deleted_message'),
                                    type: "success",
                                    timer: 3000,
                                    showCancelButton: false,
                                });
                            },
                            error: function(results) {
                                $('section.content, .eto-show-report').LoadingOverlay('hide');
                            },
                            complete: function() {
                                $('section.content, .eto-show-report').LoadingOverlay('hide');
                            }
                        });
                    }, 0);
                }
            });
        })
        .on('click', '.eto-btn-send-report', function (e) {
            var button = $(this),
                driver = button.data('etoUser');

            if(typeof etoFn.config.reportData.id != "undefined") {
                etoFn.sendSavedReport(etoFn.config.reportData.id, driver);
            } else {
                etoFn.sendReport(driver);
            }
        })
        .on('click', '.eto-btn-send-report-all', function (e) {
            var button = $(this);

            if(typeof etoFn.config.reportData.id != "undefined") {
                etoFn.sendSavedReportToAll(etoFn.config.reportData.id, button);
            } else {
                etoFn.sendReportToAll();
            }
        })
        .on('click', '.eto-btn-export', function (e) {
            e.preventDefault();
            etoFn.exportReport(this);
        })
        .on('change', '.eto-modal-settings input', function(e) {
            var inputs = $('.eto-modal-settings').find('input.eto-settings-input'),
                values = ETO.parseSettings(inputs);

            if (Object.keys(values).length > 0) {
                ETO.saveSettings(values);
            }
        })
        .on('click', '.eto-btn-info-report.eto-type-payment button', function (e) {
            var button = $(this),
                container = button.closest('.eto-type-payment'),
                id = container.data('etoId'),
                show = button.hasClass('eto-btn-show');

            if(show) {
                container.find('.eto-btn-show').addClass('hidden');
                container.find('.eto-btn-hide').removeClass('hidden');
                $('#'+id).show('slow');
            } else {
                container.find('.eto-btn-show').removeClass('hidden');
                container.find('.eto-btn-hide').addClass('hidden');
                $('#'+id).hide('slow');
            }
        });

        etoFn.initSettings();
    };

    etoFn.initSettings = function()
    {
        var relSettings = {subscription: {eto_report: ETO.settings('eto_report')}};

        ETO.configFormUpdate($('.eto-modal-settings').find('input'), relSettings);
    };

    etoFn.renderReport = function(report)
    {
        etoFn.config.reportData = report;
        etoFn.config.driverTransactionStatuses = {};
        etoFn.config.fleetTransactionStatuses = {};
        etoFn.config.paymentTotal = {};

        if (report.type == 'driver' || report.type == 'fleet') {
            $('.send-report-links').removeClass('hidden');
            var emailCount = 0;
        }

        var fleetsHtml = '',
            driversHtml = '',
            customersHtml = '',
            paymentsHtml = '',
            container = $('.eto-show-report'),
            statusColors = report.status_colors;

        container.html('');

        if (report.type == 'fleet' && typeof report.fleets == 'object' && Object.keys(report.fleets).length > 0) {
            var companyPaydDriver = 0,
                driverCompanyPayd = 0;

            $.each(report.fleets, function (idFleet, fleet) {
                var whoPay = fleet.commission > 0
                        ? ETO.trans('reports.company_payd_fleet')
                        : ETO.trans('reports.fleet_payd_company'),
                    buttons = '';

                if (!etoFn.config.isTrash) {
                    if (typeof fleet.email != "undefined"
                        && null != fleet.email
                        && fleet.email != ''
                    ) {
                        buttons += '<button type="button" data-eto-user="' + idFleet + '" class="btn btn-sm btn-default eto-btn-send-report">' +
                            ETO.trans('reports.button.send_report_fleet') +
                            '</button>';
                        emailCount++;
                    }
                }

                if (!etoFn.config.isTrash) {
                    buttons += etoFn.exportLinks(idFleet);
                }

                if (fleet.commission > 0) {
                    companyPaydDriver += fleet.commission;
                } else {
                    driverCompanyPayd += fleet.commission;
                }

                fleetsHtml += '<div class="clearfix eto-report-driver-container">\
                    <div class="eto-report-driver-data">\
                        <div class="eto-report-driver-name-container-wrap clearfix">\
                            <div class="eto-report-driver-name-container">\
                                <div class="eto-report-driver-name">\
                                    ' + ETO.trans('reports.form.fleet') + ': ' + fleet.name + '\
                                </div>\
                                <div>\
                                    ' + ETO.trans('reports.fleet_income_set_to') + ' ' + fleet.percent + '%\
                                </div>\
                            </div>\
                            <div class="eto-report-driver-buttons-container">' + buttons + '</div>\
                        </div>\
                        <div class="table-responsive">\
                            ' + etoFn.fleetBookingList(fleet.bookings, idFleet) + '\
                        </div>\
                        <div class="eto-final-balance-wrap">\
                            <span class="eto-final-balance">' +
                                ETO.trans('reports.final_balance') +
                            '</span>: ' + whoPay + ' ' + ETO.formatPrice(Math.abs(fleet.commission)) + '\
                        </div>\
                    </div>';
                if (parseBoolean(ETO.settings('report_settings.show_charts', false))) {
                    fleetsHtml += '<div class="table-responsive eto-charts-left">\
                    <div class="eto-charts" data-eto-user="' + idFleet + '">\
                        <div class="eto-chart clearfix">\
                            <div class="btn-group eto-chart-buttons pull-right">\
                                <button class="btn btn-sm btn-default eto-chart-type active" data-eto-type="line">' + ETO.trans('reports.charts.line') + '</button>\
                                <button class="btn btn-sm btn-default eto-chart-type" data-eto-type="bar">' + ETO.trans('reports.charts.bar') + '</button>\
                                <button class="btn btn-sm btn-default eto-chart-type" data-eto-type="pie">' + ETO.trans('reports.charts.pie') + '</button>\
                            </div>\
                            <div class="btn-group eto-chart-buttons pull-right hidden" style="margin-right: 10px">\
                                <button class="btn btn-sm btn-default eto-range active" data-eto-range="daily">' + ETO.trans('reports.range.daily') + '</button>\
                                <button class="btn btn-sm btn-default eto-range" data-eto-range="weekly">' + ETO.trans('reports.range.weekly') + '</button>\
                                <button class="btn btn-sm btn-default eto-range" data-eto-range="monthly">' + ETO.trans('reports.range.monthly') + '</button>\
                            </div>\
                            <div class="clearfix">\
                                <canvas id="' + ETO.uuidHTML() + '" class="eto-chart-container">' + ETO.trans('reports.no_support_canvas') + '</canvas>\
                            </div>\
                        </div>\
                    </div>\
                    </div>';
                }
                fleetsHtml += ' <hr>\
                    </div>';
            });

            if (!etoFn.config.isTrash) {
                fleetsHtml += '<div class="row clearfix"><div class="col-lg-12 eto-report-actions-send hidden">\
                    <button type="button" class="btn btn-sm btn-default eto-btn-send-report-all">\
                    ' + ETO.trans('reports.button.send_report_to_all_' + report.type) + '\
                    </button>\
                    ' + etoFn.exportAllLinks() + '\
                    </div></div>';
            }
        }

        if (report.type == 'driver' && typeof report.drivers == 'object' && Object.keys(report.drivers).length > 0) {
            var companyPaydDriver = 0,
                driverCompanyPayd = 0;

            $.each(report.drivers, function (idDriver, driver) {
                var balance = (driver.commission - driver.cash),
                    whoPay = balance > 0
                        ? ETO.trans('reports.company_payd_driver')
                        : ETO.trans('reports.driver_payd_company'),
                    buttons = '';

                if (!etoFn.config.isTrash) {
                    if (typeof driver.email != "undefined"
                        && null != driver.email
                        && driver.email != ''
                    ) {
                        buttons += '<button type="button" data-eto-user="' + idDriver + '" class="btn btn-sm btn-default eto-btn-send-report">' +
                            ETO.trans('reports.button.send_report_driver') +
                            '</button>';
                        emailCount++;
                    }
                }

                if (!etoFn.config.isTrash) {
                    buttons += etoFn.exportLinks(idDriver);
                }

                if (balance > 0) {
                    companyPaydDriver += balance;
                } else {
                    driverCompanyPayd += balance;
                }

                driversHtml += '<div class="clearfix eto-report-driver-container">\
                    <div class="eto-report-driver-data">\
                        <div class="eto-report-driver-name-container-wrap clearfix">\
                            <div class="eto-report-driver-name-container">\
                                <div class="eto-report-driver-name">\
                                    ' + ETO.trans('reports.form.driver') + ': ' + driver.name + '\
                                </div>\
                                <div>\
                                    ' + ETO.trans('reports.driver_income_set_to') + ' ' + driver.percent + '%\
                                </div>\
                            </div>\
                            <div class="eto-report-driver-buttons-container">' + buttons + '</div>\
                        </div>\
                        <div class="table-responsive">\
                            ' + etoFn.driverBookingList(driver.bookings, idDriver) + '\
                        </div>\
                        <div class="eto-final-balance-wrap">\
                            <span class="eto-final-balance">' +
                                ETO.trans('reports.final_balance') +
                            '</span>: ' + whoPay + ' ' + ETO.formatPrice(Math.abs(balance)) + '\
                        </div>\
                    </div>';
                if (parseBoolean(ETO.settings('report_settings.show_charts', false))) {
                    driversHtml += '<div class="table-responsive eto-charts-left">\
                    <div class="eto-charts" data-eto-user="' + idDriver + '">\
                        <div class="eto-chart clearfix">\
                            <div class="btn-group eto-chart-buttons pull-right">\
                                <button class="btn btn-sm btn-default eto-chart-type active" data-eto-type="line">' + ETO.trans('reports.charts.line') + '</button>\
                                <button class="btn btn-sm btn-default eto-chart-type" data-eto-type="bar">' + ETO.trans('reports.charts.bar') + '</button>\
                                <button class="btn btn-sm btn-default eto-chart-type" data-eto-type="pie">' + ETO.trans('reports.charts.pie') + '</button>\
                            </div>\
                            <div class="btn-group eto-chart-buttons pull-right hidden" style="margin-right: 10px">\
                                <button class="btn btn-sm btn-default eto-range active" data-eto-range="daily">' + ETO.trans('reports.range.daily') + '</button>\
                                <button class="btn btn-sm btn-default eto-range" data-eto-range="weekly">' + ETO.trans('reports.range.weekly') + '</button>\
                                <button class="btn btn-sm btn-default eto-range" data-eto-range="monthly">' + ETO.trans('reports.range.monthly') + '</button>\
                            </div>\
                            <div class="clearfix">\
                                <canvas id="' + ETO.uuidHTML() + '" class="eto-chart-container">' + ETO.trans('reports.no_support_canvas') + '</canvas>\
                            </div>\
                        </div>\
                    </div>\
                    </div>';
                }
                driversHtml += ' <hr>\
                    </div>';
            });

            if (!etoFn.config.isTrash) {
                driversHtml += '<div class="row clearfix"><div class="col-lg-12 eto-report-actions-send hidden">\
                    <button type="button" class="btn btn-sm btn-default eto-btn-send-report-all">\
                    ' + ETO.trans('reports.button.send_report_to_all_' + report.typ) + '\
                    </button>\
                    ' + etoFn.exportAllLinks() + '\
                    </div></div>';
            }
        }

        // if (report.type == 'customer' && typeof report.customers == 'object' && Object.keys(report.customers).length > 0) {
        //     $.each(report.customers, function(idCustomer, customer) {
        //
        //     });
        // }

        if (report.type == 'payment' && typeof report.payments == 'object' && Object.keys(report.payments).length > 0) {
            $.each(report.payments, function(idPayment, payment) {
                var site = typeof payment.site != "undefined" ? ' <span>('+payment.site+')</span>' : '';

                paymentsHtml += '<div class="report-data-container">';
                paymentsHtml += '<div class="report-data-header">' + payment.name + site +'</div>';
                $.each(payment.status, function(idStatus, status) {
                    paymentsHtml += '<div class="report-data-row clearfix">';
                    paymentsHtml += '<span class="report-data-row-name">'+ status.name +':</span>';
                    paymentsHtml += '</div>';
                    paymentsHtml += etoFn.paymentBookingList(status.bookings, payment.id, idStatus, status.name);
                });
                paymentsHtml += '</div>';
            });

            if (Object.keys(etoFn.config.paymentTotal).length > 0 ) {
                paymentsHtml += '<div class="report-data-container report-data-container-summary">';
                paymentsHtml += '<div class="report-data-header">All Payments</div>';
                $.each(etoFn.config.paymentTotal, function($key, $value) {
                    paymentsHtml += '<div class="report-data-row clearfix">';
                    paymentsHtml += '<span class="report-data-row-name">'+ $value.name +':</span>';
                    paymentsHtml += '<span class="report-data-row-value">'+ ETO.formatPrice($value.total) +'</span>';
                    paymentsHtml += '</div>';
                });
                paymentsHtml += '</div>';
            }

            if (paymentsHtml != '') {
                paymentsHtml = '<div class="eto-report-payment-container">'+ paymentsHtml +'</div>';

                if (typeof report.id != 'undefined') {
                    paymentsHtml += '<div class="row clearfix"><div class="col-lg-12">';
                    if (!etoFn.config.isTrash) {
                        paymentsHtml += etoFn.exportLinks();
                    }
                    paymentsHtml += '</div>';
                }
                paymentsHtml += '</div>';
            }
        }

        if ((report.type == 'fleet' || report.type == 'driver' || report.type == 'payment')
            && typeof report.unassignedList == 'object' && Object.keys(report.unassignedList).length > 0
        ) {
            $('.eto-report-invalid-bookings').html('<div class="eto-content-info-report-wrap">\
                <span class="eto-payments-warning">Warning! There are some bookings where total price do not match payment transactions, this might cause miscalculation.<br>Please click info icon to see booking reference number and missing amount.</span>\
                '+ etoFn.unassignedBookingList(report.unassignedList) +'\
                </div>').removeClass('hidden');
        }

        if (fleetsHtml != '' || driversHtml != '' || customersHtml != '' || paymentsHtml != '') {
            container.append(fleetsHtml+driversHtml+customersHtml+paymentsHtml);
            $('.eto-btn-report-befor-save').removeClass('hidden');
            $('.eto-no-data').addClass('hidden');
            if (report.type == 'payment') {
                $('.eto-btn-group-send').addClass('hidden');
            }
        } else {
            $('.eto-btn-report-befor-save').addClass('hidden');
            $('.eto-no-data').removeClass('hidden');
        }

        if ((report.type == 'payment'
            && typeof report.payments == 'object' && (Object.keys(report.payments).length > 0 || report.payments.length > 0))
            || report.type == 'driver'
            || report.type == 'fleet'
        ) {
            if (parseBoolean(ETO.settings('report_settings.show_charts', false))) {
                etoFn.generateCharts(report.type);
            }

            if (typeof report.type == 'payment') {
                $('.eto-btn-group-send').addClass('hidden');
            }
        }

        $.each(etoFn.config.driverTransactionStatuses, function (idDriver, statuses) {
            var totalStatusesHtml = '';

            $.each(statuses, function (status, value) {
                totalStatusesHtml += '<span class="eto-report-transaction">\
                    <span class="eto-report-transaction-status">' + statusColors[status].name + '</span>\
                    <span class="eto-report-transaction-value">\
                    ' + ETO.formatPrice(value) + '\
                    </span>\
                </span>';
            });

            ETO.removeSetPopoverTooltip($('.eto-report-total-statuses[data-eto-user="' + idDriver + '"]'), {
                title: ETO.trans('reports.titles.all_payments'),
                tooltip: totalStatusesHtml,
            });
        });

        $.each(etoFn.config.fleetTransactionStatuses, function (idFleet, statuses) {
            var totalStatusesHtml = '';

            $.each(statuses, function (status, value) {
                totalStatusesHtml += '<span class="eto-report-transaction">\
                    <span class="eto-report-transaction-status">' + statusColors[status].name + '</span>\
                    <span class="eto-report-transaction-value">\
                    ' + ETO.formatPrice(value) + '\
                    </span>\
                </span>';
            });

            ETO.removeSetPopoverTooltip($('.eto-report-total-statuses[data-eto-user="' + idFleet + '"]'), {
                title: ETO.trans('reports.titles.all_payments'),
                tooltip: totalStatusesHtml,
            });
        });

        if ((typeof report.drivers == 'object' && Object.keys(report.drivers).length > 1 && $('.eto-report-actions').length === 0)
            || (typeof report.fleets == 'object' && Object.keys(report.fleets).length > 1 && $('.eto-report-actions').length === 0)
        ) {
            $('.eto-report-actions-send').removeClass('hidden');
        }

        if (emailCount === 0) {
            $('.eto-report-actions .eto-btn-group-send').addClass('hidden');
        }

        if (((typeof report.drivers == 'object' && Object.keys(report.drivers).length > 1)
                || (typeof report.payments == 'object' && Object.keys(report.payments).length > 0)
                || (typeof report.fleets == 'object' && Object.keys(report.fleets).length > 0))
            && $('.eto-btn-export-actions').length !== 0
        ) {
            $('.eto-btn-export-actions').removeClass('hidden');
        }
    };

    etoFn.showReport = function(el)
    {
        var action = $(el).data('etoAction'),
            modal = $('.eto-modal-report');

        $('.eto-show-report').LoadingOverlay('show');

        setTimeout(function () {
            ETO.ajax(action, {
                data: {},
                success: function (results) {
                    if(typeof results.status_message != "undefined") {
                        ETO.swalWithBootstrapButtons({
                            title: results.status_message,
                            type: 'warning',
                            timer: 3000,
                            showCancelButton: false,
                        });

                    } else {
                        modal.find('.modal-title').html(ETO.trans('reports.titles.' + $(el).data('reportType')) +
                            ' ' + ETO.convertDate(ETO.config.date_format, results.from_date_timestamp, true)
                            + ' - ' + ETO.convertDate(ETO.config.date_format, results.to_date_timestamp, true));
                        etoFn.renderReport(results);

                        modal.modal('show');
                    }
                },
                error: function (results) {
                    $('.eto-show-report').LoadingOverlay('hide');
                },
                complete: function () {
                    $('.eto-show-report').LoadingOverlay('hide');
                }
            });
        },0);

        // if (parseBoolean(ETO.settings('report_settings.show_charts', false))) {
        //     etoFn.generateCharts(etoFn.config.reportData.type);
        // }
        return false;
    };

    etoFn.fleetBookingList = function(bookings, idFleet)
    {
        var html = '',
            total = 0,
            totalTransactionsPaid = 0,
            totalCommission = 0,
            statusColors = etoFn.config.reportData.status_colors,
            payments = etoFn.config.reportData.payments;

        $.each(bookings, function (key, index) {
            var booking = etoFn.config.reportData.bookings[index],
                transactions = '';

            $.each(booking.transactions, function (payment_id, statuses) {
                var paymentNameTruncated = payments[payment_id].name.truncate(15, 10);
                var paymentName = payments[payment_id].name;

                $.each(statuses, function (status, values) {
                    var totalTransaction = parseFloat(values.amount) + parseFloat(values.payment_charge);

                    if (typeof etoFn.config.fleetTransactionStatuses[idFleet] == 'undefined') {
                        etoFn.config.fleetTransactionStatuses[idFleet] = {};
                    }
                    if (typeof etoFn.config.fleetTransactionStatuses[idFleet][status] == 'undefined') {
                        etoFn.config.fleetTransactionStatuses[idFleet][status] = 0;
                    }

                    etoFn.config.fleetTransactionStatuses[idFleet][status] += totalTransaction;

                    if (status == 'paid') {
                        totalTransactionsPaid += totalTransaction;
                    }

                    transactions += '<span class="eto-report-transaction">\
                        <span class="eto-report-transaction-value" title="' + paymentName + ' - ' + statusColors[status].name + '" style="color: ' + statusColors[status].color + '">\
                        ' + ETO.formatPrice(totalTransaction) + '\
                        </span>\
                        <span class="eto-report-transaction-method" title="' + paymentName + ' - ' + statusColors[status].name + '">' + paymentNameTruncated + '</span>\
                        </span>';
                });
            });

            totalCommission +=  booking.fleet_commission;
            total += booking.total_price;
            html += '<tr>' +
                '<td>' +
                    '<a href="' + ETO.config.appPath + '/admin/bookings/' + booking.id + '" target="_blank"' +
                        ' onclick="ETO.modalIframe(this); return false;">' +
                    booking.ref_number +
                    '</a>' +
                '</td>' +
                '<td>' + ETO.formatPrice(booking.total_price) + '</td>' +
                '<td>' + transactions + '</td>'+
                '<td>' + ETO.formatPrice( booking.fleet_commission) + '</td>' +
                '</tr>';
        });

        var output = '\
            <table class="table-condensed table-hover">\
            <thead>\
            <tr>\
            <th class="eto-raport-table-col-ref_number">' + ETO.trans('reports.columns.ref_number') + '</th>\
            <th>' + ETO.trans('reports.columns.total') + '</th>\
            <th class="eto-raport-table-col-company_take">' + ETO.trans('reports.columns.company_take') + '</th>\
            <th class="eto-raport-table-col-commission">' + ETO.trans('reports.columns.fleet_commission') + '</th>\
            </tr>\
            </thead>\
            <tbody>\
            ' + html + '\
            </tbody>\
            <tfoot>\
            <tr>\
            <td>' + ETO.trans('reports.total') + ':</td>' +
            '<td>' + ETO.formatPrice(total) + '</b></td>\
            <td class="eto-report-total-statuses" data-eto-user="' + idFleet + '">' + ETO.formatPrice(totalTransactionsPaid) + '</td>\
            <td>' + ETO.formatPrice(totalCommission) + '</td>\
            </tr>\
            </tfoot>\
            </table>';

        return output;
    };

    etoFn.driverBookingList = function(bookings, idDriver)
    {
        var html = '',
            total = 0,
            totalTransactionsPaid = 0,
            totalCash = 0,
            totalCommission = 0,
            statusColors = etoFn.config.reportData.status_colors,
            payments = etoFn.config.reportData.payments,
            versionData = parseInt(etoFn.config.reportData.version);

        $.each(bookings, function (key, index) {
            var booking = etoFn.config.reportData.bookings[index],
                transactions = '';

            if (versionData > 1) {
                $.each(booking.transactions, function (payment_id, statuses) {
                    var paymentNameTruncated = payments[payment_id].name.truncate(15, 10);
                    var paymentName = payments[payment_id].name;

                    $.each(statuses, function (status, values) {
                        var totalTransaction = parseFloat(values.amount) + parseFloat(values.payment_charge);

                        if (typeof etoFn.config.driverTransactionStatuses[idDriver] == 'undefined') {
                            etoFn.config.driverTransactionStatuses[idDriver] = {};
                        }
                        if (typeof etoFn.config.driverTransactionStatuses[idDriver][status] == 'undefined') {
                            etoFn.config.driverTransactionStatuses[idDriver][status] = 0;
                        }

                        etoFn.config.driverTransactionStatuses[idDriver][status] += totalTransaction;

                        if (status == 'paid') {
                            totalTransactionsPaid += totalTransaction;
                        }

                        transactions += '<span class="eto-report-transaction">\
                        <span class="eto-report-transaction-value" title="' + paymentName + ' - ' + statusColors[status].name + '" style="color: ' + statusColors[status].color + '">\
                        ' + ETO.formatPrice(totalTransaction) + '\
                        </span>\
                        <span class="eto-report-transaction-method" title="' + paymentName + ' - ' + statusColors[status].name + '">' + paymentNameTruncated + '</span>\
                        </span>';
                    });
                });
            }

            totalCash += booking.cash;
            totalCommission +=  booking.commission;
            total += booking.total_price;
            html += '<tr>' +
                '<td>' +
                    '<a href="' + ETO.config.appPath + '/admin/bookings/' + booking.id + '" target="_blank"' +
                        ' onclick="ETO.modalIframe(this); return false;">' +
                    booking.ref_number +
                    '</a>' +
                '</td>';

            if (versionData > 1) {
                html += '<td>' + ETO.formatPrice(booking.total_price) + '</td>' +
                    '<td>' + transactions + '</td>';
            }

            html += '<td>' + ETO.formatPrice(booking.cash) + '</td>' +
                '<td>' + ETO.formatPrice( booking.commission) + '</td>' +
                '</tr>';
        });

        var output = '\
            <table class="table-condensed table-hover">\
            <thead>\
            <tr>\
            <th class="eto-raport-table-col-ref_number">' + ETO.trans('reports.columns.ref_number') + '</th>';
        if (versionData > 1) {
            output += '<th>' + ETO.trans('reports.columns.total') + '</th>\
            <th class="eto-raport-table-col-company_take">' + ETO.trans('reports.columns.company_take') + '</th>';
        }
        output += '<th class="eto-raport-table-col-cash">' + ETO.trans('reports.columns.cash') + '</th>\
            <th class="eto-raport-table-col-commission">' + ETO.trans('reports.columns.commission') + '</th>\
            </tr>\
            </thead>\
            <tbody>\
            ' + html + '\
            </tbody>\
            <tfoot>\
            <tr>\
            <td>' + ETO.trans('reports.total') + ':</td>';
        if (versionData > 1) {
            output += '<td>' + ETO.formatPrice(total) + '</b></td>\
            <td class="eto-report-total-statuses" data-eto-user="' + idDriver + '">' + ETO.formatPrice(totalTransactionsPaid) + '</td>';
        }
        output += '<td>' + ETO.formatPrice(totalCash) + '</b></td>\
            <td>' + ETO.formatPrice(totalCommission) + '</td>\
            </tr>\
            </tfoot>\
            </table>';

        return output;
    };

    etoFn.paymentBookingList = function(bookings, idPayment, status, statusName)
    {
        var html = '',
            totalAmount = 0,
            versionData = parseInt(etoFn.config.reportData.version);

        $.each(bookings, function (key, index) {
            var booking = etoFn.config.reportData.bookings[index],
                transaction = booking.transactions[idPayment][status],
                total = 0;

            if (versionData === 1) {
                total = transaction;
            } else {
                total = parseFloat(transaction.payment_charge) + parseFloat(transaction.amount);
            }

            if (typeof etoFn.config.paymentTotal[status] == "undefined") {
                etoFn.config.paymentTotal[status] = {
                    name: statusName,
                    total: 0,
                };
            }

            etoFn.config.paymentTotal[status]['total'] += total;
            totalAmount += total;
            html += '<tr>' +
                '<td class="report-payment-column-ref">' +
                '<a href="' + ETO.config.appPath + '/admin/bookings/' + booking.id + '" target="_blank"' +
                ' onclick="ETO.modalIframe(this); return false;">' +
                booking.ref_number +
                '</a>' +
                '</td>' +
                '<td class="report-payment-column-total">' + ETO.formatPrice(total) + '</td>' +
                '</tr>';
        });

        return '<div class="table-responsive"><table class="table-condensed table-hover">\
            <tbody>\
            ' + html + '\
            </tbody>\
            <tfoot>\
            <tr>\
            <td>' + ETO.trans('reports.total') + ':</td>\
            <td>' + ETO.formatPrice(totalAmount) + '</td>\
            </tr>\
            </tfoot>\
            </table></div>';
    };

    etoFn.unassignedBookingList = function(bookings)
    {
        var id = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15),
            html = '',
            totalMissing = 0,
            totalOvercharged = 0,
            totalAmount = 0;

        $.each(bookings, function (key, values) {
            var booking = etoFn.config.reportData.bookings[values.ref_number],
                missing = values.amount > 0 ? 0 : Math.abs(values.amount),
                overcharged = values.amount > 0 ? Math.abs(values.amount) : 0;

            totalMissing += missing;
            totalOvercharged += overcharged;
            totalAmount += parseFloat(values.amount);
            html += '<tr>' +
                '<td>' +
                '<a href="' + ETO.config.appPath + '/admin/bookings/' + booking.id + '" target="_blank"' +
                ' onclick="ETO.modalIframe(this); return false;">' +
                booking.ref_number +
                '</a>' +
                '</td>' +
                '<td>' + ETO.formatPrice(missing) + '</td>' +
                '<td>' + ETO.formatPrice(overcharged) + '</td>' +
                '</tr>';
        });

        html = '<hr><table class="table-condensed table-hover">\
            <thead>\
            <tr>\
            <th>' + ETO.trans('reports.columns.ref_number') + '</th>\
            <th>' + ETO.trans('reports.columns.missing') + '</th>\
            <th>' + ETO.trans('reports.columns.overcharged') + '</th>\
            </tr>\
            </thead>\
            <tbody>\
            ' + html + '\
            </tbody>\
            <tfoot>\
            <tr>\
            <td>' + ETO.trans('reports.total') + ':</td>\
            <td>' + ETO.formatPrice(totalMissing) + '</td>\
            <td>' + ETO.formatPrice(totalOvercharged) + '</td>\
            </tr>\
            </tfoot>\
            </table><hr>';

        return '<div class="clearfix eto-btn-info-report eto-type-payment" data-eto-id="'+id+'">' +
            '<button class="btn btn-default btn-sm eto-btn-show">' + ETO.trans('reports.show_incomplete') + '</button>' +
            '<button class="btn btn-default btn-sm eto-btn-hide hidden">' + ETO.trans('reports.hide_incomplete') + '</button>' +
            '</div>' +
            '<div id="'+id+'" class="eto-content-info-report">'+ html +'</div>';
    };

    etoFn.generateCharts = function (type, container)
    {
        var data = {};

        if (type == 'fleet') {
            $.each(etoFn.config.reportData.fleets, function (fleetId, values) {
                container = $('.eto-charts[data-eto-user="' + fleetId + '"]');
                var id = container.find('canvas').attr('id');

                data = etoFn.getFleetDataToCharts(values);
                ETO.Chart.renderChart(id, data.labels, data.datasets);
            });
        } else if (type == 'driver') {
            $.each(etoFn.config.reportData.drivers, function (driverId, values) {
                container = $('.eto-charts[data-eto-user="' + driverId + '"]');
                var id = container.find('canvas').attr('id');

                data = etoFn.getDriverDataToCharts(values);
                ETO.Chart.renderChart(id, data.labels, data.datasets);
            });
        } else if (type == 'customer') {
            data = etoFn.getCustomerDataToCharts();
        } else if (type == 'payment') {
            var id = ETO.uuidHTML();
            container = typeof container != 'undefined' ? container : $('.eto-charts');
            container.html('<div class="table-responsive"><div class="eto-chart eto-chart-wrapper clearfix">\
                 <div class="btn-group eto-chart-buttons pull-right">\
                    <button class="btn btn-sm btn-default eto-chart-type active" data-eto-type="line">'+ ETO.trans('reports.charts.line') +'</button>\
                    <button class="btn btn-sm btn-default eto-chart-type" data-eto-type="bar">'+ ETO.trans('reports.charts.bar') +'</button>\
                    <button class="btn btn-sm btn-default eto-chart-type" data-eto-type="pie">'+ ETO.trans('reports.charts.pie') +'</button>\
                </div>\
                <div class="btn-group eto-chart-buttons pull-right hidden" style="margin-right: 10px">\
                    <button class="btn btn-sm btn-default eto-range active" data-eto-range="daily">'+ ETO.trans('reports.range.daily') +'</button>\
                    <button class="btn btn-sm btn-default eto-range" data-eto-range="weekly">'+ ETO.trans('reports.range.weekly') +'</button>\
                    <button class="btn btn-sm btn-default eto-range" data-eto-range="monthly">'+ ETO.trans('reports.range.monthly') +'</button>\
                </div>\
                <canvas id="' + id + '" class="eto-chart-container">'+ ETO.trans('reports.no_support_canvas') +'</canvas>\
            </div></div>');

            data = etoFn.getPaymentDataToCharts();
            ETO.Chart.renderChart(id, data.labels, data.datasets);
        } else {
            return false;
        }
    };

    etoFn.getFleetDataToCharts = function (FleetData)
    {
        var commissionPerDate = {},
            datasets = [{
                    label: ETO.trans('reports.columns.fleet_commission'),
                    data: [],
                }],
            labels = [];

        $.each(FleetData.bookings, function (key, index) {
            var booking = etoFn.config.reportData.bookings[index],
                date = booking.date;

            if(typeof commissionPerDate[date] == 'undefined') {
                commissionPerDate[date] = 0;
            }

            commissionPerDate[date] += booking.fleet_commission;
        });

        $.each(commissionPerDate, function (date,commission) {
            labels.push(date);
            datasets[0].data.push(commission);
        });

        return {labels: labels, datasets: datasets};
    };

    etoFn.getDriverDataToCharts = function (driverData)
    {
        var cashPerDate = {},
            commissionPerDate = {},
            datasets = [{
                    label: ETO.trans('reports.columns.cash'),
                    data: [],
                }, {
                    label: ETO.trans('reports.columns.commission'),
                    data: [],
                }],
            labels = [];

        $.each(driverData.bookings, function (key, index) {
            var booking = etoFn.config.reportData.bookings[index],
                date = booking.date;

            if(typeof cashPerDate[date] == 'undefined') {
                cashPerDate[date] = 0;
            }
            if(typeof commissionPerDate[date] == 'undefined') {
                commissionPerDate[date] = 0;
            }

            cashPerDate[date] += booking.cash;
            commissionPerDate[date] += booking.commission;
        });

        $.each(cashPerDate, function (date,cash) {
            labels.push(date);
            datasets[0].data.push(cash);
            datasets[1].data.push(commissionPerDate[date]);
        });

        return {labels: labels, datasets: datasets};
    };

    etoFn.getCustomerDataToCharts = function ()
    {
        var customerData = etoFn.config.reportData.customer,
            data = {};

        $.each(customerData, function (customertId, customer) {
            data[customer.name] = [];
        });

        return data;
    };

    etoFn.getPaymentDataToCharts = function ()
    {
        var paymentData = etoFn.config.reportData.payments,
            statuese = {},
            labels = [],
            datasets = [],
            labelsPerTimestamp = {},
            dataPerTimestamp = {},
            versionData = parseInt(etoFn.config.reportData.version);

        $.each(paymentData, function (paymentId, payment) {
            var  totalAmount = 0;

            $.each(payment.status, function(idStatus, status) {
                if(typeof statuese[idStatus] == 'undefined') {
                    statuese[idStatus] = {
                        label: status.name,
                        data: [],
                        total: status.total,
                    };
                }

                $.each(status.bookings, function (key, index) {
                    var booking = etoFn.config.reportData.bookings[index],
                        transaction = booking.transactions[payment.id][idStatus],
                        total = 0;

                    if (versionData === 1) {
                        total = transaction;
                    } else {
                        total = parseFloat(transaction.payment_charge) + parseFloat(transaction.amount);
                    }

                    if (typeof labelsPerTimestamp[booking.timestamp] == 'undefined') {
                        labelsPerTimestamp[booking.timestamp] = booking.date;
                    }

                    if (typeof dataPerTimestamp[idStatus] == 'undefined') {
                        dataPerTimestamp[idStatus] = {};
                    }

                    if (typeof dataPerTimestamp[idStatus][booking.timestamp] == 'undefined') {
                        dataPerTimestamp[idStatus][booking.timestamp] = {};
                        dataPerTimestamp[idStatus][booking.timestamp].value = 0;
                    }

                    dataPerTimestamp[idStatus][booking.timestamp].value +=total;
                });
            });
        });

        $.each(dataPerTimestamp, function (idStatus, values) {
            $.each(labelsPerTimestamp, function (t, date) {
                var price = 0;

                if (typeof values[t] != 'undefined') {
                    price = Math.round(values[t].value * 100) / 100
                }

                statuese[idStatus].data.push(price);
            });

            datasets.push(statuese[idStatus]);
        });

        $.each(labelsPerTimestamp, function (t, date) {
            labels.push(date);
        });

        return {labels: labels, datasets: datasets};
    };

    etoFn.weekendAreas = function(axes)
    {
        var markings = [],
            d = new Date(axes.xaxis.min);

        // go to the first Saturday

        d.setUTCDate(d.getUTCDate() - ((d.getUTCDay() + 1) % 7));
        d.setUTCSeconds(0);
        d.setUTCMinutes(0);
        d.setUTCHours(0);

        var i = d.getTime();

        // when we don't set yaxis, the rectangle automatically
        // extends to infinity upwards and downwards

        do {
            markings.push({ xaxis: { from: i, to: i + 2 * 24 * 60 * 60 * 1000 } });
            i += 7 * 24 * 60 * 60 * 1000;
        }
        while (i < axes.xaxis.max);

        return markings;
    };

    etoFn.sendReport = function(driver)
    {
        $('section.content').LoadingOverlay('show');
        setTimeout(function () {
            ETO.ajax('/reports/send_report/' + driver, {
                data: {
                    data: JSON.stringify(etoFn.config.reportData)
                },
                success: function (results) {
                    ETO.swalWithBootstrapButtons({
                        title: ETO.trans('reports.report_send_success'),
                        type: 'success',
                        timer: 3000,
                        showCancelButton: false,
                    });
                },
                error: function (results) {
                    $('section.content').LoadingOverlay('hide');
                },
                complete: function () {
                    $('section.content').LoadingOverlay('hide');
                }
            });
        },0);
    };

    etoFn.sendReportToAll = function(showLoader)
    {
        if (typeof showLoader == "undefined" || showLoader === true) {
            $('section.content, .eto-show-report').LoadingOverlay('show');
        }
        setTimeout(function () {
            ETO.ajax('/reports/send_report_to_all', {
                data: {
                    data: JSON.stringify(etoFn.config.reportData),
                },
                success: function (results) {
                    ETO.swalWithBootstrapButtons({
                        title: ETO.trans('reports.report_send_success'),
                        type: 'success',
                        timer: 3000,
                        showCancelButton: false,
                    });
                },
                error: function (results) {
                    if (typeof showLoader == "undefined" || showLoader === true) {
                        $('section.content, .eto-show-report').LoadingOverlay('hide');
                    }
                },
                complete: function () {
                    if (typeof showLoader == "undefined" || showLoader === true) {
                        $('section.content, .eto-show-report').LoadingOverlay('hide');
                    }
                }
            });
        },0);
    };

    etoFn.sendSavedReport = function(report, driver)
    {
        $('.eto-show-report').LoadingOverlay('show');
        setTimeout(function () {
            ETO.ajax('/reports/send_saved_report/' + report + '/user/' + driver, {
                data: {},
                success: function (results) {
                    ETO.swalWithBootstrapButtons({
                        title: ETO.trans('reports.report_send_success'),
                        type: 'success',
                        timer: 3000,
                        showCancelButton: false,
                    });
                },
                error: function (results) {
                    $('.eto-show-report').LoadingOverlay('hide');
                },
                complete: function () {
                    $('.eto-show-report').LoadingOverlay('hide');
                }
            });
        },0);
    };

    etoFn.sendSavedReportToAll = function(report, button, showLoader)
    {
        if (typeof showLoader == "undefined" || showLoader === true) {
            $('section.content, .eto-show-report').LoadingOverlay('show');
        }
        setTimeout(function () {
            ETO.ajax('/reports/send_saved_report_to_all/' + report, {
                data: {},
                success: function (results) {
                    var title = ETO.trans('reports.report_send_success');

                    if (button.hasClass('eto-btn-save-report')) {
                        title = ETO.trans('reports.report_save_send_success')
                    }

                    ETO.swalWithBootstrapButtons({
                        title: title,
                        type: 'success',
                        timer: 3000,
                        showCancelButton: false,
                    });
                },
                error: function (results) {
                    if (typeof showLoader == "undefined" || showLoader === true) {
                        $('section.content, .eto-show-report').LoadingOverlay('hide');
                    }
                },
                complete: function () {
                    if (typeof showLoader == "undefined" || showLoader === true) {
                        $('section.content, .eto-show-report').LoadingOverlay('hide');
                    }
                }
            });
        },0);
    };

    etoFn.exportReport = function(el)
    {
        var url = $(el).attr('href'),
            isSaved = typeof etoFn.config.reportData.id != "undefined",
            data = {};

        if (isSaved) {
            window.location = url;
        } else {
            $('section.content').LoadingOverlay('show');
            data.data = JSON.stringify(etoFn.config.reportData);

            setTimeout(function () {
                ETO.ajax(url, {
                    cache: false,
                    data: data,
                    success: function (results) {
                        window.location = ETO.config.appPath + '/reports/export/download/' + results.downloadUrl;
                    },
                    error: function (results) {
                        $('section.content').LoadingOverlay('hide');
                    },
                    complete: function () {
                        $('section.content').LoadingOverlay('hide');
                    }
                });
            },0);
        }
    };

    etoFn.exportLinks = function(userId, )
    {
        var uri = ETO.config.appPath + '/reports/export/';

        if (etoFn.config.reportData.type == 'fleet') {
            uri = ETO.config.appPath + '/reports/export_fleet/';
            if(typeof etoFn.config.reportData.id != "undefined") {
                uri += etoFn.config.reportData.id + '/';
            }

            if(typeof userId != "undefined") {
                uri += 'fleet/'+userId;
            } else {
                uri += 'all';
            }
        }
        else if (etoFn.config.reportData.type == 'driver') {
            if(typeof etoFn.config.reportData.id != "undefined") {
                uri += etoFn.config.reportData.id + '/';
            }

            if(typeof userId != "undefined") {
                uri += 'driver/'+userId;
            } else {
                uri += 'all';
            }
        }
        else if (etoFn.config.reportData.type == 'payment') {
            uri = ETO.config.appPath + '/reports/export_all/';

            if(typeof etoFn.config.reportData.id != "undefined") {
                uri += etoFn.config.reportData.id;
            }
        }

        return '<div class="btn-group eto-btn-export-actions">\
            <a href="' + uri + '/format/xlsx" class="btn btn-sm btn-default eto-btn-export">\
            <span>' + ETO.trans('reports.button.export') + '</span>\
            </a>\
            <div class="btn-group dropup" role="group">\
            <button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown">\
            <i class="fa fa-angle-down"></i>\
            </button>\
            <ul class="dropdown-menu" role="menu">\
            <li>\
                <a href="' + uri + '/format/xlsx" class="btn btn-sm btn-link eto-btn-export"">\
                <span>' + ETO.trans('reports.button.export_xlsx') + '</span>\
                </a>\
            </li>\
            <li>\
                <a href="' + uri + '/format/xls" class="btn btn-sm btn-link eto-btn-export"">\
                <span>' + ETO.trans('reports.button.export_xls') + '</span>\
                </a>\
            </li>\
            <li>\
                <a href="' + uri + '/format/pdf" class="btn btn-sm btn-link eto-btn-export">\
                <span>' + ETO.trans('reports.button.export_pdf') + '</span>\
                </a>\
            </li>\
            </ul>\
            </div>\
            </div>';
    };

    etoFn.exportAllLinks = function()
    {
        var uri = ETO.config.appPath + '/reports/export_all/';

        if(typeof etoFn.config.reportData.id != "undefined") {
            uri += etoFn.config.reportData.id;
        }

        return '<div class="btn-group eto-btn-export-actions">\
            <a href="' + uri + '/format/xlsx" class="btn btn-sm btn-default eto-btn-export">\
            <span>' + ETO.trans('reports.button.export_all') + '</span>\
            </a>\
            <div class="btn-group dropup" role="group">\
            <button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown">\
            <i class="fa fa-angle-down"></i>\
            </button>\
            <ul class="dropdown-menu" role="menu">\
            <li>\
                <a href="' + uri + '/format/xlsx" class="btn btn-sm btn-link eto-btn-export"">\
                <span>' + ETO.trans('reports.button.export_xlsx') + '</span>\
                </a>\
            </li>\
            <li>\
                <a href="' + uri + '/format/xls" class="btn btn-sm btn-link eto-btn-export"">\
                <span>' + ETO.trans('reports.button.export_xls') + '</span>\
                </a>\
            </li>\
            <li>\
                <a href="' + uri + '/format/pdf" class="btn btn-sm btn-link eto-btn-export">\
                <span>' + ETO.trans('reports.button.export_pdf') + '</span>\
                </a>\
            </li>\
            </ul>\
            </div>\
            </div>';
    };

    return etoFn;
}();
