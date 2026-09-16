<style type="text/css">
.meeting-board-table {
	position: absolute;
	overflow: visible;
	left: 0;
	right: 0;
	top: 0;
	bottom: 0;
	width: 100%;
	height: 100%;
	margin-top: auto;
	margin-bottom: auto;
	margin-left: auto;
	margin-right: auto;
}
.meeting-board-table td {
	vertical-align: middle;
}
.meeting-board-wrap {
	position: absolute;
	overflow: visible;
	left: 0;
	right: 0;
	width: 100%; /* you must specify a width */
	margin-top: auto;
	margin-bottom: auto;
	margin-left: auto;
	margin-right: auto;
}
.meeting-board-header {
	font-family: 'Helvetica Neue', Arial, Helvetica, sans-serif;
	padding: 30px 30px;
  text-align: center;
  font-weight: 300;
  line-height: 40px;
  font-size: 40px;
	color: #333;
}
.meeting-board-header img {
  max-height: 150px;
  max-width: 400px;
}
.meeting-board-body {
	font-family: 'Helvetica Neue', Arial, Helvetica, sans-serif;
	padding: 50px 30px;
  text-align: center;
  font-weight: 300;
  line-height: {{ $fontSize }}px;
  font-size: {{ $fontSize }}px;
	color: #333;
}
.meeting-board-footer {
	font-family: 'Helvetica Neue', Arial, Helvetica, sans-serif;
	padding: 30px 30px;
	text-align: center;
  line-height: 24px;
  font-size: 16px;
}
</style>

@if ($mode == 'pdf')
		<div class="meeting-board-wrap">
@else
		<table border="0" cellspacing="0" cellpadding="0" width="100%" class="meeting-board-table" autosize="1"><tr><td>
@endif

@if ($headerInfo)
		<div class="meeting-board-header">{!! $headerInfo !!}</div>
@endif
<div class="meeting-board-body"><div>{!! $bodyInfo !!}</div></div>
@if ($footerInfo)
		<div class="meeting-board-footer">{!! $footerInfo !!}</div>
@endif

@if ($mode == 'pdf')
		</div>
@else
		</td></tr></table>
@endif
