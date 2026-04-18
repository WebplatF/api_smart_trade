<?php

namespace App\Services;

use App\Models\OtpMaster;
use App\Models\StaffMaster;
use App\Models\UserMaster;
use App\ResponseModel\LoginResponseModel;
use App\Traits\CommonTraits;
use Carbon\Carbon;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthService
{
    use CommonTraits;

    /**
     * @param  $request
     * @param \App\ResponseModel\LoginResponseModel
     * @throws Exception 
     */
    public function login($request): LoginResponseModel
    {
        try {
            $email  = $request->get('email');
            $password  = $request->get('password');
            $loginIp  = $request->get('login_ip');
            $user = $this->getUser(
                email: $email,
                password: $password,
                loginIp: $loginIp
            );
            $accessToken = $this->generateAccessToken(
                userId: $user->id,
                name: $user->name,
                role: 'user'
            );
            $refreshToken = $this->generateRefreshToken(
                userId: $user->id,
                name: $user->name,
                role: 'user'
            );
            $response = new LoginResponseModel(
                accessToken: $accessToken,
                refreshToken: $refreshToken,
                userDetails: [
                    'id' => $user->id,
                    'name' => $user->name ?? "",
                    'email' => $user->email ?? ""
                ]
            );
            return $response;
        } catch (QueryException $e) {
            throw new Exception("Login failed: " . $e->errorInfo[2] ?? $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Login failed: " . $e->getMessage());
        }
    }
    /**
     * @param  $request
     * @param \App\ResponseModel\LoginResponseModel
     * @throws Exception 
     */
    public function adminLogin($request): LoginResponseModel
    {
        try {
            $name  = $request->get('name');
            $password  = $request->get('password');
            $user = $this->getAdmin(
                name: $name,
                password: $password,
            );
            $accessToken = $this->generateAccessToken(
                userId: $user->id,
                name: $user->name,
                role: $user->role ?? "admin"
            );
            $refreshToken = $this->generateRefreshToken(
                userId: $user->id,
                name: $user->name,
                role: $user->role ?? "admin"
            );
            $response = new LoginResponseModel(
                accessToken: $accessToken,
                refreshToken: $refreshToken,
                userDetails: [
                    'id' => $user->id,
                    'name' => $user->name ?? "",
                    'email' => $user->email ?? ""
                ]
            );
            return $response;
        } catch (QueryException $e) {
            throw new Exception("Login failed: " . $e->errorInfo[2] ?? $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Login failed: " . $e->getMessage());
        }
    }
    /**
     * @param string $loginIp
     * @param string $email
     * @param string $password
     * @return App\Models\UserMaster
     * @throws Exception
     */
    public function getUser(
        string $email,
        string $password,
        string $loginIp
    ): UserMaster {
        try {
            $user = UserMaster::where('email', $email)->first();
            if (!$user) {
                throw new Exception('User account not found');
            }
            if (!Hash::check($password, $user->password)) {
                throw new Exception('email or password is incorrect');
            }
            $ips = $user->login_ip ?? [];
            // Normalize IP
            $loginIp = trim($loginIp);
            // Prevent duplicate IP
            if (!in_array($loginIp, $ips, true)) {
                // Allow only 2 unique IPs
                if (count($ips) >= 2) {
                    throw new Exception('Login limit exceeded. Only 2 devices allowed');
                }
                $ips[] = $loginIp;
            }
            $user->update([
                'last_login' => Carbon::now()->format('Y-m-d H:i:s'),
                'login_ip' => $ips
            ]);
            return $user;
        } catch (QueryException $e) {
            throw new Exception($e->errorInfo[2] ?? $e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    /**
     * @param string $email
     * @param string $password
     * @return App\Models\StaffMaster
     * @throws Exception
     */
    public function getAdmin(
        string $name,
        string $password
    ): StaffMaster {
        try {
            $user = StaffMaster::where('name', $name)->first();
            if (!$user) {
                throw new Exception('Admin account not found');
            }
            if (!Hash::check($password, $user->password)) {
                throw new Exception('email or password is incorrect');
            }
            return $user;
        } catch (QueryException $e) {
            throw new Exception($e->errorInfo[2] ?? $e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    /**
     * @param string $email
     * @param string $loginIp
     * @return App\Models\UserMaster
     * @throws Exception
     */
    public function logout(string $email, string $loginIp): bool
    {
        try {
            $user = UserMaster::where('email', $email)->first();

            if (!$user || empty($user->login_ip)) {
                throw new Exception('something went wrong...!');
            }
            $loginIp = trim($loginIp);
            $ips = $user->login_ip;
            if (!in_array($loginIp, $ips, true)) {
                throw new Exception('Session not found for this device');
            }

            $ips = array_values(
                array_filter($user->login_ip, fn($ip) => $ip !== $loginIp)
            );

            $user->update([
                'login_ip' => empty($ips) ? null : $ips
            ]);
            return true;
        } catch (QueryException $e) {
            throw new Exception("Logout failed: " . $e->errorInfo[2] ?? $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Logout failed: " . $e->getMessage());
        }
    }
    /**
     * @param string $refreshToken
     * @return LoginResponseModel
     * @throws Exception
     */
    public function refreshToken($refreshToken): LoginResponseModel
    {
        try {
            $secretkey = config('AppConfig.jwt_key');
            $decoded = JWT::decode($refreshToken, new Key($secretkey, 'HS256'));
            if ($decoded->type != 'refresh') {
                throw new Exception('Unauthorized token');
            }
            $user = UserMaster::where('id', $decoded->user_id)
                ->where('is_delete', 0)
                ->first();
            if (!$user) {
                throw new Exception('User account not found');
            }
            $accessToken = $this->generateAccessToken(
                userId: $user->id,
                name: $user->first_name,
                role: $user->role
            );
            $refreshToken = $this->generateRefreshToken(
                userId: $user->id,
                name: $user->first_name,
                role: $user->role
            );
            $loginResponse = new LoginResponseModel(
                accessToken: $accessToken,
                refreshToken: $refreshToken,
                userDetails: [
                    'id' => $user->id,
                    'name' => $user->name ?? "",
                    'email' => $user->email ?? ""
                ]
            );
            return $loginResponse;
        } catch (QueryException $e) {
            throw new Exception($e->errorInfo[2] ?? $e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    /**
     * @param string $email
     * @return array transactionId
     * @throws Exception
     */
    public function sendOtp(string $email)
    {
        try {
            $user = UserMaster::where('email', $email)->first();
            if (!$user) {
                throw new Exception("This mail is not register.");
            }
            $otp = rand(100000, 999999);
            $txnId = Str::uuid();
            OtpMaster::create([
                'user_id' => $user->id,
                'trx_id' => $txnId,
                'otp' => $otp
            ]);
            Mail::send('otp', [
                'name' => $user->name,
                'otp' => $otp,
            ], function ($message) use ($email) {
                $message->to($email)
                    ->subject('Password Reset OTP');
            });
            return [
                'trx_id' => $txnId
            ];
        } catch (QueryException $e) {
            throw new Exception($e->errorInfo[2] ?? $e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    /**
     * @param string $email
     * @param string $txnId
     * @param int $otp
     * @return bool 
     * @throws Exception
     */
    public function verifyOtp(string $email, string $txnId, int $otp)
    {
        try {
            $user = UserMaster::where('email', $email)->first();
            if (!$user) {
                throw new Exception("This mail is not register.");
            }
            $otpRow =   OtpMaster::where('trx_id', $txnId)->where('is_delete', 0)->fist();
            if (!$otpRow) {
                throw new Exception("This OTP is invalid.");
            }
            if ($otpRow->otp != $otp) {
                throw new Exception("This OTP is invalid.");
            }
            $otpRow->update(['is_delete' => true]);
            return true;
        } catch (QueryException $e) {
            throw new Exception($e->errorInfo[2] ?? $e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    /**
     * @param string $email
     * @param string $password
     * @return bool 
     * @throws Exception
     */
    public function changePassword(string $email, string $password)
    {
        try {
            $user = UserMaster::where('email', $email)->first();
            if (!$user) {
                throw new Exception("This mail is not register.");
            }
            $user->update([
                'password' => password_hash($password, PASSWORD_BCRYPT)
            ]);
            return $user;
        } catch (QueryException $e) {
            throw new Exception($e->errorInfo[2] ?? $e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
