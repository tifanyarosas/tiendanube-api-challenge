<?php

namespace App\Http\Requests;

use App\Models\CreditCardPayment;
use App\Models\DebitCardPayment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderPostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'value' => ['required', 'decimal:2'],
            'description' => ['required', 'string', 'max:255'],
            'paymentMethod' => ['required', Rule::in([DebitCardPayment::DEBIT_CARD, CreditCardPayment::CREDIT_CARD])],
            'cardNumber' => ['required', 'digits:4'],
            'cardHolderName' => 'required',
            'cardExpirationDate' => ['required'],
            'cardCvv' => ['required', 'digits:3'],
        ];
    }
}
