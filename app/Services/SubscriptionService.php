<?php

namespace App\Services;

use App\Models\SubscriptionMaster;
use App\Models\UserSubscription;
use App\Resources\SubscriptionResources;
use App\Resources\UserSubscriptionResources;
use App\ResponseModel\UserSubscriptionListResponseModel;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\QueryException;


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
        int $userId
    ) {
        try {
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
                throw new Exception('Already some plan subscription is going');
            }
            $subscriptions = UserSubscription::where('user_id', $userId)
                ->where('subscription_id', $planId)
                ->where('is_delete', 0)
                ->first();
            if ($subscriptions) {
                if ($subscriptions->status == 'pending') {
                    throw new Exception('Already this subscription waiting for admin approval');
                }
                if ($subscriptions->status == 'approved') {
                    throw new Exception('Already subscription is available renewable from admin');
                }
                if ($subscriptions->status == 'rejected') {

                    $subscriptions->update([
                        'status' => 'pending',
                        'imageid' => $imageId
                    ]);
                    return  [
                        "id" => $subscriptions->id,
                        "status" => $subscriptions->status ?? "pending"
                    ];
                }
            } else {
                $userSubscription = UserSubscription::create(
                    [
                        'user_id' => $userId,
                        'subscription_id' => $planId,
                        'image_id' => $imageId
                    ]
                );
                return [
                    "id" => $userSubscription->id,
                    "status" => $userSubscription->status ?? "pending"
                ];
            }
        } catch (QueryException $e) {
            throw new Exception('Subscription  Failed :' . $e->errorInfo[2] ?? $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Subscription  Failed :" . $e->getMessage());
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
            $user = UserSubscription::find($id);
            if (!$user) {
                throw new Exception("Subscrition is not found");
            }
            if ($action == "approved") {
                $plan = SubscriptionMaster::find($user->subscription_id);

                if (!$plan) {
                    throw new Exception("Plan not found");
                }
                $startDate = Carbon::now();
                if ($plan->validity == "Years") {
                    $endDate = $startDate->copy()->addYears($plan->duration);
                } elseif ($plan->validity == "Months") {
                    $endDate = $startDate->copy()->addMonths($plan->duration);
                } elseif ($plan->validity == "Days") {
                    $endDate = $startDate->copy()->addDays($plan->duration);
                }
                $user->update([
                    "status" => "approved",
                    "start_date" => $startDate->toDateString(),
                    "renew_date" => $endDate->toDateString(),
                    "end_date" => $endDate->toDateString()
                ]);
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
}
