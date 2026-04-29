<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try {
            $Validator = Validator::make($request->all(), [
                'email' => 'required|strict_string',
                'password' => 'required|strict_string',
                'login_ip' => 'required|strict_string',
            ]);
            if ($Validator->fails()) {
                return ResponseHelper::failureResponse(message: $Validator->errors()->first(), code: 400);
            }
            $response = $this->authService->login(request: $request)->toArray();
            return ResponseHelper::successResponse(data: $response, message: "User Logined Successfully..!", code: 200);
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage(), code: 400);
        }
    }
    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminLogin(Request $request)
    {
        try {
            $Validator = Validator::make($request->all(), [
                'name' => 'required|strict_string',
                'password' => 'required|strict_string'
            ]);
            if ($Validator->fails()) {
                return ResponseHelper::failureResponse(message: $Validator->errors()->first(), code: 400);
            }
            $response = $this->authService->adminLogin(request: $request)->toArray();
            return ResponseHelper::successResponse(data: $response, message: "User Logined Successfully..!", code: 200);
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage(), code: 400);
        }
    }
    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(Request $request)
    {
        try {
            $Validator = Validator::make($request->all(), [
                'refresh_token' => 'required|strict_string'
            ]);
            if ($Validator->fails()) {
                return ResponseHelper::failureResponse(message: $Validator->errors()->first(), code: 400);
            }
            $refresh = $request->get('refresh_token');
            $response = $this->authService->refreshToken(refreshToken: $refresh)->toArray();
            return ResponseHelper::successResponse(data: $response, message: "Data Fetched", code: 200);
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage(), code: 400);
        }
    }
    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            $Validator = Validator::make($request->all(), [
                'email' => 'required|strict_string',
                'login_ip' => 'required|strict_string',
            ]);
            if ($Validator->fails()) {
                return ResponseHelper::failureResponse(message: $Validator->errors()->first(), code: 400);
            }
            $response = $this->authService->logout(
                email: $request->get('email'),
                loginIp: $request->get('login_ip')
            );
            return ResponseHelper::successResponse(data: [], message: "user logout successfully...!", code: 200);
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage(), code: 400);
        }
    }
    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getForgotOtp(Request $request)
    {
        try {
            $Validator = Validator::make($request->all(), [
                'email' => 'required|strict_string'
            ]);
            if ($Validator->fails()) {
                return ResponseHelper::failureResponse(message: $Validator->errors()->first(), code: 400);
            }
            $response = $this->authService->sendOtp(
                email: $request->get('email'),
            );
            return ResponseHelper::successResponse(data: $response, message: "Otp sent to registered email successfully...!", code: 200);
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage(), code: 400);
        }
    }
    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyOtp(Request $request)
    {
        try {
            $Validator = Validator::make($request->all(), [
                'email' => 'required|strict_string',
                'otp' => 'required|strict_int',
                'trx_id' => 'required|strict_string',
            ]);
            if ($Validator->fails()) {
                return ResponseHelper::failureResponse(message: $Validator->errors()->first(), code: 400);
            }
            $response = $this->authService->verifyOtp(
                email: $request->get('email'),
                txnId: $request->get('trx_id'),
                otp: (int)$request->get('otp')
            );
            return ResponseHelper::successResponse(data: $response, message: "Otp verified successfully...!", code: 200);
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage(), code: 400);
        }
    }
    /**
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function chnagePassword(Request $request)
    {
        try {
            $Validator = Validator::make($request->all(), [
                'email' => 'required|strict_string',
                'password' => 'required|strict_string',
            ]);
            if ($Validator->fails()) {
                return ResponseHelper::failureResponse(message: $Validator->errors()->first(), code: 400);
            }
            $response = $this->authService->changePassword(
                email: $request->get('email'),
                password: $request->get('password')
            );
            return ResponseHelper::successResponse(data: $response, message: "Password changed successfully...!", code: 200);
        } catch (Throwable $e) {
            return ResponseHelper::failureResponse(message: $e->getMessage(), code: 400);
        }
    }
}
