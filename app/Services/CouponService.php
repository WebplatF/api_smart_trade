<?php

namespace App\Services;

use App\Models\CouponMaster;
use App\RequestModel\CouponCreateModel;
use App\RequestModel\CouponEditModel;
use App\RequestModel\CouponStatusUpdateModel;
use App\Resources\CouponListResources;
use App\ResponseModel\CommonListResponseModel;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class CouponService
{
    public function create(CouponCreateModel $couponCreateModel)
    {
        try {
            DB::transaction(function () use ($couponCreateModel) {
                CouponMaster::create([
                    'code' => $couponCreateModel->code,
                    'discount_type' => $couponCreateModel->discountType,
                    'value' => $couponCreateModel->value
                ]);
            });
        } catch (QueryException $e) {
            throw new Exception($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    public function edit(CouponEditModel $couponEditModel)
    {
        try {
            DB::transaction(function () use ($couponEditModel) {
                CouponMaster::updateOrCreate(
                    ['id' => $couponEditModel->id],
                    [
                        'code' => $couponEditModel->code,
                        'discount_type' => $couponEditModel->discountType,
                        'value' => $couponEditModel->value
                    ]
                );
            });
        } catch (QueryException $e) {
            throw new Exception($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    public function statusUpdate(CouponStatusUpdateModel $couponStatusUpdateModel)
    {
        try {
            DB::transaction(function () use ($couponStatusUpdateModel) {
                $coupon = CouponMaster::find($couponStatusUpdateModel->id);
                if (!$coupon) {
                    throw new Exception("Invalid coupon");
                }
                $coupon->update([
                    'is_delete' => $couponStatusUpdateModel->status ?? false
                ]);
            });
        } catch (QueryException $e) {
            throw new Exception($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    public function list()
    {
        try {
            $coupon = CouponMaster::paginate(15);
            $couponList = CouponListResources::collection($coupon)->resolve();
            return new CommonListResponseModel(
                currentPage: $coupon->currentPage(),
                totalRecords: $coupon->total(),
                dataList: $couponList
            );
        } catch (QueryException $e) {
            throw new Exception($e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
