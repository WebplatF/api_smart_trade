<?php

namespace App\Services;

use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class TradeEntryService
{

    public function create()
    {
        try {
            // DB::transaction()
        } catch (QueryException $e) {
            throw new Exception('Trade entry creation Failed :' . ($e->errorInfo[2] ?? $e->getMessage()));
        } catch (Exception $e) {
            throw new Exception("Trade entry creation Failed :" . $e->getMessage());
        }
    }
}
