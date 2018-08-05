@extends('Admin.Master')

@section('content')
<!-- content   -->

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

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        <div class="col-md-7">
            {{trans('labels.questionmanagement')}}
        </div>
        <div class="col-md-1">
            <a href="{{ url('admin/addQuestionData') }}" class="btn btn-block btn-primary add-btn-primary pull-right">{{trans('labels.add')}}</a>
        </div>
        <div class="col-md-2">
            <a href="{{ url('admin/importquestiondata') }}" class="btn btn-block btn-primary">{{trans('labels.importdata')}}</a>
        </div>
        <div class="col-md-2">
            <a href="{{ url('admin/exportquestiondata') }}" class="btn btn-block btn-primary">{{trans('labels.exportdata')}}</a>
        </div>
    </h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box-header pull-right ">
                <i class="s_active fa fa-square"></i> {{trans('labels.activelbl')}} <i class="s_inactive fa fa-square"></i>{{trans('labels.inactivelbl')}}
            </div>
        </div>
        <div class="col-md-12">
            <div class="box box-primary">

                <div class="box-body">
                <form onsubmit="return fetch_checkbox(this);" id="QuestionListingForm" class="form-horizontal" name="QuestionListingForm" method="post" action="{{ url('/admin/deleteQuestionDataRow') }}" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="box-footer">
                <button type="submit" class="btn btn-primary btn-flat pull-left" id="submitQuestionData" name="submitQuestionData">{{trans('labels.deletebtn')}}</button>
                </div><!-- /.box-footer -->

                    <table id="QuestionListingTable" class="table table-striped">
                        <thead>
                            <tr class="filters">
                                <th style="width: 10px !important;"><input type="checkbox" id="checkall" name="checkall"/></th>
                                <th>{{trans('labels.formno')}}</th>
                                <th>{{trans('labels.questiontext')}}</th>
                                <th>{{trans('labels.questionoption')}}</th>
                                <th>{{trans('labels.formlblquestiondiff')}}</th>
                                <th>{{trans('labels.formlblquestionimportance')}}</th>
                                <th>{{trans('labels.formlbltotalquestion')}}</th>
                                <th>{{trans('labels.formlblratio')}}</th>
                                <th>{{trans('labels.blheadstatus')}}</th>
                                <th>{{trans('labels.blheadactions')}}</th>
                            </tr>
                        </thead>
                    </table>

                </form>

                </div>
            </div>
        </div>
    </div>
</section>
@stop

@section('script')

<script type="text/javascript">

    function fetch_checkbox(val)
    {
        var cboxes = document.getElementsByName('id[]');
        var len = cboxes.length;
        var checkedValue = false;
        for (var i=0; i<len; i++)
        {
          if(cboxes[i].checked)
          {
            checkedValue = true;
            break;
          }
        }
        if(checkedValue == true)
        {
            return confirm('<?php echo trans("labels.confirmdelete"); ?>');
        }
        else
        {
            alert('<?php echo trans("labels.pleaseselectrecord"); ?>');
            return false;
        }
    }

$.ajaxSetup({ headers: { 'csrftoken' : '{{ csrf_token() }}' } });

    $('#checkall').click(function(event) {
        if(this.checked) {
            $(':checkbox').each(function() {
                this.checked = true;
            });
        }
        else {
          $(':checkbox').each(function() {
                this.checked = false;
            });
        }
    });

$.ajaxSetup({ headers: { 'csrftoken' : '{{ csrf_token() }}' } });

    $(document).ready($.fn.myFunction = function  () {

            $(function () {
            var table = $('#QuestionListingTable').DataTable({
            pageLength: 200,
            lengthMenu: [ 100, 150 ,200 ,300 ,400 ],
            processing: true,
            serverSide: true,
            method: "post",
            ajax: '{!! route('admin.datatables.questionListing') !!}',
            columns: [
                    {data: 'id', name: 'id', orderable: false, searchable: false},
                    {data: 'id', name: 'id'},
                    {data: 'q_question_text', name: 'q_question_text'},
                    {data: 'qo_option', name: 'qo_option', searchable: false},
                    {data: 'q_difficulty', name: 'q_difficulty', searchable: false},
                    {data: 'q_importance', name: 'q_importance', searchable: false},
                    {data: 'total_question', name: 'total_question'},
                    {data: 'ratio', name: 'ratio', searchable: false},
                    {data: 'deleted', name: 'deleted', orderable: false, searchable: false},
                    {data: 'actions', name: 'actions', orderable: false, searchable: false}
                ],

                // define  first column , is column zero
                columnDefs: [{
                       searchable: false,
                       orderable: false,
                       targets: 0
                   }],

                // sort at column one
                order: [[ 0, 'asc' ]],

            rowCallback: function( row, data, iDisplayIndex ) {
                    var info = table.page.info();
                    var page = info.page;
                    var length = info.length;
                    var index = (page * length + (iDisplayIndex +1));
                    $('td:eq(1)', row).html(index);
                },

            });

            var tt = new $.fn.dataTable.TableTools(table);
            $(tt.fnContainer()).insertBefore('div.dataTables_wrapper');

        });

    });

 $( "#submitQuestionData" ).mouseover(function() {
       $('form :submit').prop("disabled", false);
    });

</script>

@stop
