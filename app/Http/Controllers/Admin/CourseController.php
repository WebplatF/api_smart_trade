<?php

namespace App\Http\Controllers\Admin;

use App\Services\CourseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helper\ResponseHelper;
use Throwable;
use App\Http\Controllers\Controller;

class CourseController extends Controller
{
    protected CourseService $courseService;

    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }
    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function courseCreation(Request $request)
    {
        try {
            $role = $request->get('role');
            if ($role != "admin") {
                return ResponseHelper::failureResponse(message: "Forbidden", code: 403);
            }
            $Validator = Validator::make($request->all(), [
                'title' => 'required|strict_string',
                'expert' => 'required|strict_string',
                'image_id' => 'required|strict_int',
            ]);
            if ($Validator->fails()) {
                return ResponseHelper::failureResponse(message: $Validator->errors()->first(), code: 400);
            }
            $title = $request->get('title');
            $exprt = $request->get('expert');
            $imageId = $request->get('image_id');
            $returnResponse = $this->courseService->courseCreation(title: $title, expert: $exprt, imageId: $imageId);
            return ResponseHelper::successResponse(data: $returnResponse, message: "Course Created Successfully...!");
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage());
        }
    }
    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function courseEdit(Request $request)
    {
        try {
            $role = $request->get('role');
            if ($role != "admin") {
                return ResponseHelper::failureResponse(message: "Forbidden", code: 403);
            }
            $Validator = Validator::make($request->all(), [
                'course_id' => 'required|strict_int',
                'title' => 'required|strict_string',
                'expert' => 'required|strict_string',
                'image_id' => 'required|strict_int',
            ]);
            if ($Validator->fails()) {
                return ResponseHelper::failureResponse(message: $Validator->errors()->first(), code: 400);
            }
            $courseId = $request->get('course_id');
            $title = $request->get('title');
            $exprt = $request->get('expert');
            $imageId = $request->get('image_id');
            $returnResponse = $this->courseService->courseEdit(courseId: $courseId, title: $title, expert: $exprt, imageId: $imageId);
            return ResponseHelper::successResponse(data: $returnResponse, message: "Course Edited Successfully...!");
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage());
        }
    }
    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function courseDetailCreation(Request $request)
    {
        try {
            $role = $request->get('role');
            if ($role != "admin") {
                return ResponseHelper::failureResponse(message: "Forbidden", code: 403);
            }
            $Validator = Validator::make($request->all(), [
                'title' => 'required|strict_string',
                'course_id' => 'required|strict_int',
            ]);
            if ($Validator->fails()) {
                return ResponseHelper::failureResponse(message: $Validator->errors()->first(), code: 400);
            }
            $title = $request->get('title');
            $courseId = $request->get('course_id');
            $returnResponse = $this->courseService->courseDetailsCreation(title: $title, courseId: $courseId);
            return ResponseHelper::successResponse(data: $returnResponse, message: "Lesson Created Successfully...!");
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage());
        }
    }
    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function courseDetailEdit(Request $request)
    {
        try {
            $role = $request->get('role');
            if ($role != "admin") {
                return ResponseHelper::failureResponse(message: "Forbidden", code: 403);
            }
            $Validator = Validator::make($request->all(), [
                'title' => 'required|strict_string',
                'details_id' => 'required|strict_int',
            ]);
            if ($Validator->fails()) {
                return ResponseHelper::failureResponse(message: $Validator->errors()->first(), code: 400);
            }
            $title = $request->get('title');
            $detailsId = $request->get('details_id');
            $returnResponse = $this->courseService->courseDetailsEdit(title: $title, detailsId: $detailsId);
            return ResponseHelper::successResponse(data: $returnResponse, message: "Lesson Edited Successfully...!");
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage());
        }
    }
    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function lessonVideoMap(Request $request)
    {
        try {
            $role = $request->get('role');
            if ($role != "admin") {
                return ResponseHelper::failureResponse(message: "Forbidden", code: 403);
            }
            $Validator = Validator::make($request->all(), [
                 'title' => 'required|strict_string',
                'video_id' => 'required|strict_int',
                'details_id' => 'required|strict_int',
                'thumbnail_id' => 'required|strict_int',
            ]);
            if ($Validator->fails()) {
                return ResponseHelper::failureResponse(message: $Validator->errors()->first(), code: 400);
            }
            $detailsId = $request->get('details_id');
            $videoId = $request->get('video_id');
            $thumbnailId = $request->get('thumbnail_id');
           $title = $request->get('title');
            $returnResponse = $this->courseService->mapCourseVideo(videoId: $videoId, detailId: $detailsId, thumbnailId: $thumbnailId, title: $title);
            return ResponseHelper::successResponse(data: $returnResponse, message: "Video Added Successfully...!");
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage());
        }
    }
    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function courseList(Request $request)
    {
        try {
            $role = $request->get('role');
            if ($role != "admin") {
                return ResponseHelper::failureResponse(message: "Forbidden", code: 403);
            }
            $returnResponse = $this->courseService->courseList();
            return ResponseHelper::successResponse(data: $returnResponse, message: "Lesson Edited Successfully...!");
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage());
        }
    }
    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function courseDetailsList(Request $request)
    {
        try {
            $role = $request->get('role');
            if ($role != "admin") {
                return ResponseHelper::failureResponse(message: "Forbidden", code: 403);
            }
            $courseId = $request->query('course');
            if (!$courseId) {
                return ResponseHelper::failureResponse(message: "Invalid Request");
            }
            $returnResponse = $this->courseService->courseLessonList(courseId: $courseId);
            return ResponseHelper::successResponse(data: $returnResponse, message: "Lesson Data Successfully...!");
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage());
        }
    }
    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function lessonVideoList(Request $request)
    {
        try {
            $role = $request->get('role');
            if ($role != "admin") {
                return ResponseHelper::failureResponse(message: "Forbidden", code: 403);
            }
            $detailId = $request->query('detail');
            if (!$detailId) {
                return ResponseHelper::failureResponse(message: "Invalid Request");
            }
            $returnResponse = $this->courseService->lessonVideoList(detailId: $detailId);
            return ResponseHelper::successResponse(data: $returnResponse, message: "Lesson Video Successfully...!");
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage());
        }
    }
    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCourseWithLesson(Request $request)
    {
        try {
            $role = $request->get('role');
            if ($role == "admin") {
                return ResponseHelper::failureResponse(message: "Forbidden", code: 403);
            }
            $userId = $request->get('user_id');
            $returnResponse = $this->courseService->courseWithLesson(userId: $userId);
            return ResponseHelper::successResponse(data: $returnResponse, message: "Course Details Arrived Successfully...!");
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage());
        }
    }
    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function courseActions(Request $request)
    {
        try {
            $role = $request->get('role');
            if ($role != "admin") {
                return ResponseHelper::failureResponse(message: "Forbidden", code: 403);
            }
            $Validator = Validator::make($request->all(), [
                'tag' => 'required|strict_string',
                'status' => 'required|strict_bool',
                'action_id' => 'required|strict_int',
            ]);
            if ($Validator->fails()) {
                return ResponseHelper::failureResponse(message: $Validator->errors()->first(), code: 400);
            }
            $actionId = $request->get('action_id');
            $status = $request->get('status');
            $tag = $request->get('tag');
            $returnResponse = $this->courseService->courseAcion(tag: $tag, status: $status, id: $actionId);
            return ResponseHelper::successResponse(data: [], message: $returnResponse);
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage());
        }
    }
}
