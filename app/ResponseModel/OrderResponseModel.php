<?php

namespace App\ResponseModels;

class OrderResponseModel
{
    public string $apiKey;
    public string $orderId;
    public string $amount;
    public string $receipt;
    public array $paymentMethod;

    public function __construct(
        string $apiKey,
        string $orderId,
        string $amount,
        string $receipt,
        array $paymentMethod
    ) {
        $this->apiKey =  $apiKey;
        $this->orderId =  $orderId;
        $this->amount =  $amount;
        $this->receipt =  $receipt;
        $this->paymentMethod =  $paymentMethod;
    }

    public function toArray(): array
    {
        return [
            'apiKey' => $this->apiKey,
            'orderId' => $this->orderId,
            'amount' => $this->amount,
            'receipt' => $this->receipt,
            'paymentMethod' => $this->paymentMethod
        ];
    }
}
