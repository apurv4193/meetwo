@extends('Admin.Master')

@section('content')
<!-- content   -->

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        Profile Pic Management
        <?php if (!empty($PhotosDetail)) { $userId = $PhotosDetail[0]->up_user_id;} else { $userId = $id;} ?>
        <a class="btn btn-danger btn-flat pull-right" href="{{ url('admin/editUserDetail') }}/{{$userId}}">Back</a>
    </h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">

            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Profile Pictures</h3>
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

                <form id="addUserPhotosDetail" name="addUserPhotosDetail" class="form-horizontal" method="post" action="{{ url('/admin/saveUserPhotosDetail') }}" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="user_id" value="<?php echo (isset($PhotosDetail) && !empty($PhotosDetail)) ? $PhotosDetail[0]->up_user_id : $id ?>">

                    <div class="box-body">

                    <?php  $image = [];
                    if (isset($PhotosDetail) && !empty($PhotosDetail)) {
                        foreach ($PhotosDetail as $key => $value) {
                            $userId = $value->up_user_id;
                            $image = explode(",", $value->up_photo_name);
                            $IsProfile = explode(",", $value->up_is_profile_photo);
                            $pId = explode(",", $value->p_id);
                            for ($i = 0; $i < count($image); $i++) 
                            {
                                $photo = $image[$i];
//                              if ($photo != '' && File::exists(public_path($uploadProfilePath . $photo))) 
                                if ($photo != '' && Storage::disk('s3')->has(Config::get('constant.USER_PROFILE_THUMB_IMAGE_UPLOAD_PATH').$photo) == 1) 
                                {
//                                  $url = url($uploadProfilePath . $photo);
                                    $url = Config::get('constant.AWS_FILE_UPLOAD_URL').Config::get('constant.USER_PROFILE_THUMB_IMAGE_UPLOAD_PATH').$photo;
                                } else {
                                    $url = asset("/backend/images/logo.png");
                                }
                                $isPic = $IsProfile[$i];
                                ?>
                                    <div class="form-group">
                                        <div class="col-sm-2">
                                            <img src="{{ $url }}" alt="" width="50px" height="50px">
                                        </div>
                                        <div class="col-sm-1">
                                            <input type="radio" value="{{$pId[$i]}}" onclick="setProfilePic({{$pId[$i]}},{{$value->up_user_id}});" name="u_profile_active" id="u_profile_active" <?php
                                              if ($isPic) {
                                                  echo 'checked="cehcked"';
                                              }
                                              ?>/>
                                        </div>
                                        <div class="col-sm-1">
                                            <input type="button" value="Delete" class="btn btn-danger btn-flat" id ="delete" onclick="deleteMedia({{$pId[$i]}},'{{$photo}}');">
                                        </div>
                                    </div>
                            <?php
                            }
                        }
                    }
                    ?>

                    <div id="addoption" class="addoption">

                    </div>
                    <div class="form-group" id="addNew">
                        <div class="col-sm-3" >
                            <a href="" id="add" class="btn btn-block btn-primary add-btn-primary">{{trans('labels.add')}}</a>
                        </div>
                    </div>

                  </div>
                  <div class="box-footer">
                      <button type="submit" class="btn btn-primary btn-flat" id="submitUserDetails" name="submitUserDetails">{{trans('labels.savebtn')}}</button>

                  </div><!-- /.box-footer -->
                </form>

            </div>


        </div>
    </div>
</section>
@stop

@section('script')

<script type="text/javascript">
    function deleteMedia(id,media_name)
    {
        res = confirm('Are you sure you want to delete this record?');
        if(res){
        $.ajax({
            url: "{{ url('admin/deleteUserProfilePhotoById') }}",
            type: 'post',
            data: {
                "_token": '{{ csrf_token() }}',
                "id": id,
                "media_name": media_name
            },
            success: function(response) {
               location.reload();
            }
        });
        }else{
            return false;
        }
    }

    function setProfilePic(id,user_id)
    {
        res = confirm('Are you sure you want to set this image as profile pic?');
        if(res){
        $.ajax({
            url: "{{ url('admin/setProfilePic') }}",
            type: 'post',
            data: {
                "_token": '{{ csrf_token() }}',
                "id": id,
                "user_id": user_id
            },
            success: function(response) {
               location.reload();
            }
        });
        }else{
            return false;
        }
    }

    jQuery(document).ready(function()
    {
        <?php  $value = count($image); ?>
        var data = <?php echo $value; ?>;
        if (data == 6) {
            $('#addNew').css("display", "none");
        }
        var wrapper = $("#addoption");
         $('#add').click(function(e)
         {
            e.preventDefault();
            if (data < 6 ) {
                var option = '<div class="form-group" id="click">'+
                              '<div class="col-sm-3">'+
                                   '<input type="file" id="up_photo_name" name="up_photo_name[]"/>'+
                              '</div>'+
                          '</div>';
                $(wrapper).append(option);
                data++;
                if (data == 6 ) {
                    $('#addNew').css("display", "none");
                }
            } else {
                $('#addNew').css("display", "none");
            }
         });

         $(wrapper).on("click",".remove", function(){
                 $(this).parents('#click').remove();
        });

        var count = <?php echo $value; ?>;
        if (count == 1) {
            $('#delete').prop('disabled', true);
        }
    });
</script>
@stop