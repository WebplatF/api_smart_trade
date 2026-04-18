<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UsersResources extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name ?? "",
            'email' => $this->email ?? "",
            'mobile' => $this->mobile ?? "",
            'login_ip' => $this->login_ip ?? [],
            'status' => (bool)$this->is_delete ?? false
        ];
    }
}
