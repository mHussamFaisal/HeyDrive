<div class="modal fade eto-generate-buckup-form" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" class="eto-new-backup" action="">
                {{ csrf_field() }}
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">{{ trans('backup.newbackup') }}</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group clearfix">
                            <label class="col-sm-4 control-label">
                                <span @if ($minSize > $freeSpace) style="color: {{ trans('backup.statusColor.0') }}" @endif>
                                    {{ trans('backup.free_space') }}
                                </span>
                            </label>
                            <div class="col-sm-8">
                                {{ round(($freeSpace / 1024 / 1024), 2) }} MB
                                @if ($minSize > $freeSpace) {{ trans('backup.lackOfDiskSpace') }} @endif
                            </div>
                        </div>
                    </div>

                    <div class="row eto-form-data">
                        <div class="form-group clearfix">
                            <label for="backup-name" class="col-sm-4 control-label">
                                {{ trans('backup.input.name') }}
                            </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="name" id="backup-name" placeholder="{{ trans('backup.input.norequired') }}" />
                                <span class="error-block hidden">
                                    <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                                    <span class="eto-error"></span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="row eto-form-data">
                        <div class="form-group clearfix">
                            <label for="backup-comments" class="col-sm-4 control-label">
                                {{ trans('backup.input.comments') }}
                            </label>
                            <div class="col-sm-8">
                                <textarea type="text" class="form-control" name="comments" id="backup-comments" placeholder="{{ trans('backup.input.norequired') }}" ></textarea>
                                <span class="error-block hidden">
                                    <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                                    <span class="eto-error"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                    @if (count($disks) > 0)
                    <div class="row eto-form-data">
                        <div class="form-group clearfix">
                            <label for="backup-comments" class="col-sm-4 control-label">
                                {{ trans('backup.input.driverBackup') }}
                            </label>
                            <div class="col-sm-8">
                                <select name="backupDisk" class="form-control" placeholder="{{ trans('backup.input.driverBackup') }}" id="backup-disk"></select>
                                <span class="error-block hidden">
                                    <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                                    <span class="eto-error"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                    @endif
                    @if(!config('eto.multi_subscription'))
                    <div class="row eto-form-data">
                        <div class="form-group clearfix">
                            <label for="backup-type" class="col-sm-4 control-label">
                                {{ trans('backup.input.type') }}
                            </label>
                            <div class="col-sm-8">
                                <select name="type" class="form-control" placeholder="{{ trans('backup.input.type') }}" id="backup-type" required></select>
                                <span class="error-block hidden">
                                    <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                                    <span class="eto-error"></span>
                                </span>
                            </div>
                        </div>
                    </div>
{{--                    <div class="row eto-form-data">--}}
{{--                        <div class="eto-files-backup hidden">--}}
{{--                            <div class="form-group clearfix">--}}
{{--                                <label for="backup-dirList" class="col-sm-4 control-label">--}}
{{--                                    {{ trans('backup.input.directories') }}--}}
{{--                                </label>--}}
{{--                                <div class="col-sm-8">--}}
{{--                                    <select name="dirList[]" class="form-control" placeholder="{{ trans('backup.input.directories') }}" id="backup-dirList" multiple></select>--}}
{{--                                    <span class="error-block hidden">--}}
{{--                                        <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>--}}
{{--                                        <span class="eto-error"></span>--}}
{{--                                    </span>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="row eto-form-data">--}}
{{--                        <div class="eto-files-backup hidden">--}}
{{--                            <div class="form-group clearfix">--}}
{{--                                <label for="backup-fileList" class="col-sm-4 control-label">--}}
{{--                                    {{ trans('backup.input.files') }}--}}
{{--                                </label>--}}
{{--                                <div class="col-sm-8">--}}
{{--                                    <select name="fileList[]" class="form-control" placeholder="{{ trans('backup.input.files') }}" id="backup-fileList" multiple></select>--}}
{{--                                    <span class="error-block hidden">--}}
{{--                                        <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>--}}
{{--                                        <span class="eto-error"></span>--}}
{{--                                    </span>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
                    @else
                        <input type="hidden" name="type" value="subscription">
                    @endif
                    <div class="row eto-form-progress hidden">
                        <p class="eto-form-progress-info"></p>
                        <div class="progress">
                            <div class="progress-bar progress-bar-primary progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">{{ trans('backup.button.generate') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
