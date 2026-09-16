@extends('admin.index')

@section('title', trans('backup.page_title'))
@section('subtitle', /*'<i class="fa fa-database"></i> '.*/ trans('backup.page_title'))

@section('subheader')
    <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/dataTables.bootstrap.css') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/extensions/Buttons/css/buttons.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/extensions/Buttons/css/buttons.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/extensions/ColReorder/css/colReorder.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/extensions/ColReorder/css/colReorder.bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset_url('plugins','bootstrap-daterangepicker/daterangepicker.css') }}">
@endsection

@section('subcontent')
    <div class="box-header" style="left: -6px;">
        <h4 class="box-title">{{ trans('backup.page_title') }}</h4>
        <div class="box-tools pull-right" style="right: 0;">
            @if ($minSize <+ $freeSpace)
            @permission('admin.backups.create')
            <button type="button" style="margin-right: 5px;" class="btn btn-sm btn-default eto-generate-buckup" data-toggle="modal" data-target=".eto-generate-buckup-form">
                <span>{{ trans('backup.button.create') }}</span>
            </button>
            @endpermission
            <button type="button" class="btn btn-sm btn-default eto-btn-settings" data-toggle="modal" data-target=".eto-modal-settings">
                <i class="fa fa-cogs"></i>
            </button>
            @endif
        </div>
    </div>

    <div class="eto-backups">
        <table class="table table-hover table-backups">
            <thead>
                <tr>
                    @if (auth()->user()->hasPermission([
                       'admin.backups.destroy',
                       'admin.backups.create',
                       'admin.backups.move']))
                    <th style="max-width:120px">Actions</th>
                    @endif
                    <th style="width:30%">Name</th>
                    <th style="width:8%">Size</th>
                    <th>Created at</th>
                </tr>
            </thead>
            <tbody>
            @foreach($backups as $id=>$backup)
                <tr class="eto-item" data-eto-id="{{ $backup->id }}" data-eto-disk="{{ $backup->disk }}">
                    @if (auth()->user()->hasPermission([
                        'admin.backups.destroy',
                        'admin.backups.create',
                        'admin.backups.move']))
                    <td>
                        <div class="btn-group pull-left">
                            @if ((int)$backup->status === 1 && $backup->file_exists)
                            <a href="{{ route('backup.download', ['id'=>$backup->id]) }}" class="btn btn-default btn-sm eto-download-buckup">
                                <span title="{{ trans('backup.button.download') }}">
                                    <i class="fa fa-download"></i>
                                </span>
                            </a>
                            @permission('admin.backups.recovery')
                            @if (($backup->type == 'full' || auth()->user()->hasRole('service')) && $backup->status == '1')
                            <a href="javascript:void(0);" class="btn btn-default btn-sm eto-recovery-buckup" data-eto-id="{{ $backup->id }}">
                                <span title="{{ trans('backup.button.recovery') }}">
                                    <i class="fa fa-mail-reply"></i>
                                </span>
                            </a>
                            @endif
                            @endpermission
                            @endif
                            @permission('admin.backups.destroy')
                            <a href="{{ route('backup.delete', ['id'=>$backup->id]) }}"  class="btn btn-default btn-sm eto-delete-buckup">
                                <span title="{{ trans('backup.button.delete') }}">
                                    <i class="fa fa-trash-o"></i>
                                </span>
                            </a>
                            @endpermission

                            @if (count($disks) > 0)
                            <a href="javascript:void(0);" class="btn btn-sm btn-default dropdown-toggle hidden" data-toggle="dropdown"  aria-haspopup="true" aria-expanded="false">
                                <span class="caret"></span>
                            </a>
                            <ul class="dropdown-menu" role="menu">
                                @permission('admin.backups.create')
    {{--                            <li>--}}
    {{--                                <a href="javascript:void(0);" class="eto-copy-to-driver" data-eto-id="{{ $backup->id }}"  data-toggle="modal" data-target=".eto-modal-copy">--}}
    {{--                                    <span>{{ trans('backup.button.copy') }}</span>--}}
    {{--                                </a>--}}
    {{--                            </li>--}}
                                @endpermission
                                @permission('admin.backups.move')
    {{--                            <li>--}}
    {{--                                <a href="javascript:void(0);" class="eto-move-to-driver" data-eto-id="{{ $backup->id }}"  data-toggle="modal" data-target=".eto-modal-move">--}}
    {{--                                    <span>{{ trans('backup.button.move') }}</span>--}}
    {{--                                </a>--}}
    {{--                            </li>--}}
                                @endpermission
                            </ul>
                            @endif
                        </div>
                    </td>
                    @endif
                    <td title="{{ $backup->file }}.zip">
                        {{ $backup->name }} <span style="color: #6e6e6e">({{ strtoupper($backup->disk) }})</span>
                        @if (!empty($backup->parent_id))
                            <i class="ion-ios-information-outline eto-has-patent" data-eto-parent-id="{{ $backup->parent_id }}" style="color:#a0a0a0; font-size:16px;" data-toggle="popover" data-title="" data-content="{{ trans('backup.parentInfo') }}" data-target="webuiPopover188"></i>
                            <br><span style="color: #6e6e6e">{{ $backup->comments }}</span>
                        @endif
                        @if (!empty($backup->comments))
                            <br><span style="color: #6e6e6e">{{ $backup->comments }}</span>
                        @endif
                        @if ((int)$backup->status === 0)
                            <br><span style="color: {{ trans('backup.statusColor.'.$backup->status) }}">{{ trans('backup.status.'.$backup->status) }}</span>
                        @endif
                        @if (!$backup->file_exists)
                            <br><span style="color: #CA3C3C">{{ trans('backup.status.file_not_exists') }}</span>
                        @endif
                    </td>
                    <td>{{ round(($backup->size / 1024 /1024), 2) }} MB</td>
                    <td>{{ $backup->created_at }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @include('backup.settings')
    @include('backup.backup-form')
@stop

@section('subfooter')
<script src="{{ asset_url('plugins','moment/moment-with-locales.min.js') }}"></script>
<script src="{{ asset_url('plugins','data-tables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset_url('plugins','data-tables/dataTables.bootstrap.min.js') }}"></script>
<script src="{{ asset_url('plugins','data-tables/extensions/Buttons/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset_url('plugins','data-tables/extensions/Buttons/js/buttons.bootstrap.min.js') }}"></script>
<script src="{{ asset_url('plugins','data-tables/extensions/Buttons/js/buttons.colVis.min.js') }}"></script>
<script src="{{ asset_url('plugins','data-tables/extensions/Buttons/js/buttons.server-side.js') }}"></script>
<script src="{{ asset_url('plugins','data-tables/extensions/ColReorder/js/dataTables.colReorder.min.js') }}"></script>
@include('layouts.eto-js')
<script src="{{ asset_url('js','recovery.js') }}"></script>
<script>
    var backups =  {!! \GuzzleHttp\json_encode($backups) !!};
    var disks =  {!! \GuzzleHttp\json_encode($disks) !!};
    var percent = 0;
    window.translations = {
        backup: {!! \GuzzleHttp\json_encode(trans('backup')) !!}
    };

    function backup(data, uri, progress, num) { // num - max percentage to process
        var response = {};

        progress = typeof progress != 'undefined' ? progress : 1;

        ETO.ajax('backup/' + uri, {
            data: data,
            async: false,
            success: function (resp) {
                response = resp;

                if(typeof response.list != 'undefined' && Object.keys(response.list).length > 0) {
                    num = typeof num == 'undefined' ? Object.keys(response.list).length : num;
                    var process = ((progress * Object.keys(response.list).length) / num);

                    progressbar(process + percent);

                    setTimeout(function () {
                        var dataResp = backup(response, uri, progress, num);

                        if(typeof dataResp.backupId != 'undefined') {
                            response = dataResp;
                        } else {
                            response.status = 'fail';
                        }
                    }, 1000);
                }
                else {
                    response.status = true;
                }
            },
            error: function () {
                response = data;
                response.status = false;
            }
        });

        return response;
    }

    function backupVendor(data, progress, num) {
        var response = {};

        progress = typeof progress != 'undefined' ? progress : 1;

        ETO.ajax('backup/backup-vendor-files', {
            data: data,
            async: false,
            success: function (resp) {
                response = resp;

                if(typeof response.vendor != 'undefined' && Object.keys(response.vendor).length > 0) {
                    var process = ((progress * Object.keys(response.vendor).length) / num);
                    progressbar(process + percent);

                    setTimeout(function () {
                        var dataResp = backupVendor(response, progress, num);
                        if(typeof dataResp.backupId != 'undefined') {
                            response = dataResp;
                        } else {
                            response.status = 'fail';
                        }
                    }, 1000);
                }
                else {
                    response.status = true;
                }
            },
            error: function () {
                response = {status: false};
            }
        });

        return response;
    }

    function objectifyForm(formArray) {//serialize data function

        var returnArray = {};
        for (var i = 0; i < formArray.length; i++){
            returnArray[formArray[i]['name']] = formArray[i]['value'];
        }
        return returnArray;
    }

    function updateTableHeight() {
        var height = parseFloat($('.wrapper > .content-wrapper').css('min-height')) -
            $('.table-backups > .topContainer').height() -
            $('.table-backups > .bottomContainer').height() -
            $('.dataTables_scrollHead').height() - 120;

        if( height < 200 ) {
            height = 200;
        }

        $('.eto-backups .dataTables_scrollBody').css({'min-height': height +'px'});
    }

    function beforeunload(e) {
        e.preventDefault();
        e.returnValue = '';
        // delete e['returnValue'];
    }

    $(document).ready(function() {
        if (ETO.model === false) {
            ETO.init({ config: ['settings'], lang: ['user', 'backup'] }, 'backup');
        }

        var settings = {user: {filesystems: {disks: ETO.current_user.settings.filesystems.disks}}},
            lang = ETO.lang.backup,
            settingsContainer = $('.eto-modal-settings');

        $('.table-backups').dataTable({
            deferLoading: 0,
            colReorder: true,
            paging: true,
            pagingType: 'full_numbers',
            dom: ETO.datatable.dom,
            buttons: ETO.datatable.buttons(),
            scrollX: true,
            searching: true,
            ordering: true,
            lengthChange: true,
            info: true,
            autoWidth: false,
            stateSave: true,
            stateDuration: 0,
            order: [],
            pageLength: 10,
            lengthMenu: ETO.datatable.lengthMenu,
            language: ETO.datatable.language(ETO.lang.user),
            drawCallback: function(settings) {
                if ($(this).find('tr').length > 0) {
                    var pagination = $(this).closest('.dataTables_wrapper').find('.dataTables_paginate');
                    if (typeof this.api().page.info() != 'undefined') {
                        pagination.toggle(this.api().page.info().pages > 1);
                    }
                }
            },
            infoCallback: function( settings, start, end, max, total, pre ) {
                return '<i class="ion-ios-information-outline" data-toggle="popover" data-title="" data-content="'+ pre +'"></i>';
            },
        });

        $('.eto-modal-settings input.eto-settings-driver').on('change', function(e) {
            var value = $(this).closest('.form-group').find(':checked').val();

            if(value.localeCompare('none') === 0) {
                $(this).closest('.eto-ftp-data').find('input:not(.eto-settings-driver)').attr('disabled', true);
            }
            else if(value.localeCompare('ftp') === 0) {
                $(this).closest('.eto-ftp-data').find('input:not(.eto-settings-driver)').attr('disabled', true);
                $(this).closest('.eto-ftp-data').find('input.eto-ftp').attr('disabled', false);
            }
            else if(value.localeCompare('sftp') === 0) {
                $(this).closest('.eto-ftp-data').find('input:not(.eto-settings-driver)').attr('disabled', true);
                $(this).closest('.eto-ftp-data').find('input.eto-sftp').attr('disabled', false);
            }
        });

        ETO.configFormUpdate(settingsContainer.find('input'), settings);

        $('.eto-settings-driver:checked').change();

        if($('.eto-settings-driver:checked').val() == 'ftp' && typeof settings.user.filesystems.disks.backup_ftp.host != "undefined") {
            $('.eto-modal-settings input#use_ftp').attr('checked', true).change();
            $('.eto-ftp-data').removeClass('hidden');
        }

        settingsContainer.on('hide.bs.modal', function (e) {
            if(!$('.eto-form-progress').hasClass('hidden')) {
                return false;
            }
        })
        .on('change', 'input#use_ftp', function(e) {
            if ($(this).attr('checked') == 'checked') {
                $('.eto-ftp-data').removeClass('hidden');
                $('.eto-settings-driver[value="none"]').attr('checked', false);
                $('.eto-settings-driver[value="ftp"]').attr('checked', true);
            } else {
                $('.eto-ftp-data').addClass('hidden');
                $('.eto-settings-driver[value="ftp"]').attr('checked', false);
                $('.eto-settings-driver[value="none"]').attr('checked', true);
            }
            $('.eto-settings-driver:checked').change();
        })
        .on('change', 'input', function(e) {
            var values = ETO.parseSettings($(this));

            if (Object.keys(values).length > 0) {
                ETO.saveSettings(values);
            }
        });

        $('.eto-recovery-buckup').on('click', function() {
            window.addEventListener('beforeunload', beforeunload, false);
            var id = $(this).data('etoId');
            recoveryRun(id);
            window.removeEventListener('beforeunload', beforeunload, false);
        });

        $(".eto-delete-buckup").click(function(e) {
            var href = $(this).attr('href');
            e.preventDefault();

            ETO.swalWithBootstrapButtons({
                type: 'warning',
                html: '<h4>{{ trans('backup.deleteMessage') }}</h4>',
                showCancelButton: true,
                confirmButtonText: '{{ trans('backup.button.delete') }}',
            })
            .then(function (result) {
                if (result.value) {
                    window.location.href = href;
                }
            });
        });

        $('#backup-type').select2({
            data: [
                // {id: '', text: ''},
                {id: 'full', text: '{{ trans('backup.type.full') }}'},
                {id: 'subscription', text: '{{ trans('backup.type.subscription') }}'},
                {{--{id: 'db', text: '{{ trans('backup.type.db') }}'},--}}
                {{--{id: 'files', text: '{{ trans('backup.type.files') }}'}--}}
            ],
            width: '100%',
            placeholder: '{{ trans('backup.input.type') }}',
            closeOnSelect: true,
            dropdownAutoWidth: true,
            allowClear: false,
        })
      /*  .on('change', function() {
            if($(this).val() == 'full' || $(this).val() == 'files') {
                $('.eto-files-backup').removeClass('hidden');
            }
            else {
                $('.eto-files-backup').addClass('hidden');
            }
        })*/;

        $('#backup-fileList').select2({
            data: [
                {id: '', text: ''},
                    <?php $i = 0; ?>
                @foreach($fileList as $id=>$item)
                {id: '{{ $item }}', text: '{{ $item }}'}@if ($i <= count($fileList)),
                    @endif
                    <?php $i++; ?>
                @endforeach
            ],
            width: '100%',
            placeholder: '{{ trans('backup.input.files') }}',
            closeOnSelect: true,
            dropdownAutoWidth: true,
            allowClear: true,
        });

        $('#backup-dirList').select2({
            data: [
                {id: '', text: ''},
                    <?php $i = 0; ?>
                @foreach($dirList as $idd=>$item)
                {id: '{{ $item }}', text: '{{ $item }}'}@if ($i <= count($dirList)),
                    @endif
                    <?php $i++; ?>
                @endforeach
            ],
            width: '100%',
            placeholder: '{{ trans('backup.input.directories') }}',
            closeOnSelect: true,
            dropdownAutoWidth: true,
            allowClear: true,
        });

        $('#backup-disk').select2({
            data: [
                {id: 'local', text: '{{ trans('backup.driverLocal') }}'},
                    <?php $i = 0; ?>
                @foreach($disks as $key=>$item)
                {id: '{{ str_replace('backup_', '', $key) }}', text: '{{ strtoupper (str_replace('backup_', '', $key)) }}'}@if ($i <= count($disks)),
                    @endif
                    <?php $i++; ?>
                @endforeach
            ],
            width: '100%',
            placeholder: '{{ trans('backup.input.directories') }}',
            closeOnSelect: true,
            dropdownAutoWidth: true,
            allowClear: false,
        });

        $('.eto-new-backup').on('submit', function(e) {
            e.preventDefault();
            window.addEventListener('beforeunload', beforeunload, false);
            var data = objectifyForm($(this).serializeArray());

            progressbar(1, "{{ trans('backup.new.bd_backup') }}");

            setTimeout(function () {

                percent = 10;
                var response = backup(data, 'backup-db', percent, 20);

                if (response.status === true) {
                    percent = 30;
                    progressbar(percent, "{{ trans('backup.new.app_files_backup') }}");

                    setTimeout(function () {
                    response = backup(response, 'backup-app-files', percent, 30);

                        if (response.status === true) {
                            percent = 50;
                            progressbar(percent, "{{ trans('backup.new.vendor_backup') }}");

                            setTimeout(function () {
                                response = backupVendor(response, percent, 40);
                                progressbar(90, "{{ trans('backup.new.termination_process') }}");
                                response = backup(response, 'move-backup-zip');
                                window.removeEventListener('beforeunload', beforeunload, false);

                                setTimeout(function () {
                                    if (response.status === true) {
                                        progressbar(100, "{{ trans('backup.new.completed') }}", function() { window.location.reload(); });
                                    } else {
                                        progressbar(100, "{{ trans('backup.new.not_completed') }}");
                                    }
                                }, 300);
                            }, 0);
                        }
                    }, 0);
                }
            }, 0);
        });

        $('.eto-new-copy').on('submit', function(e) {
            var timerInterval;
            e.preventDefault();
            $.LoadingOverlay('show');
            ETO.ajax('backup/copy', {
                data: $(this).serialize(),
                async: false,
                success: function (resp) {
                    $.LoadingOverlay('hide');
                    if (resp.status === true) {
                        Swal.fire({
                            html: '{{ trans('installer.backupCopied') }}',
                            timer: 3000,
                            onClose: function () {
                                clearInterval(timerInterval);
                                window.location.reload();
                            }
                        })
                    } else {
                        if(resp.message != '') {
                            Swal.fire({
                                type: 'warning',
                                html: resp.message,
                                onClose: function () {
                                    clearInterval(timerInterval);
                                    window.location.reload();
                                }
                            })
                        }
                        else {
                            Swal.fire({
                                type: 'warning',
                                html: '{{ trans('installer.connectionFail') }}',
                                timer: 5000,
                                onClose: function () {
                                    clearInterval(timerInterval);
                                    window.location.reload();
                                }
                            })
                        }
                    }
                    $('.eto-new-backup')[0].reset();
                },
                error: function () {
                    $.LoadingOverlay('hide');
                    Swal.fire({
                        type: 'warning',
                        html: '{{ trans('backup.connectionFail') }}',
                        timer: 3000,
                        onClose: function () {
                            clearInterval(timerInterval);
                            window.location.reload();
                        }
                    })
                }
            });
        });

        $('.eto-new-move').on('submit', function(e) {
            var timerInterval;
            e.preventDefault();
            $.LoadingOverlay('show');
            ETO.ajax('backup/move', {
                data: $(this).serialize(),
                async: false,
                success: function (resp) {
                    $.LoadingOverlay('hide');
                    if (resp.status === true) {
                        Swal.fire({
                            html: '{{ trans('installer.backupMoved') }}',
                            timer: 3000,
                            onClose: function () {
                                clearInterval(timerInterval);
                                window.location.reload();
                            }
                        })
                    } else {
                        if(resp.message != '') {
                            Swal.fire({
                                type: 'warning',
                                html: resp.message,
                                onClose: function () {
                                    clearInterval(timerInterval);
                                    window.location.reload();
                                }
                            })
                        }
                        else {
                            Swal.fire({
                                type: 'warning',
                                html: '{{ trans('installer.connectionFail') }}',
                                timer: 5000,
                                onClose: function () {
                                    clearInterval(timerInterval);
                                    window.location.reload();
                                }
                            })
                        }
                    }
                    $('.eto-new-backup')[0].reset();
                },
                error: function () {
                    $.LoadingOverlay('hide');
                    Swal.fire({
                        type: 'warning',
                        html: '{{ trans('backup.connectionFail') }}',
                        timer: 3000,
                        onClose: function () {
                            clearInterval(timerInterval);
                            window.location.reload();
                        }
                    })
                }
            });
        });

        $('.eto-generate-buckup').on('click', function() {
            $('.eto-new-backup')[0].reset();
        });

        $('.eto-has-patent').on('mouseover', function() {
            var id = $(this).data('etoParentId');

            $('tr[data-eto-id="'+id+'"]').find('td').css('background-color', '#b9b9b9')
        }).on('mouseleave', function() {
            var id = $(this).data('etoParentId');

            $('tr[data-eto-id="'+id+'"]').find('td').css('background-color', 'transparent')
        });

        $('.eto-copy-to-driver').on('click', function() {
            var id = $(this).closest('tr').data('etoId'),
                usedDisk = $(this).closest('tr').data('etoDisk'),
                backup = {},
                data = [];

            for(var i in backups) {
                if(backups[i].id == id) {
                    backup = backups[i];
                    break;
                }
            }

            if(usedDisk != 'local') {
                data.push({id: 'local', text: lang.driverLocal})
            }

            for(var i in disks) {
                if(i != usedDisk) {
                    data.push({id: i.replace('backup_', ''), text: i.replace('backup_', '').toUpperCase()})
                }
            }

            $('.eto-modal-copy').find('input[name="id"]').val(id);
            $('.eto-label-backup').html(backup.name +' - '+ backup.created_at);


            $('#backup-copy').select2({
                data: data,
                width: '100%',
                placeholder: '{{ trans('backup.input.directories') }}',
                closeOnSelect: true,
                dropdownAutoWidth: true,
                allowClear: true,
            });
        });

        $('.eto-move-to-driver').on('click', function() {
            var id = $(this).closest('tr').data('etoId'),
                usedDisk = $(this).closest('tr').data('etoDisk'),
                backup = {},
                data = [];

            for(var i in backups) {
                if(backups[i].id == id) {
                    backup = backups[i];
                    break;
                }
            }

            if(usedDisk != 'local') {
                data.push({id: 'local', text: lang.driverLocal})
            }

            for(var i in disks) {
                if(i != usedDisk) {
                    data.push({id: i.replace('backup_', ''), text: i.replace('backup_', '').toUpperCase()})
                }
            }

            $('.eto-modal-move').find('input[name="id"]').val(id);
            $('.eto-label-backup').html(backup.name +' - '+ backup.created_at);

            $('#backup-move').select2({
                data: data,
                width: '100%',
                placeholder: '{{ trans('backup.input.directories') }}',
                closeOnSelect: true,
                dropdownAutoWidth: true,
                allowClear: true,
            });
        });

        $("select").closest("form").on("reset",function(ev){
            var targetJQForm = $(ev.target);
            setTimeout((function(){
                this.find("select").trigger("change");
            }).bind(targetJQForm),0);
            $('[name="_token"]').val('{{ csrf_token() }}');

        });

        // Adjust panels
        updateTableHeight();
        ETO.updatePopover();
    });

    $(window).resize(function() {
        updateTableHeight();
    });
</script>
@stop
