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
        {{trans('labels.CMS')}}
        <a href="{{ url('admin/addcms') }}" class="btn btn-block btn-primary add-btn-primary pull-right">{{trans('labels.add')}}</a>
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
                <form onsubmit="return fetch_checkbox(this);" id="CMSListingForm" class="form-horizontal" name="CMSListingForm" method="post" action="{{ url('/admin/deleteCMSRow') }}" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary btn-flat pull-left" id="submitCMSData" name="submitCMSData">{{trans('labels.deletebtn')}}</button>
                    </div>
                    <table id="CMSListingTable" class="table table-striped">
                        <thead>
                            <tr class="filters">
                                <th style="width: 10px !important;"><input type="checkbox" id="checkall" name="checkall"/></th>
                                <th>{{trans('labels.formno')}}</th>
                                <th>{{trans('labels.cmsblheadsubject')}}</th>
                                <th>{{trans('labels.cmsblheadslug')}}</th>
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

$.ajaxSetup({ headers: { 'csrftoken' : '{{ csrf_token() }}' } });

$(document).ready($.fn.myFunction = function  () {

        $(function () {
            var table = $('#CMSListingTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{!! route('admin.datatables.cmsListing') !!}',
                columns: [
                    {data: 'id', name: 'id',orderable: false, searchable: false},
                    {render: '[]'},
                    {data: 'cms_subject', name: 'cms_subject'},
                    {data: 'cms_slug', name: 'cms_slug'},
                    {data: 'deleted', name: 'q.deleted', orderable: false, searchable: false},
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
           // create index for table at columns zero

            table.on('order.dt search.dt', function () {
                table.column(1, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                    cell.innerHTML = i+1;
                });
            }).draw();

            var tt = new $.fn.dataTable.TableTools(table);
            $(tt.fnContainer()).insertBefore('div.dataTables_wrapper');

        });
    });

</script>

@stop
