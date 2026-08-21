<?php

namespace App\Services;

use App\Helper\DatabaseErrorHelper;
use App\Models\CouponMaster;
use App\Models\InvoiceDetails;
use App\Models\InvoiceMaster;
use App\Models\InvoiceSequence;
use App\Models\SubscriptionMaster;
use App\Models\UserMaster;
use App\Models\UserSubscription;
use App\Resources\InvoiceDetailsResources;
use App\Resources\InvoiceMasterResources;
use App\Resources\InvoiceUserResources;
use App\Resources\SubscriptionResources;
use App\Resources\UserSubscriptionResources;
use App\ResponseModel\UserSubscriptionListResponseModel;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SubscriptionService
{
    /**
     * @param $request
     * @return array subscriptionData
     * @throws Exception
     */
    public function createSubScription($request)
    {
        try {
            $planName = $request->get('plan_name');
            $amount = $request->get('amount');
            $validity = $request->get('validity');
            $duration = $request->get('duration');
            $subscription = SubscriptionMaster::create([
                'plan_name' => $planName,
                'amount' => $amount,
                'validity' => $validity,
                'duration' => $duration
            ]);
            return new SubscriptionResources($subscription);
        } catch (QueryException $e) {
            throw new Exception('Subscription Failed :' . $e->errorInfo[2] ?? $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Subscription Failed :" . $e->getMessage());
        }
    }
    /**
     * @param int $planId
     * @param string $planName
     * @param string $amount
     * @param string $validity
     * @param string $duration
     * @return array subscriptionData
     * @throws Exception
     */
    public function subscriptionEdit(
        int $planId,
        string $planName,
        string $amount,
        string $validity,
        string $duration
    ) {
        try {
            $subscription = SubscriptionMaster::findOrFail($planId);
            if (!$subscription) {
                throw new Exception('Plan is not in database');
            }
            $subscription->update([
                'plan_name' => $planName,
                'amount' => $amount,
                'validity' => $validity,
                'duration' => $duration
            ]);
            return new SubscriptionResources($subscription);
        } catch (QueryException $e) {
            throw new Exception('Subscription Edit Failed :' . $e->errorInfo[2] ?? $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Subscription Edit Failed :" . $e->getMessage());
        }
    }
    /**
     * @return array subscription List
     * @throws Exception
     */
    public function subScriptionList()
    {
        try {
            $subscription = SubscriptionMaster::get();
            $reponse = SubscriptionResources::collection($subscription);
            return $reponse->resolve();
        } catch (QueryException $e) {
            throw new Exception('Subscription List Failed :' . $e->errorInfo[2] ?? $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Subscription List Failed :" . $e->getMessage());
        }
    }
    /**
     * @param int $planId
     * @param bool $status
     * @return bool result
     * @throws Exception
     */
    public function statusUpdate(int $planId, bool $status): bool
    {
        try {
            $subscription = SubscriptionMaster::findOrFail($planId);
            if (!$subscription) {
                throw new Exception('Plan is not in database');
            }
            $subscription->update([
                'is_delete' => $status
            ]);
            return true;
        } catch (QueryException $e) {
            throw new Exception('Subscription  Failed :' . $e->errorInfo[2] ?? $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Subscription  Failed :" . $e->getMessage());
        }
    }
    /**
     * @param int $planId
     * @param int $imageId
     * @return array subscriptionData
     * @throws Exception
     */
    public function userSubscription(
        int $planId,
        int $imageId,
        int $userId,
        string $code = '',
        bool $isRenew = false,
    ) {
        try {
            return  DB::transaction(function () use ($planId, $imageId, $userId, $code, $isRenew) {
                $now = Carbon::now();
                $subscription = SubscriptionMaster::findOrFail($planId);
                if (!$subscription) {
                    throw new Exception('Plan is not in database');
                }
                $runningSubscription = UserSubscription::where('user_id', $userId)
                    ->where('status', 'approved')
                    ->where('is_delete', 0)
                    ->where('end_date', '>=', $now)
                    ->first();
                if ($runningSubscription) {
                    if ($isRenew) {
                        $renewable = $this->renewalSubscription(planId: $planId, imageId: $imageId, userId: $userId, code: $code,);
                        return $renewable;
                    }
                    throw new Exception('Already some plan subscription is going');
                }
                $subscriptions = UserSubscription::where('user_id', $userId)
                    ->where('subscription_id', $planId)
                    ->where('is_delete', 0)
                    ->first();
                if ($subscriptions) {
                    if ($subscriptions->status == 'pending') {
                        $invoiceDetails = InvoiceDetails::where('is_delete', 0)->where('user_sub_id', $subscriptions->id)->first();
                        // throw new Exception('Already this subscription waiting for admin approval');
                        return  [
                            "id" => $subscriptions->id,
                            "status" => $subscriptions->status ?? "pending",
                            "invoice_id" => $invoiceDetails->invoice_id
                        ];
                    }
                    if ($subscriptions->status == 'approved') {
                        throw new Exception('Already subscription is available renewable from admin');
                    }
                    if ($subscriptions->status == 'rejected') {
                        $invoiceDetails = InvoiceDetails::where('is_delete', 0)->where('user_sub_id', $subscriptions->id)->first();
                        $subscriptions->update([
                            'status' => 'pending',
                            'imageid' => $imageId
                        ]);
                        return  [
                            "id" => $subscriptions->id,
                            "status" => $subscriptions->status ?? "pending",
                            "invoice_id" => $invoiceDetails->invoice_id
                        ];
                    }
                } else {
                    $userSubscription = UserSubscription::create(
                        [
                            'user_id' => $userId,
                            'subscription_id' => $planId,
                            'image_id' => $imageId,
                            'plan_name' => $subscription->plan_name ?? "",
                            'amount' => $subscription->amount ?? "",
                            'duration' => $subscription->duration ?? "",
                            'coupon' => $code ?? "",
                        ]
                    );
                    $inovice = $this->invoiceCreate(userId: $userId, code: $code, userSubId: $userSubscription->id);
                    return [
                        "id" => $userSubscription->id,
                        "status" => $userSubscription->status ?? "pending",
                        "invoice_id" => $inovice->invoice_id
                    ];
                }
            });
        } catch (QueryException $e) {
            throw new Exception('Subscription  Failed :' . $e->errorInfo[2] ?? $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Subscription  Failed :" . $e->getMessage());
        }
    }
    public function renewalSubscription(int $planId, int $imageId, int $userId, string $code)
    {
        try {
            $subscriptions = UserSubscription::where('user_id', $userId)
                ->where('subscription_id', $planId)
                ->where('is_delete', 0)
                ->first();
            if ($subscriptions) {
                if ($subscriptions->status == 'pending') {
                    $invoiceDetails = InvoiceDetails::where('is_delete', 0)->where('user_sub_id', $subscriptions->id)->first();
                    // throw new Exception('Already this subscription waiting for admin approval');
                    return  [
                        "id" => $subscriptions->id,
                        "status" => $subscriptions->status ?? "pending",
                        "invoice_id" => $invoiceDetails->invoice_id
                    ];
                }
                if ($subscriptions->status == 'approved') {
                    throw new Exception('Already subscription is available renewable from admin');
                }
                if ($subscriptions->status == 'rejected') {
                    $invoiceDetails = InvoiceDetails::where('is_delete', 0)->where('user_sub_id', $subscriptions->id)->first();
                    $subscriptions->update([
                        'status' => 'pending',
                        'imageid' => $imageId
                    ]);
                    return  [
                        "id" => $subscriptions->id,
                        "status" => $subscriptions->status ?? "pending",
                        "invoice_id" => $invoiceDetails->invoice_id
                    ];
                }
            } else {
                $userSubscription = UserSubscription::create(
                    [
                        'user_id' => $userId,
                        'subscription_id' => $planId,
                        'image_id' => $imageId,
                        'plan_name' => $subscription->plan_name ?? "",
                        'amount' => $subscription->amount ?? "",
                        'duration' => $subscription->duration ?? "",
                        'coupon' => $code ?? "",
                    ]
                );
                $inovice = $this->invoiceCreate(userId: $userId, code: $code, userSubId: $userSubscription->id);
                return [
                    "id" => $userSubscription->id,
                    "status" => $userSubscription->status ?? "pending",
                    "invoice_id" => $inovice->invoice_id
                ];
            }
        } catch (QueryException $e) {
            return DatabaseErrorHelper::handle(e: $e);
        } catch (Exception $e) {
            throw new Exception("Renewal subscription failed :" . $e->getMessage());
        }
    }
    /**
     * @return UserSubscriptionListResponseModel
     * @throws Exception
     */
    public function userSubscriptionList(): UserSubscriptionListResponseModel
    {
        try {
            $count = UserSubscription::where('is_delete', 0)->count();
            $users = UserSubscription::with(['user', 'subscription', 'image'])->where('is_delete', 0)->paginate(15);
            $userList = UserSubscriptionResources::collection($users->items())->resolve();
            $response = new UserSubscriptionListResponseModel(
                currentPage: $users->currentPage(),
                totalRecords: $count,
                userList: $userList
            );
            return $response;
        } catch (QueryException $e) {
            throw new Exception('Subscription List Failed :' . $e->errorInfo[2] ?? $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Subscription List Failed :" . $e->getMessage());
        }
    }
    /**
     * @param string $action
     * @param int $id
     * @param string $reason
     * @return bool
     * @throws Exception
     */
    public function subscriptionAction(string $action, int $id, string $reason): bool
    {
        try {
            $user = UserSubscription::where('status', '!=', 'approved')
                ->where('id', $id)
                ->first();
            if (!$user) {
                throw new Exception("Subscrition is not found");
            }
            if ($action == "approved") {
                $plan = SubscriptionMaster::find($user->subscription_id);

                if (!$plan) {
                    throw new Exception("Plan not found");
                }
                $runningSubscription = UserSubscription::where('user_id', $user->user_id)
                    ->where('status', 'approved')
                    ->where('is_delete', 0)
                    ->where('end_date', '>=', Carbon::now()->toDateString())
                    ->first();
                if ($runningSubscription) {
                    $runningSubscription->update(['is_delete' => 1]);
                }
                $startDate = Carbon::now();
                $endDate = Carbon::now();
                if ($plan->validity == "Years") {
                    $endDate = $startDate->copy()->addYears($plan->duration);
                } elseif ($plan->validity == "Months") {
                    $endDate = $startDate->copy()->addMonths($plan->duration);
                } elseif ($plan->validity == "Days") {
                    $endDate = $startDate->copy()->addDays($plan->duration);
                }
                $user->update([
                    "status" => "approved",
                    'plan_name' => $plan->plan_name ?? "",
                    'amount' => $plan->amount ?? "",
                    'validity' => $plan->validity ?? "",
                    'duration' => $plan->duration ?? "",
                    "start_date" => $startDate->toDateString(),
                    "renew_date" => $endDate->toDateString(),
                    "end_date" => $endDate->toDateString()
                ]);
                try {
                    $this->getInvoice(userId: $user->user_id, subId: $user->id);
                } catch (Exception $e) {
                    Log::info("Email sending error", $e);
                }
            } else {
                $user->update([
                    "status" => $action
                ]);
            }
            return true;
        } catch (QueryException $e) {
            throw new Exception('Subscription List Failed :' . $e->errorInfo[2] ?? $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Subscription List Failed :" . $e->getMessage());
        }
    }
    public function deactivateSubscription(int $subId)
    {
        try {
            DB::transaction(function () use ($subId) {
                $subscription = UserSubscription::where('is_delete', 0)->find($subId);
                if (!$subscription) {
                    throw new Exception("Invalid subscription");
                }
                $subscription->update([
                    'is_delete' => 1
                ]);
            });
        } catch (QueryException $e) {
            return DatabaseErrorHelper::handle(e: $e);
        } catch (Exception $e) {
            return new Exception($e->getMessage());
        }
    }

    public function invoiceCreate(int $userId, string $code, int $userSubId): object
    {
        try {
            return DB::transaction(function () use ($userId, $code, $userSubId) {
                $discount = 0;
                $type = null;
                if ($code != '') {
                    $dis = CouponMaster::where('is_delete', 0)->where('code', $code)->first();
                    if ($dis) {
                        $discount = (float)$dis->value;
                        $type = $dis->discount_type;
                    }
                }
                $invoice = InvoiceMaster::create([
                    'invoice_no' => $this->generateInvoiceNo(),
                    'user_id' => $userId,
                    'discount' => $discount,
                    'discount_type' => $discount == 0 ? "" : $type,
                    'sub_total' => '0',
                    'tax' => '18',
                    'grand_total' => '0',
                ]);
                $ivoiceDetails = InvoiceDetails::create([
                    'invoice_id' => $invoice->id,
                    'user_sub_id' => $userSubId,
                ]);
                return (object)[
                    'invoice_id' => $invoice->id,
                ];
            });
        } catch (QueryException $e) {
            throw DatabaseErrorHelper::handle(e: $e);
        } catch (Exception $e) {
            throw new Exception("Invoice creation Failed :" . $e->getMessage());
        }
    }
    /**
     * Inovice No Generation
     *
     * @return void
     */
    public function generateInvoiceNo()
    {
        $sequence = InvoiceSequence::create();
        return 'SMTA-IN-' . str_pad($sequence->id, 3, '0', STR_PAD_LEFT);
    }
    public function getInvoice(int $userId, int $subId)
    {
        try {
            $invoiceDetails = $this->userDetailsSubscription(
                userId: $userId,
                subId: $subId
            );
            $html = view('invoice', [
                'data' => $invoiceDetails
            ])->render();
            $dompdf = new Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $pdf = $dompdf->output();
            $fileName =
                ($invoiceDetails['invoice']['invoice_no'] ?? 'invoice')
                . '.pdf';
            $email =  $invoiceDetails['user']['email'];
            Mail::raw(
                'Please find the attached invoice for your Smart Trade subscription purchase.',
                function ($message) use ($pdf, $fileName, $email) {
                    $message->to($email)
                        ->subject(
                            'Invoice for Your Smart Trade Subscription Purchase'
                        )
                        ->attachData(
                            $pdf,
                            $fileName,
                            [
                                'mime' => 'application/pdf',
                            ]
                        );
                }
            );
            return response($html)
                ->header('Content-Type', 'text/html');
        } catch (Exception $e) {
            Log::info("Email error", $e);
        }
    }
    public function userDetailsSubscription(int $userId, int $subId)
    {
        try {
            $user = UserMaster::where('is_delete', 0)->find($userId);
            $invoiceDetails = InvoiceDetails::with('userSubscription')->where('is_delete', 0)->where('user_sub_id', $subId)->first();
            $invoice = InvoiceMaster::where('is_delete', 0)->find($invoiceDetails->invoice_id);
            $invoiceUser = InvoiceUserResources::make($user)->resolve();
            $invoiceMaster = InvoiceMasterResources::make($invoice)->resolve();
            $invoiceDet = InvoiceDetailsResources::make($invoiceDetails)->resolve();
            return [
                "user" => $invoiceUser,
                "invoice" => $invoiceMaster,
                "invoice_details" => $invoiceDet
            ];
        } catch (QueryException $e) {
            throw DatabaseErrorHelper::handle(e: $e);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    public function manualUserSubscription(
        int $planId,
        string $startDate,
        string $endDate,
        string $tag,
        array $userList
    ) {
        try {

            DB::transaction(function () use (
                $planId,
                $userList,
                $tag,
                $startDate,
                $endDate
            ) {

                $subscription = SubscriptionMaster::findOrFail($planId);

                foreach ($userList as $userId) {

                    if ($tag === 'custom') {

                        $pStartDate = Carbon::parse($startDate);
                        $pEndDate   = Carbon::parse($endDate);
                    } else {

                        $pStartDate = Carbon::now();

                        if ($subscription->validity === 'Years') {

                            $pEndDate = $pStartDate->copy()
                                ->addYears($subscription->duration);
                        } elseif ($subscription->validity === 'Months') {

                            $pEndDate = $pStartDate->copy()
                                ->addMonths($subscription->duration);
                        } elseif ($subscription->validity === 'Days') {

                            $pEndDate = $pStartDate->copy()
                                ->addDays($subscription->duration);
                        } else {

                            throw new Exception('Invalid subscription validity');
                        }
                    }

                    UserSubscription::create([
                        'user_id'         => $userId,
                        'subscription_id' => $planId,
                        'image_id'        => 1,
                        'plan_name'       => $subscription->plan_name ?? '',
                        'amount'          => $subscription->amount ?? 0,
                        'duration'        => $subscription->duration ?? 0,
                        'validity' => $subscription->validity ?? "",
                        'coupon'          => '',
                        'start_date'      => $pStartDate->toDateString(),
                        'renew_date'      => $pEndDate->toDateString(),
                        'end_date'        => $pEndDate->toDateString(),
                        'status'          => 'approved',
                        'is_delete'       => 0,
                    ]);
                }
            });
        } catch (QueryException $e) {

            $message = $e->errorInfo[2] ?? $e->getMessage();

            throw new Exception('Subscription Failed: ' . $message);
        } catch (Exception $e) {

            throw new Exception('Subscription Failed: ' . $e->getMessage());
        }
    }
}
