

function trans(key, replace = {}) {
    var translation = key.split('.').reduce((t, i) => t[i] || null, window.translations);

    for (var placeholder in replace) {
        translation = translation.replace(`:${placeholder}`, replace[placeholder]);
    }

    return translation;
}

function progressbar(progress, info, calback) {
    if(progress > 0 && progress < 100) {
        $('.eto-form-data, .modal-footer').addClass('hidden');
        $('.eto-form-progress').removeClass('hidden');
        if(typeof info != "undefined") {
            $('.eto-form-progress-info').html(info);
        }
        $('.progress-bar').attr('style', 'width: '+progress+'%').html(progress + '%');
    } else if(progress === 100) {
        if(typeof info != "undefined") {
            $('.eto-form-progress-info').html(info);
        }
        $('.progress-bar').attr('style', 'width: '+progress+'%').html(progress + '%');
        if(typeof calback != 'undefined') {
            setTimeout(calback, 5000);
        }
        setTimeout(progressbar, 5000);
    } else {
        $('.eto-form-data, .modal-footer').removeClass('hidden');
        $('.eto-form-progress').addClass('hidden');
        $('.eto-form-progress-info').html('');
        $('.progress-bar').attr('style', 'width: 0').html('');
    }
}

function url(uri) {
    return ETO.config.appPath + '/' + uri;
}

function recoveryComplete(backup) {
    $.LoadingOverlay("progress", 90);
    $.LoadingOverlay("text", trans('backup.recovery.recoveryComplite'));

    ETO.ajax('updater.php?get=complete&dir=' + backup.file + '&systemRecovery=1', {
        data: {},
        async: false,
        success: function (data) {
            if (data.status === true) {
                $.LoadingOverlay("progress", 100);
                setTimeout(function(){
                    window.location.reload();
                }, 5000);
            } else {
                $.LoadingOverlay("text", trans('backup.recovery.recoveryFailRecoveryFiles', {url: url('updater.php?fail=files')}));
            }
        },
        error: function () {
            $.LoadingOverlay("text", trans('backup.recovery.recoveryFailRecoveryFiles', {url: url('updater.php?fail=files')}));
        },
    });

}

function recoveryFiles(backup) {
    ETO.ajax('updater.php?get=files&dir=' + backup.file + '&systemRecovery=1', {
        data: {},
        async: false,
        success: function (data) {
            if (data.status === true) {
                if(backup.type != 'files') {
                    $.LoadingOverlay("progress", 50);
                    $.LoadingOverlay("text", trans('backup.recovery.recoveryDb'));

                    recoveryDb(backup);
                }
                else {
                    recoveryComplete(backup)
                }
            } else {
                $.LoadingOverlay("text", trans('backup.recovery.recoveryFailRecoveryFiles', {url: url('updater.php?fail=files')}));
            }
        },
        error: function () {
            $.LoadingOverlay("text", trans('backup.recovery.recoveryFailRecoveryFiles', {url: url('updater.php?fail=files')}));
        },
    });
}

function recoveryExtract(backup) {
    if (typeof backup != 'undefined') {
        ETO.ajax('updater.php?get=extract&dir=' + backup.file + '&systemRecovery=1', {
            data: {},
            async: false,
            success: function (data) {
                if (data.status === true) {

                } else {
                    $.LoadingOverlay("text", trans('backup.recovery.recoveryFailRecoveryFiles', {url: url('updater.php?fail=files')}));
                }
            },
            error: function () {
                $.LoadingOverlay("text", trans('backup.recovery.recoveryFailRecoveryFiles', {url: url('updater.php?fail=files')}));
            },
        });
    }
}

function recoveryDb(backup) {
    ETO.ajax('updater.php?get=db&dir=' + backup.file + '&systemRecovery=1', {
        data: {},
        async: false,
        success: function (data) {
            if (data.status === true) {
                recoveryComplete(backup)
            } else {
                $.LoadingOverlay("text", trans('backup.recovery.recoveryFailRecoveryDb', {url: url('updater.php?fail=files')}));
            }
        },
        error: function () {
            $.LoadingOverlay("text", trans('backup.recovery.recoveryFailRecoveryDb', {url: url('updater.php?fail=files')}));
        },
    });
}

function recoveryRun(id) {
    ETO.swalWithBootstrapButtons({
        type: 'warning',
        input: 'text',
        inputPlaceholder: trans('backup.enter_license_key'),
        inputAttributes: {
            autocapitalize: 'off',
            isRequired: true,
        },
        html: '<h4>'+ trans('backup.recoveryMessage') +'</h4>',
        showCancelButton: true,
        confirmButtonText: trans('backup.button.recovery'),
    })
    .then(function (result) {
        if (result.value && result.value.length > 0) {
            $.LoadingOverlay("show", {
                image: '',
                progress    : true,
                text  : trans('backup.recovery.extracting'),
                textResizeFactor: 0.3,
                textAutoResize: true,
            });

            $.LoadingOverlay("progress", 1);

            ETO.ajax('backup/recovery/' + id, {
                data: {license: result.value},
                async: true,
                success: function(data) {
                    var backup = data.backup,
                        message = typeof data.message != 'undefined' ? data.message : '';

                    if(data.status === true)  {
                        $.LoadingOverlay("progress", 10);
                        $.LoadingOverlay("text", trans('backup.recovery.startRecovery'));
                        setTimeout(function(){
                            $.LoadingOverlay("progress", 20);
                            $.LoadingOverlay("text", trans('backup.recovery.recoveryFiles'));

                            recoveryExtract(backup);

                            if(backup.type != 'db') {
                                recoveryFiles(backup);
                            }
                            else {
                                recoveryDb(backup);
                            }

                        }, 1000);
                    }
                    else {
                        $.LoadingOverlay("text", trans('backup.recovery.recoveryFailExtract') + message);
                        setTimeout(function(){
                            $.LoadingOverlay('hide');
                        }, 10000);
                    }
                },
                error: function() {
                    // $.LoadingOverlay('hide');
                    ETO.swalWithBootstrapButtons({type: 'error', title: 'Please try again'});
                },
                complete: function() {
                    // $.LoadingOverlay('hide');
                }
            });
        }
    });
}
