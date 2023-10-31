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

    public function getSummary(\DateTime $startDate, \DateTime $endDate): array {
        $payables = $this->payableRepository->getAll();
        $totalToGetPaid = $totalFees = $totalPaid = 0;
        foreach ($payables as $payable) {
            if ($this->isDateValid($payable->createDate, $startDate, $endDate)) {
                if ($payable->status === 'paid') {
                    $totalFees += $payable->discount;
                    $totalPaid += $payable->total;
                } else {
                    $totalToGetPaid += $payable->total;
                }
            }
        }

        return [
            'totalFees' => $totalFees,
            'totalToGetPaid' => $totalToGetPaid,
            'totalPaid' => $totalPaid,
        ];
    }

    private function isDateValid(\DateTime $date, \DateTime $startDate, \DateTime $endDate): bool {
        return $date >= $startDate && $date <= $endDate;
    }
}
