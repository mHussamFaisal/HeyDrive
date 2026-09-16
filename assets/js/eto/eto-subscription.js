
ETO.Subscription = function() {
    var etoFn = {};

    etoFn.config = {
        init: [],
        lang: ['user'],
        updateUrl: '',
        storeUrl: '',
        errors: [],
        modules: {},
        client: {},
        coreUpdate: {},
        licenseKey: '',
        percent: 0,
        backup_data: {},
        fail_message: '',
        tmp_folder: '',
        request_data: {},
        update_percent: 20,
        backup_percent: 50,
    };

    etoFn.init = function(config) {
        ETO.extendConfig(this, config, 'subscription');
        window.translations = ETO.lang;

        etoFn.config.fail_message = ETO.trans('subscription.update.fail') + ' ';

        $.LoadingOverlay('show');

        $('body').on('click', '.eto-button-check', function(e) {
            etoFn.checkUpdates();
        })
        .on('click', '.eto-button-disable-license', function(e) {
            ETO.swalWithBootstrapButtons({
                type: 'info',
                html: '<h3>'+ ETO.trans('subscription.message.disableLicense') + '</h3>',
                showCancelButton: true,
                confirmButtonText: ETO.trans('subscription.button.disableLicense'),
                showLoaderOnConfirm: true,
            })
            .then(function(result) {
                if (result.value) {
                    window.location.href = 'deactivation';
                }
            });
        })
        .on('click', '.eto-close-modal', function(e) {
            $(this).closest('#update-modal').modal('hide')
        })
        .on('change', '[name="typeLicense"]', function(e) {
            var val = $('[name="typeLicense"]:checked').val();

            if (val == 'pro') {
                $('.typeLicense').removeClass('hidden');
            }
            else {
                $('.typeLicense').addClass('hidden');
            }
        })
        .on('click', '.eto-button-install', function(e) {
            var type = $(this).closest('tr').attr('data-eto-type'),
                module = [],
                html = '<h4>' + ETO.trans('subscription.install_header') + '</h4><input name="license_key" class="form-control" placeholder="' + ETO.trans('subscription.install_placeholder') + '">';

            for (var i in etoFn.config.modules) { if (etoFn.config.modules[i].type == type) { module = etoFn.config.modules[i]; break;} }

            if (typeof module.type != 'undefined') {
                if ((module.free === 1 && module.pro === 0) || (typeof module.license != 'undefined' && typeof module.license.diff != 'undefined' && module.license.isExpire === false)) {
                    etoFn.install( module ); return true;
                }
                else if (module.pro === 1 && parseInt(module.trial) > 0) {
                    html = ETO.trans('subscription.message.info_trial') +
                    '<br>' + ETO.trans('subscription.message.installTrial') + module.trial + ' days' +
                    '<br><a href="'+etoFn.config.storeUrl+'" target="_blank">' + ETO.trans('subscription.action.installTrial') + '</a>';
                }
            }

            ETO.swalWithBootstrapButtons({
                title: module.name,
                showCancelButton: true,
                html: html,
                confirmButtonText: ETO.trans('subscription.button.install'),
            })
            .then(function (result) {
                if (result.value) {
                    etoFn.install(module);
                }
            });
        })
        .on('click', '.eto-button-uninstall', function(e) {
            var type = $(this).closest('tr').attr('data-eto-type'),
                module = [];

            for (var i in etoFn.config.modules) { if (etoFn.config.modules[i].type == type) { module = etoFn.config.modules[i]; break;} }

            ETO.swalWithBootstrapButtons({
                title: 'Are you sure?',
                type: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, uninstall it',
            })
            .then(function (result) { if (result.value) { etoFn.uninstall(module); } });
        })
        .on('click', '.eto-button-update-core', function(e) {
            if (typeof etoFn.config.coreUpdate.versions != 'undefined') {
                var conditions = '',
                    header = ETO.trans('subscription.message.update_conditions'),
                    btnText = ETO.trans('subscription.button.continue');

                if (typeof etoFn.config.coreUpdate.params.conditions != 'undefined' && etoFn.config.coreUpdate.params.conditions.length) {
                    conditions +=  etoFn.config.coreUpdate.params.conditions + "<br>";
                }

                if (etoFn.config.coreUpdate.params.noUpdates === true) {
                    conditions += '<b style="color: darkred">Core update couldn\'t be completed "'+etoFn.config.coreUpdate.name+'"</b>';
                    etoFn.doModal('update-modal', header, conditions);
                } else {
                    conditions += ETO.trans('subscription.message.update_conditions_message');
                    conditions.replace(/\n\r/g, '<br />').replace(/\n/g, '<br />').replace(/\r/g, '<br />');
                    etoFn.doModal('update-modal', header, conditions, 'ETO.Subscription.updateCore()', btnText);
                }
            }
        })
        .on('click', '.eto-module-status', function(e) {
            var button = $(this),
                type = button.closest('tr').attr('data-eto-type'),
                status = button.attr('data-eto-status') == '1' ? 0 : 1;

            if (status === 0) {
                ETO.swalWithBootstrapButtons({
                    title: 'Are you sure?',
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, deactivate it',
                })
                .then(function (result) {
                    if (result.value) {
                        etoFn.disableModule(type, status, button);
                    }
                });
            }
            else {
                etoFn.disableModule(type, status, button);
            }
        })
        .on('click', '.eto-button-view-changelog', function(e) {
            var button = $(this),
                isCore = button.hasClass('eto-core'),
                licenseKey = button .closest('tr').attr('data-eto-key'),
                changelog = '',
                moduleTitle = '';

            if (isCore) {
                for (var ii in etoFn.config.coreUpdate.versions) {
                    if (etoFn.config.coreUpdate.versions[ii].changelog) {
                        changelog += '<div style="margin-bottom:20px;">';
                        changelog += '<h3 style="margin:0 0 5px 0;">v'+ etoFn.config.coreUpdate.versions[ii].version + '</h3>';
                        changelog += '<div>'+ etoFn.config.coreUpdate.versions[ii].changelog + '</div>';
                        changelog += '</div>';
                    }
                }
                moduleTitle = etoFn.config.coreUpdate.name;
            }
            else {
                for (var i in etoFn.config.modules) {
                    if (etoFn.config.modules[i].license == licenseKey) {
                        for (var ii in etoFn.config.modules[i].versions) {
                            if (etoFn.config.modules[i].versions[ii].changelog) {
                                changelog += '<div style="margin-bottom:20px;">';
                                changelog += '<h3 style="margin:0 0 5px 0;">v'+ etoFn.config.modules[i].versions[ii].version + '</h3>';
                                changelog += '<div>'+ etoFn.config.modules[i].versions[ii].changelog + '</div>';
                                changelog += '</div>';
                            }
                        }
                        moduleTitle = etoFn.config.modules[i].name;
                    }
                }
            }

            changelog.replace(/\n\r/g, '<br />').replace(/\n/g, '<br />').replace(/\r/g, '<br />');

            if (changelog == '') {
                changelog = ETO.trans('subscription.changelog_msg');
            }

            etoFn.doModal('update-modal', ETO.trans('subscription.changelog') + ' - ' + moduleTitle, changelog);
        });
        // .on('click', '.eto-button-change-license-key', function(e) {
        //     ETO.swalWithBootstrapButtons({
        //         title: ETO.trans('subscription.headers.changeLicense'),
        //         input: 'text',
        //         inputAttributes: {
        //             autocapitalize: 'off'
        //         },
        //         // html: '<input class="license_key" value="'+etoFn.config.licenseKey+'">',
        //         confirmButtonText: ETO.trans('subscription.button.change'),
        //     })
        //         .then(function (result) {
        //             if (result.value) {
        //                 etoFn.install(module);
        //             }
        //         });
        // });

        $('.modal:not(.eto-modal-settings)').on('hide.bs.modal', function (e) {
            if(!$(this).find('.eto-form-progress').hasClass('hidden')) {
                return false;
            }
        });

    };

    etoFn.checkUpdates = function () {
        Swal.fire({
            allowOutsideClick: false,
            title: ETO.trans('subscription.message.updatesChecking'),
            onBeforeOpen: function() {
                Swal.showLoading();
            },
        });

        ETO.ajax('subscription/check', {
            data: {},
            async: true,
            success: function(instalation) {
                if (instalation.status === true) {
                    if (instalation.new_versions === true) {
                        Swal.fire({
                            title: ETO.trans('subscription.message.available'),
                            timer: 2000,
                            showCloseButton: false,
                            showCancelButton: false,
                            showConfirmButton: false
                        }).then(function(result) {
                            window.location.reload();
                        });
                    }
                    else {
                        ETO.swalWithBootstrapButtons({
                            allowOutsideClick: false,
                            title: ETO.trans('subscription.message.noAvailable')
                        });
                    }
                }
                else {
                    if (typeof instalation.code != 'undefined') {
                        ETO.swalWithBootstrapButtons({type: 'varning', title: 'Response Fail Code '+instalation.code, html: instalation.message});
                    }
                    else {
                        ETO.swalWithBootstrapButtons({type: 'error', title: 'Please try again'});
                    }
                }
                $.LoadingOverlay('hide');
            },
            error: function() {
                $.LoadingOverlay('hide');
                ETO.swalWithBootstrapButtons({type: 'error', title: 'Please try again'});
            },
            complete: function() {
                $.LoadingOverlay('hide');
            }
        });
    };

    etoFn.recoveryOnFail = function () {
        if(typeof etoFn.config.backup_data.id != 'undefined') {
            recoveryExtract(etoFn.config.backup_data);
            recoveryFiles(etoFn.config.backup_data);
        }
    };

    etoFn.disableModule = function (type, status, button) {
        ETO.ajax('subscription/status', {
            data: {
                type: type,
                status: status,
            },
            async: true,
            success: function(update) {
                if (update.status === true) {
                    if (status === 0) {
                        button.attr('data-eto-status', status).html(ETO.trans('subscription.button.enable'));
                    }
                    else {
                        button.attr('data-eto-status', status).html(ETO.trans('subscription.button.disable'));
                    }
                    ETO.swalWithBootstrapButtons({
                        type: 'success',
                        title: 'Module has been updated'
                    });
                }
                else {
                    ETO.swalWithBootstrapButtons({
                        type: 'error',
                        title: 'Module could not be updated'
                    });
                }
            },
            error: function() {
                $.LoadingOverlay('hide');
                ETO.swalWithBootstrapButtons({
                    type: 'error',
                    title: 'An error has occurred during module update'
                });
            }
        });
    };

    etoFn.updateCore = function () {
        window.addEventListener('beforeunload', etoFn.beforeunload, false);

        if (typeof etoFn.config.coreUpdate.versions != 'undefined' && typeof etoFn.config.coreUpdate.maxUpdateVersion != 'undefined') {
            etoFn.config.request_data = {type: etoFn.config.coreUpdate.type, maxVersion: etoFn.config.coreUpdate.maxUpdateVersion};

            etoFn.progressbar(1, ETO.trans('subscription.update.start_process'), function() {
                etoFn.getUpdateArchive(etoFn.config.coreUpdate.name);
            });
        } else {
            etoFn.config.errors.push({module: etoFn.config.coreUpdate.name, message: ETO.trans('subscription.message.noAvailable')});
            etoFn.progressbar(null, etoFn.config.fail_message, etoFn.viewErrors);
        }
    };

    etoFn.getUpdateArchive = function(moduleName) {
        ETO.ajax(etoFn.config.updateUrl, {
            data: etoFn.config.request_data,
            async: true,
            success: function (response) {
                etoFn.config.tmp_folder = response.folder;
                delete response.list;
                etoFn.config.request_data = response;

                if (etoFn.config.request_data.status.toString().localeCompare('fail_request') !== 0) {
                    if (response.process.toString().localeCompare('backup') === 0) {
                        etoFn.progressbar(20, ETO.trans('subscription.update.generate_backup'), function() {
                            etoFn.backup(50, 20);
                        });
                    } else if (response.process.toString().localeCompare('extract') === 0) {
                        etoFn.progressbar(20, ETO.trans('subscription.update.extract_archive'), function() {
                            etoFn.update(response.process, 20);
                        });
                    }
                }
            },
            error: function (data) {
                $.LoadingOverlay('hide');
                etoFn.config.errors.push({module: moduleName, message: etoFn.config.fail_message});
                etoFn.progressbar(null, etoFn.config.fail_message, etoFn.viewErrors);
            },
            complete: function () {
                $.LoadingOverlay('hide');
            }
        });
    };

    etoFn.update = function (uri, progress, num) { // num - max percentage to process
        progress = typeof progress != 'undefined' ? progress : 1;
        num = typeof num != 'undefined' ? num : 1;

        ETO.ajax(etoFn.config.updateUrl + '/' + uri, {
            data: etoFn.config.request_data,
            async: true,
            success: function (response) {
                var isList = typeof response.list == 'object',
                    listLength = isList ? response.list.length : 1;

                delete response.list;
                etoFn.config.request_data = response;

                if (typeof response.status == 'undefined') {
                    etoFn.config.request_data.status = 'fail_request';
                } else {
                    etoFn.config.request_data = response;
                    var maxPercent = etoFn.config.backup_percent - etoFn.config.update_percent,
                        process = Math.round(progress + ((maxPercent * num) / listLength));

                    etoFn.progressbar(process , response.message, function () {
                        if (isList || typeof response.process != 'undefined' || response.status === true) {
                            num = typeof num == 'undefined' ? listLength : num;
                        }

                        if (['backup', 'move_backup'].indexOf(response.process.toString()) !== 0) {
                            etoFn.update(response.process, process, num);
                        } else {
                            if (typeof etoFn.config.request_data.status != 'undefined') {
                                etoFn.progressbar(process, response.message, function () {
                                    etoFn.backup(50, 20);
                                });
                            } else {
                                etoFn.config.request_data.status = 'fail_request';
                            }
                        }
                    });
                }
            },
            error: function () {
                etoFn.config.request_data.status = 'fail_request';
            }
        });
    };

    etoFn.backup = function (percent, progress, num) { // num - max percentage to process
        progress = typeof progress != 'undefined' ? progress : 1;

        if (typeof etoFn.config.request_data.status == 'undefined') {
            etoFn.config.errors.push({module: module, message: etoFn.config.fail_message});
            etoFn.progressbar(null, etoFn.config.fail_message, etoFn.viewErrors);
        } else if (etoFn.config.request_data.process.toString().localeCompare('update') === 0) {
            etoFn.progressbar(75, ETO.trans('subscription.update.files'), function () {
                etoFn.changeFiles();
            });
        } else {
            var isBackup = false;

            if (typeof etoFn.config.request_data.backup != 'undefined') {
                isBackup = true;
                etoFn.config.request_data.backupName = etoFn.config.request_data.backup.backupName;
                etoFn.config.request_data.backupId = etoFn.config.request_data.backup.backupId;
                etoFn.config.request_data.list = etoFn.config.request_data.backup.list;
                delete etoFn.config.request_data.backup;
            }

            delete etoFn.config.request_data.list;

            ETO.ajax(etoFn.config.updateUrl + '/backup', {
                data: etoFn.config.request_data,
                async: true,
                success: function (response) {
                    var isList = typeof response.list == 'object',
                        listLength = isList ? response.list.length : 1;

                    delete response.list;
                    etoFn.config.request_data = response;

                    if (typeof response.backup != 'undefined') {
                        var listNum = isBackup && isList ? listLength : 1;
                        num = typeof num == 'undefined' ? listNum : num;
                        var process = Math.round(percent + ((progress * num) / listNum));

                        etoFn.progressbar(process, response.message, function () {
                            etoFn.backup(process, progress, num);
                        });
                    } else {
                        etoFn.progressbar(75, ETO.trans('subscription.update.files'), function () {
                            etoFn.changeFiles();
                        });
                    }
                },
                error: function () {
                    etoFn.config.errors.push({module: module, message: etoFn.config.fail_message});
                    etoFn.progressbar(null, etoFn.config.fail_message, etoFn.viewErrors);
                }
            });
        }
    };

    etoFn.changeFiles = function() {
        ETO.ajax('updater.php', {
            data: {folder: etoFn.config.tmp_folder},
            async: true,
            success: function (update) {
                if (update.status === true) {
                    etoFn.progressbar(90, ETO.trans('subscription.update.migration'), function () {
                        etoFn.migrate()
                    });
                } else {
                    etoFn.config.errors.push({module: module, message: etoFn.config.fail_message + update.message});
                    etoFn.progressbar(null, etoFn.config.fail_message, etoFn.viewErrors);
                }
            },
            error: function (data) {
                $.LoadingOverlay('hide');
                etoFn.config.errors.push({module: module, message: etoFn.config.fail_message});
                etoFn.progressbar(null, etoFn.config.fail_message, etoFn.viewErrors);
            }
        });
    };

    etoFn.migrate = function() {
        ETO.ajax('subscription/migrate', {
            data: {type: etoFn.config.coreUpdate.type},
            async: true,
            success: function (update) {
                if (update.status === false) {
                    etoFn.config.errors.push({module: module, message: etoFn.config.fail_message + update.message});
                } else {
                    etoFn.progressbar(100, ETO.trans('subscription.update.success'), etoFn.finishUpdate);
                }
            },
            error: function (update) {
                $.LoadingOverlay('hide');
                etoFn.config.errors.push({module: module, message: etoFn.config.fail_message});
                etoFn.progressbar(null, etoFn.config.fail_message, etoFn.viewErrors);
            }
        });
    };

    etoFn.finishUpdate = function() {
        window.removeEventListener('beforeunload', etoFn.beforeunload, false);

        if (etoFn.config.errors.length === 0) {
            $('.modal').modal('hide');
            window.location.reload();
        }
        else {
            // etoFn.recoveryOnFail();
            etoFn.viewErrors();
        }
    };

    etoFn.viewErrors = function() {
        var errorHtml = '';
        for (var i in etoFn.config.errors) {
            errorHtml += etoFn.config.errors[i].module + ' - ' + etoFn.config.errors[i].message;
        }

        setTimeout(function() {
            ETO.swalWithBootstrapButtons({
                type: 'warning',
                title: 'The system could not be updated',
                html: errorHtml,
            })
            .then(function (result) {
                window.location.reload();
            });
        }, 0);
    };

    etoFn.install = function (module) {
        var installation_type = typeof module.params.free != 'undefined' && module.params.free === 1
            ? 'free'
            : (typeof module.params.trial != 'undefined' && module.params.trial > 0
            && (typeof module.license == 'undefined'
                || (typeof module.license.mode != 'undefined' && module.license.mode != 'pro' && module.license.mode != 'free'))
                ? 'trial'
                : 'pro');

        $.LoadingOverlay('show');
        ETO.ajax('subscription/install', {
            data: {
                type: module.type,
                installation_type: installation_type
            },
            async: true,
            success: function(instalation) {
                $.LoadingOverlay('hide');
                if (instalation.status === true) {
                    ETO.swalWithBootstrapButtons({type: 'success', title: 'Module has been successfully installed'})
                        .then(function (result) { if (result.value) { window.location.reload(); } })
                }
                else {
                    var title = 'Module could not be installed',
                        message = typeof instalation.message != 'undefined' ? instalation.message : '';

                    ETO.swalWithBootstrapButtons({type: 'error', title: title, html: message});
                }
            },
            error: function() {
                $.LoadingOverlay('hide');
                ETO.swalWithBootstrapButtons({type: 'error', title: 'An error has occurred during module installation'});
            }
        });
    };

    etoFn.uninstall = function (module) {
        var installation_type = typeof module.params.free != 'undefined' && module.params.free === 1
            ? 'free'
            : (typeof module.params.trial != 'undefined' && module.params.trial > 0
            && (typeof module.license == 'undefined'
                || (typeof module.license.mode != 'undefined' && module.license.mode != 'pro' && module.license.mode != 'free'))
                ? 'trial'
                : 'pro');

        ETO.ajax('subscription/uninstall', {
            data: {
                key: module.license,
                type: module.type,
                client_id: etoFn.config.client.id,
                installation_type: installation_type
            },
            async: true,
            success: function(instalation) {
                if (instalation.status === true) {
                    ETO.swalWithBootstrapButtons({type: 'success', title: 'Module has been successfully uninstalled'})
                        .then(function (result) { if (result.value) { window.location.reload(); } });
                }
                else {
                    ETO.swalWithBootstrapButtons({type: 'error', title: 'Module could not be uninstalled'});
                }
            },
            error: function() {
                $.LoadingOverlay('hide');
                ETO.swalWithBootstrapButtons({type: 'error', title: 'An error has occurred during module uninstallation'});
            }
        });
    };

    etoFn.doModal = function (placementId, heading, formContent, strSubmitFunc, btnText, classes) {
        var buttons = '';
        if(typeof classes == 'undefined') {
            classes = 'btn-success';
        }
        if (btnText != '' && typeof btnText != 'undefined') {
            buttons += '<span class="btn '+classes+'" onclick="'+strSubmitFunc+'">'+btnText+'</span>';
        }

        var html = '<div class="modal-header">\
            <button type="button" class="close" data-dismiss="modal">&times;</button>\
            <h4 class="modal-title">'+heading+'</h4>\
            </div>\
            <div class="modal-body">\
            <div class="row clearfix eto-form-data"><div class="col-xs-12">' + formContent + '</div></div>\
            <div class="row clearfix eto-form-progress hidden">\
            <p class="eto-form-progress-info"></p>\
            <div class="progress">\
            <div class="progress-bar progress-bar-primary progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>\
            </div>\
            </div>\
            </div>' +
            (buttons ? '<div class="modal-footer">'+buttons+'</div>' : '');

        $("#"+placementId+' .modal-content').html(html);
        $("#"+placementId).modal('show');
    };

    etoFn.beforeunload = function (e) {
        e.preventDefault();
        e.returnValue = '';
    };

    etoFn.progressbar = function (progress, info, calback) {
        if(progress > 0 && progress < 100) {
            $('.eto-form-data, .modal-footer').addClass('hidden');
            $('.eto-form-progress').removeClass('hidden');
            if(typeof info != "undefined") {
                $('.eto-form-progress-info').html(info);
            }
            $('.progress-bar').attr('style', 'width: '+progress+'%').html(progress + '%');
            if(typeof calback != 'undefined') {
                calback();
            }
        } else if(progress === 100) {
            if(typeof info != "undefined") {
                $('.eto-form-progress-info').html(info);
            }
            $('.progress-bar').attr('style', 'width: '+progress+'%').html(progress + '%');
            if(typeof calback != 'undefined') {
                setTimeout(calback, 5000);
            }
            setTimeout(etoFn.progressbar, 5000);
        } else {
            $('.eto-form-data, .modal-footer').removeClass('hidden');
            $('.eto-form-progress').addClass('hidden');
            $('.eto-form-progress-info').html('');
            $('.progress-bar').attr('style', 'width: 0').html('');
        }
    };

    return etoFn;
}();
