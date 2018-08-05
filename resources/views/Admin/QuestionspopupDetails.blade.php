<?php

        $html = '';

        // Question  Data Start

        $html .= '<div class="modal-dialog userAllDetailBox">';
        $html .= '<div class="modal-content userAllDetailInbox">';
        $html .= '<div class="modal-header">';
        $html .= '<button type="button" class="close" data-dismiss="modal">&times;</button>';
        $html .= '<h4 class="modal-title">' . trans('labels.questionalldetails') . '&nbsp;:</h4>';
        $html .= '</div>'; // modal-header end
        $html .= '<div class="modal-body">';

        $questionNo = 1;
        if (isset($data) && !empty($data)) {
            foreach ($data as $key => $question) {
                $html .= '<div class="form-group clearfix">';
                $html .= '<div class="col-sm-12"><span>'. $questionNo.'.</span>&nbsp;&nbsp;&nbsp;' . $question->q_question_text . '</div>';
                $html .= '<div class="col-sm-12"><span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>' . $question->qo_option . '</div>';
                $html .= '</div><hr>'; // form-group q_question_text end
                $questionNo++;
            }
        } else {
            $html .= '<div class="form-group clearfix">';
            $html .= '<div class="col-sm-12">' . trans('labels.noquestionsdata') . '</div>';
            $html .= '</div><hr>';
        }

        if (isset($userData) && !empty($userData)) {
            $html .= '<div class="modal-header">';
            $html .= '<h4 class="modal-title">' . trans('labels.otheruseralldetails') . '&nbsp;:</h4>';
            $html .= '</div>';

            $html .= '<label class="col-sm-3 control-label">' . trans('labels.formlbllname') . '</label>';
            $html .= '<label class="col-sm-3 control-label">' . trans('labels.formlbllgender') . '</label>';
            $html .= '<label class="col-sm-3 control-label">' . trans('labels.formlbllprofile') . '</label>';
            $html .= '<label class="col-sm-3 control-label">' . trans('labels.formlbllstatus') . '</label>';
            foreach ($userData as $key => $value) {
                $html .= '<div class="form-group clearfix">';
                $html .= '<div class="col-sm-3 u_latitude_text">' . $value['first_name'] ." ". $value['last_name']. '</div>';
                if ($value['gender'] == 1) {
                    $html .= '<div class="col-sm-3 u_latitude_text">' . trans('labels.formblmale') . '</div>';
                } else if($value['gender'] == 2) {
                    $html .= '<div class="col-sm-3 u_latitude_text">' . trans('labels.formblfemale') . '</div>';
                } else {
                    $html .= '<div class="col-sm-3 u_latitude_text"> - </div>';
                }

                $photo = $value['profile_pic_url'];
//              if ($photo != '' && file_exists($uploadProfilePath . $photo)) {
                if ($photo != '' && Storage::disk('s3')->has(Config::get('constant.USER_PROFILE_ORIGINAL_IMAGE_UPLOAD_PATH').$photo) == 1) {
//                  $image_url = asset($uploadProfilePath. $photo);
                    $image_url = Config::get('constant.AWS_FILE_UPLOAD_URL').Config::get('constant.USER_PROFILE_ORIGINAL_IMAGE_UPLOAD_PATH').$photo;
                    $html .= '<div class="col-sm-3 u_latitude_text"><img src='.$image_url.'></div>';
                } else {
                    $html .= '<div class="col-sm-3 u_latitude_text"><img src='.asset("/backend/images/logo.png") .' height='.Config::get("constant.USER_PROFILE_THUMB_IMAGE_HEIGHT") .' width='.Config::get("constant.USER_PROFILE_THUMB_IMAGE_WIDTH").'></div>';
                }
                if ($value['status'] == 1) {
                    $html .= '<div class="col-sm-3 u_latitude_text">' . trans('labels.formblmatch') . '</div>';
                } else {
                    $html .= '<div class="col-sm-3 u_latitude_text">' . trans('labels.formblnotmatch') . '</div>';
                }
                $html .= '</div><hr>';
            }
        }

        $html .= '</div>'; // modal-body end

        $html .= '<div class="modal-footer">';
        $html .= '<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>';
        $html .= '</div>'; // modal-footer end

        $html .= '</div>'; // modal-content end
        $html .= '</div>'; // modal-dialog end

        echo $html;

?>