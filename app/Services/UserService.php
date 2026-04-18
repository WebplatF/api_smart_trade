<?php

namespace App\Services;

use App\Models\StaffMaster;
use App\Models\UserMaster;
use App\Models\UserSubscription;
use App\Models\WatchHistory;
use App\Resources\UsersResources;
use App\Resources\UserSubscriptionResources;
use App\ResponseModel\UserSubscriptionListResponseModel;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;

class UserService
{
    /**
     * @param $request
     * @return UserMaster
     * @throws Exception
     */
    public function userRegister($request): UserMaster
    {
        try {
            $name = $request->get('name');
            $email = $request->get('email');
            $mobile = $request->get('mobile');
            $password = $request->get('password');
            $user = UserMaster::create([
                'name' => $name,
                'email' => $email,
                'mobile' => $mobile,
                'password' => password_hash($password, PASSWORD_BCRYPT)
            ]);
            return $user;
        } catch (QueryException $e) {
            throw new Exception("User Register failed: " . ($e->errorInfo[2] ?? $e->getMessage()));
        } catch (Exception $e) {
            throw new Exception("User Register failed: " . $e->getMessage());
        }
    }
    /**
     * @param $request
     * @return StaffMaster
     * @throws Exception
     */
    public function staffRegister($request): StaffMaster
    {
        try {
            $name = $request->get('name');
            $password = $request->get('password');
            $user = StaffMaster::create([
                'name' => $name,
                'role' => 'admin',
                'password' => password_hash($password, PASSWORD_BCRYPT)
            ]);
            return $user;
        } catch (QueryException $e) {
            throw new Exception("Staff Register failed: " . ($e->errorInfo[2] ?? $e->getMessage()));
        } catch (Exception $e) {
            throw new Exception("Staff Register failed: " . $e->getMessage());
        }
    }
    /**
     * @return UserSubscriptionListResponseModel $userList
     * @throws Exception
     */
    public function userList(): UserSubscriptionListResponseModel
    {
        try {
            $users = UserMaster::paginate(15);
            $userList = UsersResources::collection($users->items())->resolve();
            $response = new UserSubscriptionListResponseModel(
                currentPage: $users->currentPage(),
                totalRecords: $users->total(),
                userList: $userList
            );
            return $response;
        } catch (QueryException $e) {
            throw new Exception("User Register failed: " . ($e->errorInfo[2] ?? $e->getMessage()));
        } catch (Exception $e) {
            throw new Exception("User Register failed: " . $e->getMessage());
        }
    }
    /**
     * @param int $userId
     * @param bool $status
     * @return bool result
     * @throws Exception
     */
    public function statusUpdate(int $userId, bool $status): bool
    {
        try {
            $user = UserMaster::findOrFail($userId);
            if (!$user) {
                throw new Exception('User is not in database');
            }
            $user->update([
                'is_delete' => $status
            ]);
            return true;
        } catch (QueryException $e) {
            throw new Exception('User Failed :' . ($e->errorInfo[2] ?? $e->getMessage()));
        } catch (Exception $e) {
            throw new Exception("User Failed :" . $e->getMessage());
        }
    }
    /**
     * @param int $userId
     * @return array userProfile
     * @throws Exception
     */
    public function userProfile(int $userId): array
    {
        try {
            $user = UserMaster::findOrFail($userId);
            if (!$user) {
                throw new Exception('User is not in database');
            }
            $subscription = UserSubscription::with([
                'user',
                'subscription',
                'image'
            ])->where('user_id', $userId)
                ->where('is_delete', 0)
                ->latest()
                ->first();
            return [
                "profile" => [
                    "id" => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                ],
                "subscription" => !$subscription ? null :
                    new UserSubscriptionResources($subscription)
            ];
        } catch (QueryException $e) {
            throw new Exception('User Profile Failed :' . ($e->errorInfo[2] ?? $e->getMessage()));
        } catch (Exception $e) {
            throw new Exception("User Profile Failed :" . $e->getMessage());
        }
    }
    /**
     * @param int $userId
     * @param int videoId
     * @param int $subscriptionId
     * @return bool $result
     * @throws Exception
     */
    public function userUnlockVideo(int $userId, int $videoId, int $subscriptionId)
    {
        try {
            $user = UserMaster::find($userId);
            if (!$user) {
                throw new Exception("User is not found");
            }
            $subscription = UserSubscription::find($subscriptionId);
            if (!$subscription) {
                throw new Exception('User not have subscription');
            }
            WatchHistory::create([
                'user_id' => $userId,
                'video_id' => $videoId,
                'subscription_id' => $subscriptionId,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            return true;
        } catch (QueryException $e) {
            throw new Exception('Video Unlock Failed :' . ($e->errorInfo[2] ?? $e->getMessage()));
        } catch (Exception $e) {
            throw new Exception("Video Unlock Failed :" . $e->getMessage());
        }
    }
    /**
     * @param int $userId
     * @param int videoId
     * @param int $subscriptionId
     * @param int $duration
     * @param bool $status
     * @return bool $result
     * @throws Exception
     */
    public function updateVideoStram(
        int $userId,
        int $videoId,
        int $subscriptionId,
        int $duration,
        bool $status
    ) {
        try {
            $user = UserMaster::find($userId);
            if (!$user) {
                throw new Exception("User is not found");
            }
            $subscription = UserSubscription::find($subscriptionId);
            if (!$subscription) {
                throw new Exception('User not have subscription');
            }
            $watchHistory = WatchHistory::where('user_id', $userId)
                ->where('video_id', $videoId)
                ->where('subscription_id', $subscriptionId)->first();
            
            $watchHistory->update([
                'last_time_stamp' => $duration,
                'is_watch' => true,
                'is_finshed' => $status,
                'updated_at' => Carbon::now(),
            ]);
            return true;
        } catch (QueryException $e) {
            throw new Exception('Video Stream Update Failed :' . ($e->errorInfo[2] ?? $e->getMessage()));
        } catch (Exception $e) {
            throw new Exception("Video Stream Update Failed :" . $e->getMessage());
        }
    }
}
