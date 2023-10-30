<?php

namespace App\Services;

use App\Models\Transaction;
use App\Repositories\PayableRepository;
use App\Repositories\TransactionRepository;

class OrderService {

    public function __construct(
        private TransactionRepository $transactionRepository,
        private PayableRepository $payableRepository,
    )
    {
    }

    public function createOrder(Transaction $transaction): array|null {
        $transactionId = $this->transactionRepository->create($transaction);
        if (!$transactionId) {
            return null;
        }
        $payableId = $this->payableRepository->create(
            $transaction->paymentMethod,
            $transaction->value
        );

        if (!$payableId) {
            $this->transactionRepository->deleteById($transactionId);
            return null;
        }

        return [
            'transactionId' => $transactionId,
            'payableId' => $payableId,
        ];
    }
}
