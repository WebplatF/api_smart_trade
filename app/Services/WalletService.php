<?php

namespace App\Services;

use App\Helper\DatabaseErrorHelper;
use App\Models\PaymentLogs;
use App\Models\TradeEntry;
use App\Models\Wallet;
use App\Resources\CalenderMonthResources;
use App\Resources\PaymentLogsResources;
use App\Resources\WalletResources;
use App\Resources\WalletSummaryResources;
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
    /**
     * Get Wallet Summary
     *
     * @param integer $walletId
     * @return array
     */
    public function getWalletSummary(int $walletId)
    {
        try {
            $wallet = Wallet::where('is_delete', 0)->find($walletId);
            if (!$wallet) {
                throw new Exception("User wallet not found");
            }
            // Payment summary
            $paymentSummary = PaymentLogs::where('is_delete', 0)
                ->where('wallet_id', $walletId)
                ->select(
                    DB::raw("SUM(CASE WHEN action = 'DEPOSITE' THEN amount ELSE 0 END) as total_deposit"),
                    DB::raw("SUM(CASE WHEN action = 'WITHDRAWAL' THEN amount ELSE 0 END) as total_withdrawal"),
                    DB::raw("SUM(CASE WHEN action = 'TRADE ENTRY' AND direction = 'Inward' THEN amount ELSE 0 END) as total_profit"),
                    DB::raw("SUM(CASE WHEN action = 'TRADE ENTRY' AND direction = 'Outward' THEN amount ELSE 0 END) as total_loss")
                )
                ->first();
            $totalDeposit = $paymentSummary->total_deposit ?? 0;
            $totalWithdrawal = $paymentSummary->total_withdrawal ?? 0;
            $totalProfit = $paymentSummary->total_profit ?? 0;
            $totalLoss = $paymentSummary->total_loss ?? 0;
            // Trade history
            $tradeHistory = TradeEntry::where('is_delete', 0)
                ->where('wallet_id', $walletId)
                ->get();
            // Last month
            $lastMonth = $tradeHistory
                ->filter(fn($item) => Carbon::parse($item->created_at)->isLastMonth())
                ->groupBy(fn($item) => Carbon::parse($item->date)->format('d-m-Y'))
                ->map(function ($trades, $date) {
                    $amount = $trades->sum(
                        fn($trade) => ($trade->profit ?? 0) - ($trade->loss ?? 0)
                    );
                    return [
                        'date'        => $date,
                        'trade_count' => $trades->count(),
                        'amount'      => (string) $amount,
                        'direction'   => $amount >= 0 ? 'Inward' : 'Outward',
                    ];
                })
                ->values();
            // Available years
            $years = $tradeHistory
                ->pluck('created_at')
                ->map(fn($date) => Carbon::parse($date)->format('Y'))
                ->unique()
                ->sortDesc()
                ->values()
                ->toArray();
            // Available months
            $months = $tradeHistory
                ->pluck('created_at')
                ->map(fn($date) => Carbon::parse($date)->format('M'))
                ->unique()
                ->values()
                ->toArray();
            $data = [
                "wallet" => $wallet->amount ?? "0.00",
                "total_profits" => $totalProfit ?? "0.00",
                "total_loss" => $totalLoss ?? "0.00",
                "total_withdraw" =>  $totalWithdrawal ?? "0.00",
                "total_deposit" =>   $totalDeposit ?? "0.00",
                "years" => $years ?? [],
                "months" => $months ?? [],
                "calender_month" => $lastMonth ?? [],
            ];
            return WalletSummaryResources::make($data);
        } catch (QueryException $e) {
            throw DatabaseErrorHelper::handle(e: $e);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    /**
     * Calender View Summary
     *
     * @param integer $walletId
     * @param string $month
     * @param string $year
     * @return array
     */
    public function getCalenderData(
        int $walletId,
        string $month,
        string $year
    ) {
        try {
            $targetMonth = Carbon::parse("1 {$month} {$year}");
            $tradeHistory = TradeEntry::where('is_delete', 0)
                ->where('wallet_id', $walletId)
                ->get();
            $calendarMonth = $tradeHistory
                ->filter(
                    fn($item) =>
                    Carbon::parse($item->date)->isSameMonth($targetMonth)
                )
                ->groupBy(
                    fn($item) =>
                    Carbon::parse($item->date)->format('d-m-Y')
                )
                ->map(function ($trades, $date) {

                    $amount = $trades->sum(
                        fn($trade) => ($trade->profit ?? 0) - ($trade->loss ?? 0)
                    );

                    return [
                        'date'        => $date,
                        'trade_count' => $trades->count(),
                        'amount'      => (string) $amount,
                        'direction'   => $amount >= 0 ? 'Inward' : 'Outward',
                    ];
                })
                ->values();
            return CalenderMonthResources::collection($calendarMonth)->resolve();
        } catch (QueryException $e) {
            throw DatabaseErrorHelper::handle(e: $e);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    /**
     * Get Chart Summary
     *
     * @param integer $walletId
     * @param string|null $month
     * @param string $year
     * @param string $tag
     * @return array
     */
    public function getChartSummary(
        int $walletId,
        ?string $month,
        string $year,
        string $tag,
    ) {
        try {
            $tradeHistory = TradeEntry::where('wallet_id', $walletId)->get();
            $totalWin = $tradeHistory
                ->where('win_loss', 'WIN')
                ->sum('profit');
            $totalLoss = $tradeHistory
                ->where('win_loss', 'LOSS')
                ->sum('loss');
            $totalTrades = $tradeHistory->count();
            $winTrades = $tradeHistory
                ->where('win_loss', 'WIN')
                ->count();
            $winPercentage = $totalTrades > 0
                ? number_format(($winTrades / $totalTrades) * 100, 2) . '%'
                : '0%';
            $views = match ($tag) {
                'weekly' => $this->getWeekData(
                    walletId: $walletId,
                    month: $month,
                    year: $year
                ),
                'monthly' => $this->getMonthData(
                    walletId: $walletId,
                    year: $year
                ),
                'yearly' => $this->getYearData(
                    walletId: $walletId
                ),
                default => $this->getYearData(
                    walletId: $walletId
                ),
            };
            $da = [
                "total_win" => $totalWin ?? "0.00",
                "total_loss" => $totalLoss ?? "0.00",
                "win_percentage" => $winPercentage ?? "0%",
                "view" => $tag
            ];
            $key = match ($tag) {
                'weekly' => 'calender_month',
                'monthly' => 'calender_year',
                default => 'calender_years',
            };
            $da[$key] = $views ?? [];
            return $da;
        } catch (QueryException $e) {
            throw DatabaseErrorHelper::handle(e: $e);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    /**
     * Weekly Data
     *
     * @param integer $walletId
     * @param string $month
     * @param string $year
     * @return array
     */
    private function getWeekData(int $walletId, string $month, string $year)
    {
        try {
            $targetMonth = Carbon::createFromFormat('m Y', "{$month} {$year}")
                ->startOfMonth();
            $paymentLogs = PaymentLogs::where('is_delete', 0)
                ->where('wallet_id', $walletId)->get();
            $weeklyBalance = $paymentLogs
                ->filter(function ($item) use ($targetMonth) {
                    return Carbon::parse($item->created_at)
                        ->isSameMonth($targetMonth);
                })
                ->groupBy(function ($item) {
                    $day = Carbon::parse($item->created_at)->day;

                    return (int) ceil($day / 7);
                });
            // Always create all weeks
            $weeksInMonth = (int) ceil($targetMonth->daysInMonth / 7);
            $weeklyBalance = collect(range(1, $weeksInMonth))
                ->map(function ($weekNumber) use ($weeklyBalance, $targetMonth) {
                    $logs = $weeklyBalance->get($weekNumber, collect());
                    $amount = $logs->sum(function ($log) {
                        return $log->direction === 'Inward'
                            ? (float) $log->amount
                            : -(float) $log->amount;
                    });
                    $lastLog = $logs->sortBy('created_at')->last();
                    return [
                        'week' => $weekNumber,
                        // 'amount' => number_format($amount, 2, '.', ''),
                        'amount' => $lastLog->balance ?? '0.00',
                    ];
                })
                ->values();
            return $weeklyBalance;
        } catch (QueryException $e) {
            throw DatabaseErrorHelper::handle(e: $e);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    /**
     * Month Data
     *
     * @param integer $walletId
     * @param string $year
     * @return array
     */
    private function getMonthData(int $walletId, string $year)
    {
        try {
            $paymentLogs = PaymentLogs::where('is_delete', 0)
                ->where('wallet_id', $walletId)->get();
            $monthlyBalance = $paymentLogs
                ->filter(function ($item) use ($year) {
                    return Carbon::parse($item->created_at)->year == $year;
                })
                ->groupBy(function ($item) {
                    return Carbon::parse($item->created_at)->month;
                });
            $monthlyBalance = collect(range(1, 12))
                ->map(function ($monthNumber) use ($monthlyBalance, $year) {
                    $logs = $monthlyBalance->get($monthNumber, collect());
                    $amount = $logs->sum(function ($log) {
                        return $log->direction === 'Inward'
                            ? (float) $log->amount
                            : -(float) $log->amount;
                    });
                    $lastLog = $logs
                        ->sortBy('created_at')
                        ->last();
                    return [
                        'month' => $monthNumber,
                        'month_name' => Carbon::create($year, $monthNumber, 1)->format('M'),
                        // 'amount' => number_format($amount, 2, '.', ''),
                        'amount' => $lastLog->balance ?? '0.00',
                    ];
                })
                ->values();
            return $monthlyBalance;
        } catch (QueryException $e) {
            throw DatabaseErrorHelper::handle(e: $e);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    /**
     * Month Data
     *
     * @param integer $walletId
     * @return array
     */
    private function getyearData(int $walletId)
    {
        try {
            $paymentLogs = PaymentLogs::where('is_delete', 0)
                ->where('wallet_id', $walletId)->get();
            $yearlyBalance = $paymentLogs
                ->groupBy(fn($item) => Carbon::parse($item->created_at)->year)
                ->sortKeys()
                ->values()
                ->map(function ($logs, $index) {
                    $logs = $logs->sortBy('created_at');
                    $year = Carbon::parse($logs->first()->created_at)->year;
                    $lastLog = $logs->last();
                    $amount = $logs->sum(function ($log) {
                        return $log->direction === 'Inward'
                            ? (float) $log->amount
                            : -(float) $log->amount;
                    });

                    return [
                        // 'year' => $index + 1,
                        'year' => (string) $year,
                        // 'amount' => number_format($amount, 2, '.', ''),
                        'amount' => number_format((float) ($lastLog->balance ?? 0), 2, '.', ''),
                    ];
                })
                ->values();
            return $yearlyBalance;
        } catch (QueryException $e) {
            throw DatabaseErrorHelper::handle(e: $e);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
