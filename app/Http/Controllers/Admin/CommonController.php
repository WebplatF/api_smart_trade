<?php

namespace App\Http\Controllers\Admin;

use App\Helper\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\CommonService;
use Illuminate\Http\Request;
use Throwable;
use Illuminate\Support\Facades\Validator;

class CommonController extends Controller
{
    protected CommonService $commonService;

    public function __construct(CommonService $commonService)
    {
        $this->commonService = $commonService;
    }

    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function imageUpload(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'image' => 'required|file|mimes:jpg,jpeg,png',
            ]);
            if ($validator->fails()) {
                return ResponseHelper::failureResponse(message: $validator->errors()->first(), code: 400);
            }
            $title = $request->get('title');
            $response = $this->commonService->storeImage(file: $request->file('image'), folder: 'images', title: $title);
            return ResponseHelper::successResponse(data: ["url" => $response], message: "Image Uploaded", code: 200);
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage(), code: 400);
        }
    }
    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fileUpload(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'upload_id'     => 'required',
                'chunk_index'   => 'required|integer',
                'total_chunks'  => 'required|integer',
                'thumbnail_id'  => 'required|integer',
                'file' => 'required|file',
            ]);
            if ($validator->fails()) {
                return ResponseHelper::failureResponse(message: $validator->errors()->first(), code: 400);
            }
            $response = $this->commonService->storeVideo($request);
            return ResponseHelper::successResponse(data: ["url" => $response], message: "Image Uploaded", code: 200);
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage(), code: 400);
        }
    }
    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWasabiFile(Request $request)
    {
        try {
            $path = $request->query('path');
            if (!$path) {
                return ResponseHelper::failureResponse(message: "Invalid Path", code: 400);
            }
            $returnUrl = $this->commonService->getWasabiFile(filePath: $path);
            return ResponseHelper::successResponse(data: $returnUrl, message: "Url is arrived");
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage(), code: 400);
        }
    }
    /**
     * @param Illuminate\Http\Request $request
     * @return string response
     */
    public function getWasabiVideo(Request $request)
    {
        try {
            $path = $request->query('path');
            if (!$path) {
                return ResponseHelper::failureResponse(message: "Invalid Path", code: 400);
            }
            $playlist = $this->commonService->getMasterFileContent(videoId: $path);
            return response($playlist, 200)
                ->header('Content-Type', 'application/vnd.apple.mpegurl')
                ->header('Cache-Control', 'public, max-age=20');
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage(), code: 400);
        }
    }
    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVideoUrl(Request $request)
    {
        try {
            $path = $request->query('path');
            if (!$path) {
                return ResponseHelper::failureResponse(message: "Invalid Path", code: 400);
            }
            $returnPath = $this->commonService->getVideoUrl(path: $path);
            return ResponseHelper::successResponse(data: $returnPath, message: "Data Arrived");
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage(), code: 400);
        }
    }
    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getImageList(Request $request)
    {
        try {
            $role = $request->get('role');
            if ($role != "admin") {
                return ResponseHelper::failureResponse(message: "Forbidden", code: 403);
            }
            $returnImages = $this->commonService->imageList();
            return ResponseHelper::successResponse(data: $returnImages, message: "Images Data Arrived");
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage(), code: 400);
        }
    }
    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVideoList(Request $request)
    {
        try {
            $role = $request->get('role');
            if ($role != "admin") {
                return ResponseHelper::failureResponse(message: "Forbidden", code: 403);
            }
            $returnImages = $this->commonService->videoList();
            return ResponseHelper::successResponse(data: $returnImages, message: "Video Data Arrived");
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage(), code: 400);
        }
    }
}
