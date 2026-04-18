<?php

namespace App\ResponseModel;

class LoginResponseModel
{
    public string $accessToken;
    public string $refreshToken;
    public array $userDetails;

    public function __construct(string $accessToken, string $refreshToken, array $userDetails)
    {
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->userDetails = $userDetails;
    }

    public function toArray(): array
    {
        return [
            "access_token" => $this->accessToken,
            "refresh_token" => $this->refreshToken,
            "user_details" => $this->userDetails
        ];
    }
}
