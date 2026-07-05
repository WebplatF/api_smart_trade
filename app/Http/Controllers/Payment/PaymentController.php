<?php

namespace App\Http\Controllers\Payment;

use App\Helper\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\TransactionMaster;
use App\Services\PaymentService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Throwable;

class PaymentController extends Controller
{
    private PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function orderUnlock(Request $request)
    {
        try {
            $userId = $request->get('user_id');
            $Validator = Validator::make($request->all(), [
                'amount' => 'required|strict_string',
                'plan_id' => 'required|strict_int',
                'tag' => 'required|strict_string',
            ]);
            if ($Validator->fails()) {
                return ResponseHelper::failureResponse(message: $Validator->errors()->first(), code: 400);
            }
            $amount = (int)$request->get('amount');
            $planId = (int)$request->get('plan_id');
            $code = $request->get('code' ?? "");
            $tag = $request->get('tag');
            $response = $this->paymentService->orderCreate(code:$code,amount: $amount, userId: $userId, tag: $tag, planId: $planId);
            return ResponseHelper::successResponse(data: $response->toArray(), message: "Order is created", code: 200);
        } catch (Throwable $e) {
            Log::error($e->getMessage());
            return ResponseHelper::failureResponse(message: $e->getMessage(), code: 400);
        }
    }

    public function paymentCapture(Request $request)
    {
        try {
            $payload = $request->getContent();
            $data = json_decode($payload, true);
            $event = $data['event'];
            Log::info($event);
            switch ($event) {
                case 'payment.authorized':
                    // Handle authorized payment
                    $this->updatePaymentDetails($data);
                    break;

                case 'payment.captured':
                    // Update DB
                    // Activate subscription
                    break;
            }
        } catch (Throwable $e) {
            Log::error($e->getMessage());
        }
    }

    public function updatePaymentDetails($data)
    {
        // try {
        // $orderId = $data['']
        // $transaction = TransactionMaster::where('order_id', $orderId)->first();
        // if (!$transaction) {
        //     throw new Exception("Order is not created for this profile.");
        // } else {
        //     $transaction->update([
        //         'status' => "Completed",
        //         'razorypay_order_id' => $razorOrderId,
        //         'razorypay_payment_id' => $paymentId,
        //         'razorypay_signature' => $signature
        //     ]);
        //     UnlockProfile::create([
        //         'user_id' => $userId,
        //         'profile_id' => $profileId
        //     ]);
        //     $this->sendSMSMessage(
        //         mobile: $profile->phone,
        //         tag: 'matrimony_profile_view',
        //         name: $profile->first_name . $profile->last_name,
        //         profileId: $user->profile_id
        //     );
        //     return true;
        //     }
        // } catch (Exception $e) {
        //     throw new Exception($e->getMessage());
        // }
    }
}
