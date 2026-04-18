<?php

namespace App\Http\Middleware;

use App\Helper\ResponseHelper;
use Closure;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Guest;
use App\Models\StaffMaster;
use App\Models\UserMaster;

class TokenAuth
{
    /**
     * @param Illuminate\Http\Request $request
     * @param \Closure $next
     * @return Illuminate\Http\Request|\Illuminate\Http\JsonResponse|mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->header('Authorization')) {
            return ResponseHelper::failureResponse(message: "Invalid header or missing authorization", code: 400);
        }

        $token = $request->bearerToken();

        if (!$token) {
            return ResponseHelper::failureResponse(message: "Invalid or missing Bearer token", code: 400);
        }
        try {
            $secretkey = config('AppConfig.jwt_key');
            $decoded = JWT::decode($token, new Key($secretkey, 'HS256'));

            if ($decoded->role == "admin") {
                $user = StaffMaster::where('id', $decoded->user_id)
                    ->first();
            } else {
                $user = UserMaster::where('id', $decoded->user_id)
                    ->where('is_delete', 0)
                    ->first();
            }
            if (!$user) {
                return ResponseHelper::failureResponse('User not found or deleted', 401);
            }
            $request->merge(['user_id' => $user->id, 'role' => $decoded->role]);
        } catch (\Firebase\JWT\ExpiredException $e) {
            return ResponseHelper::failureResponse(
                message: "Unauthoized token",
                code: 401
            );
        } catch (Exception $e) {
            return ResponseHelper::failureResponse(message: "Unauthorized token", code: 401);
        }
        return $next($request);
    }
}
