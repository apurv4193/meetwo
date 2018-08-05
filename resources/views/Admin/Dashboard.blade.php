@extends('Admin.Master')

@section('content')
<!-- content   -->

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        {{trans('labels.dashboard')}}
    </h1>
</section>

<section class="content">
    <div class="row">
        <div>
            <section class="content-header">
                <h1>
                    USERS STATISTICS
                </h1>
            </section>
        </div>
        <div class="col-md-3">
            <div class="box box-primary">
                <div class="box-body box-content">
                    <span class="dashboard_text">
                        New User Today
                    </span>
                    <h2>
                        {{$response['todayUser']}}
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="box box-primary">
                <div class="box-body box-content">
                <span class="dashboard_text">
                    New User This Month
                </span>
                <h2>
                    {{$response['thisMonthUser']}}
                </h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="box box-primary">
                <div class="box-body box-content">
                <span class="dashboard_text">
                    New User Last Month
                </span>
                <h2>
                    {{$response['lastMonthUser']}}
                </h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="box box-primary">
                <div class="box-body box-content">
                <span class="dashboard_text">
                    Total User
                </span>
                <h2>
                    {{$response['totalUser']}}
                </h2>
                </div>
            </div>
        </div>
    </div> 
    <div class="row">
        <div>
            <section class="content-header">
                <h1>
                    CHEMISTRY STATISTICS
                </h1>
            </section>
        </div>
        <div class="col-md-3">
            <div class="box box-primary">
                <div class="box-body box-content">
                    <span class="dashboard_text">
                        Chemistry created (matches) Today
                    </span>
                    <h2>
                        {{$response['todayChemistry']}}
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="box box-primary">
                <div class="box-body box-content">
                <span class="dashboard_text">
                    Chemistry created (matches) This Month
                </span>
                <h2>
                    {{$response['thisMonthChemistry']}}
                </h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="box box-primary">
                <div class="box-body box-content">
                <span class="dashboard_text">
                    Chemistry created (matches) Last Month
                </span>
                <h2>
                    {{$response['lastMonthChemistry']}}
                </h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="box box-primary">
                <div class="box-body box-content">
                <span class="dashboard_text">
                    Total Chemistry created (matches)
                </span>
                <h2>
                    {{$response['totalChemistry']}}
                </h2>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div>
            <section class="content-header">
                <h1>
                    PERSONALITY TEST TAKEN
                </h1>
            </section>
        </div>
        <div class="col-md-3">
            <div class="box box-primary">
                <div class="box-body box-content">
                    <span class="dashboard_text">
                        Personality Test Taken Today
                    </span>
                    <h2>
                        {{$response['todayPersonalityTest']}}
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="box box-primary">
                <div class="box-body box-content">
                <span class="dashboard_text">
                    Personality Test Taken This Month
                </span>
                <h2>
                    {{$response['thisMonthPersonalityTest']}}
                </h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="box box-primary">
                <div class="box-body box-content">
                <span class="dashboard_text">
                    Personality Test Taken Last Month
                </span>
                <h2>
                    {{$response['lastMonthPersonalityTest']}}
                </h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="box box-primary">
                <div class="box-body box-content">
                <span class="dashboard_text">
                    Total Personality Test Taken
                </span>
                <h2>
                    {{$response['totalPersonalityTest']}}
                </h2>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div>
            <section class="content-header">
                <h1>
                    FLIP STATISTICS
                </h1>
            </section>
        </div>
        <div class="col-md-3">
            <div class="box box-primary">
                <div class="box-body box-content">
                    <span class="dashboard_text">
                         Flip Today
                    </span>
                    <h2>
                        {{$response['todayLD']}}
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="box box-primary">
                <div class="box-body box-content">
                <span class="dashboard_text">
                    Flip This Month
                </span>
                <h2>
                    {{$response['thisMonthLD']}}
                </h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="box box-primary">
                <div class="box-body box-content">
                <span class="dashboard_text">
                    Flip Last Month
                </span>
                <h2>
                    {{$response['lastMonthLD']}}
                </h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="box box-primary">
                <div class="box-body box-content">
                <span class="dashboard_text">
                    Total Flip
                </span>
                <h2>
                    {{$response['totalLD']}}
                </h2>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div>
            <section class="content-header">
                <h1>
                    MESSAGE STATISTICS
                </h1>
            </section>
        </div>
        <div class="col-md-3">
            <div class="box box-primary">
                <div class="box-body box-content">
                    <span class="dashboard_text">
                        Message Today
                    </span>
                    <h2>
                        {{$response['todayMessage']}}
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="box box-primary">
                <div class="box-body box-content">
                <span class="dashboard_text">
                    Message This Month
                </span>
                <h2>
                    {{$response['thisMonthMessage']}}
                </h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="box box-primary">
                <div class="box-body box-content">
                <span class="dashboard_text">
                    Message Last Month
                </span>
                <h2>
                    {{$response['lastMonthMessage']}}
                </h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="box box-primary">
                <div class="box-body box-content">
                <span class="dashboard_text">
                    Total Message
                </span>
                <h2>
                    {{$response['totalMessage']}}
                </h2>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div>
            <section class="content-header">
                <h1>
                    PUSH NOTIFICATION TRACK
                </h1>
            </section>
        </div>
        <div class="col-md-3">
            <div class="box box-primary">
                <div class="box-body box-content">
                    <span class="dashboard_text">
                        Someone just tried...
                    </span>
                    <h2>
                        {{$response['messageType1']}}
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="box box-primary">
                <div class="box-body box-content">
                <span class="dashboard_text">
                    You\'re on fire...
                </span>
                <h2>
                    {{$response['messageType2']}}
                </h2>
                </div>
            </div>
        </div>
    </div>  
</section>
@stop