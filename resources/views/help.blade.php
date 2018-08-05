@extends('Admin.Master')

@section('content')
<div class="login-box" style="width: 800px;">
    <div class="login-logo">
        <h1><span class="title_border"><img class="logo_img" src="{{ asset('/backend/images/logo.png')}}" alt="Meetwo" title="Meetwo" ></span></h1>
    </div>
    <div>
    <?php if(isset($help))
        { ?>
            {!!$help['cms_body']!!}
    <?php } else { ?>
                {{trans('validation.whoops')}}{{trans('validation.somethingwrong')}}
    <?php } ?>
    </div>
</div>
@stop