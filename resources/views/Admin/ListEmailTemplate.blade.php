@extends('Admin.Master')

@section('content')
<!-- content   -->

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        <div class="col-md-10">
            {{trans('labels.emailtemplate')}}
        </div>
        <div class="col-md-2">
            <a href="{{ url('admin/addemailtemplate') }}" class="btn btn-block btn-primary add-btn-primary pull-right">{{trans('labels.add')}}</a>
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
                    <form onsubmit="return fetch_checkbox(this);" id="TemplaleListingForm" class="form-horizontal" name="TemplaleListingForm" method="post" action="{{ url('/admin/deleteEmailTemplateRow') }}" enctype="multipart/form-data">
                      <input type="hidden" name="_token" value="{{ csrf_token() }}">
                      <div class="box-footer">
                          <button type="submit" class="btn btn-primary btn-flat pull-left" id="submitTemplateData" name="submitTemplateData">{{trans('labels.deletebtn')}}</button>
                      </div>
                      <table id="TempleteListingTable" class="table table-striped">
                          <thead>
                              <tr class="filters">
                                  <th style="width: 10px !important;"><input type="checkbox" id="checkall" name="checkall"/></th>
                                  <th>{{trans('labels.formno')}}</th>
                                  <th>{{trans('labels.templateblheadname')}}</th>
                                  <th>{{trans('labels.templateblheadpseudoname')}}</th>
                                  <th>{{trans('labels.templateblheadsubject')}}</th>
                                  <th>{{trans('labels.cmsblheadstatus')}}</th>
                                  <th>{{trans('labels.cmsblheadaction')}}</th>
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

    $(document).ready(function () {
        var table = $('#TempleteListingTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{!! route('.gettemplate') !!}',
            columns: [
                {data: 'id', name: 'id',orderable: false, searchable: false},
                {render: '[]'},
                {data: 'et_templatename', name: 'et_templatename'},
                {data: 'et_templatepseudoname', name: 'et_templatepseudoname'},
                {data: 'et_subject', name: 'et_subject'},
                {data: 'deleted', name: 'deleted', orderable: false, searchable: false},
                {data: 'actions', name: 'actions', orderable: false, searchable: false}
            ],
            "language": {
                  "emptyTable": "<?php echo trans('labels.norecordfound'); ?>"
            },
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
        table.on( 'draw', function () {
            $(table.table().container())
          .find('div.dataTables_paginate')
          .css( 'display', table.page.info().pages <= 1 ?
               'none' :
               'block'
          )
      });
    });

</script>

@stop