@extends('admin.index')

@section('title',  trans('roles.titles.create_role') )
@section('subtitle', /*'<i class="fa fa-user-secret "></i> '.*/ trans('roles.titles.create_role') )

@section('subcontent')
    <div class="box-header col-md-8 col-lg-6 col-md-offset-2 col-lg-offset-3" style="left: -6px;">
        <h4 class="box-title">
            {!! trans('roles.titles.create_role') !!}
        </h4>

        <div class="box-tools pull-right" style="right: 0;"></div>
    </div>
    <div class="row">
        <div class="col-md-8 col-lg-6 col-md-offset-2 col-lg-offset-3">
            @include('roles.partials.form-status')
            <form action="{{ route('roles.store') }}" method="POST" accept-charset="utf-8" id="store_role_form" class="form-master  needs-validation" enctype="multipart/form-data" role="form" >
                {{ method_field('POST') }}
                <div class="card-body">
                    @include('roles.partials.role-form')
                </div>
                <div class="card-footer">
                    <div class="row ">
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-primary" value="save" name="action">
                                {!! trans("roles.form.buttons.save_role") !!}
                            </button>
                            @if(isset($typeDeleted))
                                <a href="{{ route('roles-deleted') }}" class="btn btn-link">
                                    {!! trans('roles.buttons.cancel') !!}
                                </a>
                            @else
                                <a href="{{ route('roles.index') }}" class="btn btn-link">
                                    {!! trans('roles.buttons.cancel') !!}
                                </a>
                            @endif
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
