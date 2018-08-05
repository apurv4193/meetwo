@extends('Admin.Master')

@section('content')
<!-- content   -->

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        {{ trans('labels.usersmanagement') }}
    </h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">

            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><?php echo (isset($userDetail) && !empty($userDetail)) ? trans('labels.edit') : trans('labels.add') ?> {{trans('labels.useralldetails')}}</h3>
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

                <form id="addUserDetail" name="addUserDetail" class="form-horizontal" method="post" action="{{ url('/admin/saveUserDetail') }}" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="id" value="<?php echo (isset($userDetail) && !empty($userDetail)) ? $userDetail->id : '0' ?>">
                    <input type="hidden" name="u_fb_identifier" value="<?php echo (isset($userDetail) && !empty($userDetail)) ? $userDetail->u_fb_identifier : '' ?>">

                    <div class="box-body">

                     <?php
                        if (old('u_firstname'))
                            $u_firstname = old('u_firstname');
                        elseif ($userDetail)
                            $u_firstname = $userDetail->u_firstname;
                        else
                            $u_firstname = '';
                    ?>
                    <div class="form-group">
                        <label for="u_firstname" class="col-sm-2 control-label">{{trans('labels.formlblfirstname')}}
                            <span class="required-field">(*)</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" id="u_firstname" name="u_firstname" placeholder="{{trans('labels.formlblfirstname')}}" value="{{$u_firstname }}" />
                            </div>
                    </div>

                    <?php
                        if (old('u_lastname'))
                            $u_lastname = old('u_lastname');
                        elseif ($userDetail)
                            $u_lastname = $userDetail->u_lastname;
                        else
                            $u_lastname = '';
                    ?>
                    <div class="form-group">
                        <label for="u_lastname" class="col-sm-2 control-label">{{trans('labels.formlbllastname')}}
                            <span class="required-field">(*)</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" id="u_lastname" name="u_lastname" placeholder="{{trans('labels.formlbllastname')}}" value="{{ $u_lastname }}" />
                            </div>
                    </div>


                    <!--<?php
                        if (old('u_email'))
                            $u_email = old('u_email');
                        elseif ($userDetail)
                            $u_email = $userDetail->u_email;
                        else
                            $u_email = '';
                    ?>
                    <div class="form-group">
                        <label for="u_email" class="col-sm-2 control-label">{{trans('labels.formlblemail')}}
                            <span class="required-field">(*)</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" id="u_email" name="u_email" placeholder="{{trans('labels.formlblemail')}}" value="{{ $u_email }}" />
                            </div>
                    </div>-->


                    <?php
                        if (old('u_gender'))
                            $u_gender = old('u_gender');
                        elseif ($userDetail)
                            $u_gender = $userDetail->u_gender;
                        else
                            $u_gender = '';
                    ?>
                    <div class="form-group">
                        <label for="u_gender" class="col-sm-2 control-label">{{trans('labels.formlblgender')}}
                        <span class="required-field">(*)</span></label>
                        <div class=" col-sm-6" data-toggle="buttons">
                              <label class="btn btn-primary btn-lg buttoneasy gender_cst" style="position:relative;" >
                                  <input type="radio"  name="u_gender" id="u_gender1" <?php echo ($u_gender == 1 || $u_gender == '')?'checked':'' ?> value="1" > {{trans('labels.formblmale')}} <?php  echo '<i class="fa fa-check-circle " aria-hidden="true" style="position:absolute;font-size:28px;color:#000000;left:-5px;top:-10px;"></i>'; ?>
                              </label>
                              <label>&nbsp;&nbsp;</label>
                              <label class="btn btn-primary btn-lg buttondifficult gender_cst" style="position:relative;" >
                                  <input type="radio"  name="u_gender" id="u_gender2" <?php echo ($u_gender == 2)?'checked':'' ?> value="2"> {{trans('labels.formblfemale')}} <?php echo '<i class="fa fa-check-circle" aria-hidden="true" style="position:absolute;font-size:28px;color:#000000;left:-5px;top:-10px;"></i>'; ?>
                              </label>
                        </div>
                    </div>


                    <?php
                        if (old('u_phone'))
                            $u_phone = old('u_phone');
                        elseif ($userDetail)
                            $u_phone = $userDetail->u_phone;
                        else
                            $u_phone = '';
                    ?>
                    <div class="form-group">
                        <label for="u_phone" class="col-sm-2 control-label">{{trans('labels.formlblphone')}}</label>
                            <div class="col-sm-6">
                                 <input type="name" class="form-control" id="u_phone" name="u_phone" placeholder="{{trans('labels.formlblphone')}}" value="{{ $u_phone }}" maxlength="15"/>
                            </div>
                    </div>


                   <!-- <?php
                        if (old('u_birthdate'))
                            $u_birthdate = old('u_birthdate');
                        elseif ($userDetail)
                            $u_birthdate = date('m/d/Y', strtotime($userDetail->u_birthdate));
                        else
                            $u_birthdate = '';
                        //yyyy-mm-dd
                        ?>
                        <div class="form-group">
                            <label for="u_birthdate" class="col-sm-2 control-label">{{trans('labels.formlblbirthday')}}
                            <span class="required-field">(*)</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" id="u_birthdate" name="u_birthdate" value="{{$u_birthdate}}" />
                            </div>
                        </div>-->

                    <?php
                        if (old('u_age'))
                            $u_age = old('u_age');
                        elseif ($userDetail)
                            $u_age = $userDetail->u_age;
                        else
                            $u_age = '';
                    ?>
                    <div class="form-group">
                        <label for="u_age" class="col-sm-2 control-label">{{trans('labels.formlblage')}}
                            <span class="required-field">(*)</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" id="u_age" name="u_age" placeholder="{{trans('labels.formlblage')}}" value="{{ $u_age }}" />
                            </div>
                    </div>

                    <?php
                        if (old('u_description'))
                            $u_description = old('u_description');
                        elseif ($userDetail)
                            $u_description = $userDetail->u_description;
                        else
                            $u_description = '';
                    ?>
                    <div class="form-group">
                        <label for="u_description" class="col-sm-2 control-label">{{trans('labels.formlbldescription')}}
                            <span class="required-field">(*)</span></label>
                            <div class="col-sm-6">
                                <textarea name='u_description' id='u_description' rows="3"cols="50" placeholder="{{trans('labels.formlbldescription')}}">{{ $u_description }}</textarea>
                            </div>
                    </div>

                    <?php
                        if (old('u_school'))
                            $u_school = old('u_school');
                        elseif ($userDetail)
                            $u_school = $userDetail->u_school;
                        else
                            $u_school = '';
                    ?>
                    <div class="form-group">
                        <label for="u_school" class="col-sm-2 control-label">{{trans('labels.formlblschool')}}
                            <span class="required-field">(*)</span></label>
                            <div class="col-sm-6">
                                <textarea name='u_school' id='u_school' rows="3"cols="50" placeholder="{{trans('labels.formlblschool')}}">{{ $u_school }}</textarea>
                            </div>
                    </div>

                    <?php
                        if (old('u_current_work'))
                            $u_current_work = old('u_current_work');
                        elseif ($userDetail)
                            $u_current_work = $userDetail->u_current_work;
                        else
                            $u_current_work = '';
                    ?>
                    <div class="form-group">
                        <label for="u_current_work" class="col-sm-2 control-label">{{trans('labels.formlblcurrentwork')}}
                            <span class="required-field">(*)</span></label>
                            <div class="col-sm-6">
                                <textarea name='u_current_work' id='u_current_work' rows="3"cols="50" placeholder="{{trans('labels.formlblcurrentwork')}}">{{ $u_current_work }}</textarea>
                            </div>
                    </div>

                    <!--<?php
                        if (old('u_looking_for'))
                            $u_looking_for = old('u_looking_for');
                        elseif ($userDetail)
                            $u_looking_for = $userDetail->u_looking_for;
                        else
                            $u_looking_for = '';
                    ?>
                    <div class="form-group">
                        <label for="u_looking_for" class="col-sm-2 control-label">{{trans('labels.formlbllooking')}}
                        <span class="required-field">(*)</span></label>
                        <div class=" col-sm-6" data-toggle="buttons">
                              <label class="btn btn-primary btn-lg buttoneasy" style="position:relative;" >
                                  <input type="radio" name="u_looking_for" id="u_looking_for1" <?php echo ($u_looking_for == 1 || $u_looking_for == '')?'checked':'' ?> value="1" > {{trans('labels.formblmale')}} <?php if ($u_looking_for == 1) { echo '<i class="fa fa-check-circle" aria-hidden="true" style="position:absolute;font-size:28px;color:#000000;left:-5px;top:-10px;"></i>';} ?>
                              </label>
                              <label>&nbsp;&nbsp;</label>
                              <label class="btn btn-primary btn-lg buttondifficult " style="position:relative;">
                                  <input type="radio" name="u_looking_for" id="u_looking_for2" <?php echo ($u_looking_for == 2)?'checked':'' ?> value="2"> {{trans('labels.formblfemale')}} <?php if ($u_looking_for == 2) { echo '<i class="fa fa-check-circle" aria-hidden="true" style="position:absolute;font-size:28px;color:#000000;left:-5px;top:-10px;"></i>';} ?>
                              </label>
                        </div>
                    </div>-->

                    <div class="form-group">
                        <label for="u_country" class="col-sm-2 control-label">{{trans('labels.formlbllookingdistance')}}</label>
                            <div class="col-sm-6">
                                 <label for="u_country" class="control-label">200</label>
                            </div>
                    </div>

                    <div class="form-group">
                        <label for="u_country" class="col-sm-2 control-label">{{trans('labels.formlbllookingminage')}}</label>
                            <div class="col-sm-6">
                                 <label for="u_country" class="control-label">18</label>
                            </div>
                    </div>

                    <div class="form-group">
                        <label for="u_country" class="col-sm-2 control-label">{{trans('labels.formlbllookingmaxage')}}</label>
                            <div class="col-sm-6">
                                 <label for="u_country" class=" control-label">50</label>
                            </div>
                    </div>
                    <!--<?php
                        if (old('u_country'))
                            $u_country = old('u_country');
                        elseif ($userDetail)
                            $u_country = $userDetail->u_country;
                        else
                            $u_country = '';
                    ?>
                    <div class="form-group">
                        <label for="u_country" class="col-sm-2 control-label">{{trans('labels.formlblcountry')}}</label>
                            <div class="col-sm-6">
                                 <input type="name" class="form-control" id="u_country" name="u_country" placeholder="{{trans('labels.formlblcountry')}}" value="{{ $u_country }}" maxlength="15"/>
                            </div>
                    </div>

                    <?php
                        if (old('u_pincode'))
                            $u_pincode = old('u_pincode');
                        elseif ($userDetail)
                            $u_pincode = $userDetail->u_pincode;
                        else
                            $u_pincode = '';
                        ?>
                    <div class="form-group">
                        <label for="u_pincode" class="col-sm-2 control-label">{{trans('labels.formlblpincode')}}</label>
                        <div class="col-sm-6">
                            <input type="name" class="form-control" id="u_pincode" name="u_pincode" placeholder="{{trans('labels.formlblpincode')}}" value="{{$u_pincode}}" minlength="6" maxlength="6"/>
                        </div>
                    </div>

                    <?php
                        if (old('u_location'))
                            $u_location = old('u_location');
                        elseif ($userDetail)
                            $u_location = $userDetail->u_location;
                        else
                            $u_location = '';
                        ?>
                    <div class="form-group">
                        <label for="u_location" class="col-sm-2 control-label">{{trans('labels.formlbllocation')}}
                        <span class="required-field">(*)</span></label>
                        <div class="col-sm-6">
                            <input type="name" class="form-control" id="u_location" name="u_location" placeholder="{{trans('labels.formlbllocation')}}" value="{{$u_location}}"  onkeyup="codeAddress()" onblur="codeAddress()" onfocus="codeAddress()" onchange="codeAddress()" tab-index="9"/>
                        </div>
                    </div>

                    <?php
                        if (old('u_latitude'))
                            $u_latitude = old('u_latitude');
                        elseif ($userDetail)
                            $u_latitude = $userDetail->u_latitude;
                        else
                            $u_latitude = '';
                        ?>
                    <div class="form-group">
                        <label for="u_latitude" class="col-sm-2 control-label">{{trans('labels.formlbllatitude')}}
                        <span class="required-field">(*)</span></label>
                        <div class="col-sm-6">
                            <input type="name" class="form-control" id="u_latitude" name="u_latitude" placeholder="{{trans('labels.formlbllatitude')}}" value="{{$u_latitude}}"/>
                        </div>
                    </div>

                    <?php
                        if (old('u_longitude'))
                            $u_longitude = old('u_longitude');
                        elseif ($userDetail)
                            $u_longitude = $userDetail->u_longitude;
                        else
                            $u_longitude = '';
                    ?>
                    <div class="form-group">
                        <label for="u_longitude" class="col-sm-2 control-label">{{trans('labels.formlbllongitude')}}
                        <span class="required-field">(*)</span></label>
                        <div class="col-sm-6">
                            <input type="name" class="form-control" id="u_longitude" name="u_longitude" placeholder="{{trans('labels.formlbllongitude')}}" value="{{$u_longitude}}" />
                        </div>
                        <div class="col-sm-4">
                            <div id="map_canvas" style="height:200px;width:320px;" class="allpro_show"></div>
                            <input type="hidden" class="form-control allpro_show" name="latlan" id="latlan" value=""/>
                        </div>
                    </div>-->

                    <?php
                        if (old('id'))
                            $id = old('id');
                        elseif ($userDetail)
                            $id = $userDetail->id;
                        else
                            $id = '';
                    ?>
                    @if($id != '')
                      <div class="form-group">
                          <label for="l2ac_text" class="col-sm-2 control-label"></label>
                          <div class="col-sm-6">
                              <a href="{{ url('/admin/manageMedia') }}/{{$id}}" title="Manage Profile Pic" style="color: #E66A45;">Manage Profie Pic &nbsp;&nbsp;&nbsp;&nbsp;</a>
                          </div>
                      </div>
                    @endif
                    @if($id == '')
                        <div class="form-group">
                            <label for="up_photo_name" class="col-sm-2 control-label">{{trans('labels.formlblphoto')}}</label>
                            <div class="col-sm-2">
                                <input type="file" id="up_photo_name" name="up_photo_name"/>
                            </div>
                        </div>
                    @endif

                    <?php
                        if (old('u_profile_active'))
                            $u_profile_active = old('u_profile_active');
                        elseif ($userDetail)
                            $u_profile_active = $userDetail->u_profile_active;
                        else
                            $u_profile_active = '';
                        ?>
                        <div class="form-group">
                            <label for="u_profile_active" class="col-sm-2 control-label">{{trans('labels.formlblprofileactive')}}</label>
                            <div class="col-sm-6">
                                <input type="checkbox" value="1" name="u_profile_active" id="u_profile_active" <?php
                                if ($u_profile_active) {
                                    echo 'checked="cehcked"';
                                }
                                ?>/>
                            </div>
                        </div>

                    <?php
                        if (old('deleted'))
                            $deleted = old('deleted');
                        elseif ($userDetail)
                            $deleted = $userDetail->deleted;
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
                      <button type="submit" class="btn btn-primary btn-flat" id="submitUserDetails" name="submitUserDetails">{{trans('labels.savebtn')}}</button>
                        <a class="btn btn-danger btn-flat pull-right" href="{{ url('admin/usersManagement') }}">{{trans('labels.cancelbtn')}}</a>

                  </div><!-- /.box-footer -->
                </form>

            </div>


        </div>
    </div>
</section>
@stop

@section('script')

<!-- Google Map Start -->
<script src="http://maps.googleapis.com/maps/api/js?sensor=false&amp;libraries=places"></script>
<script>

      $(window).load(function() {
          initialize();
      })
      var geocoder;
      var map;
      var marker;
      var infowindow = new google.maps.InfoWindow({size: new google.maps.Size(150, 50)});
      var input = document.getElementById('u_location');
      var autocomplete = new google.maps.places.Autocomplete(input);
      google.maps.event.addListener(autocomplete, 'place_changed', function() {
          $('#u_location').focus();
      });
      function initialize() {
          geocoder = new google.maps.Geocoder();
          var latlng = new google.maps.LatLng(23.022505, 72.571362);
          var mapOptions = {
              zoom: 8,
              center: latlng,
              mapTypeId: google.maps.MapTypeId.ROADMAP
          }
          map = new google.maps.Map(document.getElementById('map_canvas'), mapOptions);
          google.maps.event.addListener(map, 'click', function() {
              infowindow.close();
          });
      }
      function clone(obj) {
          if (obj == null || typeof (obj) != 'object')
              return obj;
          var temp = new obj.constructor();
          for (var key in obj)
              temp[key] = clone(obj[key]);
          return temp;
      }
      function geocodePosition(pos) {
          geocoder.geocode({
              latLng: pos
          }, function(responses) {
              if (responses && responses.length > 0) {
                  marker.formatted_address = responses[0].formatted_address;
                  document.getElementById('u_location').value = marker.formatted_address;
                  var latlan = marker.getPosition().toUrlValue(6);
                  var res = latlan.split(",");
                  $("#u_latitude").val(res[0]);
                  $("#u_longitude").val(res[1]);
              } else {
                  marker.formatted_address = 'Cannot determine address at this location.';
              }
              infowindow.setContent(marker.formatted_address + "<br>coordinates: " + marker.getPosition().toUrlValue(6));
              infowindow.open(map, marker);
          });
      }
      function codeAddress() {
          var address = $("#u_location").val();
          //var address = document.getElementById('address').value;
          geocoder.geocode({'address': address}, function(results, status) {
              if (status == google.maps.GeocoderStatus.OK) {
                  map.setCenter(results[0].geometry.location);
                  if (marker) {
                      marker.setMap(null);
                      if (infowindow)
                          infowindow.close();
                  }
                  marker = new google.maps.Marker({
                      map: map,
                      draggable: true,
                      position: results[0].geometry.location
                  });
                  google.maps.event.addListener(marker, 'dragend', function() {
                      // updateMarkerStatus('Drag ended');
                      geocodePosition(marker.getPosition());
                  });
                  google.maps.event.addListener(marker, 'click', function() {
                      if (marker.formatted_address) {
                          infowindow.setContent(marker.formatted_address + "<br>coordinates: " + marker.getPosition().toUrlValue(6));
                          var latlan = marker.getPosition().toUrlValue(6);
                          var res = latlan.split(",");
                          $("#u_latitude").val(res[0]);
                          $("#u_longitude").val(res[1]);
                      } else {
                          infowindow.setContent(address + "<br>coordinates: " + marker.getPosition().toUrlValue(6));
                          var latlan = marker.getPosition().toUrlValue(6);
                          var res = latlan.split(",");
                          $("#u_latitude").val(res[0]);
                          $("#u_longitude").val(res[1]);
                      }
                      infowindow.open(map, marker);
                  });
                  google.maps.event.trigger(marker, 'click');
              } else {
              }
          });
      }

</script>
<!-- Google Map End -->

<script type="text/javascript">

    jQuery(document).ready(function() {
        // $ = jQuery.noConflict();
        jQuery("#u_birthdate").datepicker({
            yearRange: "-130:-13",
            maxDate: -4749,
            changeMonth: true,
            changeYear: true,
            dateFormat: 'mm/dd/yy',
            defaultDate: null
        }).on('change', function () {
            $(this).valid();
        });

        <?php if(isset($userDetail->id) && $userDetail->id != '0') { ?>
        var validationRules = {

            u_firstname : {
                required: true,
                minlength: 3
            },
            u_lastname : {
                required: true,
                minlength: 3
            },
            u_gender : {
                required : true
            },
            u_phone : {
                minlength: 10,
                maxlength: 15
            },
            u_age : {
                required : true
            },
            u_description : {
                required : true
            },
            u_school : {
                required : true
            },
            u_current_work : {
                required : true
            },
            up_photo_name : {
                required : true
            },
            deleted : {
                required : true
            }
        }
        <?php } else { ?>
        var validationRules = {

            u_firstname : {
                required: true,
                minlength: 3
            },
            u_lastname : {
                required: true,
                minlength: 3
            },
            u_gender : {
                required : true
            },
            u_phone : {
                minlength: 10,
                maxlength: 15
            },
            u_age : {
                required : true
            },
            u_description : {
                required : true
            },
            u_school : {
                required : true
            },
            u_current_work : {
                required : true
            },
            up_photo_name : {
                required : true
            },
            deleted : {
                required : true
            }
        }
        <?php } ?>

        $("#addUserDetail").validate({
            rules : validationRules,
            messages : {
                u_firstname : {
                    required : "<?php echo trans('validation.requiredfield'); ?>"
                },
                u_lastname : {
                    required : "<?php echo trans('validation.requiredfield'); ?>"
                },
                u_gender : {
                    required : "<?php echo trans('validation.requiredfield'); ?>"
                },
                u_phone : {
                    required : "<?php echo trans('validation.requiredfield'); ?>"
                },
                u_age : {
                    required : "<?php echo trans('validation.requiredfield'); ?>"
                },
                u_description : {
                    required : "<?php echo trans('validation.requiredfield'); ?>"
                },
                u_school : {
                    required : "<?php echo trans('validation.requiredfield'); ?>"
                },
                u_current_work : {
                    required : "<?php echo trans('validation.requiredfield'); ?>"
                },
                up_photo_name : {
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