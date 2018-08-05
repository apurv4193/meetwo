@extends('Admin.Master')

@section('content')

<!-- Content Wrapper. Contains page content -->

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        {{ trans('labels.questionmanagement') }}
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <!-- right column -->
        <div class="col-md-12">
            <!-- Horizontal Form -->
            <div class="box box-info">
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

                <form id="addQuestionsData" class="form-horizontal" method="post" action="{{ url('/admin/addquestiondataimportexcel') }}" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="box-body">

                    <div class="form-group">
                        <label for="question-data" class="col-sm-2 control-label">{{trans('labels.importquestion')}}</label>
                        <div class="col-sm-6">
                            <input type="file" id="importfile" name="importfile"  onchange="checkfile(this);"/>
                        </div>
                    </div>

                    </div>
                    <div class="box-footer">
                        <button type="submit" id="submit" class="btn btn-primary btn-flat" >{{trans('labels.submit')}}</button>
                        <a class="btn btn-danger btn-flat pull-right" href="{{ url('admin/question') }}">{{trans('labels.cancelbtn')}}</a>
                    </div><!-- /.box-footer -->
                </form>
            </div>   <!-- /.row -->
        </div>
    </div>
</section><!-- /.content -->

@stop

@section('script')
<script type="text/javascript">
    jQuery(document).ready(function() {

            var validationRules = {
                 importfile : {
                    required : true
                }
            }


        $("#addQuestionsData").validate({
            rules : validationRules,
            messages : {
                  importfile : {
                    required : "<?php echo trans('validation.requiredfield'); ?>"
                }
            }
        })
    });

    function checkfile(sender) {
        var validExts = new Array(".xlsx", ".xls");
        var fileExt = sender.value;
        fileExt = fileExt.substring(fileExt.lastIndexOf('.'));
        if (validExts.indexOf(fileExt) < 0) {
          alert("Invalid file selected, valid files are of " +
                   validExts.toString() + " types.");
          return false;
        }
        else return true;
    }


</script>
@stop
