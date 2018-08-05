@extends('Admin.Master')

@section('content')
<!-- content   -->

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        {{ trans('labels.questionmanagement') }}
    </h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">

            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><?php echo (isset($questionDetail) && !empty($questionDetail)) ? trans('labels.edit') : trans('labels.add') ?> {{trans('labels.question')}}</h3>
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

                <form id="addQuestionData" class="form-horizontal" method="post" action="{{ url('/admin/saveQuestionData') }}" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="id" value="<?php echo (isset($questionDetail) && !empty($questionDetail)) ? $questionDetail->id : '0' ?>">

                    <div class="box-body">

                    <?php
                        if (old('q_question_text'))
                            $q_question_text = old('q_question_text');
                        elseif ($questionDetail)
                            $q_question_text = $questionDetail->q_question_text;
                        else
                            $q_question_text = '';
                    ?>
                    <?php
                        if (old('q_fr_question_text'))
                            $q_fr_question_text = old('q_fr_question_text');
                        elseif ($questionDetail)
                            $q_fr_question_text = $questionDetail->q_fr_question_text;
                        else
                            $q_fr_question_text = '';
                    ?>
                    <div class="form-group">
                        <label for="q_question_text" class="col-sm-2 control-label">{{trans('labels.questiontext')}}
                            <span class="required-field">(*)</span></label>
                            <div class="col-sm-4">
                                <textarea rows="3" cols="50" class="form-control" id="q_question_text" name="q_question_text" placeholder="{{trans('labels.questiontextenglish')}}">{{ $q_question_text }}</textarea>
                            </div>
                            <div class="col-sm-4">
                                <textarea rows="3" cols="50" class="form-control" id="q_fr_question_text" name="q_fr_question_text" placeholder="{{trans('labels.questiontextfrench')}}">{{ $q_fr_question_text }}</textarea>
                            </div>
                    </div>

                    <div id="qo_option_block" class="qo_option_block">
                    <?php
                        $qo_option_text = [];
                        $qo_fr_option_text = [];
                        $qo_option_id = [];
                        $qo_fr_option_id = [];
                        if($questionOptionDetail)
                        {
                            foreach($questionOptionDetail as $value)
                            {
                                $qo_option_text[] = $value->qo_option;
                                $qo_fr_option_text[] = $value->qo_fr_option;
                                $qo_option_id[] = $value->id;
                                $qo_fr_option_id[] = $value->id;
                            }
                        }
                        else
                        {
                            $qo_option_text[0] = 'YES';
                            $qo_option_text[1] = 'NO';
                            $qo_fr_option_text[0] = 'OUI';
                            $qo_fr_option_text[1] = 'NON';
                            $qo_option_id[0] = '';
                            $qo_option_id[1] = '';
                            $qo_fr_option_id[0] = '';
                            $qo_fr_option_id[1] = '';
                        }
                        if ($qo_fr_option_text[0] == '' || $qo_fr_option_text[1] == '') {
                            $qo_fr_option_text[0] = 'OUI';
                            $qo_fr_option_text[1] = 'NON';
                        }
                    ?>
                    <div class="form-group">
                        <label for="qo_option" class="col-sm-2 control-label">{{trans('labels.questionoption')}}
                            <span class="required-field">(*)</span></label>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" id="qo_option" name="qo_option[]" placeholder="{{trans('labels.questionoptionen')}}" value="{{ $qo_option_text[0] }}" />
                            </div>
                            <div class="col-sm-4">
                                <input type="text" class="form-control" id="qo_fr_option" name="qo_fr_option[]" placeholder="{{trans('labels.questionoptionfr')}}" value="{{ $qo_fr_option_text[0] }}" />
                            </div>

                    </div>

                    <div class="form-group">
                        <div class="col-sm-2"></div>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" id="qo_option1" name="qo_option[]" placeholder="{{trans('labels.questionoptionen')}}" value="<?php if($qo_option_text[1]) echo $qo_option_text[1]; ?>" />
                        </div>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" id="qo_fr_option1" name="qo_fr_option[]" placeholder="{{trans('labels.questionoptionfr')}}" value="<?php if($qo_fr_option_text[1]) echo $qo_fr_option_text[1]; ?>" />
                        </div>
                    </div>

                    </div>

                    <?php
                        if (old('q_difficulty'))
                            $q_difficulty = old('q_difficulty');
                        elseif ($questionDetail)
                            $q_difficulty = $questionDetail->q_difficulty;
                        else
                            $q_difficulty = '';

                        if (old('q_question_type'))
                            $q_question_type = old('q_question_type');
                        elseif ($questionDetail)
                            $q_question_type = $questionDetail->q_question_type;
                        else
                            $q_question_type = '';
                    ?>
                   <div class="form-group">
                        <!-- <label for="q_difficulty" class="col-sm-2 control-label">{{trans('labels.formlblquestiondiff')}}</label>
                        <div class=" col-sm-4" data-toggle="buttons">
                              <label class="btn btn-primary btn-lg buttoneasy gender_cst" style="position:relative;" >
                                  <input type="radio" name="q_difficulty" id="q_difficulty1" <?php echo ($q_difficulty == 1 || $q_difficulty == '')?'checked':'' ?> value="1" > Easy <?php echo '<i class="fa fa-check-circle" aria-hidden="true" style="position:absolute;font-size:28px;color:#000000;left:-5px;top:-10px;"></i>'; ?>
                              </label>
                              <label>&nbsp;&nbsp;</label>
                              <label class="btn btn-primary btn-lg buttondifficult gender_cst" style="position:relative;">
                                  <input type="radio" name="q_difficulty" id="q_difficulty2" <?php echo ($q_difficulty == 2)?'checked':'' ?> value="2"> Difficult  <?php echo '<i class="fa fa-check-circle" aria-hidden="true" style="position:absolute;font-size:28px;color:#000000;left:-5px;top:-10px;"></i>'; ?>
                              </label>
                        </div> -->
                        
                        <label for="q_question_type" class="col-sm-2 control-label">{{trans('labels.formlbllanguagetype')}}</label>
                        <div class=" col-sm-4" data-toggle="buttons">
                                <label>&nbsp;&nbsp;</label>
                                <label class="btn btn-primary btn-lg buttoneasy gender_cst questiontype_cst <?php echo ($q_question_type == 1)?'active':'' ?>" style="position:relative;" >
                                    <input type="checkbox" name="q_question_type" id="q_question_type" <?php echo ($q_question_type == 1)?'checked':'' ?> value="1" > Sexual <?php echo '<i class="fa fa-check-circle" aria-hidden="true" style="position:absolute;font-size:28px;color:#000000;left:-5px;top:-10px;"></i>'; ?>
                                </label>
                        </div>
                    </div>
                    <?php
                        if (old('q_importance'))
                            $q_importance = old('q_importance');
                        elseif ($questionDetail)
                            $q_importance = $questionDetail->q_importance;
                        else
                            $q_importance = '';
                    ?>

                    <div class="form-group">
                        <label for="q_importance" class="col-sm-2 control-label">{{trans('labels.formlblquestionimportance')}}</label>
                        <div class=" col-sm-6" data-toggle="buttons">
                              <label class="btn btn-primary btn-lg buttondifficult gender_cst" style="position:relative;" >
                                  <input type="radio" name="q_importance" id="q_importance1" <?php echo ($q_importance == 1 || $q_importance == '')?'checked':'' ?> value="1" > 1 <?php echo '<i class="fa fa-check-circle" aria-hidden="true" style="position:absolute;font-size:28px;color:#000000;left:-5px;top:-10px;"></i>'; ?>
                              </label>
                              <label>&nbsp;&nbsp;</label>
                              <label class="btn btn-primary btn-lg buttondifficult gender_cst" style="position:relative;">
                                  <input type="radio" name="q_importance" id="q_importance2" <?php echo ($q_importance == 2)?'checked':'' ?> value="2"> 2 <?php echo '<i class="fa fa-check-circle" aria-hidden="true" style="position:absolute;font-size:28px;color:#000000;left:-5px;top:-10px;"></i>'; ?>
                              </label>
                              <label>&nbsp;&nbsp;</label>
                              <label class="btn btn-primary btn-lg buttondifficult gender_cst" style="position:relative;">
                                  <input type="radio" name="q_importance" id="q_importance2" <?php echo ($q_importance == 3)?'checked':'' ?> value="3"> 3 <?php echo '<i class="fa fa-check-circle" aria-hidden="true" style="position:absolute;font-size:28px;color:#000000;left:-5px;top:-10px;"></i>'; ?>
                              </label>
                        </div>
                    </div>


                    <?php
                        if (old('deleted'))
                            $deleted = old('deleted');
                        elseif ($questionDetail)
                            $deleted = $questionDetail->deleted;
                        else
                            $deleted = '';
                    ?>
                    <div class="form-group">
                        <label for="deleted" class="col-sm-2 control-label">{{trans('labels.formlblstatus')}}</label>
                        <div class="col-sm-6">
                            <?php $staus = Helpers::status(); ?>
                            <select class="form-control" id="deleted" name="deleted">
                                <?php foreach ($staus as $key => $value) { ?>
                                    <option value="{{$key}}" <?php if($deleted == $key) echo 'selected'; ?>>{{$value}}</option>                                <?php } ?>
                            </select>
                        </div>
                    </div>

                  </div>
                  <div class="box-footer">
                      <button type="submit" class="btn btn-primary btn-flat" id="submitQuestion" name="submitQuestion">{{trans('labels.savebtn')}}</button>
                        <a class="btn btn-danger btn-flat pull-right" href="{{ url('admin/question') }}">{{trans('labels.cancelbtn')}}</a>

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

            <?php if(isset($questionDetail->id) && $questionDetail->id != '0') { ?>
            var validationRules = {

                q_question_text : {
                    required : true
                },
                'qo_option[]' : {
                    required : true
                },
                q_difficulty : {
                    required : true
                },
                q_importance : {
                    required : true
                },
                deleted : {
                    required : true
                }
            }
        <?php } else { ?>

            var validationRules = {

                q_question_text : {
                    required : true
                },
                'qo_option[]' : {
                    required : true
                },
                q_difficulty : {
                    required : true
                },
                q_importance : {
                    required : true
                },
                deleted : {
                    required : true
                }

            }
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
                q_difficulty : {
                    required : "<?php echo trans('validation.requiredfield'); ?>"
                },
                q_importance : {
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