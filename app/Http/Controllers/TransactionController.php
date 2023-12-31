<?php

namespace App\Http\Controllers;

use App\Factories\PaymentMethodFactory;
use App\Http\Requests\TransactionCreationRequest;
use App\Http\Requests\SummaryGetRequest;
use App\Models\Card;
use App\Models\Transaction;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class TransactionController extends Controller
{

    public function __construct(private TransactionService $service)
    {
    }

    public function index(SummaryGetRequest $request): JsonResponse {
        return response()->json(
            $this->service->getSummary(Carbon::parse($request->startDate), Carbon::parse($request->endDate))
        );
    }

    public function create(TransactionCreationRequest $request): JsonResponse {
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
                    ["message" => "Transaction created"],
                    $result
                ), 201);
        }

        return response()->json([
            "message" => "Error creating the transaction",
        ], 400);
    }
}
