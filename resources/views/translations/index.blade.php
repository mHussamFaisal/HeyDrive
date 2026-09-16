@extends('admin.index')

@section('title', trans('translations.page_title'))
@section('subtitle', /*'<i class="fa fa-language"></i> '.*/ trans('translations.page_title'))

@section('subheader')
    <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/dataTables.bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/extensions/Buttons/css/buttons.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/extensions/Buttons/css/buttons.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/extensions/ColReorder/css/colReorder.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/extensions/ColReorder/css/colReorder.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','x-editable/css/bootstrap-editable.css') }}">
@stop

@section('subcontent')
    <div class="box-header eto-box-header">
        <h4 class="box-title">
            {{ trans('translations.page_title') }}
        </h4>

        <div class="box-tools pull-right">
            <div class="eto-field eto-field-search-all">
                <div class="eto-btn-search-all" style="float:left; margin:0; height:auto;">{{ trans('translations.form.search_all') }}</div>
                <div class="eto-field-value" style="float:left; margin:0 0 0 5px;">
                    <input id="search-all" class="eto-js-inputs eto-js-search-all" data-eto-name="search_all" value="all" type="checkbox" style="margin:0;">
                </div>
            </div>
            <div class="eto-field eto-field-search">
                <div class="eto-field-value clearfix">
                    <input id="search" class="eto-js-inputs eto-js-search" data-eto-name="search" value="" placeholder=" {{ trans('activity.form.search') }}" type="text">
                </div>
                <div class="eto-field-placeholder hidden">{{ trans('activity.form.search') }}</div>
            </div>
        </div>
    </div>
    <div class="eto-translations">
        <div class="row eto-translations-filters">
            <div class="col-sm-4">
                <label for="searchByKey" class="control-label">
                    {{ trans('translations.group') }}
                </label>
                <select class="form-control eto-locales-group">
                    @foreach($groups as $group)
                        <option value="{{ str_replace('.php', '', $group) }}">{{ trans('translations.groups.' . str_replace('.php', '', $group) ) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-4">
                <label for="searchByKey" class="control-label">
                    {{ trans('translations.origin') }}
                </label>
                <select class="form-control eto-locales-from">
                    @foreach(config('app.locales') as $locale)
                        <option value="{{ $locale['code'] }}"@if (config('fallback_locale') == $locale['code']) checked="checked"@endif>{{ $locale['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-4">
                <label for="search" class="control-label">
                    {{ trans('translations.translatedLocale') }}
                </label>
                <select class="form-control eto-locales-to">
                    @foreach(config('app.locales') as $locale)
                        <option value="{{ $locale['code'] }}"@if (config('locale') == $locale['code']) checked="checked"@endif>{{ $locale['name'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="pageContainer">
            <table class="table table-striped1 table-bordered table-hover eto-translations-list">
                <thead>
                <tr>
                    <th>{{ trans('translations.actions') }}</th>
                    <th>{{ trans('translations.key') }}</th>
                    <th>{{ trans('translations.origin') }}</th>
                    <th>{{ trans('translations.translatedLocale') }}</th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    <div class="eto-modal-edit-translation modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4>{{ trans('translations.edit') }}</h4>
                </div>
                <form method="post" action="{{ route('translations.save') }}" class="form-horizontal eto-edit-form">
                    <input type="hidden" name="key">
                    <input type="hidden" name="group">
                    <div class="modal-body">
                        <div class="eto-translations-values">
                            @foreach(config('app.locales') as $locale)
                                <div class="eto-lang-field">
                                    <label for="" class="control-label">
                                        {{ $locale['name'] }}
                                    </label>
                                    <input type="hidden" name="code[]" value="{{ $locale['code'] }}">
                                    <textarea class="form-control eto-input-value" type="text" name="value[]" rows="2" data-eto-code="{{ $locale['code'] }}"></textarea>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-default pull-left eto-edit-submit" data-dismiss="modal">Save</button>
                        <button type="button" class="btn btn-link pull-left eto-reset-all">Reset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('subfooter')
    <script src="{{ asset_url('plugins','data-tables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset_url('plugins','data-tables/dataTables.bootstrap.min.js') }}"></script>
    <script src="{{ asset_url('plugins','data-tables/extensions/Buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset_url('plugins','data-tables/extensions/Buttons/js/buttons.bootstrap.min.js') }}"></script>
    <script src="{{ asset_url('plugins','data-tables/extensions/Buttons/js/buttons.colVis.min.js') }}"></script>
    <script src="{{ asset_url('plugins','data-tables/extensions/Buttons/js/buttons.server-side.js') }}"></script>
    <script src="{{ asset_url('plugins','data-tables/extensions/ColReorder/js/dataTables.colReorder.min.js') }}"></script>
    <script src="{{ asset_url('plugins','select2/select2.full.min.js') }}"></script>
    <script src="{{ asset_url('plugins','x-editable/js/bootstrap-editable.min.js') }}"></script>

    @include('layouts.eto-js')

    <script>
        function getAtribute(html) {
            var regex = /(\:\w+|\{\w+\})/gm,
                m,
                matches = [];

            while ((m = regex.exec(html)) !== null) {
                // This is necessary to avoid infinite loops with zero-width matches
                if (m.index === regex.lastIndex) {
                    regex.lastIndex++;
                }
                // The result can be accessed through the `m`-variable.
                m.forEach(function(match, groupIndex) {
                    var isEl = matches.filter(function(element, index, array) {
                        // console.log(element, match, groupIndex);
                        return (element == match);
                    });

                    if (isEl.length === 0) {
                        matches.push(match);
                    }
                });
            }

            return matches;
        }

        function renderEditable(code, datatable) {
            $('.eto-translate-col').each(function(){
                var el = $(this),
                    key = el.data('etoKey');

                el.editable({
                    mode: 'inline', // popup
                    type: 'textarea',
                    url: ETO.config.appPath + '/translations/save',
                    pk: 1,
                    title: '{{ trans('translations.enter_translation') }}',
                    emptytext: '{{ trans('translations.enter_translation') }}',
                    ajaxOptions: {
                        headers: {
                            'X-CSRF-TOKEN': ETO.config.csrfToken
                        },
                        type: 'POST',
                        dataType: 'json',
                    },
                    validate: function(value) {
                        var force = el.closest('td').find('#eto-force'),
                            attrFrom = el.closest('td').data('attrFrom'),
                            attributes = getAtribute(value),
                            notUsed = [];

                        if(force.length === 0) {
                            el.closest('td').find('.editable-buttons').append('<label for="eto-force"><input id="eto-force" type="checkbox"/> '+ETO.lang.translations.force_save+'</label>');
                            force = el.closest('td').find('#eto-force');
                        }

                        if(force.length > 0 && force.attr('checked') == 'checked') {
                            // return true;
                        } else {
                            $.each(attrFrom, function (k, v) {
                                if (attributes.indexOf(v) === -1) {
                                    notUsed.push(v);
                                }
                            });

                            if (notUsed.length > 0) {
                                return ETO.lang.translations.no_atribute_error + ': ' + notUsed.join(', ');
                            }
                        }
                    },
                    error: function(response, newValue) {
                        console.log(response, newValue, $(this));
                    },
                    params: function(params) {
                        var value = [],
                            force = false,
                            forceInput = el.closest('td').find('#eto-force'),
                            attrFrom = el.closest('td').data('attrFrom'),
                            attributes = getAtribute(value),
                            notUsed = [];

                        if(forceInput.length > 0 && forceInput.attr('checked') == 'checked') {
                            force = true;
                            $.each(attrFrom, function (k, v) {
                                if (attributes.indexOf(v) === -1) {
                                    notUsed.push(v);
                                }
                            });
                        }
                        value.push(params.value);
                        return {key: key, code: [code], value: value, group: $('.eto-locales-group').val(), force: force, notUsed: notUsed};
                    },
                    success: function(data, html) {
                        var attributes = getAtribute(html),
                            container = el.closest('.eto-locale-to');

                        html = ETO.escapeHtml(html);
                        attributes.forEach(function(attr, index) {
                            html = html.replace(attr, '<span class="eto-translations-attribute">' + attr + '</span>');
                        });

                        if (html != '') {
                            setTimeout(function() {
                                el.html(html);
                            }, 0);
                        }

                        if (container.find('.eto-translations-btn-clear').length === 0) {
                            container.append('<span class="eto-translations-btn-clear" title="Reset"><i class="fa fa-trash-o"></i></span>');
                        }

                        if(typeof datatable == 'undefined') {
                            datatable = dTable = new $.fn.dataTable.Api( ".eto-translations-list" )
                        }

                        drawDatatable(datatable);

                    }
                });
            });
        }

        function drawDatatable(datatable) {
            datatable.ajax.reload(null, false);
        }

        function getTranslations(key) {
            var translations = {};

            ETO.ajax('translations/get', {
                delay: 400,
                data: {
                    key: key,
                    group: $('.eto-locales-group').val(),
                },
                success: function(results) {
                    translations = results;
                }
            });

            return translations;
        }

        $(function() {
            if (ETO.model === false) {
                ETO.init({ config: ['config_site'], lang: ['user', 'translations'] }, 'translations');
            }

            ETO.updateFormPlaceholderInit($('.eto-field-search'));
            $('.eto-js-search-all').attr('checked', false);

            var edidNow = false,
                tableOptionsn = {
                    processing: true,
                    serverSide: true,
                    deferLoading: 0,
                    scrollX: true,
                    ajax: {
                        headers : {
                            'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
                        },
                        url: EasyTaxiOffice.appPath +'/translations/list',
                        method: 'POST',
                        dataType: 'json',
                        cache: false,
                        // delay: 400,
                        data: function(d) {
                            d.from = $('.eto-locales-from').val();
                            d.to = $('.eto-locales-to').val();
                            d.group = $('.eto-locales-group').val();
                            d.allFiles = $('.eto-js-search-all').attr('checked') == 'checked' ? 1 : 0;
                        },
                    },
                    columns: [
                    @if (auth()->user()->hasPermission('admin.translations.edit'))
                    {
                        title: ETO.lang.translations.actions,
                        data: 'actions',
                        className: 'eto-locale-actions',
                        orderable: false,
                        width: '5%',
                        visible: false,
                        searchable: false
                    },
                    @endif
                    {
                        title: ETO.lang.translations.key,
                        data: 'key',
                        className: 'eto-locale-key',
                        visible: false,
                        searchable: false
                    }, {
                        title: ETO.lang.translations.origin,
                        data: 'from',
                        className: 'eto-locale-from',
                        visible: true,
                        width: '30%',
                    }, {
                        title: ETO.lang.translatedLocale,
                        data: 'to',
                        className: 'eto-locale-to',
                        visible: true,
                        width: '30%',
                    }],
                    createdRow: function( row, data, dataIndex ) {
                        if(data.isTranslate === true) {
                            $(row).find('.eto-locale-to').addClass('eto-translations-has-trans');
                        } else {
                            $(row).find('.eto-locale-to').removeClass('eto-translations-has-trans');
                        }
                        $(row).find('.eto-locale-to').data('attrFrom', getAtribute(data.from))
                    },
                    columnDefs: [{
                        targets: 0,
                        data: null,
                        render: function(data, type, row) {
                            return '<button type="button" class="btn btn-xs btn-default eto-btn-edit-translate" data-eto-key="'+row.key+'" data-toggle="modal" data-target=".eto-modal-edit-translation"> \
                            {{ trans('translations.edit') }}\
                        </button>';
                        }
                    }, {
                        targets: 2,
                        render: function(data, type, row) {
                            var attributes = getAtribute(data);

                            data = ETO.escapeHtml(data);
                            attributes.forEach(function(attr, index) {
                                data = data.replace(attr, '<span class="eto-translations-attribute">' + attr + '</span>');
                            });

                            return '<span>'+ data +'</span>';
                        }
                    }, {
                        targets: 3,
                        render: function(data, type, row) {
                            var attributes = getAtribute(data),
                                attrFrom = getAtribute(row.from),
                                attrInfo = attributes.length < attrFrom.length
                                    ? '<span class="eto-translations-attr-info" title="'+ETO.lang.translations.empty_attributes+attrFrom.join(', ')+'"><i class="fa fa-info-circle"></i></span>'
                                    : '',
                                deleteBtn = row.isTranslate === true && ETO.hasPermission('admin.translations.destroy')
                                    ? '<span class="eto-translations-btn-clear" title="Reset"><i class="fa fa-trash-o"></i></span>'
                                    : '';


                            data = ETO.escapeHtml(data);
                            attributes.forEach(function(attr, index) {
                                data = data.replace(attr, '<span class="eto-translations-attribute">' + attr + '</span>');
                            });

                            return '<span class="eto-translate-col" data-eto-key="'+row.key+'" >'+ data +'</span>' + attrInfo + deleteBtn;
                        }
                    }],
                    dom: ETO.datatable.domForTranslations,
                    buttons: ETO.datatable.buttonsForTranslations(),
                    searching: true,
                    ordering: true,
                    lengthChange: true,
                    info: true,
                    autoWidth: false,
                    stateSave: true,
                    stateDuration: 0,
                    order: [],
                    pageLength: 50,
                    lengthMenu: ETO.datatable.lengthMenu,
                    language: ETO.datatable.language(ETO.lang.user),
                    infoCallback: function( settings, start, end, max, total, pre ) {
                        return '<i class="ion-ios-information-outline" title="'+ pre +'"></i>';
                    },
                    drawCallback: function(settings) {
                        if(ETO.hasPermission('admin.translations.edit')) {
                            renderEditable($('.eto-locales-to').val());
                        }
                    }
                };

            if (!ETO.hasPermission('admin.translations.edit')) {
                delete tableOptionsn.columnDefs[0];
            }

            var datatable = $('.eto-translations-list').DataTable(tableOptionsn).search('').draw();

            if(!ETO.hasPermission('admin.translations.destroy')) {
                $('.eto-clear-cache').hide();
            }

            $('body').on('click', '.eto-btn-edit-translate', function(e) {
                var key = $(this).data('etoKey'),
                    formContainer = $('.eto-edit-form');

                edidNow = getTranslations(key);

                formContainer.find('textarea').val('');
                formContainer.find('[name="key"]').val(key);
                formContainer.find('[name="group"]').val($('.eto-locales-group').val());
                if(ETO.hasPermission('admin.translations.destroy')) {
                    formContainer.find('.eto-translations-btn-clear-group-item').remove();
                }

                if (ETO.hasPermission('admin.translations.destroy') && typeof edidNow != 'undefined' && Object.keys(edidNow).length > 0 && typeof edidNow.text != "undefined" && Object.keys(edidNow.text).length > 0) {
                    for (var i in edidNow.text) {
                        var textarea = formContainer.find('[data-eto-code="'+i+'"]');
                        textarea.val(edidNow.text[i]);
                        textarea.closest('.eto-lang-field').find('label').after('<span class="eto-translations-btn-clear-group-item" title="Reset"><i class="fa fa-trash-o"></i></span>');
                    }
                }
                formContainer.find('textarea').each(function(key, field) {
                    var code = $(this).closest('.eto-lang-field').find('[name="code[]"]').val();
                    if ($(this).val() == '') {
                        $(this).val(edidNow.file[code]);
                    }
                });

            })
            .on('click', '.eto-btn-search-all', function(e) {
                $('.eto-js-search-all').click();

                if($('.eto-js-search').val() != '') {
                    datatable.search($('.eto-js-search').val()).draw();
                }
            })
            .on('change', '.eto-locales-from, .eto-locales-to, .eto-locales-group', function(e) {
                $('.eto-js-search').val('');
                drawDatatable(datatable);
            })
            .on('click', '.eto-translations-btn-clear', function() {
                var el = $(this),
                    from = $('.eto-locales-from').val(),
                    code = $('.eto-locales-to').val(),
                    group = $('.eto-locales-group').val(),
                    trans = el.closest('.eto-locale-to').find('.eto-translate-col'),
                    key = trans.data('etoKey');

                ETO.ajax('translations/clear', {
                    delay: 400,
                    data: {
                        from: from,
                        key: key,
                        code: code,
                        group: group,
                    },
                    success: function(results) {
                        if (typeof results.origin == 'string') {
                            var attributes = getAtribute(results.origin);

                            var html = ETO.escapeHtml( results.origin );
                            attributes.forEach(function(attr, index) {
                                html = html.replace(attr, '<span class="eto-translations-attribute">' + attr + '</span>');
                            });

                            el.remove();
                            trans.html(html);
                            drawDatatable(datatable);
                        }
                    }
                });
            })
            .on('keyup', '.eto-js-search', ETO.delayCallback(function(e) {
                datatable.search($(this).val()).draw();
            }, 500))
            .on('click', '.eto-edit-submit', function(e) {
                e.preventDefault();
                $.LoadingOverlay('show');
                ETO.ajax('translations/save', {
                    data: $(this).closest('form').serialize(),
                    success: function(data) {
                        drawDatatable(datatable);
                        edidNow = false;
                    },
                    complete: function(data) {
                        $.LoadingOverlay('hide');
                    },
                    error: function(data) {
                        $.LoadingOverlay('hide');
                    }
                });
            })
            .on('click', '.eto-clear-cache', function(e) {
                e.preventDefault();
                $.LoadingOverlay('show');
                ETO.ajax('translations/clearCache', {
                    data: $(this).closest('form').serialize(),
                    success: function(data) {
                        drawDatatable(datatable);
                        edidNow = false;
                    },
                    complete: function(data) {
                        $.LoadingOverlay('hide');
                    },
                    error: function(data) {
                        $.LoadingOverlay('hide');
                    }
                });
            })
            .on('click', '.eto-remove-translations', function(e) {
                e.preventDefault();
                $.LoadingOverlay('show');
                ETO.ajax('translations/clearTranslations', {
                    data: $(this).closest('form').serialize(),
                    success: function(data) {
                        drawDatatable(datatable);
                        edidNow = false;
                    },
                    complete: function(data) {
                        $.LoadingOverlay('hide');
                    },
                    error: function(data) {
                        $.LoadingOverlay('hide');
                    }
                });
            })
            .on('click', '.eto-translations-btn-clear-group-item', function(e) {
                var textarea = $(this).closest('.eto-lang-field').find('textarea'),
                    code = $(this).closest('.eto-lang-field').find('[name="code[]"]').val();

                textarea.val(edidNow.file[code]);
                $(this).removeClass('eto-translate-edited');
                $(this).remove();
            })
            .on('keyup', '.eto-input-value', function(e) {
                var containet = $(this).closest('.eto-lang-field');
                if(ETO.hasPermission('admin.translations.destroy') && containet.find('.eto-translations-btn-clear-group-item').length === 0) {
                    containet.find('label').after('<span class="eto-translations-btn-clear-group-item" title="Reset"><i class="fa fa-trash-o"></i></span>');
                }
            })
            .on('click', '.eto-reset-all', function() {
                if(ETO.hasPermission('admin.translations.destroy')) {
                    $('.eto-translations-btn-clear-group-item').click();
                }
            });
        });
    </script>
@stop
