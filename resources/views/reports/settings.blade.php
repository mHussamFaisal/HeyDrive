<div class="modal fade eto-modal-settings" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">{{ trans('reports.titles.settings') }}</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="form-group clearfix">
                        <label class="col-sm-10 control-label">
                            {{ trans('reports.form.email_show_total') }}
                        </label>
                        <div class="col-sm-2">
                            <div class="onoffswitch pull-right">
                                <input name="email_total" id="email_total" class="onoffswitch-input eto-settings-input eto-settings-email_total" type="checkbox" value="1" data-eto-relation="subscription" data-eto-key="eto_report.email.total">
                                <label class="onoffswitch-label" for="email_total"></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group clearfix">
                        <label class="col-sm-10 control-label">
                            {{ trans('reports.form.email_show_company_take') }}
                        </label>
                        <div class="col-sm-2">
                            <div class="onoffswitch pull-right">
                                <input name="email_company_take" id="email_company_take" class="onoffswitch-input eto-settings-input eto-settings-email_company_take" type="checkbox" value="1" data-eto-relation="subscription" data-eto-key="eto_report.email.company_take">
                                <label class="onoffswitch-label" for="email_company_take"></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group clearfix">
                        <label class="col-sm-10 control-label">
                            {{ trans('reports.form.export_show_total') }}
                        </label>
                        <div class="col-sm-2">
                            <div class="onoffswitch pull-right">
                                <input name="export_total" id="export_total" class="onoffswitch-input eto-settings-input eto-settings-export_total" type="checkbox" value="1" data-eto-relation="subscription" data-eto-key="eto_report.export.total">
                                <label class="onoffswitch-label" for="export_total"></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group clearfix">
                        <label class="col-sm-10 control-label">
                            {{ trans('reports.form.export_show_company_take') }}
                        </label>
                        <div class="col-sm-2">
                            <div class="onoffswitch pull-right">
                                <input name="export_company_take" id="export_company_take" class="onoffswitch-input eto-settings-input eto-settings-export_company_take" type="checkbox" value="1" data-eto-relation="subscription" data-eto-key="eto_report.export.company_take">
                                <label class="onoffswitch-label" for="export_company_take"></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group clearfix">
                        <label class="col-sm-10 control-label">
                            {{ trans('reports.form.show_charts') }}
                        </label>
                        <div class="col-sm-2">
                            <div class="onoffswitch pull-right">
                                <input name="show_charts" id="show_charts" class="onoffswitch-input eto-settings-input eto-settings-show_charts" type="checkbox" value="1" data-eto-relation="subscription" data-eto-key="eto_report.show_charts">
                                <label class="onoffswitch-label" for="show_charts"></label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade eto-modal-report-booking-details" id="modal-popup" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body"></div>
        </div>
    </div>
</div>

<div class="modal fade eto-modal-report" role="dialog" aria-hidden="true">
    <div class="modal-dialog" style="width: 90%;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body eto-show-report"></div>
            <div class="pageContent eto-report-invalid-bookings hidden"></div>
            <div class="pageContent">
                <div class="row">
                    <div class="col-lg-12 eto-charts"></div>
                </div>
            </div>
        </div>
    </div>
</div>
