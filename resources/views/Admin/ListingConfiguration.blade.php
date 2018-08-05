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
        {{trans('labels.configuration')}}
        <a href="{{ url('admin/addConfigurationData') }}" class="btn btn-block btn-primary add-btn-primary pull-right">{{trans('labels.add')}}</a>
    </h1>
</section>

<section class="content">
    <div class="row">

        <div class="col-md-12">
            <div class="box box-primary">

                <div class="box-body">
                <table id="ConfigrationListingTable" class="table table-striped">
                        <thead>
                            <tr class="filters">
                                <th>{{trans('labels.formno')}}</th>
                                <th>{{trans('labels.key')}}</th>
                                <th>{{trans('labels.value')}}</th>
                                <th>{{trans('labels.blheadactions')}}</th>
                            </tr>
                        </thead>
                    </table>

                </div>
            </div>
        </div>
    </div>
</section>
@stop

@section('script')

<script type="text/javascript">

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
            var table = $('#ConfigrationListingTable').DataTable({
        //  pageLength: 5,
            processing: true,
            serverSide: true,
            ajax: '{!! route('admin.datatables.configurationListing') !!}',
            columns: [
                    {render: '[]'},
                    {data: 'c_key', name: 'c_key'},
                    {data: 'c_value', name: 'c_value'},
                    {data: 'actions', name: 'actions', orderable: false, searchable: false}

                ],

            // sort at column one
                order: [[ 0, 'asc' ]],

                rowCallback: function( row, data, iDisplayIndex ) {
                    var info = table.page.info();
                    var page = info.page;
                    var length = info.length;
                    var index = (page * length + (iDisplayIndex +1));
                    $('td:eq(0)', row).html(index);
                },

               });
           //  create index for table at columns zero

            table.on('order.dt search.dt', function () {
                table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                    cell.innerHTML = i+1;
                });
            }).draw();


            var tt = new $.fn.dataTable.TableTools(table);
            $(tt.fnContainer()).insertBefore('div.dataTables_wrapper');

        });

    });

</script>

@stop
