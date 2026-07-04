<?php

namespace App\RequestModel;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CouponStatusUpdateModel
{
    public function __construct(
        public int $id,
        public bool $status
    ) {
        $this->id = $id;
        $this->status= $status;
    }

    public static function fromRequest(Request $request): self
    {
        $validate = Validator::make($request->all(), [
            'id' => 'required|strict_int',
            'status' => 'required|strict_bool',
        ]);
        if ($validate->fails()) {
            throw new Exception($validate->errors()->first());
        }
        return self::fromArray($validate->validate());
    }
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            status: $data['status']
        );
    }
}
