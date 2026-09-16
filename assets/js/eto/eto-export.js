

ETO.Export = function() {
    var etoFn = {};

    etoFn.config = {
        init: ['vehicle', 'service', 'source', 'booking_status', 'payment_type'],
        lang: ['export'],
    };

    etoFn.paramsToExport = {};
    etoFn.usedSection = '';

    etoFn.init = function (config) {
        if (typeof config != 'undefined') {
            etoFn.config = $.extend(etoFn.config, config);
        }

        if (ETO.model === false) {
            ETO.init({
                config: etoFn.config.init,
                lang: etoFn.config.lang
            }, 'export');
        }

        var modalColumns = $('.eto-modal-section-columns');
        var modalFilters = $('.eto-modal-section-filters');

        $.each(etoFn.config.sections, function (section, data) {
            etoFn.paramsToExport[section] = {tags: [], filters: {}, columns: data.columns};
        });

        /**
         * set|uset Tags group filters
         */
        $('body')
            .on('click', '.eto-set-save-filter', function(e) {
                $(this).closest('.eto-save-filter-apply-container').addClass('hidden');
                $(this).closest('.modal-footer').find('.eto-save-filter-save-container').removeClass('hidden');
            })
            .on('click', '.eto-unset-save-filter', function(e) {
                $(this).closest('.modal-footer').find('.eto-save-filter-apply-container').removeClass('hidden');
                $(this).closest('.eto-save-filter-save-container').addClass('hidden');
            })
            .on('change', '.eto-section-tags', function(e) {
                var section = $(this).closest('tr').data('etoSection');

                etoFn.paramsToExport[section].tags = $(this).val();
            })
            .on('click', '.eto-reset-filter-params', function () {
                var el = $(this),
                    section = el.closest('.eto-modal-section-filters').data('etoSection'),
                    tr = $('tr.eto-export-section[data-eto-section="'+section+'"]'),
                    button = tr.find('.eto-btn-filter-params');

                button.removeClass('btn-info');
                $(this).closest('form')[0].reset();
                $(this).closest('form').find('select').each(function() {
                    etoFn.setSelect2($(this), $(this).attr('placeholder'));
                });

                if (typeof etoFn.paramsToExport[etoFn.usedSection] == 'undefined') {
                    etoFn.paramsToExport[etoFn.usedSection] = {};
                }

                etoFn.paramsToExport[etoFn.usedSection].filters = el.closest('form').serializeObject();
            })
            .on('click', '.eto-use-filter-params', function (e) {
                var el = $(this),
                    modal = el.closest('.eto-modal-section-filters'),
                    section = modal.data('etoSection'),
                    tr = $('tr.eto-export-section[data-eto-section="'+section+'"]'),
                    button = tr.find('.eto-btn-filter-params'),
                    method = 'removeClass';

                if (typeof etoFn.paramsToExport[etoFn.usedSection] == 'undefined') {
                    etoFn.paramsToExport[etoFn.usedSection] = {};
                }
                etoFn.paramsToExport[etoFn.usedSection].filters = el.closest('form').serializeObject();

                $.each(Object.keys(etoFn.paramsToExport[etoFn.usedSection].filters), function (k,v) {
                    var value = etoFn.paramsToExport[etoFn.usedSection].filters[v];

                    if((typeof value != 'object' && value != '') || (typeof value == 'object' && value.length > 0)) {
                        method = 'addClass';
                        return true;
                    }
                });

                if (method == 'addClass') {
                    modal.modal('hide');
                }

                if (el.hasClass('eto-save-filter-params')) {
                    var name = el.closest('.input-group').find('[name="filter_name"]').val(),
                        newClass = name.toString().replace(/\W+/g, '_');

                    if (name.length === 0
                        || (null !== $('[data-eto-section="'+section+'"]').find('.eto-section-tags').val() && $('[data-eto-section="'+section+'"]').find('.eto-section-tags').val().indexOf(newClass) !== -1)
                    ) {
                        ETO.toast({type: 'warning', text: ETO.lang.export.enter_name});
                    } else {
                        $.LoadingOverlay('show');
                        ETO.ajax('export/store', {
                            data: etoFn.paramsToExport,
                            async: true,
                            success: function(instalation) {
                                if (null === $('[data-eto-section="'+section+'"]').find('.eto-section-tags').val()
                                    || (null !== $('[data-eto-section="'+section+'"]').find('.eto-section-tags').val() && $('[data-eto-section="'+section+'"]').find('.eto-section-tags').val().indexOf(newClass) === -1)
                                ){
                                    var data = {
                                        id: newClass,
                                        text: name
                                    };

                                    var newOption = new Option(data.text, data.id, false, false);
                                    $('[data-eto-section="'+section+'"]').find('.eto-section-tags').append(newOption);
                                    $('[data-eto-section="'+section+'"]').find('.eto-section-tags').find('option[value="'+newClass+'"]').attr('selected', true);
                                    $('[data-eto-section="'+section+'"]').find('.eto-section-tags').change();

                                    modal.find('.eto-reset-filter-params').click();
                                    modal.modal('hide');
                                }
                            },
                            error: function() {
                                ETO.swalWithBootstrapButtons({type: 'error', title: 'An error has occurred during module installation'});
                            },
                            complete: function() {
                                $.LoadingOverlay('hide');
                            }
                        });
                    }
                } else {
                    button[method]('btn-info');
                }
            })
            .on('change', '.check-all', function () {
                etoFn.checkAll(
                    '[data-eto-section="'+etoFn.usedSection+'"] input.eto-section-column',
                    '[data-eto-section="'+etoFn.usedSection+'"] input.check-all'
                );
            })
            .on('change', '.eto-export-section-all', function () {
                etoFn.checkAll(
                    'input.eto-export-section',
                    'input.eto-export-section-all'
                );
            })
            .on('change', '.eto-section-column', function () {
                var columns = modalColumns.find('.modal-body').find('input').serializeObject(true);

                if(typeof columns.export == 'object') {
                    etoFn.updateColumns(columns.export[etoFn.usedSection]);
                } else{
                    etoFn.updateColumns({});
                }
                etoFn.checkAll(
                    '[data-eto-section="'+etoFn.usedSection+'"] input.eto-section-column',
                    '[data-eto-section="'+etoFn.usedSection+'"] input.check-all',
                    true
                );
            })
            .on('change', '.eto-export-section', function () {
                etoFn.checkAll(
                    'input.eto-export-section',
                    'input.eto-export-section-all',
                    true
                );
            })
            .on('click', '.eto-btn-generate', function(e) {
                var format = $(this).data('etoExportType'),
                    excelDownloadUrl = ETO.config.appPath + '/export/format/' + format;

                etoFn.downloadZip(excelDownloadUrl, format);
            })
            .on('click', '.eto-btn-filter-columns', function(e) {
                var modalbody = modalColumns.find('.eto-export-section-columns');

                etoFn.usedSection = $(this).closest('tr.eto-export-section').data('etoSection');

                $.LoadingOverlay('show');
                modalColumns.attr('data-eto-section', etoFn.usedSection);
                modalbody.html('').append('<div class="row form-group clearfix">\
                        <label for="check-all-'+etoFn.usedSection+'" class="col-sm-10 control-label">\
                            '+ETO.lang.export.column.all+'\
                        </label>\
                        <div class="col-sm-2">\
                            <div class="onoffswitch">\
                                <input id="check-all-'+etoFn.usedSection+'" class="onoffswitch-input check-all" type="checkbox">\
                                    <label class="onoffswitch-label" for="check-all-'+etoFn.usedSection+'"></label>\
                            </div>\
                        </div>\
                    </div>');
                modalColumns.find('.eto-export-section-title').html(ETO.lang.export.section[etoFn.usedSection]);
                etoFn.renderColumnsFromTree(etoFn.config.sections[etoFn.usedSection].columns, modalbody);
                modalColumns.modal('show');
                $.LoadingOverlay('hide');
            })
            .on('click', '.eto-btn-filter-params', function(e) {
                modalFilters.find('.eto-new-filter-form').html('');
                etoFn.usedSection = $(this).closest('tr.eto-export-section').data('etoSection');
                $.LoadingOverlay('show');
                modalFilters.data('etoSection', etoFn.usedSection);
                modalFilters.find('.eto-export-section-title').html(ETO.lang.export.section[etoFn.usedSection]);
                etoFn.renderFormFilters(etoFn.config.sections[etoFn.usedSection].params, modalFilters.find('.eto-new-filter-form'));
                modalFilters.modal('show');
                $.LoadingOverlay('hide');
            });

        $('.eto-export-section-all').attr('checked', true).change();

        $('#export .eto-export-section select').each(function() {
            etoFn.setSelect2($(this), $(this).attr('placeholder'));
        });

        $('.eto-section-tags',).each(function() {
            var section = $(this).closest('tr').data('etoSection');

            etoFn.paramsToExport[section].tags = $(this).val();
        });

        $('.eto-modal-section-filters, .eto-modal-section-columns').on('hidden.bs.modal', function () {
            etoFn.usedSection = '';
        });
    };

    etoFn.setSelect2 = function(select, placeholder) {
        var width = select.hasClass('eto-section-tags') ? '300px' : '100%';

        placeholder = typeof placeholder != 'undefined'
            ? ETO.lang.export.filter[placeholder]
            : ETO.lang.export.selectFilter;

        select.select2({
            height: 'auto',
            width: width,
            placeholder: placeholder,
            cache: true,
            minimumResultsForSearch: -1,
            escapeMarkup: function(markup) {
                return markup;
            },
        });
    };

    etoFn.renderFormFilters = function(params, container) {
        $.each(params, function (key,data) {
            var field = '',
                multiple = parseBoolean(data.multiple) === true ? '[]' : '',
                multipleText = parseBoolean(data.multiple) === true ? 'multiple' : '',
                value = etoFn.paramsToExport[etoFn.usedSection].filters[key];

            if (data.type == 'select') {
                var options = '';

                $.each(data.items, function (keyItem,item) {
                    options += '<option value="' + keyItem + '">';
                    if (typeof item.color != 'undefined') {
                        options += ' <span style="color: ' + item.color + '">';
                    }

                    if (data.translations !== false
                        && ETO.indexObjectValue(ETO.lang, data.translations + keyItem) !== null
                    ) {
                        options += ETO.trans(data.translations + keyItem);
                    } else if (parseBoolean(data.translations) !== false
                        && (typeof item.trans_key != "undefined" && item.trans_key != '')
                        && ETO.indexObjectValue(ETO.lang, data.translations + item.trans_key) !== null
                    ) {
                        options += ETO.trans(data.translations + item.trans_key);
                    } else if (typeof item.name != "undefined" && item.name != '') {
                        options += item.name
                    } else {
                        options += keyItem
                    }

                    if(typeof item.color != 'undefined') {
                        options += ' </span>';
                    }
                    options += '</option>';
                });

                field = '<select class="form-control eto-filter-select" name="'+key+multiple+'" data-eto-name="'+key+'"'+multipleText+'>\
                        <option value=""></option>\
                        '+options+'\
                    </select>';
            } else if (['text', 'radio', 'checkbox', 'date'].indexOf(data.type) !== -1) {
                $.each(data.items, function (keyItem,item) {
                    var className = typeof item.class != 'undefined' ? item.class : '';

                    field += '<input type="'+data.type+'" \
                        class="form-control eto-filter-input '+className+'" \
                        name="'+key+multiple+'"/>';
                });
            } else if (data.type == 'textarea') {

            }

            container.append('<div class="form-group clearfix">\
                <label for="'+key+'" class="col-sm-4 control-label">\
                    '+ ETO.trans('export.filter.'+key) +'\
                </label>\
                <div class="col-sm-8 eto-filter-param" data-eto-filter="'+key+'">\
                '+field+'\
                </div>\
            </div>');

            if(typeof value != 'undefined') {
                var fieldObj = container.find('[name="'+key+multiple+'"]');

                if(fieldObj.length > 0) {
                    fieldObj.val(value);
                }
            }

        });

        container.find('select').each(function() {
            etoFn.setSelect2($(this), $(this).attr('placeholder'));
        });

        container.find('.eto-set-daterangepicker').each(function (key, val) {
            var format = ETO.convertDate(ETO.config.date_format) + ' ' + ETO.convertTime(ETO.config.time_format),
                input = $(this),
                container = input.closest('.eto-filter-param');

            container.append('<input type="text" class="form-control eto-set-daterangepicker-view">');
            input.attr('type', 'hidden');
            var input2 = container.find('.eto-set-daterangepicker-view');

            input2.daterangepicker({
                singleDatePicker: true,
                showDropdowns: true,
                timePicker: true,
                timePicker24Hour: ETO.config.time_format == 'H:i' ? true : false,
                autoUpdateInput: false,
                timePickerIncrement: 5,
                locale: {
                    format: format,
                    firstDay: parseInt(ETO.config.date_start_of_week)
                },
            })
                .on('apply.daterangepicker', function(ev, picker) {
                    input2.val(picker.startDate.format(format)).change();
                    input.val(picker.startDate.format('YYYY-MM-DD HH:mm')).change();
                });
        });
    };

    etoFn.renderColumnsFromTree = function(tree, container, parentKey) {
        var filterKey = '';

        if (typeof parentKey != "undefined") {
            if (parentKey != '') {
                filterKey = '[' + parentKey.replace('.', '][') + ']';
                parentKey += '.';
            }
        } else {
            parentKey = '';
        }

        $.each(tree, function (key,status) {
            if(typeof status == 'object') {
                if(parentKey == '') {
                    etoFn.renderColumnsFromTree(status, container, key)
                } else {
                    etoFn.renderColumnsFromTree(status, container, parentKey + '.' + key)
                }
            } else if(!ETO.config.allow_services && (key == 'service_id' || key == 'service_duration')) {

            } else {
                var init = parseBoolean(status) === true ? ' data-eto-init="1"' : '';
                var checked = parseBoolean(status) === true ? ' checked="checked"' : '';
                var id = etoFn.usedSection +'.'+ parentKey + key;

                container.append('<div class="row form-group clearfix">\
                    <label for="'+key+'" class="col-sm-10 control-label">\
                    '+ETO.indexObjectValue(ETO.lang.export.column,etoFn.usedSection+'.'+parentKey+key)+'\
                    </label>\
                    <div class="col-sm-2">\
                    <div class="onoffswitch">\
                    <input id="'+id+'" class="onoffswitch-input eto-section-column" type="checkbox" value="'+key+'" '+init+' name="export['+etoFn.usedSection+']'+filterKey+'['+key+']"\
                     '+checked+'>\
                    <label class="onoffswitch-label" for="'+ etoFn.usedSection +'.' +parentKey + key +'"></label>\
                    </div>\
                    </div>\
                    </div>');
            }
        });
    };

    etoFn.updateColumns = function(columns, subColumns, parentKey) {
        var columnsData = typeof subColumns == 'object' ? subColumns : etoFn.paramsToExport[etoFn.usedSection].columns;

        $.each(columnsData, function (key, status) {
            var column = key;

            if(typeof parentKey != 'undefined') {
                column = parentKey + '.' + column
            }
            if(typeof status == 'object') {
                etoFn.updateColumns(columns, status, column);
            } else {
                if (ETO.indexObjectValue(columns,column) === null) {
                    status = false
                } else {
                    status = ETO.indexObjectValue(columns,column)
                }

                ETO.setToObjectByPath(ETO.Export.paramsToExport[etoFn.usedSection].columns, column, status);
            }
        });
    };

    etoFn.checkAll = function(inputsSelector, inputAllSelector, single) {
        var inputs = $(inputsSelector),
            inputAll = $(inputAllSelector),
            inputsLength = inputs.length,
            inputsLengthChecked = $(inputsSelector+':checked').length;

        if(single === true) {
            if (inputsLength === inputsLengthChecked) {
                inputAll.attr('checked', true);
            } else {
                inputAll.attr('checked', false);
            }
        } else {
            if( inputAll.attr('checked') == 'checked') {
                inputs.attr('checked', true);
            } else {
                inputs.attr('checked', false);
            }
            inputs.first().change()
        }
    };

    etoFn.downloadZip = function(downloadUrl, format) {
        $.LoadingOverlay('show');

        etoFn.paramsToExport.toExport = {};
        $('input.eto-export-section').each(function () {
            var section = $(this).closest('tr').data('etoSection');
            etoFn.paramsToExport.toExport[section] = $(this).attr('checked') == 'checked' ? 1 : 0;
        });

        var xhttp = new XMLHttpRequest();
        // Post data to URL which handles post request
        xhttp.open("POST", downloadUrl);
        xhttp.setRequestHeader("Content-Type", "application/json");
        xhttp.setRequestHeader( 'X-CSRF-TOKEN', ETO.config.csrfToken);
        // You should set responseType as blob for binary responses
        xhttp.responseType = 'blob';
        xhttp.onreadystatechange = function() {
            if (xhttp.status === 404) {
                ETO.swalWithBootstrapButtons({type: 'error', title: 'Bad params to generate files, please try again.'});
            } else if (xhttp.status === 403 ) {
                ETO.swalWithBootstrapButtons({type: 'error', title: 'You do not have permission to export data.'});
            } else if (xhttp.status >= 500 ) {
                ETO.swalWithBootstrapButtons({type: 'error', title: 'Something went wrong, please try again.'});
            }
        };
        xhttp.onload = function(e) {
            var a,
                contentDispo = this.getResponseHeader('Content-Disposition');

            if(contentDispo !== null) {
                var fileName = contentDispo.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/)[1].replace(/^\"+|\"+$/gm, '');

                if (xhttp.readyState === 4 && xhttp.status === 200) {
                    // Trick for making downloadable link
                    a = document.createElement('a');
                    a.href = window.URL.createObjectURL(xhttp.response);
                    // Give filename you wish to download
                    a.download = fileName;
                    a.style.display = 'none';
                    document.body.appendChild(a);
                    a.click();
                }
            }

            $.LoadingOverlay('hide');
        };
        xhttp.send(JSON.stringify(etoFn.paramsToExport));
    };

    return etoFn;
}();
