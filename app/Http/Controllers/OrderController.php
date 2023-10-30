<?php

namespace App\Http\Controllers;

use App\Factories\PaymentMethodFactory;
use App\Http\Requests\OrderPostRequest;
use App\Models\Card;
use App\Models\Transaction;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{

    public function __construct(private OrderService $service)
    {
    }

    public function index(): JsonResponse {
        return response()->json([
            "message" => "Hola created"
        ], 200);
    }

    public function create(OrderPostRequest $request): JsonResponse {
        $card = new Card();
        $card->number = $request->cardNumber;
        $card->owner = $request->cardHolderName;
        $card->expirationDate = $request->cardExpirationDate;
        $card->cvv = $request->cardCvv;

        $transaction = new Transaction();
        $transaction->value = $request->value;
        $transaction->description = $request->description;
        $transaction->card = $card;
        $transaction->paymentMethod = PaymentMethodFactory::create($request->paymentMethod);

        $response = $this->service->createOrder($transaction);

        if ($response['status'] === 201) {
            return response()->json([
                "message" => "Order created",
                "data" => $response,
            ], 201);
        }

        return response()->json([
            "message" => "Error",
            "data" => $response,
        ], $response['status']);
    }
}
