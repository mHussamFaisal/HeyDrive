@extends('driver.index')

@section('title', trans('driver/jobs.page_title') .' / '. $job->getRefNumber() .' / '. trans('driver/jobs.subtitle.meeting_board'))
@section('subtitle', /*'<i class="fa fa-address-card-o"></i> '*/ '<a href="'. route('driver.jobs.index') .'">'. trans('driver/jobs.page_title') .'</a> / <a href="'. route('driver.jobs.show', $job->id) .'">'. $job->getRefNumber() .'</a> / '. trans('driver/jobs.subtitle.meeting_board') )

@section('subcontent')
<div id="job-meeting-board">
  <div id="meeting-board-container">
    {!! $job->getMeetingBoard() !!}
  </div>

  <div>
    @if( session('isMobileApp') )
      <a href="#" onclick="alert('{{ trans('driver/jobs.meeting_board.browser_mode_only') }}'); return false;" class="btn btn-md btn-default">
        <i class="fa fa-download"></i> <span>{{ trans('driver/jobs.meeting_board.button.download') }}</span>
      </a>
      <a href="#" onclick="alert('{{ trans('driver/jobs.meeting_board.browser_mode_only') }}'); return false;" class="btn btn-md btn-default">
        <i class="fa fa-print"></i> <span>{{ trans('driver/jobs.meeting_board.button.print') }}</span>
      </a>
    @else
      <a href="{{ route('driver.jobs.meeting-board', ['id' => $job->id, 'action' => 'download']) }}" class="btn btn-md btn-default">
        <i class="fa fa-download"></i> <span>{{ trans('driver/jobs.meeting_board.button.download') }}</span>
      </a>
      <a href="#" onclick="printContent('meeting-board-container'); return false;" class="btn btn-md btn-default">
        <i class="fa fa-print"></i> <span>{{ trans('driver/jobs.meeting_board.button.print') }}</span>
      </a>
    @endif

    <a href="{{ (url()->previous() != url()->full()) ? url()->previous() : route('driver.jobs.index') }}" class="btn btn-link">
      <span>{{ trans('driver/jobs.button.back') }}</span>
    </a>
  </div>
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
