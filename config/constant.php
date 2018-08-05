<?php

return [

//  OCRScreen Shot Management
    'ACTIVE_FLAG' => '1',
    'INACTIVE_FLAG' => '2',
    'DELETED_FLAG' => 3,
    'DATE_FORMAT' => 'Y-m-d / g:i A',

    'ADMIN_RECORD_PER_PAGE' => '15',
    'QUESTION_ATTEMPTED_FLAG' => '1',
    'PAGINATION_LIMIT' => '20',
    'PAGE_LIMIT' => '10',

    'USER_PROFILE_ORIGINAL_IMAGE_UPLOAD_PATH' => 'uploads/profiles/original/',
    'USER_PROFILE_THUMB_IMAGE_UPLOAD_PATH' => 'uploads/profiles/thumb/',
    'USER_PROFILE_THUMB_IMAGE_HEIGHT' => '55',
    'USER_PROFILE_THUMB_IMAGE_WIDTH' => '55',

    'CERTIFICATE_PATH' => 'certificate/MeeTwo.pem',

    'LOOKIN_FOR_MIN' => '18',
    'LOOKIN_FOR_MAX' => '30',
    //'ADMIN_USER_ID' => '10154676625142701',
    'ADMIN_USER_ID' => '123456789101112',

    'FROM_MAIL_ID' => 'krutik.inexture@gmail.com',
    'ADMIN_NAME' => 'Ronak',
    'MAIL_SUBJECT' => 'User Profile Report',

    'QUESTION_LENGTH' => '10',

    'MIN_AGE' => '18',

    'MAX_AGE' => '60',

    'USER_REPORT_TEMPLATE' => 'user-report-template',

    'SERVER_IP_ADDRESS' => 'http://meetwochat-2084182916.us-west-2.elb.amazonaws.com:9090',
    'SERVER_NAME' => '@ip-172-31-36-193',
    'AUTHORIZATION_KEY' => 'u0M8Gl5M2815j6o7',
    
    'APPLOZIC_APPLICATION_ID' => 'd49c0963288af20aba61ec7399e01d55',
    'APPLOZIC_URL' => 'https://apps.applozic.com/rest/ws/',
    'APPLOZIC_DEVICE_KEY' => '197f4af2-3ddc-4147-acd0-bed4ae617',
    'APPLOZIC_USER_PASWORD' => '12345678',
    'APPLOZIC_MSG_CONTENT_TYPE' => 95,
    'APPLOZIC_COMMON_MSG' => 'You are now connected to',
    
    'AWS_FILE_UPLOAD_URL' => 'https://s3-us-west-2.amazonaws.com/meetwoliveimage/',
    
    
    'ANDROID_VERSION' => '27',
    'IOS_VERSION' => '4.0.1',

    //point for update user score
    'PHOTO_POINT' => '5',
    'DISCRIPTION_POINT' => '5',
    'JOB_POINT' => '5',
    'SCHOOL_POINT' => '5',

    //score id for point update
    'PHOTO_ID' => '1',
    'DISCRIPTION_ID' => '2',
    'JOB_ID' => '3',
    'SCHOOL_ID' => '4',

    'USER_PROFILE_COUNT' => '6',
    'USER_PROFILE_SCORE' => '45'
];
?>