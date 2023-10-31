<?php

namespace App\Repositories;

use App\Factories\PaymentMethodFactory;
use App\Models\Card;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;

class TransactionRepository {

    const SERVER_URL = 'http://0.0.0.0:8080/transactions/';

    public function create(Transaction $transaction): string|null
    {
        $response = Http::post(
            self::SERVER_URL,
            [
                'value' => number_format($transaction->value, 2, '.', ''),
                'description' => $transaction->description,
                'method' => $transaction->paymentMethod->getName(),
                'cardNumber' => $transaction->card->number,
                'cardHolderName' => $transaction->card->owner,
                'cardExpirationDate' => $transaction->card->expirationDate,
                'cardCvv' => (string) $transaction->card->cvv,
            ]
        );

        return $response->created() ? $response->json('id') : null;
    }

    public function deleteById(string $transactionId): void {
        Http::delete(
            self::SERVER_URL . $transactionId,
        );
    }

    public function getById(string $transactionId): Transaction {
        $response = Http::get(
            self::SERVER_URL . $transactionId,
        );

        if ($response->notFound()) {
            throw new \Exception('Not found');
        }

        $card = new Card();
        $card->number = $response->json('cardNumber');
        $card->owner = $response->json('cardHolderName');
        $card->expirationDate = $response->json('cardExpirationDate');
        $card->cvv = $response->json('cardCvv');

        $transaction = new Transaction();
        $transaction->id = $response->json('id');
        $transaction->value = $response->json('value');
        $transaction->description = $response->json('description');
        $transaction->paymentMethod = PaymentMethodFactory::create($response->json('method'));
        $transaction->card = $card;

        return $transaction;
    }
}
