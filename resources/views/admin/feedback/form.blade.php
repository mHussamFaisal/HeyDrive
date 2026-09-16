<div class="form-group has-feedback {{ $errors->has('type') ? 'has-error' : '' }} {{ $mode == 'edit' ? 'hide' : '' }}">
  {{-- {!! Form::label('type', trans('admin/feedback.type')) !!} --}}
  {{-- <div class="input-group"> --}}
    {!! Form::select('type', [
      'comment' => trans('admin/feedback.types.comment'),
      'lost_found' => trans('admin/feedback.types.lost_found'),
      'complaint' => trans('admin/feedback.types.complaint'),
    ], old('type', $mode == 'edit' ? null : 'comment'), [
      'id' => 'type',
      'class' => 'form-control select2',
      'data-placeholder' => trans('admin/feedback.type'),
      'required',
    ]) !!}
    {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
  {{-- </div> --}}
  @if( $errors->has('type') )
    <span class="help-block">{{ $errors->first('type') }}</span>
  @endif
</div>

<div class="row">
  <div class="col-xs-12 col-sm-6">

    <div class="form-group has-feedback {{ $errors->has('name') ? 'has-error' : '' }}">
      {!! Form::label('name', trans('admin/feedback.name')) !!}
      {{-- <div class="input-group"> --}}
        {!! Form::text('name', old('name'), [
          'id' => 'name',
          'class' => 'form-control',
          'placeholder' => trans('admin/feedback.name'),
          'required',
        ]) !!}
        {{-- <span class="input-group-addon"><i class="fa fa-user"></i></span> --}}
      {{-- </div> --}}
      @if( $errors->has('name') )
        <span class="help-block">{{ $errors->first('name') }}</span>
      @endif
    </div>

  </div>
  <div class="col-xs-12 col-sm-6">

    <div class="form-group has-feedback {{ $errors->has('email') ? 'has-error' : '' }}">
      {!! Form::label('email', trans('admin/feedback.email')) !!}
      {{-- <div class="input-group"> --}}
        {!! Form::text('email', old('email'), [
          'id' => 'email',
          'class' => 'form-control',
          'placeholder' => trans('admin/feedback.email'),
        ]) !!}
        {{-- <span class="input-group-addon"><i class="fa fa-envelope"></i></span> --}}
      {{-- </div> --}}
      @if( $errors->has('email') )
        <span class="help-block">{{ $errors->first('email') }}</span>
      @endif
    </div>

  </div>
</div>

<div class="row">
  <div class="col-xs-12 col-sm-6">

    <div class="form-group has-feedback {{ $errors->has('phone') ? 'has-error' : '' }}">
      {!! Form::label('phone', trans('admin/feedback.phone')) !!}
      {{-- <div class="input-group"> --}}
        {!! Form::text('phone', old('phone'), [
          'id' => 'phone',
          'class' => 'form-control',
          'placeholder' => trans('admin/feedback.phone'),
        ]) !!}
        {{-- <span class="input-group-addon"><i class="fa fa-phone"></i></span> --}}
      {{-- </div> --}}
      @if( $errors->has('phone') )
        <span class="help-block">{{ $errors->first('phone') }}</span>
      @endif
    </div>

  </div>
  <div class="col-xs-12 col-sm-6">

    <div class="form-group has-feedback {{ $errors->has('ref_number') ? 'has-error' : '' }}">
      {!! Form::label('ref_number', trans('admin/feedback.ref_number')) !!}
      {{-- <div class="input-group"> --}}
        {!! Form::text('ref_number', old('ref_number'), [
          'id' => 'ref_number',
          'class' => 'form-control',
          'placeholder' => trans('admin/feedback.ref_number'),
        ]) !!}
        {{-- <span class="input-group-addon"><i class="fa fa-pencil"></i></span> --}}
      {{-- </div> --}}
      @if( $errors->has('ref_number') )
        <span class="help-block">{{ $errors->first('ref_number') }}</span>
      @endif
    </div>

  </div>
</div>

<div class="form-group has-feedback {{ $errors->has('description') ? 'has-error' : '' }}">
  {!! Form::label('description', trans('admin/feedback.description')) !!}
  {{-- <div class="input-group"> --}}
    {!! Form::textarea('description', old('description'), [
      'id' => 'description',
      'class' => 'form-control',
      'placeholder' => trans('admin/feedback.description'),
      'rows' => '2',
      'required',
    ]) !!}
    {{-- <span class="input-group-addon"><i class="fa fa-comment"></i></span> --}}
  {{-- </div> --}}
  @if( $errors->has('description') )
    <span class="help-block">{{ $errors->first('description') }}</span>
  @endif
</div>

<div class="row">
  <div class="col-xs-12 col-sm-6 pull-right">

    <div class="form-group has-feedback {{ $errors->has('order') ? 'has-error' : '' }}">
      {!! Form::label('order', trans('admin/feedback.order')) !!}
      {{-- <div class="input-group"> --}}
        {!! Form::number('order', old('order', $mode == 'edit' ? null : 0), [
          'id' => 'order',
          'class' => 'form-control',
          'placeholder' => trans('admin/feedback.order'),
          'min' => '0',
          'required',
        ]) !!}
        {{-- <span class="input-group-addon"><i class="fa fa-sort"></i></span> --}}
      {{-- </div> --}}
      @if( $errors->has('order') )
        <span class="help-block">{{ $errors->first('order') }}</span>
      @endif
    </div>

  </div>
  <div class="col-xs-12 col-sm-6">

    <div class="form-group has-feedback {{ $errors->has('status') ? 'has-error' : '' }}">
      {!! Form::label('status', trans('admin/feedback.status')) !!}
      {{-- <div class="input-group"> --}}
        {!! Form::select('status', [
          'active' => trans('admin/feedback.statuses.active'),
          'inactive' => trans('admin/feedback.statuses.inactive'),
        ], old('status', $mode == 'edit' ? null : 'active'), [
          'id' => 'status',
          'class' => 'form-control select2',
          'data-placeholder' => trans('admin/feedback.status'),
          'required',
        ]) !!}
        {{-- <span class="input-group-addon"><i class="fa fa-eye"></i></span> --}}
      {{-- </div> --}}
      @if( $errors->has('status') )
        <span class="help-block">{{ $errors->first('status') }}</span>
      @endif
    </div>

  </div>
</div>


<div class="row">
  <div class="col-sm-12">
    <div>{{ trans('admin/users.files') }}</div>
    <div id="fileList"></div>
  </div>
</div>

<div class="row">
  <div class="col-sm-12">
    {!! Form::button(/*'<i class="fa fa-check"></i> '. */trans('admin/feedback.button.'. ($mode == 'edit' ? 'update' : 'create')), [
      'type' => 'submit',
      'class' => 'btn btn-primary',
    ]) !!}

    @if (request('tmpl') == 'body')
      <input type="hidden" name="tmpl" value="body" />
    @else
      <a href="{{ route('admin.feedback.index', request('type') ? ['type' => request('type')] : []) }}" class="btn btn-link">{{ trans('admin/feedback.button.cancel') }}</a>
    @endif
  </div>
</div>


@section('subfooter')
  <script src="{{ asset_url('plugins','autosize/autosize.min.js') }}"></script>

  <script type="text/javascript">
  $(document).ready(function() {
    var form = $('#feedback form.form-master');

    autosize(form.find('textarea'));

    function updateFormPlaceholder(that) {
      var container = $(that).closest('.form-group:not(.placeholder-disabled)');

      if( $(that).val() != '' || container.hasClass('placeholder-visible') ) {
        container.find('label').show();
      }
      else {
        container.find('label').hide();
      }
    }

    form.find('input:not([type="submit"]), textarea, select').each(function() {
      updateFormPlaceholder(this);
    })
    .bind('change keyup', function(e) {
      updateFormPlaceholder(this);
    });

    // File table
    $('#fileList').html(
      '<div class="table-responsive">\
        <table class="table table-condensed table-hover" cellspacing="0" width="100%" style="margin-bottom:10px;">\
        <thead class="hidden">\
          <tr>\
            <th>{{ trans('admin/users.files_name') }}</th>\
            <th>{{ trans('admin/users.files_file') }}</th>\
            <th></th>\
          </tr>\
        </thead>\
        <tbody></tbody>\
        </table>\
      </div>\
      <button type="button" class="btn btn-default btn-sm btnNewFile">\
        <i class="fa fa-plus"></i> \
        <span>{{ trans('admin/users.files_new') }}</span>\
      </button>'
    );

    $('#fileList .btnNewFile').on('click', function(e) {
      newFile();
      e.preventDefault();
    });

    $('body').on('click', '#fileList table .btnDelete', function(e) {
        var tr = $(this).closest('tr');
        tr.find('#file_delete').val(1);
        tr.find('#file_name, #file_path').removeAttr('required');
        if (tr.find('.filename').html()) {
            tr.hide();
        }
        else {
            tr.remove();
        }
        checkFiles();
        e.preventDefault();
    });

    var lastIndex2 = 0;

    function checkFiles() {
        if ($('#fileList table tbody tr').find('#file_delete[value=0]').length > 0) {
            $('#fileList table').removeClass('hidden');
        }
        else {
            $('#fileList table').addClass('hidden');
        }
    }

    function newFile() {
      $('#fileList table tbody').append(
        '<tr class="fileRow'+ lastIndex2 +'">\
          <td>\
            <input type="hidden" name="files['+ lastIndex2 +'][id]" id="file_id" value="0" required class="form-control">\
            <input type="hidden" name="files['+ lastIndex2 +'][delete]" id="file_delete" value="0" required class="form-control">\
            <input type="text" name="files['+ lastIndex2 +'][name]" id="file_name" placeholder="{{ trans('admin/users.files_name') }}" value="" required class="form-control">\
          </td>\
          <td>\
            <input type="file" name="files['+ lastIndex2 +'][file]" id="file_path" required class="form-control">\
            <div class="filename"></div>\
          </td>\
          <td>\
            <button type="button" onclick="return false;" class="btn btn-default btn-sm btnDelete" title="{{ trans('admin/users.button.destroy') }}" style="margin-top:5px;">\
              <i class="fa fa-trash"></i>\
            </button>\
          </td>\
        </tr>'
      );

      checkFiles();

      // Delete button
      $('#fileList').find('button.btnDelete').hover(
        function() {
          $(this).removeClass('btn-default').addClass('btn-danger');
        },
        function() {
          $(this).removeClass('btn-danger').addClass('btn-default');
        }
      );

      var index = lastIndex2;
      lastIndex2++;
      return index;
    }

    var files = {!! $mode == 'edit' ? ($feedback->getFiles(true) ?: '[]') : '[]' !!};

    if( files ) {
      $.each(files, function(key, value) {
        var index = newFile();
        var row = $('#fileList tr.fileRow'+ index);
        row.find('#file_id').val(value.id);
        row.find('#file_name').val(value.name);

        var file = row.find('#file_path');
        file.remove();
        row.find('.filename').html('<a href="'+ value.path +'" style="display:inline-block; padding:10px 0;">{{ trans('admin/users.download') }}</a>');
      });
    }

    checkFiles();

  });
  </script>
@stop
