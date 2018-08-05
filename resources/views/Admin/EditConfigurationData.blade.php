@extends('Admin.Master')

@section('content')
<!-- content   -->

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        {{ trans('labels.configuration') }}
    </h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">

            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><?php echo (isset($configurationDetail) && !empty($configurationDetail)) ? trans('labels.edit') : trans('labels.add') ?> {{trans('labels.configuration')}}</h3>
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

                <form id="addConfigurationData" class="form-horizontal" method="post" action="{{ url('/admin/saveConfigurationData') }}" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="id" value="<?php echo (isset($configurationDetail) && !empty($configurationDetail)) ? $configurationDetail->id : '0' ?>">

                    <div class="box-body">

                    <?php
                        if (old('c_key'))
                            $c_key = old('c_key');
                        elseif ($configurationDetail)
                            $c_key = $configurationDetail->c_key;
                        else
                            $c_key = '';
                    ?>
                    <div class="form-group">
                        <label for="c_key" class="col-sm-2 control-label">{{trans('labels.key')}}</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" id="c_key" name="c_key" placeholder="{{trans('labels.key')}}" value="{{$c_key}}" />
                            </div>
                    </div>

                    <?php
                        if (old('c_value'))
                            $c_value = old('c_value');
                        elseif ($configurationDetail)
                            $c_value = $configurationDetail->c_value;
                        else
                            $c_value = '';
                    ?>
                    <div class="form-group">
                        <label for="c_value" class="col-sm-2 control-label">{{trans('labels.value')}}</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" id="c_value" name="c_value" placeholder="{{trans('labels.value')}}" value="{{$c_value}}" />
                            </div>
                    </div>

                  </div>
                  <div class="box-footer">
                      <button type="submit" class="btn btn-primary btn-flat" id="submitConfiguration" name="submitConfiguration">{{trans('labels.savebtn')}}</button>
                        <a class="btn btn-danger btn-flat pull-right" href="{{ url('admin/configuration') }}">{{trans('labels.cancelbtn')}}</a>

                  </div><!-- /.box-footer -->
                </form>

            </div>


        </div>
    </div>
</section>
@stop

@section('script')

<script type="text/javascript">

    jQuery(document).ready(function() {
            var readonly = '<?php echo isset($configurationDetail) && !empty($configurationDetail) ? 'readonly':''; ?>'
            if(readonly == 'readonly'){
                $('#c_key').attr('readonly', true);
                $('#c_key').keypress(function(){
                   return false;
                });
            }

            <?php if(isset($configurationDetail->id) && $configurationDetail->id != '0') { ?>
            var validationRules = {

                c_key : {
                    required : true
                },
                c_value : {
                    required : true
                },
                deleted : {
                    required : true
                }
            };
        <?php } else { ?>

            var validationRules = {

                c_key : {
                    required : true
                },
                c_value : {
                    required : true
                },
                deleted : {
                    required : true
                }

            };
        <?php } ?>

        $("#addConfigurationData").validate({
            rules : validationRules,
            messages : {
                c_key : {
                    required : "<?php echo trans('validation.requiredfield'); ?>"
                },
                c_value : {
                    required : "<?php echo trans('validation.requiredfield'); ?>"
                },
                deleted : {
                    required : "<?php echo trans('validation.requiredfield'); ?>"
                }
            }
        })


    });

    jQuery(document).ready(function() {

            <?php if(isset($questionDetail->id) && $questionDetail->id != '0') { ?>
            var validationRules = {

                q_question_text : {
                    required : true
                },
                'qo_option[]' : {
                    required : true
                },
                deleted : {
                    required : true
                }
            };
        <?php } else { ?>

            var validationRules = {

                q_question_text : {
                    required : true
                },
                'qo_option[]' : {
                    required : true
                },
                deleted : {
                    required : true
                }

            };
        <?php } ?>

        $("#addQuestionData").validate({
            rules : validationRules,
            messages : {
                q_question_text : {
                    required : "<?php echo trans('validation.requiredfield'); ?>"
                },
                'qo_option[]' : {
                    required : "<?php echo trans('validation.requiredfield'); ?>"
                },
                deleted : {
                    required : "<?php echo trans('validation.requiredfield'); ?>"
                }
            }
        })


    });


</script>


@stop