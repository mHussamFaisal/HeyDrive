@extends('admin.index')

@section('title', trans('admin/bookings.page_title') .' / '. $booking->getRefNumber() .' / '. trans('admin/bookings.subtitle.meeting_board'))
@section('subtitle', /*'<i class="fa fa-address-card-o"></i> '*/ '<a href="'. route('admin.bookings.index') .'">'. trans('admin/bookings.page_title') .'</a> / <a href="'. route('admin.bookings.show', $booking->id) .'">'. $booking->getRefNumber() .'</a> / '. trans('admin/bookings.subtitle.meeting_board') )

@section('subcontent')
<div id="booking-meeting-board">
  <div id="meeting-board-container">
    {!! $booking->getMeetingBoard() !!}
  </div>

  <div>
    <a href="{{ route('admin.bookings.meeting-board', ['id' => $booking->id, 'action' => 'download']) }}" class="btn btn-md btn-default">
      <i class="fa fa-download"></i> <span>{{ trans('admin/bookings.meeting_board.button.download') }}</span>
    </a>
    <a href="#" onclick="printContent('meeting-board-container'); return false;" class="btn btn-md btn-default">
      <i class="fa fa-print"></i> <span>{{ trans('admin/bookings.meeting_board.button.print') }}</span>
    </a>
  <div>
</div>
@stop


@section('subfooter')
<script>
function printContent(el) {
  var restorepage = document.body.innerHTML;
  var printcontent = document.getElementById(el).innerHTML;
  document.body.innerHTML = printcontent;
  window.print();
  document.body.innerHTML = restorepage;
}
</script>
@endsection
