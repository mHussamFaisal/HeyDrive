<div class="eto-modal-map-settings modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4>Map settings</h4>
            </div>
            <div class="modal-body">
                <div class="row form-group clearfix">
                    <label for="show_inactive_drivers" class="col-sm-4 control-label">
                        {{ trans('booking.show_inactive_drivers') }}
                    </label>
                    <div class="col-sm-8">
                        <div class="onoffswitch pull-right">
                            <input id="show_inactive_drivers" class="onoffswitch-input eto-settings-show_inactive_drivers" type="checkbox" value="1" data-eto-relation="user" data-eto-key="eto_map.view.show_inactive_drivers">
                            <label class="onoffswitch-label" for="show_inactive_drivers"></label>
                        </div>
                    </div>
                </div>
                <div class="row form-group clearfix">
                    <label for="traffic" class="col-sm-4 control-label">
                        Traffic
                    </label>
                    <div class="col-sm-8">
                        <div class="onoffswitch pull-right">
                            <input id="traffic" class="onoffswitch-input eto-settings-traffic" type="checkbox" name="traffic" value="1" data-eto-relation="user" data-eto-key="eto_map.view.traffic">
                            <label class="onoffswitch-label" for="traffic"></label>
                        </div>
                    </div>
                </div>
                <div class="row form-group clearfix">
                    <label for="poi_style" class="col-sm-4 control-label">
                        Labels
                    </label>
                    <div class="col-sm-8">
                        <div class="onoffswitch pull-right">
                            <input id="poi_style" class="onoffswitch-input eto-settings-poi_style" type="checkbox" name="poi_style" value="1" data-eto-relation="user" data-eto-key="eto_map.view.poi_style">
                            <label class="onoffswitch-label" for="poi_style"></label>
                        </div>
                    </div>
                </div>
                <div class="row form-group clearfix">
                    <label for="search" class="col-sm-4 control-label">
                        {{ trans('admin/map.search_location') }}
                    </label>
                    <div class="col-sm-8">
                        <input id="search" class="controls form-control eto-search" type="text" value="London, UK" data-eto-relation="user" data-eto-key="eto_map.view.search" />
                        <input id="search_lat" class="controls form-control eto-search_lat hidden" type="text" value="" data-eto-relation="user" data-eto-key="eto_map.view.search_lat" />
                        <input id="search_lng" class="controls form-control eto-search_lng hidden" type="text" value="" data-eto-relation="user" data-eto-key="eto_map.view.search_lng" />
                    </div>
                </div>
                <div class="row form-group clearfix">
                    <label for="mapRefresh" class="col-sm-4 control-label">
                        {{ trans('admin/map.refresh_time') }}
                    </label>
                    <div class="col-sm-8">
                        <input id="mapRefresh" class="controls form-control eto-settings-mapRefresh" type="text" value="20" data-eto-relation="user" data-eto-key="eto_map.interval.refresh" />
                    </div>
                </div>
                <div class="row form-group clearfix">
                    <label for="driverRefresh" class="col-sm-4 control-label">
                        {{ trans('admin/map.driver_refresh_time') }}
                    </label>
                    <div class="col-sm-8">
                        <input id="driverRefresh" class="controls form-control eto-settings-driverRefresh" type="text" value="20" data-eto-relation="subscription" data-eto-key="eto.interval.driver_refresh"/>
                    </div>
                </div>
                <div class="row form-group clearfix">
                    <label class="col-sm-4 control-label">
                        {{ trans('admin/map.map_type') }}
                    </label>
                    <div class="col-sm-8">
                        <div class="radio">
                            <label>
                                <input type="radio" name="type" value="roadmap" checked="" data-eto-relation="user" data-eto-key="eto_map.view.type">
                                Show street map
                            </label>
                        </div>
                        <div class="radio">
                            <label>
                                <input type="radio" name="type" value="terrain"  data-eto-relation="user" data-eto-key="eto_map.view.type">
                                Show street map with terrain
                            </label>
                        </div>
                        <div class="radio">
                            <label>
                                <input type="radio" name="type" value="satellite"  data-eto-relation="user" data-eto-key="eto_map.view.type">
                                Show satellite imagery
                            </label>
                        </div>
                        <div class="radio">
                            <label>
                                <input type="radio" name="type" value="hybrid"  data-eto-relation="user" data-eto-key="eto_map.view.type">
                                Show imagery with street names
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            {{--<div class="modal-footer">--}}
            {{--<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>--}}
            {{--</div>--}}
        </div>
    </div>
</div>
