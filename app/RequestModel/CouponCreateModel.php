<?php

namespace App\RequestModel;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CouponCreateModel
{
    public function __construct(
        public string $code,
        public string $discountType,
        public string $value
    ) {
        $this->code = $code;
        $this->discountType = $discountType;
        $this->value = $value;
    }

    public static function fromRequest(Request $request): self
    {
        $validate = Validator::make($request->all(), [
            'code' => 'required|strict_string',
            'discount_type' => 'required|strict_string',
            'value' => 'required|strict_string',
        ]);
        if ($validate->fails()) {
            throw new Exception($validate->errors()->first());
        }
        return self::fromArray($validate->validate());
    }
    public static function fromArray(array $data): self
    {
        return new self(
            code: $data['code'],
            discountType: $data['discount_type'],
            value: $data['value'],
        );
    }
}
