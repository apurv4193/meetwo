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
        {{trans('labels.usersmanagement')}}
        <a href="{{ url('admin/addUserData') }}" class="btn btn-block btn-primary add-btn-primary pull-right">{{trans('labels.add')}}</a>
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
                <form onsubmit="return fetch_checkbox(this);" id="UsersListingForm" class="form-horizontal" name="UsersListingForm" method="post" action="{{ url('/admin/deleteUsersRow') }}" enctype="multipart/form-data">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <div class="box-footer">
                <button type="submit" class="btn btn-primary btn-flat pull-left" id="submitUsersData" name="submitUsersData">{{trans('labels.deletebtn')}}</button>
                </div><!-- /.box-footer -->

                    <table id="UsersListingTable" class="table table-striped">
                        <thead>
                            <tr class="filters">
                                <th style="width: 10px !important;"><input type="checkbox" id="checkall" name="checkall"/></th>
                                <th>{{trans('labels.formno')}}</th>
                                <th>{{trans('labels.formlblfirstname')}}</th>
                                <th>{{trans('labels.formlbllastname')}}</th>
                                <th>{{trans('labels.useremail')}}</th>
                                <th>{{trans('labels.usergender')}}</th>
                                <th>{{trans('labels.userscore')}}</th>
                                <th>{{trans('labels.blheadcreatedat')}}</th>
                                <th>{{trans('labels.blheadactions')}}</th>
                                <th>{{trans('labels.blheadstatus')}}</th>
                                <th>{{trans('labels.blviewdetails')}}</th>
                            </tr>
                        </thead>
                    </table>

                </form>

                <div id="UserAllDetailModel" class="modal fade UserAllDetailModel" role="dialog">

                </div>

                <div id="UserAllQuestionsDetailModel" class="modal fade UserAllQuestionsDetailModel" role="dialog">

                </div>

                <div id="showUserProfile" class="modal fade UserAllDetailModel" role="dialog">
                    <div class="modal-dialog userAllDetailBox">
                        <div class="modal-content userAllDetailInbox">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title"> {{ trans('labels.userProfile') }} &nbsp;</h4>
                            </div>
                            <div class="modal-body">
                                <div id="showProfile">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                </div>
                </div>
            </div>
        </div>
    </div>
</section>
@stop

@section('script')
<script type="text/javascript">
        $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });
  </script>
<script type="text/javascript">

    function fetch_user_details($id)
    {
       $.ajax({
         type: 'post',
         url: '{{ url("admin/getAllUserDetails") }}',
         data: {
           id:$id
         },
         success: function (response)
         {
            $('#UserAllDetailModel').html(response);
         }
       });
    }

    function fetch_question_details($id)
    {
       $.ajax({
         type: 'post',
         url: '{{ url("admin/getAllQuestionsDetails") }}',
         data: {
           id:$id
         },
         success: function (response)
         {
            $('#UserAllQuestionsDetailModel').html(response);
         }
       });
    }

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

    function show_profile(image) {
        $('#showProfile').html('<img id="theImg" src="'+ image +'" class="img-responsive"/>')
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
            var table = $('#UsersListingTable').DataTable({
            pageLength: 25,
            lengthMenu: [25, 50, 100, 200],
        //  pageLength: 5,
            processing: true,
            serverSide: true,
            ajax: '{!! route('admin.datatables.usersListing') !!}',
            columns: [
                    {data: 'id', name: 'id',orderable: false, searchable: false},
                    {render: '[]'},
                    {data: 'u_firstname', name: 'u_firstname'},
                    {data: 'u_lastname', name: 'u_lastname'},
                    {data: 'u_email', name: 'u_email'},
                    {data: 'u_gender', name: 'u_gender'},
                    {data: 'u_total_score', name: 'u_total_score'},
                    {data: 'created_at', name: 'created_at'},
                    {data: 'actions', name: 'actions', orderable: false, searchable: false},
                    {data: 'deleted', name: 'deleted', orderable: false, searchable: false},
                    {data: 'viewUserDetails', name: 'viewUserDetails', orderable: false, searchable: false}
                ],

            // define  first column , is column zero
                columnDefs: [{
                       searchable: false,
                       orderable: false,
                       targets: 0
                   }],

            // sort at column one
                order: [[8, 'desc']],

                rowCallback: function( row, data, iDisplayIndex ) {
                    var info = table.page.info();
                    var page = info.page;
                    var length = info.length;
                    var index = (page * length + (iDisplayIndex +1));
                    $('td:eq(1)', row).html(index);
                },

            });

            //  create index for table at columns zero

            table.on('order.dt search.dt', function () {
                table.column(1, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                    cell.innerHTML = i+1;
                });
            }).draw();


            var tt = new $.fn.dataTable.TableTools(table);
            $(tt.fnContainer()).insertBefore('div.dataTables_wrapper');

        });

    });

 $( "#submitUsersData" ).mouseover(function() {
       $('form :submit').prop("disabled", false);
    });

</script>

@stop
