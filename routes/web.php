<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return "Api is live";
});

$router->group(['prefix' => 'api', 'middleware' => 'apikey'], function () use ($router) {
    $router->post('/user/register', 'User\UserController@register');
    $router->post('/staff/register', 'User\UserController@staffRegister');
    $router->post('/auth/login', 'AuthController@login');
    $router->post('/auth/forgot_password', 'AuthController@getForgotOtp');
    $router->post('/auth/verify_otp', 'AuthController@verifyOtp');
    $router->post('/auth/change_password', 'AuthController@chnagePassword');
    $router->post('/auth/admin_login', 'AuthController@adminLogin');
    $router->post('/auth/logout', ['middleware' => 'token', 'uses' => 'AuthController@logout']);
    $router->get('/user/profile', ['middleware' => 'token', 'uses' => 'User\UserController@userProfile']);
    $router->post('/auth/refresh', 'AuthController@refresh');
    $router->get('/initial_data', 'Admin\HomePageController@initialData');
    $router->get('/weekly_meeting', 'Admin\HomePageController@getWeeklyMeeting');
    $router->get('/common/wasabi_file', 'Admin\CommonController@getWasabiFile');
    $router->get('/common/wasabi_video', 'Admin\CommonController@getWasabiVideo');
    $router->get('/get_video_url', ['middleware' => 'token', 'uses' => 'Admin\CommonController@getVideoUrl']);
    $router->get('/subscription_list', ['middleware' => 'token', 'uses' => 'Admin\SubscriptionController@subScriptionList']);
    $router->post('/common/image_upload', ['middleware' => 'token', 'uses' => 'Admin\CommonController@imageUpload']);
    $router->post('/user_subscription', ['middleware' => 'token', 'uses' => 'Admin\SubscriptionController@userSubscription']);
    $router->get('/course_details', ['middleware' => 'token', 'uses' => 'Admin\CourseController@getCourseWithLesson']);
    $router->post('/unlock_video', ['middleware' => 'token', 'uses' => 'User\UserController@unlockVideo']);
    $router->post('/video_status', ['middleware' => 'token', 'uses' => 'User\UserController@videoUpdate']);
    $router->group(['prefix' => 'admin', 'middleware' => 'token'], function () use ($router) {
        $router->post('/add_banner', 'Admin\HomePageController@addBanner');
        $router->post('/add_demo', 'Admin\HomePageController@addDemoVideo');
        $router->post('add_weekly', 'Admin\HomePageController@addWeeklyMeeting');
        $router->post('/common/file_upload', 'Admin\CommonController@fileUpload');
        $router->get('/image_list', 'Admin\CommonController@getImageList');
        $router->get('/video_list', 'Admin\CommonController@getVideoList');
        $router->get('/user_list', 'User\UserController@userList');
        $router->post('/user_action', 'User\UserController@userStatusUpdate');
        $router->get('/home_builder', 'Admin\HomePageController@homebuilderData');
        $router->group(['prefix' => 'subscription'], function () use ($router) {
            $router->post('/create', 'Admin\SubscriptionController@subScriptionCreation');
            $router->post('/edit', 'Admin\SubscriptionController@subScriptionUpdate');
            $router->post('/update_status', 'Admin\SubscriptionController@subScriptionStatusUpdate');
            $router->get('/list', 'Admin\SubscriptionController@userSubscriptionList');
            $router->post('/action', 'Admin\SubscriptionController@subscriptionAction');
        });
        $router->group(['prefix' => 'course'], function () use ($router) {
            $router->get('/list', 'Admin\CourseController@courseList');
            $router->get('/lesson_list', 'Admin\CourseController@courseDetailsList');
            $router->get('/video_list', 'Admin\CourseController@lessonVideoList');
            $router->post('/create', 'Admin\CourseController@courseCreation');
            $router->post('/lesson_create', 'Admin\CourseController@courseDetailCreation');
            $router->post('/video_map', 'Admin\CourseController@lessonVideoMap');
            $router->post('/edit', 'Admin\CourseController@courseEdit');
            $router->post('/lesson_edit', 'Admin\CourseController@courseDetailEdit');
            $router->post('/action', 'Admin\CourseController@courseActions');
        });
    });
});
