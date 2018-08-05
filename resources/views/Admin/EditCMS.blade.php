@extends('Admin.Master')

@section('content')

<!-- Content Wrapper. Contains page content -->

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        {{trans('labels.CMS')}}
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
                     <h3 class="box-title"><?php echo (isset($cmsDetail) && !empty($cmsDetail)) ? trans('labels.edit') : trans('labels.add') ?> {{trans('labels.CMS')}}</h3>
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

                <form id="addCMS" class="form-horizontal" method="post" action="{{ url('/admin/savecms') }}" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="id" value="<?php echo (isset($cmsDetail) && !empty($cmsDetail)) ? $cmsDetail->id : '0' ?>">
                    <div class="box-body">

                        <?php
                        if (old('cms_subject'))
                            $cms_subject = old('cms_subject');
                        elseif ($cmsDetail)
                            $cms_subject = $cmsDetail->cms_subject;
                        else
                            $cms_subject = '';
                        ?>
                        <div class="form-group">
                            <label for="cms_subject" class="col-sm-2 control-label">{{trans('labels.formlblsubject')}}</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="cms_subject" name="cms_subject" placeholder="{{trans('labels.formlblsubject')}}" value="{{$cms_subject}}">
                            </div>
                        </div>

                        <?php
                        if (old('cms'))
                            $cms_slug = old('cms_slug');
                        elseif ($cmsDetail)
                            $cms_slug = $cmsDetail->cms_slug;
                        else
                            $cms_slug = '';
                        ?>
                        <div class="form-group">
                            <label for="cms_slug" class="col-sm-2 control-label">{{trans('labels.formlblslug')}}</label>
                            <div class="col-sm-10">
                                <input type="text" readonly="true" class="form-control" id="cms_slug" name="cms_slug" placeholder="{{trans('labels.formlblslug')}}" value="{{$cms_slug}}">
                            </div>
                        </div>

                        <?php
                        if (old('cms_body'))
                            $cms_body = old('cms_body');
                        elseif ($cmsDetail)
                            $cms_body = $cmsDetail->cms_body;
                        else
                            $cms_body = '';
                        ?>
                        <div class="form-group">
                            <label for="cms_body" class="col-sm-2 control-label">{{trans('labels.formlblbody')}}</label>
                            <div class="col-sm-10">
                                <textarea name="cms_body" id="cms_body">{{$cms_body}}</textarea>
                            </div>
                        </div>

                        <?php
                        if (old('deleted'))
                            $deleted = old('deleted');
                        elseif ($cmsDetail)
                            $deleted = $cmsDetail->deleted;
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
                        <a class="btn btn-danger btn-flat pull-right" href="{{ url('admin/cms') }}">{{trans('labels.cancelbtn')}}</a>
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
    CKEDITOR.replace( 'cms_body' );
    jQuery(document).ready(function() {

    jQuery.validator.addMethod("emptyetbody", function(value, element) {
    var cms_body_data = CKEDITOR.instances['cms_body'].getData();

     return cms_body_data != '';
    }, "<?php echo trans('validation.requiredfield')?>");

            var validationRules = {
                cms_subject : {
                    required : true
                },
                cms_slug : {
                    required : true
                },
                cms_body : {
                  emptyetbody: true
                },
                deleted : {
                    required : true
                }
            }

        $("#addCMS").validate({
            ignore : "",
            rules : validationRules,
            messages : {
                cms_subject : {
                    required : "<?php echo trans('validation.requiredfield'); ?>"
                },
                cms_slug : {
                    required : "<?php echo trans('validation.requiredfield'); ?>"
                },
                cms_body : {
                    emptyetbody : "<?php echo trans('validation.requiredfield'); ?>"
                },
                deleted : {
                    required : "<?php echo trans('validation.requiredfield'); ?>"
                }
            }
        })
    });
</script>
    <?php if (empty($cmsDetail)){ ?>
      <script>
        $('#cms_subject').keyup(function ()
        {
            var str = $(this).val();
            str = str.replace(/[^a-zA-Z0-9\s]/g, "");
            str = str.toLowerCase();
            str = str.replace(/\s/g, '-');
            $('#cms_slug').val(str);
        });
      </script>
    <?php } ?>
@stop

