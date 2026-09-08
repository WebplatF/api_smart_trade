<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WalletSummaryResources extends JsonResource
{

    public function toArray($request)
    {
        return [
            "balance" => [
                "wallet" => $this->wallet ?? "0.00",
                "total_profits" => $this->totoal_profits ?? "0.00",
                "total_loss" => $this->total_loss ?? "0.00",
                "total_withdraw" =>  $this->total_withdraw ?? "0.00",
                "total_deposit" =>   $this->total_deposit ?? "0.00"
            ],
            "years" => $this->years ?? [],
            "months" => $this->months ?? [],
            "calender_month" => CalenderMonthResources::collection($this->calender_month),
        ];
    }
}
