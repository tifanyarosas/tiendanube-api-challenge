<?php

namespace App\Services;

use App\Models\Transaction;
use App\Repositories\PayableRepository;
use App\Repositories\TransactionRepository;
use Illuminate\Support\Collection;

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

    /*
        Valor total pago de cuentas por cobrar
        Total cobrado de tasas en los pagos
        Valor de futuros ingresos
    */
    public function getSummary(\DateTime $startDate, \DateTime $endDate): array {
        $payables = $this->payableRepository->getAll();
        $totalToGetPaid = $totalFees = $totalPaid = 0;
        foreach ($payables as $payable) {
            if ($this->isDateValid($payable->creationDate, $startDate, $endDate)) {
                if ($payable->status === 'paid') {
                    $totalFees += $payable->discount;
                } else {
                    $totalToGetPaid += $payable->total;
                }
            }
        }

        return [
            'fees' => $totalFees,
            'totalToGetPaid' => $totalToGetPaid,
        ];
    }

    private function isDateValid(\DateTime $date, \DateTime $startDate, \DateTime $endDate): bool {

    }
}
