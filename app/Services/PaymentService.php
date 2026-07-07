<?php

namespace App\Services;

use App\Models\TransactionMaster;
use App\Models\UserSubscription;
use App\ResponseModel\OrderResponseModel;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\QueryException;

class PaymentService
{
    private SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    public function orderCreate(string $code, int $planId, int $amount, string $tag, int $userId)
    {
        try {
            $paise = (int) round($amount * 100);
            $uniqueId = 'rcpt_' . time() . '_' . bin2hex(random_bytes(8));
            $client = new Client();
            $apikey = config('AppConfig.key_id');
            $secretKey = config('AppConfig.key_secret');
            $userSubscribe = $this->subscriptionService->userSubscription(planId: $planId, imageId: 1, userId: $userId, code: $code);
            if ($userSubscribe) {
                $razorResponse = $client->post(
                    'https://api.razorpay.com/v1/orders',
                    [
                        'auth' => [
                            $apikey,
                            $secretKey
                        ],

                        'headers' => [
                            'Content-Type' => 'application/json'
                        ],

                        'json' => [
                            'amount'   => $paise,
                            'currency' => 'INR',
                            'receipt'  => $uniqueId
                        ]
                    ]
                );
                $statusCode = $razorResponse->getStatusCode();
                $body = json_decode($razorResponse->getBody(), true);
                if ($statusCode == 200 || $statusCode == 201) {
                    $this->updateSubscriptionOrder(subscription: $userSubscribe, orderId: $body['id'] ?? "");
                    $trans = TransactionMaster::create([
                        'user_id' => $userId,
                        'tag' => $tag,
                        'receipt' => $uniqueId,
                        'amount' => $paise,
                        'order_id' => $body['id'] ?? "",
                        'status' => "Order Created"
                    ]);
                    $response = new OrderResponseModel(
                        orderId: $body['id'],
                        amount: $paise,
                        apiKey: $apikey,
                        receipt: $uniqueId,
                        paymentMethod: [
                            'upi' => true,
                            'card' => true,
                            'netbanking' => true,
                        ]
                    );
                    return $response;
                } else {
                    throw new Exception('description' . $body['description'] ?? "" . ',reason' . $body['reason'] ?? "");
                }
            }
        } catch (RequestException $e) {
            $body = json_decode($e->getResponse()->getBody(), true);
            throw new Exception('description' . $body['description'] ?? "" . ',reason' . $body['reason'] ?? "");
        } catch (QueryException $e) {
            throw new Exception("Payment failed: " . $e->errorInfo[2] ?? $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Payment creation failed: " . $e->getMessage());
        }
    }
    public function updatePaymentDetails(array $data)
    {
        try {
            $orderId = $data['payload']['payment']['entity']['order_id'];
            $paymentId    = $data['payload']['payment']['entity']['id'];
            $transaction = TransactionMaster::where('order_id', $orderId)->first();
            if (!$transaction) {
                throw new Exception("Order is not created for this profile.");
            } else {
                if ($transaction->status !== 'Payment Completed') {
                    $transaction->update([
                        'status' => "Payment Completed",
                        'razorypay_order_id' => $orderId,
                        'razorypay_payment_id' => $paymentId
                    ]);
                }
            }
        } catch (QueryException $e) {
            throw new Exception("Payment failed: " . $e->errorInfo[2] ?? $e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
    public function processSubscription(array $data)
    {
        try {
            $orderId = $data['payload']['payment']['entity']['order_id'];
            $paymentId    = $data['payload']['payment']['entity']['id'];
            $transaction = TransactionMaster::where('order_id', $orderId)->first();
            if (!$transaction) {
                throw new Exception("Order is not created for this profile.");
            } else {
                if ($transaction->status !== 'Payment Completed') {
                    $transaction->update([
                        'status' => "Payment Completed",
                        'razorypay_order_id' => $orderId,
                        'razorypay_payment_id' => $paymentId
                    ]);
                } else {
                    $subscription = UserSubscription::where('order_id', $orderId)->where('status','pending')->first();
                    if (!$subscription) {
                        throw new Exception("Invalid order");
                    }
                    $this->subscriptionService->subscriptionAction(id: $subscription->id, action: "approved", reason: "");
                }
            }
        } catch (QueryException $e) {
            throw new Exception("Payment failed: " . $e->errorInfo[2] ?? $e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function updateSubscriptionOrder(array $subscription, string $orderId)
    {
        try {
            $sub = UserSubscription::find($subscription['id']);
            if (!$sub) {
                throw new Exception("Invalid subscription");
            }
            $sub->update([
                'order_id' => $orderId
            ]);
        } catch (QueryException $e) {
            throw new Exception($e->errorInfo[2] ?? $e->getMessage());
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
