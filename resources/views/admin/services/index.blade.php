@extends('admin.index')

@section('title', trans('admin/services.page_title'))
@section('subtitle', /*'<i class="fa fa-sliders"></i> '.*/ trans('admin/services.page_title'))

@section('subheader')
  <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/dataTables.bootstrap.css') }}">
  <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/extensions/Buttons/css/buttons.dataTables.min.css') }}">
  <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/extensions/Buttons/css/buttons.bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/extensions/ColReorder/css/colReorder.dataTables.min.css') }}">
  <link rel="stylesheet" href="{{ asset_url('plugins','data-tables/extensions/ColReorder/css/colReorder.bootstrap.min.css') }}">
@stop

@section('subcontent')
  @include('partials.alerts.success')
  @include('partials.alerts.errors')
  @include('partials.modals.delete')

  <div id="services">
    {!! $builder->table(['class' => 'table table-hover', 'width' => '100%', 'data-form' => 'deleteForm'], false) !!}
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
  {!! $builder->scripts() !!}

  <script type="text/javascript">
  function updateTableHeight() {
     var height = parseFloat($('.wrapper > .content-wrapper').css('min-height')) -
        $('#dataTableBuilder_wrapper > .topContainer').height() -
        $('#dataTableBuilder_wrapper > .bottomContainer').height() -
        $('.dataTables_scrollHead').height() - 50;

      if( height < 200 ) {
        height = 200;
      }
      // if( parseFloat($('.wrapper > .content-wrapper').css('min-height')) > $(window).height() ) {
      //  height = 0;
      // }
      $('#services .dataTables_scrollBody').css({'min-height': height +'px'});
  }

  $(document).ready(function() {
    $('table[data-form="deleteForm"]').on('click', '.form-delete', function(e){
      e.preventDefault();
      var form = $(this);
      $('#modal-delete').modal().on('click', '#delete-btn', function(){
        form.submit();
      });
    });

    updateTableHeight();
  });

  $(window).resize(function() {
    updateTableHeight();
  });
  </script>
@stop
