<?php

namespace App\Traits;

use Exception;
use Firebase\JWT\JWT;

trait CommonTraits
{
    /**
     * @param string $userId
     * @param string $name 
     * @param string $role 
     * @return string
     * @throws Exception
     */
    public  function generateAccessToken(string $userId, string $name, string $role): string
    {
        try {
            $payload = [
                'user_id' => $userId,
                'name' => $name,
                'role' => $role,
                'iat' => time(),
                'exp' => time() + 43200
            ];
            $secretkey = config('AppConfig.jwt_key');
            return JWT::encode($payload, $secretkey, 'HS256');
        } catch (Exception $e) {
            throw new Exception("Access JWT generation failed: " . $e->getMessage());
        }
    }

    /**
     * @param string $userId
     * @param string $name 
     * @return string
     * @throws Exception
     */
    public  function generateRefreshToken(string $userId, string $name, string $role): string
    {
        try {
            $payload = [
                'user_id' => $userId,
                'name' => $name,
                'type' => 'refresh',
                'role' => $role,
                'iat' => time(),
                'exp' => time() + 45000
            ];
            $secretkey = config('AppConfig.jwt_key');
            return JWT::encode($payload, $secretkey, 'HS256');
        } catch (Exception $e) {
            throw new Exception("Refresh JWT generation failed: " . $e->getMessage());
        }
    }
    /**
     * @param string $path
     * @param int $ttl 
     * @return string
     */
    function signCdnUrl(string $path, int $ttl = 120): string
    {
        $cdn = 'https://cdn.smarttradeind.com';
        $secret = 'kgrbIyzna4OejRYJOeZWsidKW14gZz4HJeHnno9t';

        // remove extra slash
        $path = ltrim($path, '/');

        // encode safely
        $encodedPath = implode('/', array_map(
            'rawurlencode',
            explode('/', $path)
        ));

        $expires = time() + $ttl;

        // SAME FORMAT AS WORKER
        $data = '/' . $encodedPath . $expires;

        $token = hash_hmac('sha256', $data, $secret);

        return "{$cdn}/{$encodedPath}?token={$token}&expires={$expires}";
    }
}
