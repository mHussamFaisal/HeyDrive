<div class="modal fade eto-modal-settings" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">{{ trans('backup.settings') }}</h4>
            </div>
            <div class="modal-body">
                <!-- This option will disable autocomplete option in Google Chrome -->
                <input type="text" name="randomusernameremembered" id="randomusernameremembered" value="" style="width:0;height:0;visibility:hidden;position:absolute;left:0;top:0;margin:0;padding:0;border:0;background:none;">
                <input type="password" name="randompasswordremembered" id="randompasswordremembered" value="" style="width:0;height:0;visibility:hidden;position:absolute;left:0;top:0;margin:0;padding:0;border:0;background:none;">
                <!-- End -->

                <div class="row eto-switch">
                    <div class="form-group clearfix">
                        <label class="col-sm-4 control-label">
                            {{ trans('backup.remoteDisk') }}
                        </label>
                        <div class="col-sm-8">
                            <div class="onoffswitch ">
                                <input id="use_ftp" class="onoffswitch-input" type="checkbox" value="1">
                                <label class="onoffswitch-label" for="use_ftp"></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="eto-ftp-data hidden">
                    <div class="row">
                        <div class="form-group clearfix hidden">
                        <label class="col-sm-4 control-label">
                            {{ trans('backup.input.protocol') }}
                        </label>
                        <div class="col-sm-8">
                            <div class="col-sm-8">
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="driver" value="none" class="eto-settings-driver" data-eto-relation="user" data-eto-key="filesystems.disks.backup_ftp.driver" />
                                        {{ trans('backup.input.protocolNone') }}
                                    </label>
                                </div>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="driver" value="ftp" class="eto-settings-driver" data-eto-relation="user" data-eto-key="filesystems.disks.backup_ftp.driver"/>
                                        FTP
                                    </label>
                                </div>
                                <div class="radio hidden">
                                    <label>
                                        <input type="radio" name="driver" value="sftp" class="eto-settings-driver" data-eto-relation="user" data-eto-key="filesystems.disks.backup_ftp.driver"/>
                                        SFTP
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                    <div class="row">
                        <div class="form-group clearfix">
                        <label for="host" class="col-sm-4 control-label">
                            {{ trans('backup.input.host') }}
                        </label>
                        <div class="col-sm-8">
                            <input type="text" name="host" id="host" class="form-control eto-settings-host eto-ftp" data-eto-relation="user" data-eto-key="filesystems.disks.backup_ftp.host" disabled/>
                        </div>
                    </div>
                    </div>
                    <div class="row">
                        <div class="form-group clearfix">
                        <label for="username" class="col-sm-4 control-label">
                            {{ trans('backup.input.username') }}
                        </label>
                        <div class="col-sm-8">
                            <input type="text" name="username" id="username" class="form-control eto-settings-username eto-ftp" data-eto-relation="user" data-eto-key="filesystems.disks.backup_ftp.username" disabled/>
                        </div>
                    </div>
                    </div>
                    <div class="row">
                        <div class="form-group clearfix">
                        <label for="password" class="col-sm-4 control-label">
                            {{ trans('backup.input.password') }}
                        </label>
                        <div class="col-sm-8">
                            <input type="password" name="password" id="password" class="form-control eto-settings-password eto-ftp" data-eto-relation="user" data-eto-key="filesystems.disks.backup_ftp.password" disabled/>
                        </div>
                    </div>
                    </div>
                    <div class="row">
                        <div class="form-group clearfix hidden">
                        <label for="privateKey" class="col-sm-4 control-label">
                            {{ trans('backup.input.privateKey') }}
                        </label>
                        <div class="col-sm-8">
                            <input type="text" name="privateKey" id="privateKey" class="form-control eto-settings-privateKey" data-eto-relation="user" data-eto-key="filesystems.disks.backup_ftp.private_key" disabled/>
                        </div>
                    </div>
                    </div>
                    <div class="row">
                        <div class="form-group clearfix">
                        <label for="port" class="col-sm-4 control-label">
                            {{ trans('backup.input.port') }}
                        </label>
                        <div class="col-sm-8">
                            <input type="text" name="port" id="port" class="form-control eto-settings-port eto-ftp" data-eto-relation="user" data-eto-key="filesystems.disks.backup_ftp.port" disabled/>
                        </div>
                    </div>
                    </div>
                    <div class="row">
                        <div class="form-group clearfix">
                        <label for="root" class="col-sm-4 control-label">
                            {{ trans('backup.input.root') }}
                        </label>
                        <div class="col-sm-8">
                            <input type="text" name="root" id="root" placeholder="full path on server" class="form-control eto-settings-root eto-ftp" data-eto-relation="user" data-eto-key="filesystems.disks.backup_ftp.root" disabled/>
                        </div>
                    </div>
                    </div>
                    <div class="row">
                        <div class="form-group clearfix">
                        <label for="timeout" class="col-sm-4 control-label">
                            {{ trans('backup.input.timeout') }}
                        </label>
                        <div class="col-sm-8">
                            <input type="number" min="-1" step="1" name="timeout" id="timeout" class="form-control eto-settings-timeout eto-ftp" data-eto-relation="user" data-eto-key="filesystems.disks.backup_ftp.timeout" disabled/>
                        </div>
                    </div>
                    </div>
                    <div class="row">
                        <div class="form-group clearfix">
                        <label for="passive" class="col-sm-4 control-label">
                            {{ trans('backup.input.passive') }}
                        </label>
                        <div class="col-sm-8">
                            <div class="onoffswitch">
                                <input name="passive" id="passive" class="onoffswitch-input eto-settings-passive eto-ftp" type="checkbox" value="1" data-eto-relation="user" data-eto-key="filesystems.disks.backup_ftp.passive" disabled>
                                <label class="onoffswitch-label" for="passive"></label>
                            </div>
                        </div>
                    </div>
                    </div>
                    <div class="row">
                        <div class="form-group clearfix">
                        <label for="ssl" class="col-sm-4 control-label">
                            {{ trans('backup.input.ssl') }}
                        </label>
                        <div class="col-sm-8">
                            <div class="onoffswitch">
                                <input name="ssl" id="ssl" class="onoffswitch-input eto-settings-ssl eto-ftp" type="checkbox" value="1" data-eto-relation="user" data-eto-key="filesystems.disks.backup_ftp.ssl" disabled>
                                <label class="onoffswitch-label" for="ssl"></label>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade eto-modal-copy" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" class="eto-new-copy" action="{{ route('backup.copy') }}">
                {{ csrf_field() }}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4>{{ trans('backup.button.copyToDriver') }}</h4>
                </div>
                <div class="row modal-body">
                    <label class="col-sm-12 control-label eto-label-backup"> </label>
                    <input type="hidden" name="id" />
                    <div class="row clearfix">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label for="backup-comments" class="col-sm-4 control-label">
                                    {{ trans('backup.input.driverBackup') }}
                                </label>
                                <div class="col-sm-8">
                                    <select name="backupDisk" class="form-control" placeholder="{{ trans('backup.input.driverBackup') }}" id="backup-copy"></select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">{{ trans('backup.button.copy') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade eto-modal-move" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" class="eto-new-move" action="{{ route('backup.move') }}">
                {{ csrf_field() }}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4>{{ trans('backup.button.moveToDriver') }}</h4>
                </div>
                <div class="row modal-body">
                    <label class="col-sm-12 control-label eto-label-backup"> </label>
                    <input type="hidden" name="id" />
                    <div class="row clearfix">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label for="backup-comments" class="col-sm-4 control-label">
                                    {{ trans('backup.input.driverBackup') }}
                                </label>
                                <div class="col-sm-8">
                                    <select name="backupDisk" class="form-control" placeholder="{{ trans('backup.input.driverBackup') }}" id="backup-move"></select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">{{ trans('backup.button.move') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
