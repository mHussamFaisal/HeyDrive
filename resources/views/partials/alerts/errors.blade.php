@if( $errors->any() )
  <div class="alert alert-danger alert-dismissible callout callout-danger" id="{{ !empty($id) ? $id : 'alert-error-box' }}">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">
        <i class="icon fa fa-times" style="position:relative;"></i>
    </button>
    @foreach($errors->all() as $error)
      <p class="msg">{!! $error !!}</p>
    @endforeach
  </div>

  @if (!isset($close) || $close == true)
    <script>
    $(document).ready(function(){
      setTimeout(function(){
        $('#{{ !empty($id) ? $id : 'alert-error-box' }}').fadeOut('slow');
      }, {{ isset($time) ? $time : 5000 }});
    });
    </script>
  @endif
@endif
