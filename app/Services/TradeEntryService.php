<?php

namespace App\Services;

use Exception;
use Illuminate\Database\QueryException;

class TradeEntryService {

    public function create(){
        try{} catch (QueryException $e) {
            throw new Exception('Payment logs action Failed :' . ($e->errorInfo[2] ?? $e->getMessage()));
        } catch (Exception $e) {
            throw new Exception("Payment logs Failed :" . $e->getMessage());
        }
    }
}