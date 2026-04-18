<?php

namespace App\Http\Controllers\Admin;

use App\Services\HomePageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helper\ResponseHelper;
use App\Http\Controllers\Controller;
use Throwable;

class HomePageController extends Controller
{
    protected HomePageService $homePageService;

    public function __construct(HomePageService $homePageService)
    {
        $this->homePageService = $homePageService;
    }

    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addBanner(Request $request)
    {
        try {
            $role = $request->get('role');
            if ($role != "admin") {
                return ResponseHelper::failureResponse(message: "Forbidden", code: 403);
            }
            $Validator = Validator::make($request->all(), [
                'title' => 'required|strict_string',
                'image_id' => 'required|strict_int',
            ]);
            if ($Validator->fails()) {
                return ResponseHelper::failureResponse(message: $Validator->errors()->first(), code: 400);
            }
            $this->homePageService->addBanner(
                title: $request->get('title'),
                imageId: $request->get('image_id')
            );
            return ResponseHelper::successResponse(data: [], message: "Banner added successfully...!");
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage());
        }
    }

    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addDemoVideo(Request $request)
    {
        try {
            $role = $request->get('role');
            if ($role != "admin") {
                return ResponseHelper::failureResponse(message: "Forbidden", code: 403);
            }
            $Validator = Validator::make($request->all(), [
                'title' => 'required|strict_string',
                'video_id' => 'required|strict_int',
            ]);
            if ($Validator->fails()) {
                return ResponseHelper::failureResponse(message: $Validator->errors()->first(), code: 400);
            }
            $this->homePageService->addDemoVideo(
                title: $request->get('title'),
                videoId: $request->get('video_id')
            );
            return ResponseHelper::successResponse(data: [], message: "Demo video added successfully...!");
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage());
        }
    }
    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addWeeklyMeeting(Request $request)
    {
        try {
            $role = $request->get('role');
            if ($role != "admin") {
                return ResponseHelper::failureResponse(message: "Forbidden", code: 403);
            }
            $Validator = Validator::make($request->all(), [
                'title' => 'required|strict_string',
                'video_id' => 'required|strict_int',
            ]);
            if ($Validator->fails()) {
                return ResponseHelper::failureResponse(message: $Validator->errors()->first(), code: 400);
            }
            $this->homePageService->addWeeklyMeeting(
                title: $request->get('title'),
                videoId: $request->get('video_id')
            );
            return ResponseHelper::successResponse(data: [], message: "Weekly meeting video added successfully...!");
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage());
        }
    }
    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function initialData()
    {
        try {
            $response = $this->homePageService->intialData();
            return ResponseHelper::successResponse(data: $response, message: "Data Arrived");
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage());
        }
    }
    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWeeklyMeeting()
    {
        try {
            $response = $this->homePageService->getWeeklyMeeting();
            return ResponseHelper::successResponse(data: $response, message: "Data Arrived");
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage());
        }
    }
    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function homebuilderData(Request $request)
    {
        try {
            $role = $request->get('role');
            if ($role != "admin") {
                return ResponseHelper::failureResponse(message: "Forbidden", code: 403);
            }
            $response = $this->homePageService->homebuilderData();
            return ResponseHelper::successResponse(data: $response, message: "Data Arrived");
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage());
        }
    }
}
