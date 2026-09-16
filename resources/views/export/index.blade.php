@extends('admin.index')

@section('title', trans('export.page_title'))
@section('subtitle', /*'<i class="fa fa-download"></i> '.*/ trans('export.page_title'))
@section('subclass', 'export-wrapper')

@section('subheader')
    <link rel="stylesheet" href="{{ asset_url('plugins','bootstrap-daterangepicker/daterangepicker.css') }}">
@endsection

@section('subcontent')
    <div class="box-header" style="left: -6px;">
        <h4 class="box-title">
            {{ trans('export.description') }}
        </h4>

        <div class="box-tools pull-right" style="right: 0;">
            <div class="btn-group">
                <button class="btn btn-sm btn-default eto-btn-generate" data-eto-export-type="xls" type="button">
{{--                    <i class="fa fa-file-excel-o"></i>--}}
                    {{ trans('export.download') }}
                </button>
                <a href="javascript:void(0);" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown"  aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-angle-down"></i>
                </a>
                <ul class="dropdown-menu" role="menu">
                    <li>
                        <a href="javascript:void(0);"  class="eto-btn-generate" data-eto-export-type="xlsx">
{{--                            <i class="fa fa-file-excel-o"></i>--}}
                            <span>{{ trans('export.generate_xlsx') }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0);"  class="eto-btn-generate" data-eto-export-type="xls">
{{--                            <i class="fa fa-file-excel-o"></i>--}}
                            <span>{{ trans('export.generate_xls') }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0);" class="eto-btn-generate" data-eto-export-type="csv">
{{--                            <i class="fa fa-file-o"></i>--}}
                            <span>{{ trans('export.generate_csv') }}</span>
                        </a>
                    </li>
{{--                    <li>--}}
{{--                        <a href="javascript:void(0);" class="eto-btn-generate" data-eto-export-type="pdf">--}}
{{--                            <i class="fa fa-file-pdf-o"></i>--}}
{{--                            <span>{{ trans('export.generate_pdf') }}</span>--}}
{{--                        </a>--}}
{{--                    </li>--}}
                </ul>
            </div>
        </div>
    </div>
    <div id="export" class="hidden">
        <table class="table table-hover table-condensed eto-export-section-table">
            <tbody>
            @if (!empty($sections))
                @foreach ($sections as $sName=>$section)
                <tr class="eto-export-section eto-item" data-eto-section="{{ $sName }}">
                    <td class="eto-export-section-item">
                        <label for="section_{{ $sName }}" class=" control-label">
                            {{ trans('export.section.'.$sName) }}
                        </label>
                    </td>
                    <td>
                        <div class="onoffswitch">
                            <input id="section_{{ $sName }}" class="onoffswitch-input eto-export-section" type="checkbox">
                            <label class="onoffswitch-label" for="section_{{ $sName }}"></label>
                        </div>
                    </td>
                    <td>
                        <span>
                            <select class="form-control eto-section-tags pull-left" placeholder="selectSavedFilter" multiple>
                                <option></option>
                                @foreach ($section->filters as $fName=>$filter)
                                    <option value="{{$fName}}">
                                    @if ($filter->type == 'basic' || trans('export.filter.'.$fName) != 'export.filter.'.$fName)
                                    {{ trans('export.filter.'.$fName) }}
                                    @elseif (!empty($filter->name))
                                    {{ $filter->name }}
                                    @else
                                    {{ $fName }}
                                    @endif
                                </option>
                                @endforeach
                            </select>
                        </span>
                        <span>
                            <button class="btn btn-sm btn-default btn-flat eto-btn-filter-params pull-right" type="button" title="{{ trans('export.thead.filters') }}">
                                <i class="fa fa-wrench"></i>
                            </button>
                            <button class="btn btn-sm btn-default btn-flat eto-btn-filter-columns pull-right" type="button" title="{{ trans('export.columns') }}">
                                <i class="fa fa-columns"></i>
                            </button>
                        </span>
                    </td>
                </tr>
                @endforeach
            @endif
            <tr>
                <td>
                    <label for="all_sections" class=" control-label">
                        {{ trans('export.section.all') }}
                    </label>
                </td>
                <td>
                    <div class="onoffswitch">
                        <input id="all_sections" class="onoffswitch-input eto-export-section-all" type="checkbox">
                        <label class="onoffswitch-label" for="all_sections"></label>
                    </div>
                </td>
                <td></td>
            </tr>
            </tbody>
        </table>

        <div class="eto-modal-section-columns modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="post">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <h4 class="eto-export-section-title"></h4>
                        </div>
                        <div class="modal-body eto-export-section-columns"></div>
                    </form>
                </div>
            </div>
        </div>
        <div class="eto-modal-section-filters modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="post">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <h4 class="eto-export-section-title"></h4>
                        </div>
                        <div class="modal-body">
                            <div class="eto-new-filter-form row"></div>
                        </div>
                        <div class="modal-footer">
                            <div class="input-group input-group-sm eto-save-filter-save-container hidden">
                                <input type="text" class="form-control" name="filter_name" placeholder="{{ trans('export.filter_name') }}">
                                <span class="input-group-btn">
                                <button type="button" class="btn btn-default eto-unset-save-filter">x</button>
                            </span>
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-success eto-use-filter-params eto-save-filter-params">Save & Apply</button>
                                </span>
                            </div>
                            <span class="eto-save-filter-apply-container">
                            <button type="button" class="btn btn-sm btn-default pull-left eto-set-save-filter">Save & Apply</button>
                            <button type="button" class="btn btn-sm btn-success pull-right eto-use-filter-params">Apply</button>
                            <button type="button" class="btn btn-sm btn-default pull-right eto-reset-filter-params">Reset</button>
                        </span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('subfooter')
    <script src="{{ asset_url('plugins','moment/moment-with-locales.min.js') }}"></script>
    <script src="{{ asset_url('plugins','bootstrap-daterangepicker/daterangepicker.js') }}"></script>
    @include('layouts.eto-js')
    <script src="{{ asset_url('js','eto/eto-export.js') }}?_dc={{ config('app.timestamp') }}"></script>

    <script>
        $(function() {
            $.LoadingOverlay('show');
            ETO.Export.init({
                sections: {!! \GuzzleHttp\json_encode($sections) !!},
            });
        });
        window.onload = function() {
            $('#export').removeClass('hidden');
            $.LoadingOverlay('hide');
        };
    </script>
@endsection
