<?php
namespace App\Services;

use App\Models\TransactionMaster;
use App\ResponseModels\OrderResponseModel;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\QueryException;

class PaymentService{
    
    public function orderCreate(int $amount, string $tag,int $userId): OrderResponseModel
    {
        try {
            $paise = (int) round($amount * 100);
            $uniqueId = 'rcpt_' . time() . '_' . bin2hex(random_bytes(8));
            $client = new Client();
            $apikey = config('AppConfig.key_id');
            $secretKey = config('AppConfig.key_secret');
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
        } catch (RequestException $e) {
            $body = json_decode($e->getResponse()->getBody(), true);
            throw new Exception('description' . $body['description'] ?? "" . ',reason' . $body['reason'] ?? "");
        } catch (QueryException $e) {
            throw new Exception("Payment creation failed: " . $e->errorInfo[2] ?? $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Payment creation failed: " . $e->getMessage());
        }
    }
}