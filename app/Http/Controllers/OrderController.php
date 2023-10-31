<?php

namespace App\Http\Controllers;

use App\Factories\PaymentMethodFactory;
use App\Http\Requests\OrderPostRequest;
use App\Http\Requests\SummaryGetRequest;
use App\Models\Card;
use App\Models\Transaction;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{

    public function __construct(private OrderService $service)
    {
    }

    public function index(SummaryGetRequest $request): JsonResponse {
        return response()->json(
            $this->service->getSummary($request->startDate, $request->endDate)
        );
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

        $result = $this->service->createOrder($transaction);

        if ($result) {
            return response()->json(
                array_merge(
                    ["message" => "Order created"],
                    $result
                ), 201);
        }

        return response()->json([
            "message" => "Error creating the order",
        ], 400);
    }
}
