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
        {{trans('labels.reporteduser')}}
    </h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">

        </div>
        <div class="col-md-12">
            <div class="box box-primary">

                <div class="box-body">
                <form id="ReportedUserListingForm" class="form-horizontal" name="ReportedUserListingForm" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <table id="ReportedUserListingTable" class="table table-striped">
                        <thead>
                            <tr class="filters">
                                <th>{{trans('labels.formno')}}</th>
                                <th>{{trans('labels.reportedtoblhead')}}</th>
                                <th>{{trans('labels.reportedbyblhead')}}</th>
                                <th>{{trans('labels.reportedreasonblhead')}}</th>
                                <th>{{trans('labels.reporteddateblhead')}}</th>
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

    function show_profile(image) {
        $('#showProfile').html('<img id="theImg" src="'+ image +'" class="img-responsive"/>')
    }

$.ajaxSetup({ headers: { 'csrftoken' : '{{ csrf_token() }}' } });

$(document).ready($.fn.myFunction = function  () {

            $(function () {
            var table = $('#ReportedUserListingTable').DataTable({
            pageLength: 25,
            lengthMenu: [25, 50, 100, 200],
            processing: true,
            serverSide: true,
            method: "post",
            ajax: '{!! route('admin.datatables.ReportedUserListing') !!}',
            columns: [
                    {data: 'id', name: 'id'},
                    {data: 'upr_viewed_id', name: 'reported_to'},
                    {data: 'upr_viewer_id', name: 'reported_by'},
                    {data: 'upr_report_reason', name: 'upr_report_reason'},
                    {data: 'created_at', name: 'created_at'} ,
                    {data: 'viewUserDetails', name: 'viewUserDetails', orderable: false, searchable: false}
                ],

            // sort at column one
                order: [[ 4, 'desc' ]],

                rowCallback: function( row, data, iDisplayIndex ) {
                    var info = table.page.info();
                    var page = info.page;
                    var length = info.length;
                    var index = (page * length + (iDisplayIndex +1));
                    $('td:eq(0)', row).html(index);
                },

               });
           //  create index for table at columns zero


            var tt = new $.fn.dataTable.TableTools(table);
            $(tt.fnContainer()).insertBefore('div.dataTables_wrapper');

        });

    });

</script>

@stop
