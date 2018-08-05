@extends('Admin.Master')

@section('content')

<div class="col-xs-12">
    @if (count($errors) > 0)
    <div class="alert alert-danger">
        <strong>{{trans('validation.whoops')}}</strong> {{trans('validation.someproblems')}}<br><br>
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
</div>
<div class="login-box">
    <div class="login-logo">
<!--        <h1><span class="title_border">{{trans('labels.appname')}}</span></h1>-->
        <h1><span class="title_border"><img class="logo_img" src="{{ asset('/backend/images/meetwo_logo.png')}}" alt="Meetwo" title="Meetwo" ></span></h1>
    </div><!-- /.login-logo -->
    <div class="login-box-body">
        <p class="login-box-msg">{{trans('labels.startsession')}}</p>
        <form id="login_form" role="form" method="POST" action="{{ url('/admin/logincheck') }}">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <div class="form-group has-feedback">
                <input type="text" class="form-control" id="email" maxlength="30" minlength="5" name="email" placeholder="{{trans('labels.emaillbl')}}" value="">
                <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="password" class="form-control" id="password" maxlength="20" minlength="6" name="password" placeholder="{{trans('labels.passwordlbl')}}">
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <div class="row">
                <div class="col-xs-8">
                </div>
                <div class="col-xs-4">
                    <button type="submit" class="btn btn-primary btn-block btn-flat">{{trans('labels.login')}}</button>
                </div><!-- /.col -->
            </div>
        </form>
    </div><!-- /.login-box-body -->
</div><!-- /.login-box -->
@stop
@section('script')
<script type="text/javascript">
    jQuery(document).ready(function() {
        var signupRules = {
            email: {
                required: true,
                email  : true
            },
            password: {
                required: true
            }
        };
        $("#login_form").validate({
            rules: signupRules,
            messages: {
                email: {
                    required: '<?php echo trans('validation.emailrequired')?>'
                },
                password: {
                    required: '<?php echo trans('validation.passwordrequired')?>'
                }
            }
        });
    });
</script>

@stop