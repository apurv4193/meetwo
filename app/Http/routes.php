<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/webservice/get_offline_msg','MessageController@get_offline_msg');

Route::controllers([
	'auth' => 'Auth\AuthController',
	'password' => 'Auth\PasswordController',
]);

// Admin Route
Route::get('admin', ['middleware' =>'auth.admin', 'uses' => 'Admin\LoginController@login' ]);
Route::post('admin/logincheck', 'Admin\LoginController@loginCheck');
Route::get('admin/dashboard', 'Admin\DashboardController@index');
Route::get('admin/logout','Admin\LoginController@getLogout');

//  Question Management
Route::any('admin/question','Admin\QuestionManagementController@index');
Route::get('admin/addQuestionData','Admin\QuestionManagementController@add');
Route::post('admin/saveQuestionData','Admin\QuestionManagementController@save');
Route::get('admin/deleteQuestionData/{id}','Admin\QuestionManagementController@delete');
Route::get('admin/editQuestionData/{id}','Admin\QuestionManagementController@edit');
Route::get('datatables/QuestionDataListing', array('as' => 'admin.datatables.questionListing', 'uses' => 'Admin\QuestionManagementController@QuestionsListingDataTable'));
Route::post('admin/deleteQuestionDataRow','Admin\QuestionManagementController@deleteQuestionDataRow');
Route::get('admin/importquestiondata','Admin\QuestionManagementController@importExcel');
Route::get('admin/exportquestiondata','Admin\QuestionManagementController@ExportQuestionsData');
Route::post('admin/addquestiondataimportexcel','Admin\QuestionManagementController@addimportExcel');

//  Users Management
Route::any('admin/usersManagement','Admin\UsersManagementController@index');
Route::get('admin/addUserData','Admin\UsersManagementController@add');
Route::get('admin/manageMedia/{id}','Admin\UsersManagementController@manageMedia');
Route::post('admin/deleteUserProfilePhotoById/','Admin\UsersManagementController@deleteUserProfilePhotoById');
Route::post('admin/saveUserPhotosDetail','Admin\UsersManagementController@saveUserPhotosDetail');
Route::post('admin/setProfilePic/','Admin\UsersManagementController@setProfilePic');
Route::get('admin/editUserDetail/{id}','Admin\UsersManagementController@edit');
Route::post('admin/saveUserDetail','Admin\UsersManagementController@save');
Route::get('admin/deleteUserDetail/{id}','Admin\UsersManagementController@delete');
Route::get('datatables/UsersListing', array('as' => 'admin.datatables.usersListing', 'uses' => 'Admin\UsersManagementController@UsersListingDataTable'));
Route::post('admin/deleteUsersRow','Admin\UsersManagementController@deleteUserRow');
Route::get('admin/getAllUserDetails','Admin\UsersManagementController@getAllUserDetails');
Route::post('admin/getAllUserDetails','Admin\UsersManagementController@getAllUserDetails');
Route::post('admin/getAllQuestionsDetails','Admin\UsersManagementController@getAllQuestionsDetails');

// Configuration
Route::any('admin/configuration', 'Admin\ConfigurationManagementController@index');
Route::get('admin/addConfigurationData','Admin\ConfigurationManagementController@add');
Route::post('admin/saveConfigurationData', 'Admin\ConfigurationManagementController@save');
Route::get('admin/editConfigurationData/{id}','Admin\ConfigurationManagementController@edit');
Route::get('datatables/ConfigurationListing', array('as' => 'admin.datatables.configurationListing', 'uses' => 'Admin\ConfigurationManagementController@ConfigurationListingDataTable'));

//CMS
Route::any('admin/cms', 'Admin\CMSManagementController@index');
Route::get('admin/addcms', 'Admin\CMSManagementController@add');
Route::post('admin/savecms', 'Admin\CMSManagementController@save');
Route::get('admin/editcms/{id}', 'Admin\CMSManagementController@edit');
Route::get('admin/deletecms/{id}', 'Admin\CMSManagementController@delete');
Route::post('admin/deleteCMSRow','Admin\CMSManagementController@deleteCMSRow');
Route::get('datatables/CMSListing', array('as' => 'admin.datatables.cmsListing', 'uses' => 'Admin\CMSManagementController@CMSListingDataTable'));

//Email Template
Route::any('admin/emailtemplates', 'Admin\EmailTemplateManagementController@index');
Route::get('admin/deleteemailtemplate/{id}', 'Admin\EmailTemplateManagementController@delete');
Route::get('admin/addemailtemplate', 'Admin\EmailTemplateManagementController@add');
Route::post('admin/saveemailtemplate', 'Admin\EmailTemplateManagementController@save');
Route::get('admin/editemailtemplate/{id}', 'Admin\EmailTemplateManagementController@edit');
Route::post('admin/deleteEmailTemplateRow','Admin\EmailTemplateManagementController@deleteEmailTemplateRow');
Route::get('/getTemplate', array('as' => '.gettemplate', 'uses' => 'Admin\EmailTemplateManagementController@getdata'));

//Webservice Routes
Route::post('webservice', 'Webservice\WebserviceController@index'); // For old app version

// For new app version
Route::post('api/webservice/auth/token', 'Webservice\WebserviceController@_login');
Route::post('api/webservice/logout', 'Webservice\WebserviceController@_logout');
Route::group(['middleware' => 'jwt.auth', 'prefix' => 'api'], function () {
    Route::post('webservice', 'Webservice\WebserviceController@index');
});

//Help page
Route::get('/help', 'cmsController@help');

//Reported User
Route::any('admin/reporteduser', 'Admin\ReportedUserManagementController@index');
Route::get('datatables/ReportedUserListing', array('as' => 'admin.datatables.ReportedUserListing', 'uses' => 'Admin\ReportedUserManagementController@ReportedUserListingDataTable'));
