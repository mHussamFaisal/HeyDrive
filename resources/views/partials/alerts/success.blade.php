@if( session('message') )
  <div class="alert alert-success alert-dismissible callout callout-success" id="{{ !empty($id) ? $id : 'alert-success-box' }}">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
    <i class="icon fa fa-check"></i>
    <p class="msg">{!! session('message') !!}</p>
  </div>

  @if (!isset($close) || $close == true)
    <script>
    $(document).ready(function(){
      setTimeout(function(){
        $('#{{ !empty($id) ? $id : 'alert-success-box' }}').fadeOut('slow');
      }, {{ isset($time) ? $time : 5000 }});
    });
    </script>
  @endif
@endif
