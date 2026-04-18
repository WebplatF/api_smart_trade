<?php

namespace App\Http\Controllers\User;

use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helper\ResponseHelper;
use App\Http\Controllers\Controller;
use Throwable;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        try {
            $Validator = Validator::make($request->all(), [
                'name' => 'required|strict_string',
                'email' => 'required|strict_string',
                'mobile' => 'required|strict_string',
                'password' => 'required|strict_string'
            ]);
            if ($Validator->fails()) {
                return ResponseHelper::failureResponse(message: $Validator->errors()->first(), code: 400);
            }
            $response = $this->userService->userRegister(request: $request);
            return ResponseHelper::successResponse(data: [], message: "User Registered Successfully...!", code: 200);
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage(), code: 400);
        }
    }
    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function staffRegister(Request $request)
    {
        try {
            $role = $request->get('role');
            if ($role != "admin") {
                return ResponseHelper::failureResponse(message: "Forbidden", code: 403);
            }
            $Validator = Validator::make($request->all(), [
                'name' => 'required|strict_string',
                'password' => 'required|strict_string'
            ]);
            if ($Validator->fails()) {
                return ResponseHelper::failureResponse(message: $Validator->errors()->first(), code: 400);
            }
            $response = $this->userService->staffRegister(request: $request);
            return ResponseHelper::successResponse(data: [], message: "Staff Registered Successfully...!", code: 200);
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage(), code: 400);
        }
    }
    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userList(Request $request)
    {
        try {
            $role = $request->get('role');
            if ($role != "admin") {
                return ResponseHelper::failureResponse(message: "Forbidden", code: 403);
            }
            $userList = $this->userService->userList();
            return ResponseHelper::successResponse(data: $userList, message: "User List Data Arrived Successfully...!", code: 200);
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage(), code: 400);
        }
    }
    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userStatusUpdate(Request $request)
    {
        try {
            $role = $request->get('role');
            if ($role != "admin") {
                return ResponseHelper::failureResponse(message: "Forbidden", code: 403);
            }
            $Validator = Validator::make($request->all(), [
                'user_id' => 'required|strict_int',
                'status' => 'required|strict_bool',
            ]);
            if ($Validator->fails()) {
                return ResponseHelper::failureResponse(message: $Validator->errors()->first(), code: 400);
            }
            $retunResponse = $this->userService->statusUpdate(
                userId: $request->get('user_id'),
                status: $request->get('status')
            );
            return ResponseHelper::successResponse(
                data: $retunResponse,
                message: "User Status Chnaged Successfully..!"
            );
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage());
        }
    }
    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile(Request $request)
    {
        try {
            $userId = $request->get('user_id');
            $profileDetails = $this->userService->userProfile(userId: $userId);
            return ResponseHelper::successResponse(
                data: $profileDetails,
                message: "User Profile Arrived Successfully..!"
            );
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage());
        }
    }
    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unlockVideo(Request $request)
    {
        try {
            $role = $request->get('role');
            if ($role == "admin") {
                return ResponseHelper::failureResponse(message: "Forbidden", code: 403);
            }
            $Validator = Validator::make($request->all(), [
                'video_id' => 'required|strict_int',
                'subscription_id' => 'required|strict_int',
            ]);
            if ($Validator->fails()) {
                return ResponseHelper::failureResponse(message: $Validator->errors()->first(), code: 400);
            }
            $userId = $request->get('user_id');
            $videoId = $request->get('video_id');
            $subscriptionId = $request->get('subscription_id');
            $retunResponse = $this->userService->userUnlockVideo(
                userId: $userId,
                videoId: $videoId,
                subscriptionId: $subscriptionId
            );
            return ResponseHelper::successResponse(
                data: $retunResponse,
                message: "Video Unlocked Successfully..!"
            );
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage());
        }
    }
    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function videoUpdate(Request $request)
    {
        try {
            $role = $request->get('role');
            if ($role == "admin") {
                return ResponseHelper::failureResponse(message: "Forbidden", code: 403);
            }
            $Validator = Validator::make($request->all(), [
                'video_id' => 'required|strict_int',
                'subscription_id' => 'required|strict_int',
                'duration' => 'required|strict_int',
                'video_status' => 'required|strict_bool',
            ]);
            if ($Validator->fails()) {
                return ResponseHelper::failureResponse(message: $Validator->errors()->first(), code: 400);
            }
            $userId = $request->get('user_id');
            $videoId = $request->get('video_id');
            $subscriptionId = $request->get('subscription_id');
            $duration = $request->get('duration');
            $status = $request->get('video_status');
            $retunResponse = $this->userService->updateVideoStram(
                userId: $userId,
                videoId: $videoId,
                subscriptionId: $subscriptionId,
                duration: $duration,
                status: $status
            );
            return ResponseHelper::successResponse(
                data: $retunResponse,
                message: "Video Status Updated Successfully..!"
            );
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage());
        }
    }
}
