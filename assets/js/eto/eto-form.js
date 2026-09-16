

ETO.Form = function() {
    var etoFn = {};

    etoFn.config = {
        init: ['page', 'icons', 'routes', 'config_site'],
        lang: ['user', 'booking'],
        runValidation: false,
        prevClickedObject: false,
        objectForm: false,
        fieldMarkupTmp: false,
        plugins: {
            select2: {
                data: {},
                height: 'auto',
                placeholder: null,
                cache: true,
                tags: false,
                minimumResultsForSearch: 0,
                closeOnSelect: true,
                dropdownAutoWidth: true,
                // allowClear: true,
                escapeMarkup: function(markup) {
                    return markup;
                },
                createTag: function(params) {
                    return etoFn.generateTagsSelect2(params, this.$element);
                },
            },
        },
        defaults: {
            settings: {
                sectionOrder: false,
                resizeAction: false,
                advanceOpen: true,
            },
            section: {
                sectionGlobalHeader: true,
                sectionParent: false,
                sectionIcon: false,
                sectionLabel: false, // false | STRING - generate or no label for section
                sectionClass: false,
                sectionHide: false,
                sectionAutoRemove: false, // if true then remove box when don.t have required values, and have button for delete
                groupIcon: false,
                groupLabel: false, // false | STRING - generate or no label for group
                groupClass: false,
                groupOrder: false, // drag and drop
                groupAutoOpen: false,
                groupAddedOpen: false,
                summaryIcon: false,
                summaryIconRevert: false,
                summaryLabel: false, // false | STRING - generate or no label for group
                summaryPlaceholder: false,
                summaryPlaceholderAlt: false,
                summaryTooltip: false,
                summarySeparator: false,
                summaryClass: false,
                tooltipTitle: '',
                customHtml: {},
                buttons: {},
            },
            field: {
                // field attributes
                type: 'text', // text | checkbox | number | email | select | textarea
                valueConverterType: false,
                value: false,
                class: false,
                jsClass: false,
                id: false,
                placeholder: false,
                placeholderOption: false,
                placeholderAlt: false,
                title: false,
                checked: false,
                isRequired: false,
                isFocus: true,
                selectAutoOpen: true, // false | true | always
                // plugins config
                callback: {}, // [PLUGIN]: { param1: '', param2: '' }
                visible: true,
                label: false,
                icon: false,
                fieldIconRevert: false,
                iconChecked: false,
                errorMessage: false,
                help: false, // false | string with help information
                isAdvance: false,
                buttons: {},
                classAdd: false,
                validate: {
                    isRequired: false,
                    isDate: false,
                    isEmail: false,
                    isPhone : false,
                    isNumber: false,
                    isInteger: false,
                    isFloat: false,
                    minLength: false,
                    maxLength: false,
                    errorMessage: false,
                },
            },
            callback: {
                beforePlugin: false, // set value to field befor or after initialize plugin
                source: {},
                sourceFrom: 'config',
                sourceAddValue: false,
                createSource: false,
                step: 1,
                max: 100000000000000,
                min: 0,
                value: false,
            },
            button: {
                icon: false,
                text: false,
                class: false,
                tooltip: false,
                popover: false,
                tagName: 'span',
                attributes: {
                    confirm: false
                }
            },
            display: { // summary | tooltip
                displayFormat: false,
                class: false, // false |  string class - visualization
                minValue: false,
                maxLength: false, // false | integer
                truncateSign: false,// false |  string  - set to end truncated
                label: false, // Object key | string | false
                icon: false, // false | string class
                containerOrder: false,
                type: 'text', // text | icon
                // prepare geting values to summary and tooltip
                sourceTo: false, // false | string - name object or function
                sourceToIsMethod: false, // false | true - sourceTo is a function or not
                visible: true,
                place: false,
            },
        },
        form: {
            values: {
                /**
                 * a set of ready-made value grouped
                 * each value group will have its ID (TIMESTAMP)
                 * each ID will refer to a specific summary in the view
                 *
                 * (routeNo){
                 *      (SECTION): {
                 *        (TIMESTAMP): {
                 *            address: '',
                 *            complete: '',
                 *            lat: '',
                 *            lng: '',
                 *            place_id: '',
                 *            type: '',,
                 *        },
                 *        (TIMESTAMP): {
                 *            address: '',
                 *            complete: '',
                 *            lat: '',
                 *            lng: '',
                 *            place_id: '',
                 *            type: '',,
                 *        }
                 *     },
                 *     (SECTION): {
                 *       (TIMESTAMP): {
                 *           name: 'hhh'
                 *       }
                 *     }
                 * }
                 *
                 * <span id="(TIMESTAMP)" class="eto-summary" data-eto-section="(SECTION)"> __SUMMARY__ </span>
                 *
                 */
            }
        },
    };

    etoFn.markup = {
        field: {
            input: '<input__ID__ class="eto-js-inputs __CLASS__" data-eto-name="__DNAME__"  __VALUE____PLACEHOLDER__ type="__TYPE__"__REQUIRED__ autocomplete="'+ (ETO.getBrowserName() == 'Chrome' ? 'disabled' : 'off') +'">',
            radioCheckbox: '<div class="eto-field-type-__TYPE__">' +
                '<label class="clearfix">' +
                '<input type="__TYPE__" class="eto-js-inputs eto-field-type-__TYPE__-input __CLASS__"  data-eto-name="__DNAME__" name="__NAME__"__VALUE____REQUIRED____CHECKED__>' +
                '<span class="eto-field-type-__TYPE__-icon">' +
                '__ICON__' +
                '</span>' +
                '<span class="eto-field-type-__TYPE__-label">__LABEL__</span>' +
                '</label>' +
                '</div>',
            textarea: '<textarea__ID__ class="eto-js-inputs __CLASS__ " data-eto-name="__DNAME__" __PLACEHOLDER__>__VALUE__</textarea>',
            select: '<select__ID__ class="eto-js-inputs __CLASS__" data-eto-name="__DNAME__" __VALUE____REQUIRED__>__LABEL__</select>',
        },
        box: {
            checkboxButton: function(itemsString, classes, label, icon) {
                icon = icon !== false ? '<i class="' + icon + '"></i>' : '';
                label = label !== false ? label : '';
                var html = '<div class="' + classes + '">' +
                        '<div class="eto-checkboxes-hidden hidden">' +
                        itemsString +
                        '</div>' +
                        '<span class="eto-field-type-button">'+
                        '<span class="eto-field-type-button-icon">'+
                        icon +
                        '</span>' +
                        '<span class="eto-field-type-button-label">'+
                        label +
                        '</span>' +
                        '</span>' +
                        '</div>';

                return html
            },
            summary: function(data, elementName, index, formId, routeId) {
                var formContainer = $('#'+formId);

                $.each(data.summary, function(elementName, field) {
                    var displayConf = $.extend(true, {}, etoFn.config.defaults.display);
                    data.summary[elementName] = $.extend(true, displayConf, field);
                });
                $.each(data.tooltip, function(elementName, field) {
                    var displayConf = $.extend(true, {}, etoFn.config.defaults.display);
                    data.tooltip[elementName] = $.extend(true, displayConf, field);
                });

                index = typeof index != 'undefined' ? index : etoFn.getLastIndexElement(formContainer.find('.eto-section-'+elementName+' .eto-route-'+routeId).find('.eto-groups'));

                var uid = ETO.uid(),
                    groupHeaderIcon = data.groupIcon !== false ? '<span class="eto-group-header-icon"><i class="' + data.groupIcon + '"></i></span>' : '',
                    groupHeaderLabel = data.groupLabel !== false ? '<span class="eto-group-header-label"> ' + data.groupLabel + ' </span>' : '',
                    group = groupHeaderIcon.length > 0 || groupHeaderLabel.length > 0
                        ? '<div class="eto-group-header">' + groupHeaderIcon + groupHeaderLabel + '</div>' : '',
                    summaryIcon = data.summaryIcon !== false ? '<span class="eto-summary-icon"><i class="' + data.summaryIcon + '"></i></span>' : '',
                    summaryLabel= data.summaryLabel !== false ? '<span class="eto-summary-label">' + data.summaryLabel + '</span>' : '',
                    summaryPlaceholder = data.summaryPlaceholder !== false ? data.summaryPlaceholder : '',
                    summaryClass = data.summaryClass !== false ? data.summaryClass : '',
                    buttonsHtml = typeof data.buttons.summary != 'undefined'
                        ? etoFn.createButtons(data.buttons.summary, 'summary') : '',
                    autoRemove = data.sectionAutoRemove === true ? 1 : 0;

                if (summaryPlaceholder === true) { summaryPlaceholder = data.groupLabel; }

                summaryPlaceholder = summaryPlaceholder != '' ? '<span class="eto-summary-placeholder">'+ summaryPlaceholder +'</span>' : '';
                buttonsHtml = buttonsHtml != '' ? '<span class="eto-summary-buttons clearfix">' + buttonsHtml + '</span>' : '';

                summaryClass += summaryIcon != '' ? ' eto-summary-has-icon' : '';
                summaryClass += summaryLabel != '' ? ' eto-summary-has-label' : '';
                summaryClass += summaryPlaceholder != '' ? ' eto-summary-has-placeholder' : '';

                var summaryIconWithLabel = summaryIcon + summaryLabel;
                if (data.summaryIconRevert === true) {
                    summaryIconWithLabel = summaryLabel + summaryIcon;
                }

                return group + '<div class="eto-summary '+ summaryClass +' clearfix"><span id="'+uid+'" class="eto-summary-link clearfix" data-eto-section="'+elementName+'" data-eto-index="'+index+'" data-eto-autoremove="'+autoRemove+'">' + summaryIconWithLabel + '<span class="eto-summary-value clearfix"></span>'+ summaryPlaceholder +'</span>' + buttonsHtml + '</div>';
            }
        }
    };

    etoFn.init = function(config) {
        ETO.extendConfig(this, config, 'form');

        var eventClick = ETO.isMobile ? 'doubleTap' : 'click';

        $.fn.modal.Constructor.prototype.enforceFocus = function() {};

        $('body').on('mouseover tap', '.eto-summary-link', function(e) {
            $('.webui-popover').not($('#'+$(this).data('target'))).hide();
            $('#'+$(this).data('target')).replaceClass('out', 'in').show();
        })

        .on('mouseover tap', '.eto-form', function(e) {
            var el = $(e.target),
                group = el.closest('.eto-group');

            if (group.length === 0 && !el.hasClass('eto-group')) {
                $('.webui-popover').hide();
            }
        })

        .on('click', '.eto-style-fieldset-popup .eto-summary-link', function(e) {
            etoFn.fieldsetModal(this);
        })

        .on('focus', '.eto-js-inputs', function(e) {
            if (['price', 'cash', 'commission', 'code', 'vehicle_amount'].indexOf($(this).data('etoName')) !== -1) {
                $(this).select();
            }
        })

        .on('blur', '.eto-js-inputs', function(e) {
            etoFn.validateField($(this));
        })

        .on('change', '.eto-js-inputs', function(e) {
            etoFn.setValuesToObject($(this), false);

            if ($(this).closest('.eto-group').hasClass('eto-group-open-always')) {
                var el = $(this).closest('.eto-group').find('.eto-field-value').find('[data-origin-title], [data-title], [title]');
                el.removeAttr('data-toggle');
                el.removeAttr('title');
                el.removeAttr('data-title');
                el.removeAttr('data-origin-title');
                el.removeAttr('data-content');
                el.tooltip('destroy');
                el.webuiPopover('destroy');
                el.popover('destroy');
            }
        })

        .on('click', '.eto-summary-link', function(e) {
            $('.eto-bs-popover').hide();
            // e.preventDefault();
            etoFn.config.prevClickedObject = e;
            etoFn.openFieldset($(this), false);
            e.stopPropagation();
        })

        .on('click', '.eto-summary-btn-delete', function(e) {
            var el = $(this).closest('.eto-group'),
                index = el.find('.eto-summary-link').attr('data-eto-index'),
                group = el.closest('.eto-group'),
                groups = group.closest('.eto-groups'),
                formId = el.closest('.eto-form').attr('id'),
                confirm = el.find('.eto-summary-value').text().length !== 0;

            el.find('[data-toggle="popover"]').popover('hide').popover('destroy');
            el.find('[data-toggle="tooltip"]').tooltip('hide').tooltip('destroy');

            if (($(this).attr('data-eto-confirm') == 'true' && confirm === true)) {
                ETO.swalWithBootstrapButtons({
                    title: 'Are you sure?',
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                })
                .then(function(result){
                    if (result.value) {
                        ETO[etoFn.config.objectForm].Form.destroyFieldObjectValues(formId, group, index, 'remove');

                        setTimeout(function(){
                            if (groups.closest('.eto-section').attr('data-eto-section') == 'item') {
                                ETO[etoFn.config.objectForm].Form.setPriceSummary(formId);
                            }
                            etoFn.enableDisableButton(groups);
                        }, 0);

                        ETO.toast({
                            type: 'success',
                            title: 'Deleted'
                        });
                    }
                });
            }
            else {
                ETO[etoFn.config.objectForm].Form.destroyFieldObjectValues(formId, group, index, 'remove');

                setTimeout(function(){
                    if (groups.closest('.eto-section').attr('data-eto-section') == 'item') {
                        ETO[etoFn.config.objectForm].Form.setPriceSummary(formId);
                    }
                    etoFn.enableDisableButton(groups);
                }, 0);
            }
        })

        .on('click', '.eto-field-btn-clear', function(e) {
            var input = $(this).closest('.eto-field').find('.eto-js-inputs'),
                name = input.attr('data-eto-name');

            if (input.attr('type') == 'checkbox') {input.attr('checked', false);}
            else if (input[0].tagName == 'SELECT') {input.find('option:selected').attr('selected', false);}

            if (input[0].hasAttribute('min')) {input.val(input.attr('min'));}
            else { input.val(''); }
            input.focus();
            input.change();
        })

        .on('click', '.eto-field-btn-advance', function(e) {
            var el = $(this).closest('.eto-group'),
                field = el.find('.eto-fields .eto-field-advance');

            if (field.hasClass('hidden')) {
                field.removeClass('hidden');
                field.find('input').first().focusTextToEnd();
            }
            else {
                field.addClass('hidden');
                field.prev().find('input').first().focusTextToEnd();
            }
        })

        .on('click', '.eto-section-btn-add', function(e) {
            ETO[etoFn.config.objectForm].Form.addNewElementToSection($(this));
            etoFn.config.prevClickedObject = e;
            e.stopPropagation();
        })

        .on('click', '.eto-fieldset-btn-close', function(e) {
            var group = $(this).closest('.eto-group'),
                summaryLinkData = group.find('.eto-summary-link').data(),
                isEmpty = true,
                n = 0,
                inputs = $(this).find('.eto-js-inputs:not([type="hidden"]):not(.eto-js-passenger_from_customer)');

            for (var i in inputs) {
                if (isNaN(i) !== true) {
                    if (null !== $(inputs[i]).val() && $(inputs[i]).val() != '') {
                        if ($(inputs[i]).val().length > 0 || ($(inputs[i]).val().length === 0 && inputs[i].hasAttribute('required') === true && $(inputs[i]).attr('data-eto-name') != 'address')) {
                            if ($(inputs[i]).attr('data-eto-name') == 'item') {
                                ETO.Booking.Form.setPriceSummary($(inputs[i]).closest('.eto-form').attr('id'));
                            }
                            if (inputs[i].tagName != 'SELECT' || (inputs[i].tagName == 'SELECT' && $(inputs[i]).val() != '0')) {
                                n++;
                            }
                        }
                    }
                }
            }
            if (n > 0) { isEmpty = false; }
            etoFn.updateSummary(group, isEmpty);
            etoFn.hideFields(group.closest('.eto-form'));
            if (summaryLinkData.etoAutoremove == 1 ) {
                ETO[etoFn.config.objectForm].Form.checkAndRemove(group);
            }
        })

        .on('click', '.eto-btn-advance', function(e){
            var formContainer = $(this).closest('.eto-form');

            if (formContainer.find('.eto-section-advance').hasClass('hidden')) {
                formContainer.find('.eto-section-advance').removeClass('hidden');
            }
            else {
                formContainer.find('.eto-section-advance').addClass('hidden');
            }
        })

        .on('click', '.eto-field-type-button', function(e){
            var input = $(this).parent().find('input:first');

            input.attr('checked', true).change();
            input.attr('checked', false);
        })

        .on('click', '.eto-form', function(e) {
            var n = 0,
                isEmpty = true;

            if ( (!$.browser.msie && e.button == 0) || ($.browser.msie && e.button == 1) ) {
                if (($(e.target).hasClass('eto-fieldset') === false && ($(e.target).closest('.eto-fieldset').length === 0 && $(e.target).closest('.eto-field-buttons').length === 0) && $(e.target.parentElement).hasClass('eto-summary-link') === false) || $(e.target.parentElement).hasClass('eto-fieldset-btn-close') === true) {

                    if ($(etoFn.config.prevClickedObject.target).hasClass('eto-section-btn-add') === true || $(etoFn.config.prevClickedObject.target).closest('.eto-section-btn-add').length > 0) {
                        var group = $(etoFn.config.prevClickedObject.target).closest('.eto-route').find('.eto-fields').closest('.eto-group.eto-group-open'),
                            summaryLinkData = group.find('.eto-summary-link').data();

                        if (typeof summaryLinkData != 'undefined' && typeof summaryLinkData.etoAutoremove != 'undefined' && summaryLinkData.etoAutoremove == 1 ) {
                            ETO[etoFn.config.objectForm].Form.checkAndRemove(group);
                        }
                    }

                    if ($(etoFn.config.prevClickedObject.target).hasClass('eto-group') === true || $(etoFn.config.prevClickedObject.target).closest('.eto-group').length > 0) {
                        var inputs = $(etoFn.config.prevClickedObject.target).closest('.eto-group').find('.eto-js-inputs:not([type="hidden"]):not(.eto-js-passenger_from_customer)');

                        for (var i in inputs) {
                            if (isNaN(i) !== true) {
                                if (null !== $(inputs[i]).val() && $(inputs[i]).val() != '') {
                                    if ($(inputs[i]).val().length > 0 || ($(inputs[i]).val().length === 0 && inputs[i].hasAttribute('required') === true && $(inputs[i]).attr('data-eto-name') != 'address')) {
                                        if ($(inputs[i]).attr('data-eto-name') == 'item') {
                                            ETO.Booking.Form.setPriceSummary($(inputs[i]).closest('.eto-form').attr('id'));
                                        }
                                        if (inputs[i].tagName != 'SELECT' || (inputs[i].tagName == 'SELECT' && $(inputs[i]).val() != '0')) {
                                            n++;
                                        }
                                    }
                                }
                            }
                        }
                        if (n > 0) { isEmpty = false; }
                        etoFn.updateSummary($(etoFn.config.prevClickedObject.target).closest('.eto-group'), isEmpty);
                    }
                }
            }

            etoFn.hideFields($(e.target).closest('.eto-form'), $(e.target).closest('.eto-group'));
            etoFn.config.prevClickedObject = e;
        });

        $(document).on('keyup',function(e) {
            if (e.keyCode == 27) {
                $('.eto-form').each(function(fkey, form) {
                    $(form).find('.eto-group-open').each(function(gkey, group) {
                        etoFn.hideFields($(form), $(group));
                    });
                });
            }
        });
    };

    etoFn.fieldsetModal = function(form) {
        var that = $(form),
            formContainer = that.closest('.eto-form'),
            el = that.closest('.eto-group'),
            offset = that.offset();

        // WebuiPopovers.hideAll();
        if (ETO.isMobile) { return false; }

        if (formContainer.hasClass('eto-modal') === false) {
            el.find('.eto-fieldset').css({
                'position': 'fixed',
                'top': (offset.top - 1) +'px',
                'left': (offset.left - 1) +'px',
                'z-index': '999',
            });
        }
    };

    etoFn.formResizeUpdate = function(that) {
        var el = $(that),
            width = el.width();

        el.removeClass('eto-form-xs eto-form-sm eto-form-md eto-form-lg');
        if (width <= 300) {
            el.addClass('eto-form-xs');
        }
        else if (width > 300 && width <= 500) {
            el.addClass('eto-form-sm');
        }
        else if (width > 500 && width <= 768) {
            el.addClass('eto-form-md');
        }
        else if (width > 768) {
            el.addClass('eto-form-lg');
        }
    };

    etoFn.formResize = function() {
        var el = $('.eto-form');

        el.on('resize', function() {
            el.each(function(key, form) {
                etoFn.formResizeUpdate($(form));
            });
        });

        el.each(function(key, form) {
            etoFn.formResizeUpdate($(form));
        });
    };

    /**
     * Generate summary from values
     */
    etoFn.parseValuesToView = function(container, section, conf, setValues, isUpdated) {
        if (typeof conf == 'undefined' || typeof setValues == 'undefined' || isUpdated === true) { return setValues; }

        $.each(conf, function(fieldName, itemConf){
            if (typeof itemConf == 'undefined') { return; }
            var confDefault = $.extend(true, {}, etoFn.config.defaults.display);

            itemConf = $.extend(true, confDefault, itemConf);

            if (typeof setValues[fieldName] != 'undefined') {
                if (itemConf.valueConverterType == 'id') {
                    if (itemConf.sourceTo == 'config' && typeof ETO.settings(section+'.data.'+fieldName) != 'undefined') {
                        setValues[fieldName + '_text_selected'] = ETO.settings(section+'.data.'+fieldName+'.name');
                    }
                    else if (itemConf.sourceTo == 'paymentType') {
                        var object = ETO.Form.findInObject(ETO.settings(section+'.data'), setValues[fieldName]);
                        setValues.payment_type_text = object.name;
                    }
                    else if (itemConf.sourceToIsMethod === true) {
                        return ETO[etoFn.config.objectForm].Form[itemConf.sourceTo](container, section, conf, setValues);
                    }
                }
                else if (itemConf.valueConverterType == 'type') {
                    if (itemConf.sourceTo == 'customFields' && typeof ETO.fields[etoFn.config.objectForm.toLowerCase()].data[setValues['item']] != 'undefined') {
                        setValues[fieldName + '_type'] = ETO.fields[etoFn.config.objectForm.toLowerCase()].data[setValues['item']].type;

                        setValues.type_dispaly_name
                            = setValues.override_name != '' && typeof setValues.override_name != 'undefined'
                            ? setValues.override_name
                            : ETO.fields[etoFn.config.objectForm.toLowerCase()].data[setValues['item']].label;
                        setValues.type_dispaly_name_tooltip = ETO.fields[etoFn.config.objectForm.toLowerCase()].data[setValues['item']].label;
                    }
                }
                else if (itemConf.valueConverterType == 'price') {
                    if (itemConf.sourceToIsMethod === true) {
                        return ETO[etoFn.config.objectForm].Form[itemConf.sourceTo](container, section, conf, setValues, fieldName);
                    }
                }
                else if (itemConf.valueConverterType == 'name') {
                    if (typeof setValues.override_name != 'undefined' && setValues.override_name.length > 0 ) {
                        setValues.display_name = setValues.override_name
                    }
                    else { setValues.display_name = itemConf.defaultName; }
                }
                else if (itemConf.valueConverterType == 'isChecked') {
                    if (typeof setValues.display_name != 'undefined') {
                        if (setValues.override_name.length > 0) {
                            setValues.display_name = setValues.override_name
                        }
                        else {
                            setValues.display_name = itemConf.defaultName;
                        }
                    }

                    if (setValues[fieldName] == '1') { setValues[fieldName] = 'Yes'; }
                    else { setValues[fieldName] = 'No'; }
                }
            }
        });

        return setValues;
    };

    etoFn.generateTooltipSummaryString = function(string, icon, label, value) {
        var order = string.split(','),
            returnString = '';
        for (var i in order) {
            switch (order[i]) {
                case 'icon':
                    returnString += icon;
                    break;
                case 'label':
                    returnString += label;
                    break;
                case 'value':
                    returnString += value;
                    break;
            }
        }
        return returnString;
    };

    etoFn.generateTooltip = function(el, section, setValues, tmpSection) {
        var tooltipString = '';
        if (typeof tmpSection.tooltip == 'undefined') { return tooltipString; }

        setValues = etoFn.parseValuesToView(el, section, tmpSection.tooltip, setValues);

        $.each(tmpSection.tooltip, function(fieldName, tooltipConf){
            var fieldData = {},
                tooltip = $.extend(true, {}, etoFn.config.defaults.display);
            tooltipConf = $.extend(true, tooltip, tooltipConf);

            if (tooltipConf.visible) {
                if (typeof tmpSection.fields[fieldName] != 'undefined') {
                    fieldData = tmpSection.fields[fieldName];
                }

                var label = typeof tooltipConf.label == 'string'
                    ? ((typeof fieldData[tooltipConf.label] != 'undefined' && typeof tooltipConf.label != 'boolean')
                        ? fieldData[tooltipConf.label]
                        : tooltipConf.label)
                    : '',
                    icon = tooltipConf.icon != false ? '<span class=\'eto-tooltip-field-icon\'><i class=\'' + tooltipConf.icon + '\'></i></span>' : '',
                    value = setValues[fieldName],
                    className = tooltipConf.class != false ? ' ' + tooltipConf.class : '';

                if (typeof value != 'undefined') {
                    if (tooltipConf.maxLength != false) {
                        var truncateValue = null !== value ? value.substring(0, tooltipConf.maxLength) : '';
                        value = null !== value ? (truncateValue.length < value.length
                            ? (tooltipConf.truncateSign !== false
                                ? truncateValue + tooltipConf.truncateSign
                                : truncateValue + '...')
                            : truncateValue) : '';
                    }

                    if ( value !== null && (value.length > 0 || value.toString().length > 0) && (tooltipConf.minValue === false || parseInt(value) >= tooltipConf.minValue)) {
                        label = label.length > 0 ? '<span class=\'eto-tooltip-field-label' + className + '\'>' + label + '</span>' : '';
                        value = '<span class=\'eto-tooltip-field-value\'>' + value + '</span>';

                        tooltipString += '<div class=\'eto-tooltip-field clearfix\'>';
                        if (tooltipConf.containerOrder === true) {
                            tooltipConf.containerOrder = "icon,value,label";
                        }
                        else if (tooltipConf.containerOrder === false) {
                            tooltipConf.containerOrder = "icon,label,value";
                        }
                        tooltipString += etoFn.generateTooltipSummaryString(tooltipConf.containerOrder, icon, label, value);
                        tooltipString += '</div>';
                    }
                }
            }
        });
        if (tooltipString.length === 0 ) { return tooltipString; }

        return '<div class=\'eto-tooltip\'>' + tooltipString + '</div>';
    };

    etoFn.generateSummaryWithTooltip = function(el, section, index, formId, isCleared) {
        var summaryString = '',
            iconContainer = el.find('.eto-summary-icon'),
            setValues = $.extend(true, {},  ETO[etoFn.config.objectForm].Form.getSectionObjectValues(formId, el));

        if (typeof setValues[index] == 'undefined') {
            if (section == 'item' && isCleared !== true) {
                el.remove();
            }
            return false;
        }

        var tmpSection = etoFn.config.objectForm !== false
                ? $.extend(true, {}, ETO[etoFn.config.objectForm].Form.form.sections[section]) : {},
            tooltipTitle = tmpSection.tooltipTitle,
            tooltipString = typeof tmpSection.tooltip != 'undefined'
                ? etoFn.generateTooltip(el, section, setValues[index], tmpSection) : '',
            i = 0,
            defaultSectionConfigForm = $.extend(true, {}, ETO[etoFn.config.objectForm].Form.config.defaults.section),
            defaultSectionConfig = $.extend(true, {}, etoFn.config.defaults.section);

        defaultSectionConfigForm = $.extend(true, defaultSectionConfig, defaultSectionConfigForm);
        tmpSection = $.extend(true, defaultSectionConfigForm, tmpSection);
        setValues = etoFn.parseValuesToView(el, section, tmpSection.summary, setValues[index]);

        $.each(tmpSection.summary, function(fieldName, summaryConf){
            var fieldData = {},
                summary = $.extend(true, {}, etoFn.config.defaults.display);
                summaryConf = $.extend(true, summary, summaryConf);

            if (summaryConf.visible) {
                if (summaryConf.place === false) {
                    if (typeof tmpSection.fields[fieldName] != 'undefined') {
                        fieldData = tmpSection.fields[fieldName];
                    }

                    var label = typeof summaryConf.label == 'string'
                        ? ((typeof fieldData[summaryConf.label] != 'undefined' && typeof summaryConf.label != 'boolean')
                            ? fieldData[summaryConf.label]
                            : summaryConf.label)
                        : '',
                        icon = summaryConf.icon !== false ? '<span class="eto-summary-group-icon"><i class=\'' + summaryConf.icon + '\'></i></span>' : '',
                        value = typeof setValues != 'undefined' ? (typeof setValues[fieldName] != 'undefined' ? setValues[fieldName] : false) : false;

                    if (summaryConf.maxLength !== false && value !== false && value !== null) {
                        var truncateValue = value.substring(0, summaryConf.maxLength);

                        value = truncateValue.length < value.length
                            ? (summaryConf.truncateSign !== false
                                ? truncateValue + summaryConf.truncateSign
                                : truncateValue + '...')
                            : truncateValue;
                    }

                    if (typeof value != 'undefined' && value !== false && value !== null && (summaryConf.minValue === false || parseInt(value) >= summaryConf.minValue)) {
                        if ((value.toString().replace(/(\r\n|\n|\r)/gm,"").length > 0)) {
                            if (summaryConf.displayFormat !== false) {
                                value = summaryConf.displayFormat.replace('__' + fieldName + '__', value);
                            }

                            label = label.length > 0 ? '<span class="eto-summary-group-label">' + label + '</span>' : '';
                            value = '<span class="eto-summary-group-value">' + value + '</span>';

                            if (tmpSection.summarySeparator !== false && i > 0) {
                                summaryString += '<span class="eto-summary-group-separator">'+ tmpSection.summarySeparator +'</span>';
                            }
                            summaryString += '<span class="eto-summary-group eto-summary-group-' + fieldName + ' clearfix">';
                            if (summaryConf.containerOrder === true) {
                                summaryConf.containerOrder = "icon,value,label";
                            }
                            else if (summaryConf.containerOrder === false) {
                                summaryConf.containerOrder = "icon,label,value";
                            }
                            summaryString += etoFn.generateTooltipSummaryString(summaryConf.containerOrder, icon, label, value);
                            summaryString += '</span>';

                            i++;
                        }
                    }
                }
                else if (summaryConf.place == 'icon') {
                    iconContainer.find('img').remove();
                    iconContainer.append(setValues[fieldName]).find('i').addClass('hidden');
                }
            }
        });

        if (summaryString.length === 0 ) {
            if (tmpSection.summaryPlaceholderAlt !== false) {
                summaryString = tmpSection.summaryPlaceholderAlt;
            }
        }

        if (tooltipString.length === 0 ) { tooltipTitle = ''; }

        return { tooltip: tooltipString, title: tooltipTitle, summary: summaryString };
    };

    etoFn.showHideSummary = function(group, fromObject, fromDependencies) {
        var i = 0,
            inputs = group.find('.eto-field').not(group.find('.eto-field-advance.hidden')).find('.eto-js-inputs'),
            section = inputs.closest('.eto-section').attr('data-eto-section');

        if (fromObject !== true) {
            for (var no in inputs) {
                if (isNaN(no) !== true) {
                    var input = $(inputs[no]),
                        val = input.val(),
                        name = input.attr('data-eto-name'),
                        summaryConfig = typeof ETO[etoFn.config.objectForm].Form.form.sections[section] != 'undefined' && typeof ETO[etoFn.config.objectForm].Form.form.sections[section]['summary'] != 'undefined' ? $.extend(true, {}, ETO[etoFn.config.objectForm].Form.form.sections[section]['summary']) : false;

                    if (summaryConfig !== false && typeof summaryConfig[name] != 'undefined') { // czy ta wartosc ma byc przekazana do summary

                        if (inputs[no].tagName == 'SELECT' && typeof input.find('option:selected') != 'undefined' && val != '') {
                            val = input.find('option:selected').text();
                        }

                        if (input.attr('type') == 'checkbox' && typeof input.attr('checked') != 'checked') {
                            if (input.attr('checked') != 'checked') { val = ''; }
                        } else if (input.attr('type') == 'radio') {
                            if (input.closest('.eto-field').find('input:checked').length === 0) { val = ''; }
                            else { val = input.closest('.eto-field').find('input:checked').val(); }
                        }

                        if (null != val && val.toString().replace(/(\r\n|\n|\r)/gm,"").localeCompare('') !== 0) { i++; }
                    }
                }
            }
        }
        else if (fromObject === true ) { i = 0;}
        if (fromDependencies === true) { i = 1; }
        if (i === 0 && group.find('.eto-js-inputs').length > 0) { etoFn.visibilitySummaryValue(group, false); }
        else { etoFn.visibilitySummaryValue(group, true); }
    };

    etoFn.prepareSammaryValue = function(group, section, index, formId, viewSummary) {
        var summaryTextContainer = group.find('.eto-summary-value'),
            htmlObject = ETO.Form.generateSummaryWithTooltip(group, section, index, formId);

        if (typeof viewSummary == 'undefined') { viewSummary = true; }

        if (htmlObject !== false) {
            summaryTextContainer.html(htmlObject.summary);
            ETO.removeSetPopoverTooltip(group.find('.eto-summary-link'), htmlObject);
            viewSummary = (htmlObject.summary.length > 0);
            etoFn.visibilitySummaryValue(group, viewSummary);
        }
    };

    etoFn.visibilitySummaryValue = function(group, show) {
        if (show === true) {
            group.find('.eto-summary-placeholder').addClass('hidden');
            group.find('.eto-summary-value').removeClass('hidden');
            group.find('.eto-summary').addClass('eto-summary-has-value');
        }
        else {
            var section = group.closest('.eto-section').attr('data-eto-section'),
                config = $.extend(true, {}, ETO[etoFn.config.objectForm].Form.form.sections[section]),
                titleObject =  typeof config != 'undefined' && typeof config.summaryTooltip != 'undefined' && config.summaryTooltip !== false ? {title: config.summaryTooltip} : false;

            group.find('.eto-summary-placeholder').removeClass('hidden');
            group.find('.eto-summary-value').html('');
            group.find('.eto-summary-value').addClass('hidden');
            group.find('.eto-summary').removeClass('eto-summary-has-value');

            ETO.removeSetPopoverTooltip(group.find('.eto-summary-link'), titleObject);
        }
    };

    etoFn.updateSummary = function(group, isCleared) {
        var section = group.closest('.eto-section').attr('data-eto-section'),
            index = group.find('.eto-summary-link').attr('data-eto-index'),
            formId = group.closest('.eto-form').attr('id'),
            summaryLink = group.find('.eto-summary-link'),
            summaryValue = group.find('.eto-summary-value'),
            htmlObject = etoFn.generateSummaryWithTooltip(group, section, index, formId, isCleared);

        summaryValue.html(htmlObject.summary);
        if(group.hasClass('eto-group-open-always')) {
            ETO.removeSetPopoverTooltip(group, htmlObject);
        }
        else {
            ETO.removeSetPopoverTooltip(summaryLink, htmlObject);
        }

        if (typeof htmlObject.summary != 'undefined' && isCleared === true && htmlObject.summary.localeCompare('') === 0) { etoFn.visibilitySummaryValue(group, false); }
        else { etoFn.showHideSummary(group, false); }
    };
    /**
     * !! Generate summary from values !!
     */

    etoFn.openFieldset = function(e) {
        var group = e.closest('.eto-group'),
            formContainer = group.closest('.eto-form'),
            index = group.find('.eto-summary-link').attr('data-eto-index'),
            section = group.find('.eto-summary-link').attr('data-eto-section'),
            data = etoFn.config.objectForm !== false
                ? $.extend(true, {}, ETO[etoFn.config.objectForm].Form.form.sections[section]) : {},
            inputCount = group.find('.eto-js-inputs').length;

        if (formContainer.find('.daterangepicker').length > 0) {
            formContainer.find('.daterangepicker').remove();
        }

        if ((inputCount > 0 && data.groupAutoOpen !== true) || inputCount === 0) {
            if (inputCount > 0 && data.groupAutoOpen !== true) { group.find('.eto-fields').html(''); }
            etoFn.createFields(data, index, section, group.find('.eto-summary-link') );
        }

        etoFn.hideFields(group.closest('.eto-form'), group);
        group.find('.eto-summary').addClass('hidden');
        group.find('.eto-fieldset').removeClass('hidden');

        var firstInName = group.find('.eto-fields .eto-js-inputs').first().data('etoName');
        if (data.fields[firstInName].isFocus === true) {
            group.find('.eto-fields .eto-js-inputs').first().focusTextToEnd();
        }
        group.addClass('eto-group-open');

        group.find('.eto-fields .eto-field:not(.eto-not-visible)').find('.eto-js-inputs').each(function(key, input) {
            var field = $(input).closest('.eto-field');
            if (null !== $(this).val() && $(this).val().length > 0) {
                field.find('.eto-field-placeholder').removeClass('hidden');
                if (field.hasClass('eto-field-advance')) { field.removeClass('hidden'); }
            }
            else {
                field.find('.eto-field-placeholder').addClass('hidden');
                if (field.hasClass('eto-field-advance')) { field.addClass('hidden'); }
            }
        });

        ETO.updateFormPlaceholderInit(group);
        group.find('select.eto-js-inputs').each(function () {
            if((parseInt($(this).select2('val')) === 0 || $(this).select2('val') == '')) {
                $(this).closest('.eto-field').find('.eto-field-btn-clear').addClass('hidden');
            }
        });
    };

    etoFn.setValuesToObject = function(field, isCleared) {
        var value = field.val(),
            type = field.attr('type'),
            name = field.closest('.eto-field').attr('data-eto-field-name'),
            group = field.closest('.eto-group'),
            summaryLink = group.find('.eto-summary-link'),
            index = summaryLink.attr('data-eto-index'),
            formId = group.closest('.eto-form').attr('id'),
            isCorrect = 0;

        if (isCleared !== true) {
            if (type == 'text' && name == 'phone') {
                value = field.intlTelInput('getNumber');
            }
            else if (type == 'checkbox') {
                if (field.attr('checked') != 'checked') {
                    value = '';
                } else { value = 1; }
            }
            else if (type == 'radio') {
                if (field.closest('.eto-field').find('input:checked').length === 0) {
                    value = '';
                }
                else {
                    value = field.closest('.eto-field').find('input:checked').val();
                }
            }
            else if (value == '' && typeof field.attr('min') != 'undefined') {
                value = field.attr('min');
            }
            isCorrect = etoFn.validateField(field);
        }

        ETO[etoFn.config.objectForm].Form.setElementValue(formId, group, index, name, value);

        if (isCorrect > 0) {
            etoFn.visibilitySummaryValue(group, false);
            return isCorrect;
        }
        else {
            if (isCleared === true) {
                etoFn.visibilitySummaryValue(group, false);
            }
            else {
                etoFn.updateSummary(group, isCleared);
            }
            return 0;
        }
    };

    etoFn.setAllValuesToObject = function(formContainer, type, noValivateGlobal) {
        var fails = 0,
            inputs = formContainer.find('.eto-js-inputs');

        for (var i in inputs) {
            if (isNaN(i) !== true) {
               fails = +etoFn.setValuesToObject($(inputs[i])) + fails;
            }
        }
        // if (fails === 0 && noValivateGlobal !== true){
            etoFn.hideFields(formContainer);
            formContainer.find('.daterangepicker').remove();

            // return ETO[etoFn.config.objectForm].Form.validateFormObject(formContainer, type);
            if(ETO[etoFn.config.objectForm].Form.validateFormObject(formContainer, type) === false ) {
                fails++;
            }
        // }
        etoFn.config.runValidation = false;

        if (formContainer.find('.eto-route-1 .eto-group-feedback-error, eto-route-1 .eto-field-feedback').length > 0) {
            formContainer.find('.eto-error-route-1').removeClass('hidden');
        }
        if (formContainer.find('.eto-route-2 .eto-group-feedback-error, eto-route-2 .eto-field-feedback').length > 0) {
            formContainer.find('.eto-error-route-2').removeClass('hidden');
        }

        return fails === 0;
    };

    etoFn.createValuesToObject = function(formId, firstKey, secondKey, thirdKey) {
        var objectForm = $(etoFn.config.objectForm).lowerCaseFirst();

        ETO.createNewObject('Form', 'form', 'values', formId, objectForm, firstKey, secondKey, thirdKey);
    };

    etoFn.createGroup = function(section, container, data, addNew, index) {
        var formId = container.closest('.eto-form').attr('id'),
            routeId = container.closest('.eto-route').data('etoRouteId'),
            summaryHtml = etoFn.markup.box.summary(data, section, index, formId, routeId),
            buttonsHtml = typeof data.buttons.fields != 'undefined' ? etoFn.createButtons(data.buttons.fields, 'fieldset') : '',
            groupClass = data.groupClass !== false ? ' ' + data.groupClass : '';

        summaryHtml += '<div class="eto-fieldset clearfix">';
        summaryHtml += buttonsHtml.localeCompare('') !== 0 ? '<span class="eto-fieldset-buttons clearfix">' + buttonsHtml + '</span>' : '';
        summaryHtml += '<div class="eto-fields clearfix"></div></div>';

        container.append('<div class="eto-group'+ groupClass +'">'+ summaryHtml +'</div>');
        container = container.find('.eto-group:last').find('.eto-fields');

        var summaryBox = container.closest('.eto-group').find('.eto-summary-value');

        if (summaryBox.text().length === 0) {
            summaryBox.addClass('hidden');
        }

        etoFn.prepareCreatedSummaryBox(container, data, section, addNew, index);
        return container
    };

    etoFn.prepareCreatedSummaryBox = function(container, data, section, addNew, index) {
        var summaryLink = container.closest('.eto-group').find('.eto-summary-link'),
            formId = summaryLink.closest('.eto-form').attr('id');

        if (typeof data.buttons.summary != 'undefined') {
            if (typeof data.buttons.summary.delete != 'undefined') {
                etoFn.enableDisableButton(container.closest('.eto-groups'));
            }
        }

        if (typeof index != 'undefined') { // if generate form form values object
            etoFn.prepareSammaryValue(summaryLink.closest('.eto-group'), section, index, formId);
        }
        else {
            if (data.groupAutoOpen === true) {
                summaryLink.closest('.eto-group').addClass('eto-group-open-always');
                etoFn.openFieldset(summaryLink, addNew);
            }
            if (data.groupAddedOpen === true && addNew === true) {
                etoFn.fieldsetModal(summaryLink[0]);
                etoFn.openFieldset(summaryLink, addNew);
            }

            if (data.summaryTooltip !== false) { ETO.removeSetPopoverTooltip(summaryLink, {title: data.summaryTooltip}); }
        }

        if (data.groupOrder === true) { etoFn.setSortable(section, 'group', container.closest('.eto-groups')); }

        if (typeof index != 'undefined') {
            etoFn.hideFields(summaryLink.closest('.eto-form'), $({}), true);
        }
        else {
            etoFn.hideFields(summaryLink.closest('.eto-form'), summaryLink.closest('.eto-group'), true);
        }
    };

    etoFn.createFields = function(data, index, elementName, summaryLink) {
        var container = summaryLink.closest('.eto-group').find('.eto-fields'),
            formId = summaryLink.closest('.eto-form').attr('id'),
            anchorId = container.closest('.eto-group').find('.eto-summary-link').attr('id');

        $.each(data.fields, function(fieldName, field) {
            var fieldConf = $.extend(true, {}, etoFn.config.defaults.field);
            data.fields[fieldName] = $.extend(true, fieldConf, field);
            data.fields[fieldName].jsClass = fieldName;
        });

        /**
         * render HTML
         */
        for (var fieldName in data.fields) {
            var field = data.fields[fieldName],
                fieldHtml = '',
                fieldClass = field.class !== false ? field.class : '',
                fieldIcon = field.icon !== false ? '<div class="eto-field-icon"><i class="' + field.icon + '"></i></div>' : '',
                fieldLabel = field.type != 'radio' && field.type != 'checkbox' ? (field.label !== false ? '<div class="eto-field-label">' + field.label + '</div>' : '') : '',
                fieldPlaceholder = field.placeholder !== false ? '<div class="eto-field-placeholder">' + field.placeholder + '</div>' : '',
                buttonsHtml = etoFn.createButtons(field.buttons, 'field');

            fieldClass += fieldIcon != '' ? ' eto-field-has-icon' : '';
            fieldClass += fieldLabel != '' ? ' eto-field-has-label' : '';
            fieldClass += fieldPlaceholder != '' ? ' eto-field-has-placeholder' : '';
            fieldClass += field.isAdvance === true ? ' eto-field-advance' : '';

            buttonsHtml = buttonsHtml != '' ? '<span class="eto-field-buttons clearfix">' + buttonsHtml + '</span>' : '';

            fieldHtml = '<div class="eto-field eto-field-'+ fieldName +' '+ fieldClass +' clearfix" data-eto-field-name="' + field.jsClass + '">';
                fieldHtml += '<div class="eto-field-row">';
                if (field.fieldIconRevert) {
                    fieldHtml += fieldLabel + '<div class="eto-field-value clearfix"></div>' + fieldPlaceholder + fieldIcon;
                }
                else {
                    fieldHtml += fieldIcon + fieldLabel + '<div class="eto-field-value clearfix"></div>' + fieldPlaceholder;
                }
                fieldHtml += '</div>';
            fieldHtml += '</div>';

            container.append(fieldHtml);
            container.find('.eto-field-' + fieldName).find('.eto-field-row').append(buttonsHtml);

            etoFn.createFieldMarkup(elementName, fieldName, field, data, container, index, anchorId);

            if (field.visible === false) {
                container.find('.eto-field-' + fieldName).addClass('eto-not-visible');
                etoFn.checkVisibility(elementName, fieldName, field, data, container, index);
            }
        }

        /**
         * set EVENT's
         */
        etoFn.setEventsToFields(elementName, data, container, index, anchorId, formId);

        return container;
    };

    etoFn.checkVisibility = function(elementName, fieldName, field, data, fieldsContainer) {
        var groupInputs = fieldsContainer.find('.eto-field:not(.eto-not-visible)').find('.eto-js-inputs'),
            v = 0;

        for (var i in groupInputs) {
            if (isNaN(i) !== true) {
                if ($(groupInputs[i]) instanceof jQuery) {
                    if ($(groupInputs[i]).val() != '' && $(groupInputs[i]).val() != 0 && ($(groupInputs[i]).val() !== null || (groupInputs[i].tagName == 'SELECT' && $(groupInputs[i]).find('option:selected').length > 0))) {
                        v++;
                    }
                }
            }
        }

        if (v === 0) {
            fieldsContainer.find('.eto-js-' + field.jsClass ).closest('.eto-field').addClass('hidden');
        }
    };

    etoFn.createFieldMarkup = function(elementName, fieldName, field, data, container, index, anchorId) {
        if (typeof field.callback.before == 'object') {
            $.each(field.callback.before, function(key, callback) {
                var callbackConf = $.extend(true, {}, etoFn.config.defaults.callback);
                data.fields[fieldName].callback.before[key] = $.extend(true, callbackConf, callback);
                etoFn[key](data.fields[fieldName], index, fieldName, elementName, anchorId);
            });
        }

        var html = etoFn.config.fieldMarkupTmp !== false
            ? etoFn.config.fieldMarkupTmp
            : etoFn.templateToField(elementName, field, index, anchorId);

        if (field.type == 'hidden' || ETO[etoFn.config.objectForm].Form.form.settings.view[fieldName] === false || (ETO[etoFn.config.objectForm].Form.form.settings.checked[fieldName] === false && field.type == 'checkbox')) {
            container.find('.eto-field-' + fieldName).html(html).addClass('hidden');
            if (field.type == 'checkbox') {
                container.find('.eto-field-' + fieldName).find('eto-js-' + fieldName).attr('checked', false)
            }
        }
        // if (field.type == 'hidden') {
        //     container.find('.eto-field-' + fieldName).html(html).addClass('hidden');
        // }
        // else {
            container.find('.eto-field-' + fieldName + ' .eto-field-value').html(html);
        // }

        if (!field.isFocus) {
            container.find('.eto-field-' + fieldName + ' .eto-js-' + fieldName).attr('data-eto-not-focus', true)
        }

        etoFn.config.fieldMarkupTmp = false;
    };

    etoFn.setEventsToFields = function(elementName, data, container, index, anchorId, formId) {
        $.each(data.fields, function(fieldName, field) {
            if (typeof field.callback.after == 'object') {
                $.each(field.callback.after, function(key, callback) {
                    var callbackConf = $.extend(true, {}, etoFn.config.defaults.callback);
                    data.fields[fieldName].callback.after[key] = $.extend(true, callbackConf, callback);
                    etoFn[key](data.fields[fieldName], index, fieldName, elementName, anchorId);
                });
            }

            if (field.type.localeCompare('radio') === 0) { container.find('input.eto-js-'+field.jsClass+':checked'); }
            if (container.closest('.eto-group').hasClass('eto-group-open-always') === false) { etoFn.validateField(container.find('.eto-js-' + fieldName)); }

            ETO.updatePopover(container.find('[data-toggle="popover"]'));
            ETO.updateFormPlaceholderInit(container);
        });
    };

    etoFn.createButtons = function(buttons, name) {
        var html = '';
        if ( Object.keys(buttons).length > 0) {
            $.each(buttons, function(key, button) {
                var buttonConf = $.extend(true, {}, etoFn.config.defaults.button);
                button = $.extend(true, buttonConf, button);

                var className = button.class !== false ? ' ' + button.class : '',
                    icon = button.icon !== false ? '<i class="' + button.icon + '"></i>' : '',
                    text =  button.text !== false ?  button.text : '',
                    title = typeof button.popover.title != 'undefined' ? button.popover.title : '',
                    tooltip =  button.tooltip !== false ? ' data-toggle="tooltip" data-title="' + button.tooltip + '"' : '',
                    popover = button.popover !== false ?' data-toggle="popover" data-title="' + title + '" data-content="' + button.popover.html + '"' : '';

                html += '<' + button.tagName + ' class="eto-' + name + '-btn' + className + '"' + tooltip + popover  + '  data-eto-confirm="' + button.attributes.confirm + '">' + text + icon + '</' + button.tagName + '>';
            });
        }
        return html;
    };

    etoFn.templateToField = function(elementName, fieldData, index, uid, typeTemplate) {
        var summaryContainer = $('#'+uid),
            container = summaryContainer.closest('.eto-group'),
            formId = summaryContainer.closest('.eto-form').attr('id'),
            fieldHtml = '',
            template = typeof typeTemplate != 'undefined'
                ? etoFn.markup.field[typeTemplate]
                : (typeof etoFn.markup.field[fieldData.type] != 'undefined'
                    ? etoFn.markup.field[fieldData.type]
                    : etoFn.markup.field['input']),
            required = fieldData.validate.isRequired === true ? ' required="required"' : '',
            id = fieldData.id != '' ? ' id="'+fieldData.id+'"' : '',
            forId = fieldData.id != '' ? ' for="'+fieldData.id+'"' : '',
            name = typeof fieldData.params != 'undefined'
                ? (fieldData.params.name != ''
                    ? fieldData.params.name : 'amount') : fieldData.jsClass,
            className = name,
            label = fieldData.label != '' ? fieldData.label : '',
            placeholder = fieldData.placeholderAlt !== false
                ? fieldData.placeholderAlt : (fieldData.placeholder != '' ? fieldData.placeholder : ''),
            placeholderReplace = placeholder != '' ? ' placeholder="'+placeholder+'"' : '',
            checked = fieldData.checked === true ? ' checked="checked"' : '',
            value = typeof fieldData.params != 'undefined'
                ? (typeof fieldData.params.value != 'undefined'
                    ? fieldData.params.value
                    : (fieldData.type == 'number' || fieldData.type == "number_spin" ? '1' : ''))
                : (String(fieldData.value) != '' && fieldData.value !== false
                    ? fieldData.value
                    : ''),
            objectValue = ETO[etoFn.config.objectForm].Form.getElementValue(formId, container, index, name),
            icon = fieldData.iconChecked;

        value = null === value ? '' : value;

        if (objectValue !== false) {
            if (fieldData.type != 'radio') { value = objectValue; }
        }

        if (fieldData.classAdd !== false) { className += ' '+fieldData.classAdd; }

        if (value !== null && value !== '' && typeof value === 'string' && (fieldData.type == 'text' || fieldData.type == 'textarea')) {
            value = ETO.escapeHtml(value);
        }

        if (fieldData.type != 'textarea') {
            // if (value == '0.00') { value = ''; }
            value = ' value="'+value+'"';
        }
        if (fieldData.type == 'select' && fieldData.placeholderOption !== false) { value = ''; label = '<option value="">'+ fieldData.placeholderOption +'</option>'; }
        if (fieldData.type == 'radio' && icon === false) { icon = 'ion-record'; }
        if (fieldData.type == 'checkbox' && icon === false) { icon = 'ion-ios-checkmark-empty'; }
        if (icon != '') {icon = '<i class="'+ icon +'"></i>';}
        if (null === value) {value = '';}

        fieldHtml += template
            .replace(/__TYPE__/g,fieldData.type)
            .replace(/__CLASS__/g, 'eto-js-'+className)
            .replace(/__NAME__/g, className+uid)
            .replace(/__DNAME__/g, name)
            .replace(/__LABEL__/g, label)
            .replace(/__ID__/g, id)
            .replace(/__FOR__/g, forId)
            .replace(/__VALUE__/g, value)
            .replace(/__PLACEHOLDER__/g, placeholderReplace)
            .replace(/__CHECKED__/g, checked)
            .replace(/__REQUIRED__/g, required)
            .replace(/__ICON__/g, icon);

            return fieldHtml;
    };

    /**
     * Methods for callback
     */
    etoFn.radioSwitchGenerator = function(data, index, fieldName, elementName, uid) {
        var html = '',
            radioConfig = data.callback.before.radioSwitchGenerator,
            summaryContainer = $('#'+uid),
            container = summaryContainer.closest('.eto-group'),
            formId = summaryContainer.closest('.eto-form').attr('id'),
            objectValue = ETO[etoFn.config.objectForm].Form.getElementValue(formId, container, index, fieldName),
            sourceToSort = $.extend(true, {},radioConfig.source.data),
            source = {};

        for (var i in sourceToSort) {
            if (objectValue == '' && sourceToSort[i].id === 0) {
                sourceToSort[i].selected = true;
            }
            source[sourceToSort[i].name] = sourceToSort[i];
        }

        for (var name in source) {
            var conf = $.extend(true, {}, data);

            conf.value = source[name].id;
            conf.label = source[name].name;
            if (objectValue !== false && parseInt(objectValue) === parseInt(source[name].id)) {
                conf.checked = true;
            }
            else if (objectValue === false) {
                conf.checked = radioConfig.source.selected == source[name].id ? true : false;
            }
            else if (objectValue == '' && source[name].id === 0) {
                conf.checked = true;
            }
            else {
                conf.checked = false;
            }

            html += etoFn.templateToField(elementName, conf, index, uid, 'radioCheckbox');
        }

        etoFn.config.fieldMarkupTmp = html != '' ? html : false;
    };

    etoFn.checkboxSwitchGenerator = function(data, index, fieldName, elementName, uid, radioConfig) {
        var html = '',
            config = typeof radioConfig != 'undefined' ? radioConfig : data.callback.before.checkboxSwitchGenerator,
            summaryContainer = $('#'+uid),
            container = summaryContainer.closest('.eto-group'),
            formId = summaryContainer.closest('.eto-form').attr('id'),
            objectValue = ETO[etoFn.config.objectForm].Form.getElementValue(formId, container, index, fieldName);

        for (var i in config.source.data) {
            var conf = $.extend(true, {}, data);

            conf.value = config.source.data[i].value;
            conf.label = config.source.data[i].name;
            if (objectValue !== false && parseInt(objectValue) === parseInt(config.source.data[i].value)) {
                conf.checked = true;
            }
            else if (typeof ETO[etoFn.config.objectForm].Form.form.settings.checked[fieldName] == 'boolean' && objectValue != '') {
                conf.checked = ETO[etoFn.config.objectForm].Form.form.settings.checked[fieldName];
            }
            else if (objectValue === false) {
                conf.checked = config.source.selected == config.source.data[i].value  ? true : false;
            }
            else {
                conf.checked = false;
            }

            html += etoFn.templateToField(elementName, conf, index, uid, 'radioCheckbox');
        }

        if (typeof radioConfig != 'undefined') {
            return html;
        }
        etoFn.config.fieldMarkupTmp = html != '' ? html : false;
    };

    etoFn.checkboxButtonGenerator = function(data, index, fieldName, elementName, uid) {
        var html = etoFn.checkboxSwitchGenerator(data, index, fieldName, elementName, uid, data.callback.before.checkboxButtonGenerator),
            checkboxButton = etoFn.markup.box.checkboxButton(html, data.jsClass,  data.label,  data.icon);
        etoFn.config.fieldMarkupTmp = html != '' ? checkboxButton : false;
    };

    etoFn.durationSetTouchSpin = function(data, index, fieldName, elementName, uid) {
        var durationConfig = data.callback.after.durationSetTouchSpin,
            input = $('#'+uid).closest('.eto-group').find('.eto-js-' + fieldName);

        durationConfig.min = durationConfig.min !== false ? durationConfig.min:1;

        input.attr('min', durationConfig.min);
        etoFn.setTouchSpin(input, durationConfig.max, durationConfig.min, durationConfig.step, durationConfig.value, durationConfig.postfix, durationConfig.vertical);
    };

    etoFn.setDaterangepicker = function(data, index, fieldName, elementName, uid) {
        var input = $('#'+uid).closest('.eto-group').find('.eto-js-' + fieldName);

        etoFn.datePicker(input);
    };

    etoFn.setSelect2FromConfig = function(data, index, fieldName, elementName, uid) {
        var selectConfig = data.callback.after.setSelect2FromConfig,
            select = $('#'+uid).closest('.eto-group').find('.eto-js-' + fieldName),
            container = select.closest('.eto-group'),
            routeId = container.closest('.eto-route').attr('data-eto-route-id'),
            formId = container.closest('.eto-form').attr('id'),
            source = selectConfig.source,
            groupValues = ETO[etoFn.config.objectForm].Form.getSectionObjectValues(formId, container),
            value = 0;

        if (selectConfig.sourceFrom.toString().localeCompare('fields') === 0) {
            for (var i in source.data) {
                if (etoFn.objectHasValue(source.data[i].section, elementName) === false) {
                    delete source.data[i];
                }
            }
        }

        if (typeof groupValues[index] != 'undefined'){
            if (typeof groupValues[index][fieldName] != 'undefined'){
                value = groupValues[index][fieldName];
            }
        }
        if (selectConfig.sourceAddValue === true && container.closest('.eto-form').hasClass('eto-form-mode-edit') && typeof ETO[etoFn.config.objectForm].Form.config.oldData[formId] != 'undefined'){
            var oldValue = ETO[etoFn.config.objectForm].Form.config.oldData[formId][routeId][elementName][index][fieldName];
            source.data.push( {id: oldValue, name: oldValue} );
        }

        etoFn.setSelect2(source, value, data, selectConfig, container);

        function hasValue(obj, key, value) {
            var exists = false;
            $.each(obj, function(k,v) {
                exists = v.hasOwnProperty(key) && v[key] == value;
                if (exists) {
                    return false;
                }
            });
            return exists;
        }

        if (null !== value) {
            if (value.length > 0 && value !== 0 && hasValue(source.data, "id", value) === false) {
                if (selectConfig.createSource === true) {
                    var newOption = new Option(value, value, 'selected', 'selected');
                    select.append(newOption);
                }
            }
            else if (parseInt(value) > 0 ||
                fieldName.localeCompare('vehicle_type') === 0 ||
                fieldName.localeCompare('status') === 0 ||
                fieldName.localeCompare('locale') === 0 ||
                fieldName.localeCompare('source') === 0) {
                select.val(value);
            }

            etoFn.select2Autoopen(select, value, data.selectAutoOpen);
        }
        select.change();
    };

    etoFn.setSelect2FromSearchUser = function(data, index, fieldName, elementName, uid) {
        var selectConfig = data.callback.after.setSelect2FromSearchUser,
            select = $('#'+uid).closest('.eto-group').find('.eto-js-' + fieldName),
            container = select.closest('.eto-group'),
            formId = container.closest('.eto-form').attr('id'),
            source = {data: {}},
            groupValues = ETO[etoFn.config.objectForm].Form.getSectionObjectValues(formId, container),
            value = 0,
            user = ETO.settings(elementName, 0),
            summaryValue = $('#'+uid).find('.eto-summary-value').text(),
            uri = 'get-config/' + elementName + '-search';

        if (typeof groupValues[index] != 'undefined'){
            if (typeof groupValues[index][fieldName] != 'undefined'){
                value = groupValues[index][fieldName];
            }
            groupValues = groupValues[index]
        }

        if (null === value || elementName == 'passenger') { value = ''; }

        if (elementName != 'passenger' && parseInt(value) > 0) {
                user = typeof user != 'undefined' && user.id === parseInt(value)
                    ? user
                    : ETO.Booking.getUser(value, elementName);
        }

        var role = typeof user != 'undefined' && typeof user.role != 'undefined' ? user.role : '',
            summaryHtml = typeof user != 'undefined'
                ? (typeof user != 'undefined'
                    ? (role == 'driver' && typeof user.profile.unique_id != 'undefined'
                        ? user.displayName
                        : user.name)
                    : '')
                : (typeof groupValues != 'undefined' && typeof groupValues.driver_text_selected != 'undefined' ? groupValues.driver_text_selected : ''),
            selected = {
                id: value,
                text: summaryHtml,
                title: summaryValue,
                selected: typeof groupValues[fieldName] != 'undefined' && groupValues[fieldName] != '',
            };

        if (typeof selectConfig.urlParam != 'undefined') {
            uri += '/' + selectConfig.urlParam;
            if (selectConfig.urlParam == 'unavailable') {
                uri += '/true'
            }
        }
        if (elementName == 'passenger') {
            source.data = selected
        }

        selectConfig.config = ETO.Form.select2AjaxUser(uri, selected, container, data.placeholder);
        etoFn.setSelect2(source, value, data, selectConfig, container);
        etoFn.select2Autoopen(select, value, data.selectAutoOpen, true);

        if (elementName == 'customer') {
            ETO[etoFn.config.objectForm].Form.setDepartments(select, user)
        }
    };

    etoFn.setIntlTelInput = function(data, index, fieldName, elementName, uid) {
        var input = $('#'+uid).closest('.eto-group').find('.eto-js-' + fieldName);

        input.intlTelInput({
            utilsScript: ETO.config.appPath +'/assets/plugins/jquery-intl-tel-input/js/utils.js?1515077554',
            preferredCountries: ['gb'],
            initialCountry: 'auto',
            geoIpLookup: function(callback) {
                $.get('https://ipinfo.io', function() {}, 'jsonp').always(function(resp) {
                    var countryCode = (resp && resp.country) ? resp.country : "";
                    callback(countryCode);
                });
            },
        });

        input.on('countrychange', function() {
            input.change();
        });
    };

    etoFn.setTypeahead = function(data, index, fieldName, elementName, uid) {
        var group = $('#'+uid).closest('.eto-group'),
            input = group.find('.eto-js-' + fieldName),
            formContainer = input.closest('.eto-form'),
            formId = formContainer.attr('id'),
            url = ETO.config.appPath +'/etov2?apiType=frontend&isAdminRequest=1&',
            pacontainer = '';

        if (typeof ETO.settings('serviceType', undefined) != 'undefined' && ETO.settings('serviceType.'+formId) == 'scheduled') {
            var searchURL = url +'task=scheduled_locations&search=%QUERY&'+ Math.random();
        }
        else {
            var searchURL = url +'task=locations&search=%QUERY&pacontainer=' + pacontainer; // +'&'+ Math.random()
        }

        // https://github.com/corejavascript/typeahead.js
        var locations = new Bloodhound({
            name: 'locations',
            initialize: false,
            datumTokenizer: function(data) {
                return Bloodhound.tokenizers.whitespace(data.name);
            },
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            remote: {
                url: searchURL,
                wildcard: '%QUERY',
                replace: function() {
                    if (typeof ETO.settings('serviceType', undefined) != 'undefined' && ETO.settings('serviceType.'+formId) == 'scheduled') {
                        return url +'task=scheduled_locations&search='+ encodeURIComponent(input.val()) +'&'+ Math.random();
                    }
                    else {
                        return url +'task=locations&search='+ encodeURIComponent(input.val()) +'&pacontainer='+ encodeURIComponent(pacontainer); // +'&'+ Math.random()
                    }
                },
                filter: function(response) {
                    locations.showGoogleLogo = response.showGoogleLogo;
                    $.each(response.locations, function(key, value) {
                        if (locations.valueCache.indexOf(value.name) === -1) {
                            locations.valueCache.push(value.name);
                        }
                    });
                    return response.locations;
                }
            },
        });

        locations.showGoogleLogo = 0;
        locations.valueCache = [];
        locations.initialize(true);

        input.typeahead('destroy');

        var typeaheadInput = input.typeahead({
            hint: true,
            highlight: true,
            minLength: 0
        }, {
            name: 'locations',
            display: 'name',
            source: locations.ttAdapter(),
            limit: 100,
            templates: {
                header: '<div class="tt-header">' + ETO.trans('booking.bookingField_TypeAddress') + '</div>',
                footer: '<img class="powered-by-google-locations" src="' + ETO.config.appPath + '/assets/images/icons/powered-by-google-on-white.png" alt="powered-by-google" />',
                suggestion: function(data) {
                    return '<div class="clearfix ' + (data.cat_type ? 'tt-c-'+data.cat_type : '') + ' ' + (data.cat_featured ? 'tt-c-featured' : '') + '"><span class="tt-s-name pull-left">' + data.name + '</span><span class="tt-s-category pull-right">' + data.cat_icon +'</span></div>';
                },
                notFound: '<div class="tt-empty">' + ETO.trans('booking.bookingMsg_NoAddressFound') + '</div>'
            }
        });

        var typeaheadInputLastVal = '';

        $(typeaheadInput)
            .on('typeahead:selected typeahead:autocompleted', function(e, tData) {
                if (tData.pa_container) {
                    pacontainer = tData.pa_container;
                    $(typeaheadInput).focus().typeahead('val', tData.pa_text);
                    return false;
                }
                pacontainer = '';
            })
            .on('typeahead:beforeclose', function(e) {
                // keep menu open if input element is still focused https://github.com/twitter/typeahead.js/issues/796
                if ($(e.target).is(':focus') && pacontainer) {
                    return false;
                }
            })
            .on('typeahead:selected', function(e, tData) {
                var value = tData.name;
                if (tData.address) {
                    value = tData.address;
                }

                if (value.length > 0) {
                    $(typeaheadInput).typeahead('val', value).change();

                    typeaheadInputLastVal = value;
                    var fields = $(typeaheadInput).closest('.eto-fields');
                    fields.find('.eto-js-lat').val(0);
                    fields.find('.eto-js-lng').val(0);
                    fields.find('.eto-js-place_id').val(tData.place_id).change();
                }
            })
            .on('typeahead:autocompleted', function(e, tData) {
                var value = tData.name;
                if (tData.address) {
                    value = tData.address;
                }

                // if (value.length > 0) {
                //     $(typeaheadInput).change();
                // }

                if (value.length > 0) {
                    $(typeaheadInput).typeahead('val', value).change();

                    typeaheadInputLastVal = value;
                    var fields = $(typeaheadInput).closest('.eto-fields');
                    fields.find('.eto-js-lat').val(0);
                    fields.find('.eto-js-lng').val(0);
                    fields.find('.eto-js-place_id').val(tData.place_id).change();
                }
            })
            .on('keydown', function(e, tData) {
                pacontainer = '';
            })
            .on('change', function(e) {
                var isEmpty = null === $(e.target).val() || $(e.target).val().length === 0 ? true : false;
                var value = $(e.target).val();
                $(typeaheadInput).typeahead('val', value);

                if (typeaheadInputLastVal != value || value == '') {
                    typeaheadInputLastVal = value;
                    var fields = $(typeaheadInput).closest('.eto-fields');
                    fields.find('.eto-js-lat').val(0);
                    fields.find('.eto-js-lng').val(0);
                    fields.find('.eto-js-place_id').val('').change();
                }
                etoFn.updateSummary(group, isEmpty);
            })
            .on('typeahead:render', function(e, tData) {})
            .on('typeahead:change', function(e, value) {
                // if (value.length > 0) {
                //     $(typeaheadInput).change();
                // }
            })
            .on('typeahead:open', function(e) {
                var value = $(e.target).val();
                typeaheadInputLastVal = value;

                if (value.length > 0) {
                    $(typeaheadInput).change();
                }
            })
            .on('typeahead:close', function(e) {
                $(typeaheadInput).blur();
            })
            .on('typeahead:asyncreceive', function(e, values) {
                if (locations.showGoogleLogo > 0) {
                    formContainer.find('.powered-by-google-locations').show();
                } else {
                    formContainer.find('.powered-by-google-locations').hide();
                }
            })
            // .on('blur', function(e) {})
            .on('typeahead:selected', function(e) {
                var isEmpty = null === $(typeaheadInput).val() || $(typeaheadInput).val().length === 0 ? true : false;
                etoFn.updateSummary(group, isEmpty);
                // etoFn.hideFields(group.closest('.eto-form'));
            });

        // setTimeout(function() {
        //     $(typeaheadInput).focusTextToEnd();
        // }, 0);
    };
    /**
     * !! Methods for callback !!
     */

    etoFn.select2Autoopen = function(select, value, selectAutoOpen, change) {
        if(typeof select.data('select2') != 'undefined') {
            if (((value === 0
                || value.toString().localeCompare('0') === 0
                || value.toString().localeCompare('') === 0)
                && selectAutoOpen === true)
                || selectAutoOpen.toString().localeCompare('always') === 0
            ) {
                setTimeout(function () {
                    select.select2('open');
                }, 0);
            }
            else if (change === true
                && value.toString().localeCompare('0') !== 0
                && value.toString().localeCompare('') !== 0
            ) {
                select.change();
            }
        }
    };

    etoFn.select2CustomerDepartments = function(container, customer, selected) {
        var departments = customer.departments,
            options = [],
            section = container.find('.eto-summary-link').attr('data-eto-section'),
            data = etoFn.config.objectForm !== false
                ? $.extend(true, {}, ETO[etoFn.config.objectForm].Form.form.sections[section].fields['department']) : {},
            confDefault = $.extend(true, {}, etoFn.config.defaults.display),
            haveSelected = false;

        data = $.extend(false, confDefault, data);
        data.jsClass = 'department';

        var selectConfig = data.callback.after.setSelect2FromConfig;

        $.each(departments, function(key, val) {
            options.push( {id: val, text: val});

            if (typeof selected != "undefined" && selected == val) {
                haveSelected = true;
            }
        });

        if (typeof selected != "undefined" && selected != "" && (departments.length === 0 || haveSelected === false)) {
            options.push({id: selected, text: selected});
        }



        if (container.find('select.eto-js-department').hasClass('select2-hidden-accessible')) {
            container.find('select.eto-js-department').select2('destroy');
            container.find('select.eto-js-department').html('');
        }

        if (options.length > 0 ) {
            container.find('select.eto-js-department').html('');
        }

        selectConfig.config = {
            minimumResultsForSearch: -1,
            placeholder: data.placeholder,
            data: options
        };

        etoFn.setSelect2({data: []}, 0, data, selectConfig, container, false);

        setTimeout(function () {
            if (typeof selected != "undefined" && selected != "") {container.find('select.eto-js-department').val(selected);}
            else {container.find('select.eto-js-department').val('0');}

            container.find('select.eto-js-department').change();
            ETO.updateFormPlaceholderInit(container);
        }, 0);
    };

    etoFn.select2DriverVehicles = function(container, driverId, vehicleId) {
        var options = [],
            vehicle = ETO.settings('driver-vehicles.'+driverId, false) !== false ? ETO.settings('driver-vehicles.'+driverId) : ETO.Booking.driverVehicles(driverId),
            selected = 0,
            section = container.find('.eto-summary-link').attr('data-eto-section'),
            data = etoFn.config.objectForm !== false
                ? $.extend(true, {}, ETO[etoFn.config.objectForm].Form.form.sections[section].fields['vehicle']) : {},
            confDefault = $.extend(true, {}, etoFn.config.defaults.display),
            formContainer = container.closest('.eto-form'),
            formId = formContainer.attr('id'),
            routeId = container.closest('.eto-route').attr('data-eto-route-id');

        if (typeof driverId == 'undefined' || isNaN(driverId)) { driverId = 0; }
        if (parseInt(driverId) > 0) {
            var user = typeof ETO.settings('driver') != 'undefined' && ETO.settings('driver.id') === parseInt(driverId) ? ETO.settings('driver') : ETO.Booking.getUser(driverId, 'driver');
        }

        data = $.extend(false, confDefault, data);
        data.jsClass = 'vehicle';

        var selectConfig = data.callback.after.setSelect2FromConfig;

        $.each(vehicle, function(key, val) {
            var option = {
                id: val.id,
                text: function() {
                    var name = val.name,
                        role = typeof val.role != 'undefined' ? val.role : '';

                    if (role.localeCompare('driver') === 0 && typeof val.profile.unique_id != 'undefined') {name = val.displayName; }
                    return name;
                }(),
                title: val.name
            };

            if (parseInt(val.selected) === 1){
               selected = val.id;
            }
            options.push(option);
        });

        if (selected === 0 && vehicle.length > 1) {
            $.each(vehicle, function(key, val) {
                if (val.id > 0) {
                    selected = val.id;
                    return false;
                }
            });
        }

        if (container.find('select.eto-js-vehicle').hasClass('select2-hidden-accessible')) {
            container.find('select.eto-js-vehicle').select2('destroy');
            container.find('select.eto-js-vehicle').html('');
        }

        selectConfig.config = {
            minimumResultsForSearch: -1,
            placeholder: data.placeholder,
            data: options
        };

        etoFn.setSelect2({data: []}, 0, data, selectConfig, container, false);

        if (vehicleId !== null) {container.find('select.eto-js-vehicle').val(vehicleId).change();}
        else {container.find('select.eto-js-vehicle').val(selected).change();}

        ETO.updateFormPlaceholderInit(container);

        var quote = ETO[etoFn.config.objectForm].Form.getTotalPriceFromObject(ETO.Form.config.form.values[formId].booking, true, true, true),
            userCommision = typeof user != 'undefined' && typeof user.profile.commission != 'undefined' ? user.profile.commission : 0,
            commission = ETO.User.Driver.getIncome(quote, formId, routeId, userCommision);

        formContainer.find('.eto-commission-auto-calculate').remove();
        formContainer.find('.eto-js-commission').after('<span class="eto-commission-auto-calculate" data-eto-commission="'+parseFloat(commission).toFixed(2)+'" data-original-title="'+ ETO.trans('booking.useThisPrice') +'">'+ETO.trans('booking.autoCalculated')+': '+ETO.formatPrice(commission) +' ('+userCommision+'%)</span>');
    };

    etoFn.select2FleetCommission = function(container, fleetId) {
        if (parseInt(fleetId) > 0) {
            var user = typeof ETO.settings('fleet') != 'undefined' && ETO.settings('fleet.id') === parseInt(fleetId) ? ETO.settings('fleet') : ETO.Booking.getUser(fleetId, 'fleet'),
                formContainer = container.closest('.eto-form'),
                formId = formContainer.attr('id'),
                routeId = container.closest('.eto-route').attr('data-eto-route-id'),
                quote = ETO[etoFn.config.objectForm].Form.getTotalPriceFromObject(ETO.Form.config.form.values[formId].booking, true, true, true),
                userCommision = typeof user != 'undefined' && typeof user.profile.commission != 'undefined' ? user.profile.commission : 0,
                commission = ETO.User.Fleet.getIncome(quote, formId, routeId, userCommision);

            formContainer.find('.eto-commission-auto-calculate').remove();
            formContainer.find('.eto-js-commission').after('<span class="eto-commission-auto-calculate" data-eto-commission="' + parseFloat(commission).toFixed(2) + '" data-original-title="' + ETO.trans('booking.useThisPrice') + '">' + ETO.trans('booking.autoCalculated') + ': ' + ETO.formatPrice(commission) + ' (' + userCommision + '%)</span>');
        }
    };

    etoFn.select2AjaxUser = function(url, value, selector, placeholder, parent) {
        placeholder = typeof placeholder == 'undefined' ? '' : placeholder;
        parent = typeof parent == 'undefined' ? 0 : parent;

        var perPage = 10,
            select2Params = {
            placeholder: placeholder,
            minimumInputLength: 0,
            ajax: ETO.ajax(url, {
                delay: 400,
                data: function(params) {
                    return {
                        search: params.term,
                        parent: parent,
                        page: params.page || 1,
                        perPage: perPage
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;

                    return {
                        results: $.map(data.items, function(item) {
                            return $.extend(true, item,{
                                text: function(item) {
                                    var name = item.name,
                                        role = typeof item.role != 'undefined' ? item.role : '';

                                    if (role == 'driver' && typeof item.profile.unique_id != 'undefined') {name = item.displayName; }

                                    return name;
                                }(item)
                            });
                        }),
                        pagination: {
                            more: (params.page * perPage) < data.count_items
                        }
                    };
                },
            }, 'post', false),
            templateResult: function(item) {
                if (!item.id) {
                    return item.text;
                }

                return etoFn.select2OptionMarkup(item);
            },
        };
        if (value.id != '' && parseInt(value.id) > 0){
            select2Params = $.extend(true, {data: [value]}, select2Params);
        }
        return select2Params;
    };

    etoFn.setSelect2 = function(source, value, data, selectConfig, container, dropdownParent) {
        var defaultConfig = $.extend(true, {}, etoFn.config.plugins.select2),
            selec2tConfig = typeof selectConfig.config != 'undefined' ? $.extend(true, defaultConfig, selectConfig.config) : defaultConfig,
            select = container.find('select.eto-js-' + data.jsClass),
            unassigned = [{id:0, text: ETO.trans('booking.booking_unassigned')}],
            options = typeof source.data != 'undefined'
                ? (Object.keys($.extend(true, {}, source.data)).length === 0
                    ? unassigned
                    : etoFn.select2GenerateOptions(value, source, selectConfig, select))
                : unassigned;

        if (Object.keys($.extend(true, {}, selec2tConfig.data)).length === 0) { selec2tConfig.data = options; }
        if (selectConfig.createSource === true) { selec2tConfig.tags = true; }

        // if (dropdownParent !== false) { selec2tConfig.dropdownParent = select.closest('.eto-field'); }

        if (data.createSource === true && value != '') {
            var newOption = new Option(value,value,true,true);
            container.find('select.eto-js-' + data.jsClass).append(newOption);
        }

        select.select2(selec2tConfig);
        select.on('select2:select', function(e) { etoFn.select2setSelected($(this)); });

        if (select.find('option:selected').length > 0) { etoFn.select2setSelected(select); }
    };

    etoFn.datePicker = function(input) {
        var format = ETO.convertDate(ETO.settings('date_format')) + ' ' + ETO.convertTime(ETO.settings('time_format'));

        input.daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            timePicker: true,
            timePicker24Hour: ETO.settings('time_format') == 'H:i',
            autoUpdateInput: false,
            timePickerIncrement: ETO.settings('time_every_minute') ? 1 : 5,
            locale: {
                format: format,
                firstDay: parseInt(ETO.settings('date_start_of_week'))
            },
            ranges: {
                'Today': [moment(), moment()],
                'Tomorrow': [moment().add(1, 'days'), moment().add(1, 'days')],
                'After Tomorrow': [moment().add(2, 'days'), moment().add(2, 'days')],
                'Next Week': [moment().add(7, 'days'), moment().add(7, 'days')],
                'Next Month': [moment().add(1, 'months'), moment().add(1, 'months')]
            },
        })
        .on('apply.daterangepicker', function(ev, picker) {
            input.closest('.eto-fields').find('input.eto-js-formatted_date').val(picker.startDate.format(format)).change();
            input.closest('.eto-fields').find('input.eto-js-date').val(picker.startDate.format('YYYY-MM-DD HH:mm')).change();
        });
    };

    etoFn.select2setSelected = function(select) {
        var selected = select.find('option:selected').text(),
            name = select.closest('.eto-field').attr('data-eto-field-name');

        etoFn.setValuesToObject(select);
        var textSelected = select.closest('.eto-group').find('input.eto-js-'+name+'_text_selected'),
            typeInput = select.closest('.eto-group').find('input.eto-js-'+name+'_type');

        if (textSelected.length > 0) {
            textSelected.val(selected);
            etoFn.setValuesToObject(textSelected);
        }
        if (typeInput.length > 0) {
            typeInput.val(selected);
            etoFn.setValuesToObject(typeInput);
        }
    };

    etoFn.select2GenerateOptions = function(value, source, selectConfig, select) {
        var options = [];

        $.each(source.data, function(key, val) {
            if(typeof val != 'object') {
                return false
            }
            var id = typeof val.code != 'undefined'
                    ? val.code
                    : val.id,
                text = selectConfig.sourceFrom == 'fields'
                    ? val.label
                    : (selectConfig.sourceFrom == 'payment'
                        ? etoFn.select2OptionMarkup(val)
                        : (!['vehicle', 'department'].indexOf(selectConfig.sourceFrom )
                            ? val.text
                            : val.name
                          )
                      ),
                option = val;

            option.id = id;
            option.text = text;

            if (typeof val.selected != 'undefined') {
                if (value === 0) {
                    value = id;
                }
            }
            if (value == id) {
                option.selected = true;
            }
            options.push(option);
        });

        return options;
    };

    etoFn.select2OptionMarkup = function(val, type) {
        var
            // image = typeof val.image_path != 'undefined' && val.image_path != ''
            // ? '<img src="'+ val.image_path +'" />'
            // : (typeof val.avatar_path != 'undefined' && val.avatar_path != ''
            //     ? '<img src="'+ val.avatar_path +'" />'
            //     : ''),
            markupAttr = [],
            role = typeof val.role != 'undefined' ? val.role : '',
            markup = '<div class="eto-select clearfix">';

        // if (image != '') {
        //     markup += '<span class="eto-select-group eto-select-group-icon">' + image + '</span>';
        // }
        var name = val.name;

        if (role == 'driver' &&typeof val.profile.unique_id != 'undefined') {
            name = val.displayName;
        }

        markup += '<span class="eto-select-group eto-select-group-name">' + name + '</span>';

        if (role == 'customer' && typeof  val.email != 'undefined' && val.email !=  null && val.email != '') {
            markupAttr.push('<span class="eto-select-group eto-select-group-email">' + val.email + '</span>');
        }
        if (role == 'customer' && typeof  val.profile != 'undefined') {
            if (typeof  val.profile.mobile_no != 'undefined' && val.profile.mobile_no != null && val.profile.mobile_no != '') {
                markupAttr.push('<span class="eto-select-group eto-select-group-phone">' + val.profile.mobile_no + '</span>');
            }
        }
        if (role == 'passenger') {
            if (typeof  val.passenger_email != 'undefined' && val.passenger_email !=  null && val.passenger_email != '') {
                markupAttr.push('<span class="eto-select-group eto-select-group-email">' + val.passenger_email + '</span>');
            }
            if (typeof  val.passenger_phone != 'undefined' && val.passenger_phone !=  null && val.passenger_phone != '') {
                markupAttr.push('<span class="eto-select-group eto-select-group-phone">' + val.passenger_phone + '</span>');
            }
        }

        if (markupAttr.length > 0) {
            markup += etoFn.trim(markupAttr.join(' '));
        }
        markup += '</div>';

        if (role == 'driver' && ETO[etoFn.config.objectForm].Form.form.settings.view.instant_dispatch_color_system === true) {
            return '<div class="marker-driver-label ' + val.driver_status + ' search-driver">' + markup + '</div>';
        }
        return markup;
    };

    etoFn.generateTagsSelect2 = function(params, select) {
        if (select.hasClass('eto-is-integer')) {
            params.term = isNaN(parseInt(params.term)) ? 0 : params.term;
        }

        var term = $.trim(params.term),
            existsVar = false,
            newTag = {
                id: params.term,
                text: params.term,
                newTag: true
            };

        if(term.length === 0 ) {
            return null;
        }
        if (select.find('option').length > 0){ // check if there is any option already
            select.find('option').each(function(){
                if ($(this).text().toUpperCase() == term.toUpperCase()) {
                    existsVar = true;
                    return false;
                }else{ existsVar = false; }
            });
            if (existsVar){ return null; }

            if (select.closest('.eto-section').attr('data-eto-section') == 'source') {
                select.on('select2:select', function(e) {
                    if (etoFn.objectHasValue(ETO.settings('source.data', ''), select.val()) === false) {
                        ETO.config.source.data[select.val()] = {
                            id: select.val(),
                            name: select.val()
                        };
                    }
                });
            }
            return newTag
        }
        else{ return newTag } //since select has 0 options, add new without comparing
    };

    etoFn.setTouchSpin = function(name, max, min, step, value, postfix, verticalbuttons, btnClassName) {
        var config = {},
            input = {};
        if (typeof name == 'undefined') {return;}
        if (typeof max != 'undefined' && max != false) {config.max = max;}
        else { config.max = 1000000000000000 } // Not needed!
        if (typeof min != 'undefined') {config.min = min;}
        if (typeof step != 'undefined') {
            config.step = step;
            if (step % 1 !== 0) {config.decimals = 2; config.step = 0.01;}
            config.boostat = 5;
            config.maxboostedstep = 10;
        }
        if (typeof postfix != 'undefined' && postfix !== null) {config.postfix = postfix;}

        if (name instanceof jQuery === true) { input = name; }
        else if (name.indexOf('#') !== -1) { input = $('input'+name); }
        else if (name.indexOf('.') !== -1) { input = $(name); }
        else { input = $("input[name='"+name+"']");}

        config.verticalbuttons = typeof verticalbuttons != "undefined" ? parseBoolean(verticalbuttons) : true;
        config.verticalupclass = 'ion-ios-plus-empty';
        config.verticaldownclass = 'ion-ios-minus-empty';
        config.buttondown_class = typeof btnClassName != "undefined" ? btnClassName : '';
        config.buttonup_class = typeof btnClassName != "undefined" ? btnClassName : '';

        if (value !== false && input.val().length === 0) {
           input.val(value).change();
        }
        input.TouchSpin(config);
    };

    etoFn.setSortable = function(elementName, elementType, container) {
        if (elementType == 'group') {
            var containerSelector = '.eto-section-' + elementName + ' .eto-groups',
                itemSelector = '.eto-group',
                handle = '.eto-fieldset-btn-handler';
                // handle = '.eto-group';
                // handle = '.eto-summary';
        }
        else if (elementType == 'section') {
            var containerSelector = '.eto-form',
                itemSelector = '.eto-section',
                handle = '.eto-section';
        }

        container.sortable('destroy').sortable({
            containerSelector: containerSelector,
            itemSelector: itemSelector,
            placeholder: '<div class="eto-order-placeholder-' + elementType + ' eto-order-placeholder-' + elementType + '-' + elementName + ' "></div>',
            placeholderClass: 'eto-order-placeholder-' + elementType,
            handle: handle,
            tolerance: 6,
            distance: 10,
            delay: 100,
            onDrop: function(item, container, _super) {
                _super(item, container);
                if (elementType == 'group') {
                    var el = $(container.target);
                    etoFn.reorderObjectValues(el);
                }
            },
        });
    };

    etoFn.reorderObjectValues = function(el, reorder) {
        var formId = el.closest('.eto-form').attr('id'),
            fromElement = typeof from != 'undefined' ? from : el,
            oldValues = $.extend(true, {},  ETO[etoFn.config.objectForm].Form.getSectionObjectValues(formId, fromElement));
        if (reorder === true) {
            ETO[etoFn.config.objectForm].Form.destroySectionObjectValues(formId, el);
        }
        $.each(el.children(), function(key, element) {
            ETO[etoFn.config.objectForm].Form.reorderSectionObjectValues(formId, el, $(element), oldValues, key);
            $(element).find('.eto-summary-link').attr('data-eto-index', key);
        });
    };

    etoFn.getLastIndexElement = function(el) {
        var childrens = el.children(),
            nodes = Array.prototype.slice.call( childrens ),
            last = childrens.last()[0],
            index = nodes.indexOf( last );

        if ( index === -1) { index = 0; }
        else { index = +index + 1; }

        return index;
    };

    etoFn.hideFields = function(form, el, generate) {
        var containers = form.find('.eto-group:not(.eto-group-open-always)');

        if(generate === true) {
            var inputs = form.find('.eto-js-inputs:not([data-eto-not-focus])');

            if (typeof el != 'undefined' && el.hasClass('eto-group')) {
                inputs.not(el.find('.eto-js-inputs')).blur();
            }
            else {
            //     inputs.blur();
            }
        }

        containers.not(el).find('.eto-summary').removeClass('hidden');
        containers.not(el).find('.eto-fieldset').addClass('hidden');
        containers.not(el).removeClass('eto-group-open');
        containers.not(el).find('.eto-fields').html('');
    };

    etoFn.enableDisableButton = function(element) {
        if (element instanceof jQuery === false) { element = $(element); }

        var section = element.closest('.eto-section').data('etoSection'),
            min = etoFn.config.objectForm !== false
                ? ETO[etoFn.config.objectForm].Form.config.limits.min[section] : 0,
            max = etoFn.config.objectForm !== false
                ? ETO[etoFn.config.objectForm].Form.config.limits.max[section] : 0,
            group = element.find('.eto-group'),
            groupCount = group.length;

        if ( section.localeCompare('passenger') === 0 ) {
            if (groupCount < max ) {
                group.closest('.eto-route').find('.eto-section-btn-add').removeClass('hidden');
            }
            else {
                group.closest('.eto-route').find('.eto-section-btn-add').addClass('hidden');
            }
        }

        ETO.updateTooltip();
        if (groupCount <= min) {
            group.find('.eto-summary-btn-delete').addClass('hidden');
            return false;
        }
        else {
            group.find('.eto-summary-btn-delete').removeClass('hidden');
            return true;
        }
    };

    /**
     * Validation
     */
    etoFn.validateField = function(field) {
        if (field instanceof jQuery === false) { field = $(field)}
        if (field.length === 0) {return 0;}

        var container = field.closest('.eto-field'),
            group = container.closest('.eto-group'),
            inCorrect = 0,
            errorMessage = [],
            value = field.val(),
            name = field.data('etoName'),
            index =  group.find('.eto-summary-link').data('etoIndex'),
            type = typeof field.attr('type') != 'undefined'
                ? field.attr('type') : field[0].tagName.toLowerCase(),
            section = group.closest('.eto-section').attr('data-eto-section'),
            routeId = group.closest('.eto-route').data('etoRouteId'),
            defaultFieldConfig = $.extend(true, {}, etoFn.config.defaults.field),
            fieldConfig = $.extend(true, defaultFieldConfig, ETO[etoFn.config.objectForm].Form.form.sections[section].fields[name]),
            validConfig = fieldConfig.validate,
            isChecked = container.find('.eto-js-inputs:checked').length > 0,
            isSelected = (container.find('select.eto-js-inputs').find('option:selected').length && container.find('select.eto-js-inputs').find('option:selected').val().localeCompare('') !== 0);

        if (field.length > 1) {
            for (var i in field) {
                if (isNaN(i) !== true) {
                    inCorrect = inCorrect + etoFn.validateField(field[i]);
                }
            }
        }
        else {
            ETO.createNewObject('Form', 'validation', routeId, section, index);

            if (field.hasClass('tt-hint')) { return 0; }

            if (validConfig.isRequired && ETO[etoFn.config.objectForm].Form.isValidateDate(section, container) === true) {
                 if (['checkbox', 'radio'].indexOf(type) !== -1 && isChecked === false) {
                    errorMessage.push(validConfig.errorMessage);
                    inCorrect++;
                } else if (['text', 'textarea'].indexOf(type) !== -1 && value.length === 0) {
                    errorMessage.push(validConfig.errorMessage);
                    inCorrect++;
                } else if (type.localeCompare('select') === 0 && isSelected === false) {
                    errorMessage.push(validConfig.errorMessage);
                    inCorrect++;
                }
            }
            if (validConfig.isDate === true && value.length === 0 && ETO[etoFn.config.objectForm].Form.isValidateDate(section, container) === true) {
                errorMessage.push(validConfig.errorMessage);
                inCorrect++;
            }
            if (validConfig.isEmail === true && value.length > 0 && etoFn.validateEmail(value) === false) {
                errorMessage.push(validConfig.errorMessage);
                inCorrect++;
            }
            if (validConfig.isPhone === true && value.length > 0 && field.intlTelInput('isValidNumber') === false) {
                errorMessage.push(validConfig.errorMessage);
                inCorrect++;
            }

            container.next('.eto-field-feedback').remove();
            field.closest('.eto-field').removeClass('eto-field-feedback-error');
            if (group.find('.eto-field-feedback-error').length === 0) {
                group.removeClass('eto-group-feedback-error');
            }

            if (inCorrect > 0) {
                if (etoFn.config.validation[routeId][section][index][name] === false || etoFn.validateEmail(value) === false) {

                    container.after('<div class="eto-field-feedback">' + errorMessage.join('<br>') + '</div>');
                    container.addClass('eto-field-feedback-error');

                    if (group.hasClass('eto-group-feedback-error') === false) {
                        container.closest('.eto-group:not(.hidden)').addClass('eto-group-feedback-error');
                    }
                }
                etoFn.config.validation[routeId][section][index][name] = false;
            }
            else {
                etoFn.config.validation[routeId][section][index][name] = true;
            }
        }

        return inCorrect;
    };
    /**
     * !! Validation !!
     */

    /**
     * methods for check and prepare single value
     */

    etoFn.isInteger = function(n){
        if (parseInt(n) === 0) {
            return true;
        }
        return typeof parseInt(n) == 'number' && typeof n != 'undefined' && n != '' && n != 'undefined' && parseInt(n) % 1 === 0;
    };

    etoFn.isFloat = function(n){
        return typeof parseFloat(n) == 'number' && typeof n != 'undefined' && n != '' && n != 'undefined' &&   parseFloat(n) % 1 !== 0;
    };

    etoFn.isNumber = function(n){
        if (etoFn.isInteger(n) === true || etoFn.isFloat(n) === true) { return true; }
        return false;
    };

    etoFn.trim = function(x) { return x.replace(/^\s+|,+$|<br>+$|\s+$/gm,''); };

    etoFn.validateEmail = function(email) {
        var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
    };

    etoFn.objectHasValue = function(object, value) {
        var vals = Object.keys(object).map(function(key) {
            return object[key];
        });
        if (vals.indexOf(value) > -1) { return true; }
        // if (Object.values(object).indexOf(value) > -1) { return true; } // This code is not working on iOS
        return false;
    };

    etoFn.findInObject = function(object, id) {
        for (var i in object) {
            if (object[i].id == id) {
                return $.extend(true,{},object[i]);
            }
        }
        return false;
    };

    // etoFn.convertDate = function(format) {
    //     switch (format) {
    //         case "jS F Y":
    //             format = "Do MMMM YYYY";
    //             break;
    //         case "j F Y":
    //             format = "DD MMMM YYYY";
    //             break;
    //         case "jS M Y":
    //             format = "Do MMM YYYY";
    //             break;
    //         case "j M Y":
    //             format = "DD MMMM YYYY";
    //             break;
    //         case "Y/m/d":
    //             format = "YYYY/MM/DD";
    //             break;
    //         case "m/d/Y":
    //             format = "MM/DD/YYYY";
    //             break;
    //         case "d/m/Y":
    //             format = "DD/MM/YYYY";
    //             break;
    //         case "Y-m-d":
    //             format = "YYYY-MM-DD";
    //             break;
    //         case "d-m-Y":
    //             format = "DD-MM-YYYY";
    //             break;
    //         case "m-d-Y":
    //             format = "MM-DD-YYYY";
    //             break;
    //         case "Y.m.d":
    //             format = "YYYY.MM.DD";
    //             break;
    //         case "d.m.Y":
    //             format = "DD.MM.YYYY";
    //             break;
    //         case "m.d.Y":
    //             format = "MM.DD.YYYY";
    //             break;
    //     }
    //     return format;
    // };

    // etoFn.convertTime = function(format) {
    //     switch (format) {
    //         case "H:i":
    //             format = "HH:mm";
    //             break;
    //         case "g:i a":
    //             format = "h:mm a";
    //             break;
    //         case "g:i A":
    //             format = "h:mm A";
    //             break;
    //     }
    //     return format;
    // };

    /**
     * !! methods for check and prepare single value !!
     */

    return etoFn;

}();
