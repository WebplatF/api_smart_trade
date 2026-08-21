<?php

namespace App\Services;

use App\Models\PaymentLogs;
use App\Models\Wallet;
use App\Resources\PaymentLogsResources;
use App\Resources\WalletResources;
use App\ResponseModel\CommonListResponseModel;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class WalletService
{
    /**
     * Get User Wallet
     * @param int $userId
     * @return array
     */
    public function getWallet(int $userId): array
    {
        try {
            $wallet = Wallet::where('user_id', $userId)
                ->where('is_delete', 0)
                ->first();
            if (!$wallet) {
                return [];
            }
            $data = WalletResources::make($wallet);
            return $data->resolve();
        } catch (QueryException $e) {
            throw new Exception('Wallet create Failed :' . ($e->errorInfo[2] ?? $e->getMessage()));
        } catch (Exception $e) {
            throw new Exception("Wallet create Failed :" . $e->getMessage());
        }
    }
    /**
     * Get user Payment Logs
     * @param int $walletId
     * @return CommonListResponseModel
     */
    public function getPaymentLogs(int $walletId): CommonListResponseModel
    {
        try {
            $logs = PaymentLogs::where('wallet_id', $walletId)->paginate(15);
            $history = PaymentLogsResources::collection($logs)->resolve();
            return new CommonListResponseModel(
                totalRecords: $logs->total(),
                currentPage: $logs->currentPage(),
                dataList: $history
            );
        } catch (QueryException $e) {
            throw new Exception('Payment Logs Failed :' . ($e->errorInfo[2] ?? $e->getMessage()));
        } catch (Exception $e) {
            throw new Exception("Payment Logs Failed :" . $e->getMessage());
        }
    }
    /**
     * Wallet Creation
     *
     * @return object
     */
    public function walleteCreation(int $userId, string $date, float $amount)
    {
        try {
            return DB::transaction(function () use ($userId, $date, $amount) {
                $wallet = Wallet::where('user_id', $userId)
                    ->where('is_delete', 0)
                    ->first();
                if (!$wallet) {
                    $timestamp = Carbon::parse($date)->startOfDay();
                    $walletAmount = 0.00;
                    if ($amount > 0) {
                        $walletAmount = $amount;
                    } elseif ($amount < 0) {
                        throw new Exception("Amount cannot be negative.");
                    }
                    $userWallet = Wallet::create([
                        'user_id' => $userId,
                        'amount' => $walletAmount,
                        'wallet_create_date' => $timestamp,
                    ]);
                    if ($walletAmount > 0.00) {
                        $this->PaymentLogsActions(
                            amount: $amount,
                            balance: $userWallet->amount,
                            walletId: $userWallet->id,
                            action: "DEPOSIT",
                            tradeId: 0,
                            direction: "Inward",
                            description: "Amount Deposited",
                            createdDate: $timestamp->toDateString()
                        );
                    }
                    $data = WalletResources::make($userWallet);
                    return $data->resolve();
                } else {
                    throw new Exception("User have wallet already");
                }
            });
        } catch (QueryException $e) {
            throw new Exception('Wallet creation Failed :' . ($e->errorInfo[2] ?? $e->getMessage()));
        } catch (Exception $e) {
            throw new Exception("Wallet creation Failed :" . $e->getMessage());
        }
    }
    /**
     * Wallet Actions
     *
     * @return object
     */
    public function walleteAction(
        int $userId,
        string $action,
        float $amount,
        string $date,
        bool $isLog = true
    ) {
        try {
            $wallet = match ($action) {
                'deposite' => $this->deposite(
                    userId: $userId,
                    amount: $amount,
                    date: $date,
                    isLog: $isLog
                ),
                'withdraw' => $this->widthdraw(
                    userId: $userId,
                    amount: $amount,
                    date: $date,
                    isLog: $isLog
                ),
                default => throw new Exception("Invalid actions"),
            };
            return "";
        } catch (QueryException $e) {
            throw new Exception('Wallet action Failed :' . ($e->errorInfo[2] ?? $e->getMessage()));
        } catch (Exception $e) {
            throw new Exception("Wallet action Failed :" . $e->getMessage());
        }
    }
    /**
     * Payment Logs Actions
     *
     * @param float $amount
     * @param float $balance
     * @param integer $walletId
     * @param string $action
     * @param string $direction
     * @param int $tradeId
     * @param string $description 
     * @param string $createdDate
     * @return object
     */
    public function PaymentLogsActions(
        float $amount,
        float $balance,
        int $walletId,
        string $action,
        string $description,
        int $tradeId,
        string $direction,
        string $createdDate
    ): object {
        try {
            return DB::transaction(function () use (
                $amount,
                $balance,
                $walletId,
                $action,
                $direction,
                $tradeId,
                $description,
                $createdDate
            ) {
                $timestamp = Carbon::parse($createdDate)->startOfDay();
                $log = PaymentLogs::create([
                    'wallet_id' => $walletId,
                    'description' => $description ?? "",
                    'amount' => $amount,
                    'action' => $action,
                    'direction' => $direction,
                    'trade_id' => $tradeId ?? 0,
                    'balance' => $balance,
                    'created_at' => $timestamp
                ]);
                return $log;
            });
        } catch (QueryException $e) {
            throw new Exception('Payment logs action Failed :' . ($e->errorInfo[2] ?? $e->getMessage()));
        } catch (Exception $e) {
            throw new Exception("Payment logs Failed :" . $e->getMessage());
        }
    }

    private function deposite(
        int $userId,
        float $amount,
        string $date,
        bool $isLog = true
    ) {
        try {
            if ($amount <= 0) {
                throw new Exception("Amount must be greater than zero.");
            }
            return DB::transaction(function () use ($userId, $amount, $isLog, $date) {
                $wallet = Wallet::where('user_id', $userId)
                    ->where('is_delete', 0)
                    ->first();
                if (!$wallet) {
                    throw new Exception("User wallet not found");
                }
                $wallet->lockForUpdate();
                $wallet->update([
                    'amount' => bcadd($wallet->amount, $amount, 2),
                ]);
                if ($isLog) {
                    $timestamp = Carbon::parse($date)->startOfDay();
                    $history = $this->PaymentLogsActions(
                        amount: $amount,
                        balance: $wallet->amount,
                        walletId: $wallet->id,
                        action: "DEPOSIT",
                        tradeId: 0,
                        direction: "Inward",
                        description: "Amount Deposited to account",
                        createdDate: $timestamp
                    );
                    // return (object)[
                    //     'id' => $history->id,
                    //     'amount' => $history->amount,
                    //     'balance' => $history->balance,
                    //     'action' => $history->action,
                    //     'description' => $history->description
                    // ];
                } else {
                    // return [];
                }
            });
        } catch (QueryException $e) {
            throw new Exception('Wallet deposite Failed :' . ($e->errorInfo[2] ?? $e->getMessage()));
        } catch (Exception $e) {
            throw new Exception("Wallet deposite Failed :" . $e->getMessage());
        }
    }

    private function widthdraw(
        int $userId,
        float $amount,
        string $date,
        bool $isLog = true
    ) {
        try {
            if ($amount <= 0) {
                throw new Exception("Amount must be greater than zero.");
            }
            return DB::transaction(function () use ($userId, $amount, $isLog, $date) {
                $wallet = Wallet::where('user_id', $userId)
                    ->where('is_delete', 0)
                    ->first();
                if (!$wallet) {
                    throw new Exception("User wallet not found");
                }
                $wallet->lockForUpdate();
                $wallet->update([
                    'amount' => bcsub($wallet->amount, $amount, 2),
                ]);
                if ($isLog) {
                    $timestamp = Carbon::parse($date)->startOfDay();
                    $history = $this->PaymentLogsActions(
                        amount: $amount,
                        balance: $wallet->amount,
                        walletId: $wallet->id,
                        action: "WITHDRAW",
                        tradeId: 0,
                        direction: "Outward",
                        description: "Amount widthdraw from account",
                        createdDate: $timestamp
                    );
                    // return (object)[
                    //     'id' => $history->id,
                    //     'amount' => $history->amount,
                    //     'balance' => $history->balance,
                    //     'action' => $history->action,
                    //     'description' => $history->description
                    // ];
                } else {
                    // return [];
                }
            });
        } catch (QueryException $e) {
            throw new Exception(($e->errorInfo[2] ?? $e->getMessage()));
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
