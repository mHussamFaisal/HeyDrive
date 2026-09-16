<div class="row">
    <div class="col-sm-12">
        @if (session('message'))
            <div class="alert alert-{{ Session::get('status') }} status-box alert-dismissable show" role="alert">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">
                    &times;
                    <span class="sr-only">
                {!! trans('roles.flash-messages.close') !!}
            </span>
                </a>
                {!! session('message') !!}
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success alert-dismissable show" role="alert">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">
                    &times;
                    <span class="sr-only">
                {!! trans('roles.flash-messages.close') !!}
            </span>
                </a>
                {!! session('success') !!}
            </div>
        @endif

        @if(session()->has('status'))
            @if(session()->get('status') == 'wrong')
                <div class="alert alert-danger status-box alert-dismissable show" role="alert">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">
                        &times;
                        <span class="sr-only">
                    {!! trans('roles.flash-messages.close') !!}
                </span>
                    </a>
                    {!! session('message') !!}
                </div>
            @endif
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissable show" role="alert">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">
                    &times;
                    <span class="sr-only">
                {!! trans('roles.flash-messages.close') !!}
            </span>
                </a>
                {!! session('error') !!}
            </div>
        @endif

        @if (session('errors') && count($errors) > 0)
            <div class="alert alert-danger alert-dismissable show" role="alert">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">
                    &times;
                    <span class="sr-only">
                {!! trans('roles.flash-messages.close') !!}
            </span>
                </a>
                <h4>
                    {!! trans('roles.flash-messages.someProblems') !!}
                </h4>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>
                            {!! $error !!}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</div>
