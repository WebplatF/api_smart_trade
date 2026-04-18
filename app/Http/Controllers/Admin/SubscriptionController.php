<?php

namespace App\Http\Controllers\Admin;

use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helper\ResponseHelper;
use App\Http\Controllers\Controller;
use Throwable;


class SubscriptionController extends Controller
{
    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function subScriptionCreation(Request $request)
    {
        try {
            $role = $request->get('role');
            if ($role != "admin") {
                return ResponseHelper::failureResponse(message: "Forbidden", code: 403);
            }
            $Validator = Validator::make($request->all(), [
                'plan_name' => 'required|strict_string',
                'amount' => 'required|strict_string',
                'duration' => 'required|strict_string',
                'validity' => 'required|strict_string'
            ]);
            if ($Validator->fails()) {
                return ResponseHelper::failureResponse(message: $Validator->errors()->first(), code: 400);
            }
            $retunResponse = $this->subscriptionService->createSubScription($request);
            return ResponseHelper::successResponse(data: $retunResponse, message: "Subscription Created Successfully..!");
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage());
        }
    }
    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function subScriptionList(Request $request)
    {
        try {
            $retunResponse = $this->subscriptionService->subScriptionList();
            return ResponseHelper::successResponse(data: $retunResponse, message: "Subscription Data Arrived Successfully..!");
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage());
        }
    }
    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function subScriptionStatusUpdate(Request $request)
    {
        try {
            $role = $request->get('role');
            if ($role != "admin") {
                return ResponseHelper::failureResponse(message: "Forbidden", code: 403);
            }
            $Validator = Validator::make($request->all(), [
                'plan_id' => 'required|strict_int',
                'status' => 'required|strict_bool',
            ]);
            if ($Validator->fails()) {
                return ResponseHelper::failureResponse(message: $Validator->errors()->first(), code: 400);
            }
            $retunResponse = $this->subscriptionService->statusUpdate(
                planId: $request->get('plan_id'),
                status: $request->get('status')
            );
            return ResponseHelper::successResponse(
                data: $retunResponse,
                message: "Subscription Data Arrived Successfully..!"
            );
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage());
        }
    }
    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function subScriptionUpdate(Request $request)
    {
        try {
            $role = $request->get('role');
            if ($role != "admin") {
                return ResponseHelper::failureResponse(message: "Forbidden", code: 403);
            }
            $Validator = Validator::make($request->all(), [
                'plan_id' => 'required|strict_int',
                'plan_name' => 'required|strict_string',
                'amount' => 'required|strict_string',
                'duration' => 'required|strict_string',
                'validity' => 'required|strict_string'
            ]);
            if ($Validator->fails()) {
                return ResponseHelper::failureResponse(message: $Validator->errors()->first(), code: 400);
            }
            $retunResponse = $this->subscriptionService->subscriptionEdit(
                planId: $request->get('plan_id'),
                planName: $request->get('plan_name'),
                amount: $request->get('amount'),
                duration: $request->get('duration'),
                validity: $request->get('validity'),
            );
            return ResponseHelper::successResponse(
                data: $retunResponse,
                message: "Subscription Data Arrived Successfully..!"
            );
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage());
        }
    }
    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userSubscription(Request $request)
    {
        try {
            $userId = $request->get('user_id');
            $role = $request->get('role');
            if ($role == "admin") {
                return ResponseHelper::failureResponse(message: "Forbidden", code: 403);
            }
            $Validator = Validator::make($request->all(), [
                'plan_id' => 'required|strict_int',
                'image_id' => 'required|strict_int'
            ]);
            if ($Validator->fails()) {
                return ResponseHelper::failureResponse(message: $Validator->errors()->first(), code: 400);
            }
            $retunResponse = $this->subscriptionService->userSubscription(
                planId: $request->get('plan_id'),
                imageId: $request->get('image_id'),
                userId: (int)$userId
            );
            return ResponseHelper::successResponse(
                data: $retunResponse,
                message: "Subscription Data Arrived Successfully..!"
            );
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage());
        }
    }
    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userSubscriptionList(Request $request)
    {
        try {
            $role = $request->get('role');
            if ($role != "admin") {
                return ResponseHelper::failureResponse(message: "Forbidden", code: 403);
            }
            $retunResponse = $this->subscriptionService->userSubscriptionList();
            return ResponseHelper::successResponse(
                data: $retunResponse->toArray(),
                message: "Subscription Data Arrived Successfully..!"
            );
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage());
        }
    }
    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function subscriptionAction(Request $request)
    {
        try {
            $role = $request->get('role');
            if ($role != "admin") {
                return ResponseHelper::failureResponse(message: "Forbidden", code: 403);
            }
            $Validator = Validator::make($request->all(), [
                'subscrption_id' => 'required|strict_int',
                'action' => 'required|strict_string'
            ]);
            if ($Validator->fails()) {
                return ResponseHelper::failureResponse(message: $Validator->errors()->first(), code: 400);
            }
            $id = $request->get('subscrption_id');
            $action = $request->get('action');
            $retunResponse = $this->subscriptionService->subscriptionAction(id: $id, action: $action, reason: "");
            return ResponseHelper::successResponse(
                data: [],
                message: "Subscription Status Changed Successfully..!"
            );
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage());
        }
    }
}
