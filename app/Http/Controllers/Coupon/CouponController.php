<?php

namespace App\Http\Controllers\Coupon;

use App\Helper\ResponseHelper;
use App\Http\Controllers\Controller;
use App\RequestModel\CouponCreateModel;
use App\RequestModel\CouponEditModel;
use App\RequestModel\CouponStatusUpdateModel;
use App\Services\CouponService;
use Exception;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    private CouponService $couponService;
    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }
    public function create(Request $request)
    {
        try {
            $couponCreateModel = CouponCreateModel::fromRequest(request: $request);
            $this->couponService->create(couponCreateModel: $couponCreateModel);
            return ResponseHelper::successResponse(message: "Coupon created successfully...!");
        } catch (Exception $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage());
        }
    }
    public function edit(Request $request)
    {
        try {
            $couponEditModel = CouponEditModel::fromRequest(request: $request);
            $this->couponService->edit(couponEditModel: $couponEditModel);
            return ResponseHelper::successResponse(message: "Coupon edited successfully...!");
        } catch (Exception $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage());
        }
    }
    public function statusUpdate(Request $request)
    {
        try {
            $couponStatusUpdateModel = CouponStatusUpdateModel::fromRequest(request: $request);
            $this->couponService->statusUpdate(couponStatusUpdateModel: $couponStatusUpdateModel);
            return ResponseHelper::successResponse(message: "Coupon status updated successfully...!");
        } catch (Exception $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage());
        }
    }
    public function list()
    {
        try {
            $couponList = $this->couponService->list();
            return ResponseHelper::successResponse(data: $couponList, message: "Coupon list arrived successfully...!");
        } catch (Exception $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage());
        }
    }
}
