<?php

        $html = '';

        // User Data Start

        $html .= '<div class="modal-dialog userAllDetailBox">';
        $html .= '<div class="modal-content userAllDetailInbox">';
        $html .= '<div class="modal-header">';
        $html .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
        $html .= '<h4 class="modal-title">' . trans('labels.useralldetails') . '&nbsp;:</h4>';
        $html .= '</div>'; // modal-header end
        $html .= '<div class="modal-body">';

        $html .= '<div class="form-group userDataFirstName clearfix">';
        $html .= '<label for="u_firstname" class="col-sm-5 control-label">' . trans('labels.formlblfirstname') . '&nbsp;:</label>';
        $html .= '<div class="col-sm-7 userDataFirstName_text">' . $data->u_firstname . '</div>';
        $html .= '</div><hr>'; // form-group u_firstname end

        $html .= '<div class="form-group userDataLastName clearfix">';
        $html .= '<label for="u_lastname" class="col-sm-5 control-label">' . trans('labels.formlbllastname') . '&nbsp;:</label>';
        $html .= '<div class="col-sm-7 userDataLastName_text">' . $data->u_lastname . '</div>';
        $html .= '</div><hr>'; // form-group u_lastname end


        $html .= '<div class="form-group userDataEmailblock clearfix">';
        $html .= '<label for="u_email" class="col-sm-5 control-label">' . trans('labels.formlblemail') . '&nbsp;:</label>';
        $html .= '<div class="col-sm-7 userDataEmail_text">' . $data->u_email . '</div>';
        $html .= '</div><hr>'; // form-group u_email end


        $html .= '<div class="form-group userDataGenderBlock clearfix">';
        $html .= '<label for="position" class="col-sm-5 control-label">' . trans('labels.formlblgender') . '&nbsp;:</label>';
        $html .= '<div class="col-sm-7 userDataGender_text">';
        if($data->u_gender == 1){ $gender = "Male";}
        elseif($data->u_gender == 2){ $gender = "Female"; }
        else{ $gender = ""; }
        $html .= $gender .'</div>';
        $html .= '</div><hr>'; // form-group u_gender end


        $html .= '<div class="form-group usersocialProviderBlock clearfix">';
        $html .= '<label for="u_social_provider" class="col-sm-5 control-label">' . trans('labels.formlblsocialprovider') . '&nbsp;:</label>';
        $html .= '<div class="col-sm-7 usersocialProvider_text">' . $data->u_social_provider . '</div>';
        $html .= '</div><hr>'; // form-group u_social_provider end


        $html .= '<div class="form-group userPhoneBlock clearfix">';
        $html .= '<label for="u_phone" class="col-sm-5 control-label">' . trans('labels.formlblphone') . '&nbsp;:</label>';
        $html .= '<div class="col-sm-7 userPhone_text">' . $data->u_phone . '</div>';
        $html .= '</div><hr>'; // form-group u_phone end

        $html .= '<div class="form-group userBirthdayBlock clearfix">';
        $html .= '<label for="u_birthdate" class="col-sm-5 control-label">' . trans('labels.formlblbirthday') . '&nbsp;:</label>';
        $html .= '<div class="col-sm-7 userBirthday_text">' . $data->u_birthdate . '</div>';
        $html .= '</div><hr>'; // form-group u_birthdate end

        $html .= '<div class="form-group userBirthdayBlock clearfix">';
        $html .= '<label for="u_birthdate" class="col-sm-12 control-label">' . trans('labels.formlblprofilephotos') . '&nbsp;:</label>';
        $html .= '<div class="col-sm-12">';

        $profile = explode(",", $data->up_photo_name);
        foreach($profile AS $key => $value)
        {
            $photo = $value;
//          if ($photo != '' && file_exists($uploadProfilePath . $photo)) {
            if ($photo != '' && Storage::disk('s3')->has(Config::get('constant.USER_PROFILE_ORIGINAL_IMAGE_UPLOAD_PATH').$photo) == 1) {
//                $image_url = asset($uploadProfilePath. $photo);
                $image_url = Config::get('constant.AWS_FILE_UPLOAD_URL').Config::get('constant.USER_PROFILE_THUMB_IMAGE_UPLOAD_PATH').$photo;
                $image = asset($uploadProfileOriginalPath. $photo);
                $html .= '<span><a href="" onClick=show_profile("'. $image .'"); data-toggle="modal" id="#showUserProfile" data-target="#showUserProfile"><img src='.$image_url.'></a></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            }
            else
            {
                $image = asset("/backend/images/logo.png");

                $html .= '<span><a href="" onClick=show_profile("'. $image .'"); data-toggle="modal" id="#showUserProfile" data-target="#showUserProfile"><img src='.asset("/backend/images/logo.png") .' height='.Config::get("constant.USER_PROFILE_THUMB_IMAGE_HEIGHT") .' width='.Config::get("constant.USER_PROFILE_THUMB_IMAGE_WIDTH").'></a></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            }

        }
        $html .= '</div>';
        $html .= '</div><hr>'; // form-group u_photo_name end


        $html .= '<div class="form-group userDescriptionBlock clearfix">';
        $html .= '<label for="u_description" class="col-sm-5 control-label">' . trans('labels.formlbldescription') . '&nbsp;:</label>';
        $html .= '<div class="col-sm-7 userDescription_text">' . $data->u_description . '</div>';
        $html .= '</div><hr>'; // form-group u_description end


        $html .= '<div class="form-group userSchoolBlock clearfix">';
        $html .= '<label for="u_school" class="col-sm-5 control-label">' . trans('labels.formlblschool') . '&nbsp;:</label>';
        $html .= '<div class="col-sm-7 userSchool_text">' . $data->u_school . '</div>';
        $html .= '</div><hr>'; // form-group u_school end


        $html .= '<div class="form-group userCurrentWorkBlock clearfix">';
        $html .= '<label for="u_current_work" class="col-sm-5 control-label">' . trans('labels.formlblcurrentwork') . '&nbsp;:</label>';
        $html .= '<div class="col-sm-7 userCurrentWork_text">' . $data->u_current_work . '</div>';
        $html .= '</div><hr>'; // form-group u_current_work end


        $html .= '<div class="form-group userLookingForBlock clearfix">';
        $html .= '<label for="u_looking_for" class="col-sm-5 control-label">' . trans('labels.formlbllookingfor') . '&nbsp;:</label>';
        $html .= '<div class="col-sm-7 userLookingFor_text">';
        if($data->u_looking_for == 1){ $looking_for = "Male";}
        elseif($data->u_looking_for == 2){ $looking_for = "Female"; }
        elseif($data->u_looking_for == 3){ $looking_for = "Both"; }
        else{ $looking_for = ""; }
        $html .= $looking_for .'</div>';
        $html .= '</div><hr>'; // form-group u_looking_for end


        $html .= '<div class="form-group userLookingDistanceBlock clearfix">';
        $html .= '<label for="u_looking_distance" class="col-sm-5 control-label">' . trans('labels.formlbllookingdistance') . '&nbsp;:</label>';
        $html .= '<div class="col-sm-7 userLookingDistance_text">' . $data->u_looking_distance . '</div>';
        $html .= '</div><hr>'; // form-group u_looking_distance end

        $html .= '<div class="form-group userLookingAgeBlock clearfix">';
        $html .= '<label for="u_looking_age_min" class="col-sm-5 control-label">' . trans('labels.formlbllookingagemin') . '&nbsp;:</label>';
        $html .= '<div class="col-sm-7 userLookingAge_text">' . $data->u_looking_age_min . '</div>';
        $html .= '</div><hr>'; // form-group u_looking_age end

        $html .= '<div class="form-group userLookingAgeBlock clearfix">';
        $html .= '<label for="u_looking_age_max" class="col-sm-5 control-label">' . trans('labels.formlbllookingagemax') . '&nbsp;:</label>';
        $html .= '<div class="col-sm-7 userLookingAge_text">' . $data->u_looking_age_max . '</div>';
        $html .= '</div><hr>'; // form-group u_looking_age end

        $html .= '<div class="form-group u_compatibility_notificationBlock clearfix">';
        $html .= '<label for="u_compatibility_notification" class="col-sm-5 control-label">' . trans('labels.formlblucnotification') . '&nbsp;:</label>';
        $html .= '<div class="col-sm-7 u_compatibility_notification_text">';
        if($data->u_compatibility_notification == 1){ $ucNotification = "Receive";}
        elseif($data->u_looking_for == 0){ $ucNotification = "Not receive"; }
        else{ $ucNotification = ""; }
        $html .= $ucNotification .'</div>';
        $html .= '</div><hr>'; // form-group u_compatibility_notification end


        $html .= '<div class="form-group u_newchat_notificationBlock clearfix">';
        $html .= '<label for="u_newchat_notification" class="col-sm-5 control-label">' . trans('labels.formlblunnotification') . '&nbsp;:</label>';
        $html .= '<div class="col-sm-7 u_compatibility_notification_text">';
        if($data->u_newchat_notification == 1){ $unNotification = "Receive";}
        elseif($data->u_newchat_notification == 0){ $unNotification = "Not receive"; }
        else{ $unNotification = ""; }
        $html .= $unNotification .'</div>';
        $html .= '</div><hr>'; // form-group u_newchat_notification end


        $html .= '<div class="form-group u_newchat_notificationBlock clearfix">';
        $html .= '<label for="u_acceptance_notification" class="col-sm-5 control-label">' . trans('labels.formlbluanotification') . '&nbsp;:</label>';
        $html .= '<div class="col-sm-7 u_acceptance_notification_text">';
        if($data->u_acceptance_notification == 1){ $uaNotification = "Receive";}
        elseif($data->u_acceptance_notification == 0){ $uaNotification = "Not receive"; }
        else{ $uaNotification = ""; }
        $html .= $uaNotification .'</div>';
        $html .= '</div><hr>'; // form-group u_acceptance_notification end


        $html .= '<div class="form-group u_countryBlock clearfix">';
        $html .= '<label for="u_country" class="col-sm-5 control-label">' . trans('labels.formlblcountry') . '&nbsp;:</label>';
        $html .= '<div class="col-sm-7 u_country_text">' . $data->u_country . '</div>';
        $html .= '</div><hr>'; // form-group u_country end


        $html .= '<div class="form-group u_pincodeBlock clearfix">';
        $html .= '<label for="u_pincode" class="col-sm-5 control-label">' . trans('labels.formlblpincode') . '&nbsp;:</label>';
        $html .= '<div class="col-sm-7 u_pincode_text">' . $data->u_pincode . '</div>';
        $html .= '</div><hr>'; // form-group u_pincode end


        $html .= '<div class="form-group u_locationBlock clearfix">';
        $html .= '<label for="u_location" class="col-sm-5 control-label">' . trans('labels.formlbllocation') . '&nbsp;:</label>';
        $html .= '<div class="col-sm-7 u_location_text">' . $data->u_location . '</div>';
        $html .= '</div><hr>'; // form-group u_location end


        $html .= '<div class="form-group u_latitudeBlock clearfix">';
        $html .= '<label for="u_latitude" class="col-sm-5 control-label">' . trans('labels.formlbllatitude') . '&nbsp;:</label>';
        $html .= '<div class="col-sm-7 u_latitude_text">' . $data->u_latitude . '</div>';
        $html .= '</div><hr>'; // form-group u_latitude end


        $html .= '<div class="form-group u_longitudeBlock clearfix">';
        $html .= '<label for="u_longitude" class="col-sm-5 control-label">' . trans('labels.formlbllongitude') . '&nbsp;:</label>';
        $html .= '<div class="col-sm-7 u_latitude_text">' . $data->u_longitude . '</div>';
        $html .= '</div><hr>'; // form-group u_longitude end


        $html .= '<div class="form-group u_profile_activeBlock clearfix">';
        $html .= '<label for="u_profile_active" class="col-sm-5 control-label">' . trans('labels.formlblprofileactive') . '&nbsp;:</label>';
        $html .= '<div class="col-sm-7 u_acceptance_notification_text">';
        if($data->u_profile_active == 1){ $profile_active = "Yes";}
        elseif($data->u_acceptance_notification == 0){ $profile_active = "No"; }
        else{ $profile_active = ""; }
        $html .= $profile_active .'</div>';
        $html .= '</div><hr>'; // form-group u_profile_active end


        $html .= '<div class="form-group publish_status_block clearfix">';
        $html .= '<label for="deleted" class="col-sm-5 control-label">' . trans('labels.formlbldeleted') . '&nbsp;:</label>';
        $html .= '<div class="col-sm-7 publish_status_text">';
        if($data->deleted == 1){ $deleted = " Active";}
        elseif($data->deleted == 1){ $deleted = " Inactive"; }
        else{ $deleted = "Deleted"; }
        $html .= $deleted .'</div>';
        $html .= '</div>'; // form-group deleted end

        $html .= '</div>'; // modal-body end

        $html .= '<div class="modal-footer">';
        $html .= '<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>';
        $html .= '</div>'; // modal-footer end

        $html .= '</div>'; // modal-content end
        $html .= '</div>'; // modal-dialog end

        echo $html;

?>