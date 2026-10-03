<?php

namespace App\Http\Requests\Admin;

class UpdateSubscriptionRequest extends AdminFormRequest
{
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'next_payment_date' => ['nullable', 'date'],
        ];
    }
}