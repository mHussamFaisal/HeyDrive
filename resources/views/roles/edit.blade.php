@extends('admin.index')

@section('title', trans('roles.titles.edit_role_title'))
@section('subtitle', /*'<i class="fa fa-user-secret "></i> '.*/ trans('roles.titles.edit_role_title') )

@section('subcontent')
    <div class="box-header col-md-8 col-lg-6 col-md-offset-2 col-lg-offset-3" style="left: -6px;">
        <h4 class="box-title">
            {!! trans('roles.titles.edit_role', ['name' => $name]) !!}
        </h4>

        <div class="box-tools pull-right" style="right: 0;"></div>
    </div>
    <div class="row">
        <div class="col-md-8 col-lg-6 col-md-offset-2 col-lg-offset-3">
            @include('roles.partials.form-status')
            {{ method_field('PUT') }}
            <form action="{{ route('roles.update', $id) }}" method="POST" accept-charset="utf-8" id="edit_role_form" class="form-master needs-validation" enctype="multipart/form-data" role="form" >
                {{ method_field('PATCH') }}
                <div class="card-body">
                    <input type="hidden" name="id" value="{{ $id }}">
                    @include('roles.partials.role-form')
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-sm-12">
                            <button type="submit" class="btn btn-primary" value="save" name="action">
                                {!! trans("roles.form.buttons.update_role") !!}
                            </button>
                            <a href="@if(isset($typeDeleted)){{ route('roles-deleted') }}@else{{ route('roles.index') }}@endif" class="btn btn-link">
                                {!! trans('roles.buttons.cancel') !!}
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('subfooter')
    @include('roles.partials.index')
@endsection
