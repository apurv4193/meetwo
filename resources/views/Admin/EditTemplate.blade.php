@extends('Admin.Master')

@section('content')

<!-- Content Wrapper. Contains page content -->

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        {{trans('labels.emailtemplates')}}
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <!-- right column -->
        <div class="col-md-12">
            <!-- Horizontal Form -->
            <div class="box box-info">
                <div class="box-header with-border">
                     <h3 class="box-title"><?php echo (isset($templateDetail) && !empty($templateDetail)) ? trans('labels.edit') : trans('labels.add') ?> {{trans('labels.emailtemplate')}}</h3>
                </div><!-- /.box-header -->
                @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <strong>{{trans('validation.whoops')}}</strong>{{trans('validation.someproblems')}}<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form id="addTemplate" class="form-horizontal" method="post" action="{{ url('/admin/saveemailtemplate') }}" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="id" value="<?php echo (isset($templateDetail) && !empty($templateDetail)) ? $templateDetail->id : '0' ?>">
                    <div class="box-body">

                        <?php
                        if (old('et_templatename'))
                            $et_templatename = old('et_templatename');
                        elseif ($templateDetail)
                            $et_templatename = $templateDetail->et_templatename;
                        else
                            $et_templatename = '';
                        ?>
                        <div class="form-group">
                            <label for="et_templatename" class="col-sm-2 control-label">{{trans('labels.templateblheadname')}}</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="et_templatename" name="et_templatename" placeholder="{{trans('labels.templateblheadname')}}" value="{{$et_templatename}}" minlength="6" maxlength="50">
                            </div>
                        </div>

                        <?php
                        if (old('et_templatepseudoname'))
                            $et_templatepseudoname = old('et_templatepseudoname');
                        elseif ($templateDetail)
                            $et_templatepseudoname = $templateDetail->et_templatepseudoname;
                        else
                            $et_templatepseudoname = '';
                        ?>
                        <div class="form-group">
                            <label for="et_templatepseudoname" class="col-sm-2 control-label">{{trans('labels.templateblheadpseudoname')}}</label>
                            <div class="col-sm-10">
                                <input type="text" readonly="true" class="form-control" id="et_templatepseudoname" name="et_templatepseudoname" placeholder="{{trans('labels.templateblheadpseudoname')}}" value="{{$et_templatepseudoname}}" minlength="6" maxlength="50">
                            </div>
                        </div>

                        <?php
                        if (old('et_subject'))
                            $et_subject = old('et_subject');
                        elseif ($templateDetail)
                            $et_subject = $templateDetail->et_subject;
                        else
                            $et_subject = '';
                        ?>
                        <div class="form-group">
                            <label for="et_subject" class="col-sm-2 control-label">{{trans('labels.formlblsubject')}}</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="et_subject" name="et_subject" placeholder="{{trans('labels.formlblsubject')}}" value="{{$et_subject}}" minlength="6" maxlength="50">
                            </div>
                        </div>

                        <?php
                        if (old('et_body'))
                            $et_body = old('et_body');
                        elseif ($templateDetail)
                            $et_body = $templateDetail->et_body;
                        else
                            $et_body = '';
                        ?>
                        <div class="form-group">
                            <label for="et_body" class="col-sm-2 control-label">{{trans('labels.formlblbody')}}</label>
                            <div class="col-sm-10">
                                <textarea  name="et_body" id="et_body">{{$et_body}}</textarea>
                            </div>
                        </div>

                        <?php
                        if (old('deleted'))
                            $deleted = old('deleted');
                        elseif ($templateDetail)
                            $deleted = $templateDetail->deleted;
                        else
                            $deleted = '';
                        ?>
                        <div class="form-group">
                            <label for="deleted" class="col-sm-2 control-label">{{trans('labels.formlblstatus')}}</label>
                            <div class="col-sm-6">
                                <?php $staus = Helpers::status();
                                ?>
                                <select class="form-control" id="deleted" name="deleted">
                                    <?php foreach ($staus as $key => $value) { ?>
                                        <option value="{{$key}}" <?php if($deleted == $key) echo 'selected'; ?> >{{$value}}</option>
                                    <?php } ?>

                                </select>
                            </div>
                        </div>

                    </div>
                    <div class="box-footer">
                        <button type="submit" id="submit" class="btn btn-primary btn-flat" >{{trans('labels.savebtn')}}</button>
                        <a class="btn btn-danger btn-flat pull-right" href="{{ url('admin/emailtemplates') }}">{{trans('labels.cancelbtn')}}</a>
                    </div><!-- /.box-footer -->
                </form>
            </div>   <!-- /.row -->
        </div>
    </div>
</section><!-- /.content -->

@stop

@section('script')

<script src="{{ asset('ckeditor/ckeditor.js') }}"></script>
<script type="text/javascript">
    CKEDITOR.replace( 'et_body' );
    jQuery(document).ready(function() {
    jQuery.validator.addMethod("emptyetbody", function(value, element) {
    var et_body_data = CKEDITOR.instances['et_body'].getData();
  return et_body_data != '';
}, "<?php echo trans('validation.requiredfield')?>");
            var adminvalidationRules = {
                et_templatename : {
                    required : true
                },
                et_templatepseudoname : {
                    required : true
                },
                et_subject : {
                    required : true
                },
                et_body: {
                    emptyetbody : true
                },
                deleted : {
                    required : true
                }
            }

        $("#addTemplate").validate({
            ignore : "",
            rules : adminvalidationRules,
            messages : {
                et_templatename : {
                    required : "<?php echo trans('validation.requiredfield'); ?>"
                },
                et_templatepseudoname : {
                    required : "<?php echo trans('validation.requiredfield'); ?>"
                },
                et_subject : {
                    required : "<?php echo trans('validation.requiredfield'); ?>"
                },
                et_body : {
                    emptyetbody : "<?php echo trans('validation.requiredfield'); ?>"
                },
                deleted : {
                    required : "<?php echo trans('validation.requiredfield'); ?>"
                }
            }
        })
    });
</script>

<?php if (empty($templateDetail)){ ?>
    <script>
    $('#et_templatename').keyup(function ()
    {
        var str = $(this).val();
        str = str.replace(/[^a-zA-Z0-9\s]/g, "");
        str = str.toLowerCase();
        str = str.replace(/\s/g, '-');
        $('#et_templatepseudoname').val(str);
        $('#et_templatepseudoname').valid();
    });
    </script>
    <?php } ?>
@stop

